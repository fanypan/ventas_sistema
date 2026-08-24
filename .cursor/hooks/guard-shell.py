#!/usr/bin/env python3
"""beforeShellExecution: bloquea comandos que tiran datos o el legado."""
import json
import re
import sys

DENY_PATTERNS = [
    (r"migrate:fresh", "migrate:fresh borra la DB central y no recrea los tenants."),
    (r"migrate:reset", "migrate:reset destruye migraciones; no usarlo en este SaaS."),
    (r"db:wipe", "db:wipe borra todas las bases visibles."),
    (r"tenants:rollback", "Rollback masivo de tenants. Pedí confirmación explícita al usuario."),
    (r"drop\s+database", "DROP DATABASE no se corre desde el agente."),
    (r"git\s+push\s+[^\n]*(-f|--force)", "No force-push."),
    (r"git\s+reset\s+--hard", "reset --hard no, salvo que el usuario lo pida."),
    (r"rm\s+-rf\s+[^\n]*(_archive|Sistemaventas|storage|vendor|database)", "Borrado destructivo de dirs sensibles."),
    (r"down\s+-v", "docker compose down -v borra Postgres/Redis/storage de producción."),
]

ASK_PATTERNS = [
    (r"artisan\s+migrate(?:\s|$)", "Migración central. Para comercios usá tenants:migrate."),
    (r"artisan\s+tenants:migrate", "Migración de tenants: confirma ambiente (dev vs prod)."),
    (r"artisan\s+db:seed", "Seed: DatabaseSeeder es central; TenantDatabaseSeeder es por comercio."),
    (r"artisan\s+tenants:seed", "Seed por tenant: confirma cuál comercio y qué seeder."),
]


def main() -> None:
    try:
        payload = json.load(sys.stdin)
    except Exception:
        json.dump({"permission": "allow"}, sys.stdout)
        return

    command = payload.get("command") or ""
    lower = command.lower()

    for pattern, reason in DENY_PATTERNS:
        if re.search(pattern, lower, flags=re.I):
            json.dump(
                {
                    "permission": "deny",
                    "user_message": reason,
                    "agent_message": f"Hook bloqueó: {reason}",
                },
                sys.stdout,
            )
            sys.stdout.write("\n")
            return

    for pattern, reason in ASK_PATTERNS:
        if re.search(pattern, lower, flags=re.I):
            json.dump(
                {
                    "permission": "ask",
                    "user_message": reason,
                    "agent_message": reason,
                },
                sys.stdout,
            )
            sys.stdout.write("\n")
            return

    json.dump({"permission": "allow"}, sys.stdout)
    sys.stdout.write("\n")


if __name__ == "__main__":
    main()
