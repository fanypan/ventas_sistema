<?php

return [

    /*
    | Lista de proxies de confianza (IPs/CIDR, "private" o "*").
    | Vacío: no se confía en X-Forwarded-*. En producción, detrás de Nginx/Caddy,
    | usá "private" (redes Docker). Nunca "*" en internet.
    */
    'proxies' => env('TRUSTED_PROXIES'),

];
