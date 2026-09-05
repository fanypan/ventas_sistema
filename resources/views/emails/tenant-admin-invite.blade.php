<p>Hola {{ $tenant->admin_name }},</p>
<p>Tu sistema de ventas ya está listo. Para entrar, definí tu contraseña con este enlace (vale 48 horas):</p>
<p><a href="{{ $setupUrl }}">{{ $setupUrl }}</a></p>
<p><strong>Usuario:</strong> {{ $tenant->admin_email }}</p>
<p>Si no pediste esta cuenta, ignorá el mail. Soporte: WhatsApp {{ config('saas.whatsapp') }}.</p>
