<p>Hola {{ $tenant->admin_name }},</p>
<p>Tu sistema de ventas ya está listo.</p>
<p><strong>URL:</strong> <a href="{{ $tenant->url() }}">{{ $tenant->url() }}</a></p>
<p><strong>Usuario:</strong> {{ $tenant->admin_email }}</p>
<p><strong>Contraseña:</strong> {{ $plainPassword }}</p>
<p>Cambiá la contraseña después de entrar. Soporte: WhatsApp {{ config('saas.whatsapp') }}.</p>
