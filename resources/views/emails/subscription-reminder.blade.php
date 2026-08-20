<p>Hola {{ $tenant->admin_name }},</p>
<p>El plan <strong>{{ $tenant->plan?->name }}</strong> de {{ $tenant->name }} vence el {{ optional($subscription->ends_at)->format('d/m/Y') }}.</p>
<p>Pagá por transferencia o efectivo y avisá a AranduTech. Tenés {{ config('saas.grace_days') }} días de gracia; después el POS pasa a solo lectura y luego se pausa.</p>
<p>WhatsApp: {{ config('saas.whatsapp') }}</p>
