#!/usr/bin/env python3
"""sessionStart: inyecta el mapa mental del SaaS."""
import json
import sys

CONTEXT = """
Arnés AranduTech Ventas: Laravel 9 SaaS, DB por tenant (stancl), staff en /plataforma, comercio en {slug}.localhost.
Leé docs/SAAS.md si tocás onboarding, colas o DNS. Producto: PRODUCT.md. Visual: DESIGN.md (índigo en POS y plataforma; teal solo en landing).
Skills del repo: saas-tenancy, onboard-cliente, modulo-negocio, facturacion-electronica.
No mezcles migraciones centrales con tablas de POS. No hagas migrate:fresh ni revivas _archive/Sistemaventas. No rearmar menús del POS.
Este POS no habla con SIFEN/SET/DNIT: factura electrónica es HTTP a api_facturacion_electronica (URL + API key en el panel del comercio).
Commits: Conventional Commits (tipo(alcance): descripción). Ver AGENTS.md.
"""

def main() -> None:
    try:
        json.load(sys.stdin)
    except Exception:
        pass
    json.dump({"additional_context": CONTEXT.strip()}, sys.stdout)
    sys.stdout.write("\n")


if __name__ == "__main__":
    main()
