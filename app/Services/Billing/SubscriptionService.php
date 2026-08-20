<?php

namespace App\Services\Billing;

use App\Mail\SubscriptionReminderMail;
use App\Models\ManualPayment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SubscriptionService
{
    public function start(Tenant $tenant, Plan $plan, string $interval = Subscription::INTERVAL_MONTHLY, ?Carbon $from = null): Subscription
    {
        $from ??= now();
        $ends = $interval === Subscription::INTERVAL_YEARLY ? $from->copy()->addYear() : $from->copy()->addMonth();

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'interval' => $interval,
            'status' => Tenant::STATUS_ACTIVE,
            'starts_at' => $from,
            'ends_at' => $ends,
            'grace_ends_at' => $ends->copy()->addDays(config('saas.grace_days')),
            'readonly_ends_at' => $ends->copy()->addDays(config('saas.grace_days') + config('saas.readonly_days')),
        ]);

        $tenant->update([
            'plan_id' => $plan->id,
            'status' => $tenant->status === Tenant::STATUS_PENDING
                ? Tenant::STATUS_PENDING
                : Tenant::STATUS_ACTIVE,
        ]);

        $this->record($tenant, $subscription, 'started', 'Suscripción iniciada en plan '.$plan->name);

        return $subscription;
    }

    public function registerPayment(Tenant $tenant, array $data, ?int $staffId = null): ManualPayment
    {
        $subscription = $tenant->subscription;
        $plan = $tenant->plan;
        $interval = $data['interval'] ?? $subscription?->interval ?? Subscription::INTERVAL_MONTHLY;

        $payment = ManualPayment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription?->id,
            'platform_user_id' => $staffId,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'paid_at' => $data['paid_at'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'attachment_path' => $data['attachment_path'] ?? null,
        ]);

        if ($plan) {
            $from = ($subscription && $subscription->ends_at && $subscription->ends_at->isFuture())
                ? $subscription->ends_at
                : now();
            $this->start($tenant, $plan, $interval, $from);
        } else {
            $tenant->update(['status' => Tenant::STATUS_ACTIVE]);
        }

        $this->record($tenant, $tenant->fresh()->subscription, 'payment', 'Pago '.$payment->method.' de Gs. '.number_format($payment->amount, 0, ',', '.'));

        return $payment;
    }

    public function tick(?Carbon $now = null): void
    {
        $now ??= now();

        Tenant::with(['subscription', 'plan'])->whereNotIn('status', [Tenant::STATUS_CANCELLED, Tenant::STATUS_PENDING])->each(function (Tenant $tenant) use ($now) {
            $this->tickTenant($tenant, $now);
        });
    }

    public function tickTenant(Tenant $tenant, Carbon $now): void
    {
        $subscription = $tenant->subscription;
        if (! $subscription || ! $subscription->ends_at) {
            return;
        }

        $reminderAt = $subscription->ends_at->copy()->subDays(config('saas.reminder_days_before'));
        if ($now->gte($reminderAt) && $now->lt($subscription->ends_at) && ! $subscription->reminded_at) {
            $this->remind($tenant, $subscription);
        }

        $newStatus = $this->statusFor($subscription, $now);
        if ($newStatus !== $tenant->status) {
            $tenant->update(['status' => $newStatus]);
            $subscription->update(['status' => $newStatus]);
            $this->record($tenant, $subscription, 'status', 'Estado actualizado a '.$newStatus);
        }
    }

    public function suspend(Tenant $tenant, string $reason = 'Suspensión manual'): void
    {
        $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);
        $tenant->subscription?->update(['status' => Tenant::STATUS_SUSPENDED]);
        $this->record($tenant, $tenant->subscription, 'suspended', $reason);
    }

    public function reactivate(Tenant $tenant): void
    {
        $tenant->update(['status' => Tenant::STATUS_ACTIVE]);
        $tenant->subscription?->update(['status' => Tenant::STATUS_ACTIVE]);
        $this->record($tenant, $tenant->subscription, 'reactivated', 'Cuenta reactivada');
    }

    public function cancel(Tenant $tenant): void
    {
        $tenant->update(['status' => Tenant::STATUS_CANCELLED]);
        $tenant->subscription?->update(['status' => Tenant::STATUS_CANCELLED]);
        $this->record($tenant, $tenant->subscription, 'cancelled', 'Baja de servicio');
    }

    private function statusFor(Subscription $subscription, Carbon $now): string
    {
        if ($now->lt($subscription->ends_at)) {
            return Tenant::STATUS_ACTIVE;
        }
        if ($subscription->grace_ends_at && $now->lt($subscription->grace_ends_at)) {
            return Tenant::STATUS_GRACE;
        }
        if ($subscription->readonly_ends_at && $now->lt($subscription->readonly_ends_at)) {
            return Tenant::STATUS_READONLY;
        }

        return Tenant::STATUS_SUSPENDED;
    }

    private function remind(Tenant $tenant, Subscription $subscription): void
    {
        $subscription->update(['reminded_at' => now()]);
        $this->record($tenant, $subscription, 'reminder', 'Recordatorio de vencimiento enviado');

        if ($tenant->admin_email) {
            Mail::to($tenant->admin_email)->send(new SubscriptionReminderMail($tenant, $subscription));
        }
    }

    private function record(Tenant $tenant, ?Subscription $subscription, string $type, string $message, array $payload = []): void
    {
        SubscriptionEvent::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription?->id,
            'type' => $type,
            'message' => $message,
            'payload' => $payload,
        ]);
    }
}
