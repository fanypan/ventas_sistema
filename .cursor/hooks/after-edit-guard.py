#!/usr/bin/env python3
"""afterFileEdit: avisa si una migración de POS cayó en el folder central."""
import json
import sys

def main() -> None:
    try:
        payload = json.load(sys.stdin)
    except Exception:
        json.dump({}, sys.stdout)
        return

    path = (
        payload.get("file_path")
        or payload.get("path")
        or payload.get("filePath")
        or ""
    ).replace("\\", "/")

    extra = None
    if "database/migrations/" in path and "database/migrations/tenant/" not in path:
        name = path.rsplit("/", 1)[-1].lower()
        if name.endswith(".php") and ("create_" in name or "add_" in name):
            extra = (
                "Migración en database/migrations/ (central). "
                "Tablas de POS/productos/ventas van en database/migrations/tenant/ "
                "o Modules/*/Database/Migrations."
            )
    if "Modules/" in path and path.endswith("ServiceProvider.php"):
        try:
            with open(path, encoding="utf-8") as fh:
                body = fh.read()
        except OSError:
            body = payload.get("content") or ""
        if "loadMigrationsFrom" in body:
            extra = (
                "ServiceProvider de módulo: sacá loadMigrationsFrom; "
                "stancl ya corre Modules/*/Database/Migrations. "
                "Rutas con TenantMiddleware::web()."
            )

    if extra:
        json.dump({"additional_context": extra}, sys.stdout)
    else:
        json.dump({}, sys.stdout)
    sys.stdout.write("\n")


if __name__ == "__main__":
    main()
