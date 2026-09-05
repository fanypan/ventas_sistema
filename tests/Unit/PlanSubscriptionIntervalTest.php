<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Subscription;
use Tests\TestCase;

class PlanSubscriptionIntervalTest extends TestCase
{
    public function test_public_plan_keeps_requested_interval(): void
    {
        $plan = new Plan(['is_public' => true]);

        $this->assertSame(Subscription::INTERVAL_MONTHLY, $plan->subscriptionInterval('monthly'));
        $this->assertSame(Subscription::INTERVAL_YEARLY, $plan->subscriptionInterval('yearly'));
        $this->assertSame(Subscription::INTERVAL_LIFETIME, $plan->subscriptionInterval('lifetime'));
    }

    public function test_internal_plan_always_uses_lifetime(): void
    {
        $plan = new Plan(['is_public' => false]);

        $this->assertSame(Subscription::INTERVAL_LIFETIME, $plan->subscriptionInterval('monthly'));
        $this->assertSame(Subscription::INTERVAL_LIFETIME, $plan->subscriptionInterval('yearly'));
    }
}
