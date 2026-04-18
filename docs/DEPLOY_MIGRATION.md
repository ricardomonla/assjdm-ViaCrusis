# Deploy y Migración de Datos — ViaCrucis

## Flujo de Deploy Automático

El sistema usa un **webhook de GitHub** para desplegar automáticamente a producción cuando se hace push a la rama `main`.

### Arquitectura

```
┌──────────────┐      ┌─────────────────┐      ┌──────────────────┐
│   GitHub     │ ───→ │  deploy.php     │ ───→ │  Producción      │
│   (push)     │      │  (webhook)      │      │  (rmonla.duckdns.org) │
└──────────────┘      └─────────────────┘      └──────────────────┘
                              │
                              ▼
                       data/db_import.php
                       (migración DB)
```

### Configuración del Webhook

**URL:** `https://rmonla.duckdns.org/vcby/deploy.php`

**En GitHub:**
1. Ir a `Settings → Webhooks → Add webhook`
2. Configurar:
   - **Payload URL:** `https://rmonla.duckdns.org/vcby/deploy.php`
   - **Content type:** `application/json`
   - **Secret:** `vcby2026deploy` (definido en `deploy.php`)
   - **Events:** `Just the push event`

---

## Migración de Datos (Test → Producción)

### Problema

No hay acceso SSH/SCP al servidor de producción. La DB vive en el servidor y no se puede modificar directamente.

### Solución: Migración vía Git

Los datos se exportan a JSON, se commitean al repo, y el webhook los importa automáticamente.

### Flujo Completo

```
┌─────────────────┐
│ 1. Test (Docker)│
│    DB SQLite    │
└────────┬────────┘
         │ php tools/db_export.php
         ▼
┌─────────────────┐
│ 2. JSON         │
│    migration_   │
│    personas.json│
└────────┬────────┘
         │ git add + commit + push
         ▼
┌─────────────────┐
│ 3. GitHub       │
│    (repo)       │
└────────┬────────┘
         │ webhook POST
         ▼
┌─────────────────┐
│ 4. Producción   │
│    deploy.php   │
│    ↓            │
│    db_import.php│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 5. DB Producción│
│    actualizada  │
└─────────────────┘
```

### Comandos para Migrar

```bash
# 1. Exportar datos desde test (Docker)
docker exec vcby-test php /app/tools/db_export.php

# 2. Verificar JSON generado
cat data/migration_personas.json

# 3. Commitear y pushear
git add data/migration_personas.json
git commit -m "v26.X: Migración DB - datos personas"
git push origin main

# 4. El webhook hace el resto automáticamente
```

### Verificación

**Endpoint de estado:**
```bash
curl https://rmonla.duckdns.org/vcby/personas/api.php?action=status
```

**Respuesta esperada:**
```json
{
  "ok": true,
  "status": "healthy",
  "version": "26.12",
  "counts": {
    "personas": 27,
    "roles": 6,
    "persona_roles": 27
  },
  "migration": {
    "done": true,
    "has_real_data": true
  },
  "last_person_created": "2026-04-18 00:22:02",
  "timestamp": "2026-04-18 01:00:00"
}
```

---

## Archivos Involucrados

| Archivo | Función |
|---------|---------|
| `deploy.php` | Webhook que recibe push de GitHub |
| `data/db_import.php` | Importa JSON a SQLite en producción |
| `tools/db_export.php` | Exporta DB local a JSON |
| `data/migration_personas.json` | Datos migrados (se commitea) |
| `data/migration_personas.json.imported` | Marker post-importación |

---

## Logs

**Deploy log:** `deploy.log` (en producción)
```
2026-04-18 00:45:00 | code=0 | Already up to date.
2026-04-18 00:45:01 | DB import: [db_import] Migración completada: {"roles":6,"personas":27,"persona_roles":27}
```

**Import log:** `data/import.log` (en producción)
```
2026-04-18 00:45:01 | Migración 26.12: {"roles":6,"personas":27,"persona_roles":27}
```

---

## Troubleshooting

### El webhook no dispara

1. Verificar en GitHub: `Settings → Webhooks` — ¿último delivery fue exitoso?
2. Verificar `deploy.log` en producción
3. Verificar que `deploy.php` es accesible: `curl https://rmonla.duckdns.org/vcby/deploy.php`

### La migración falla

1. Verificar que `data/db_import.php` existe en producción
2. Verificar que el JSON es válido: `php -r "json_decode(file_get_contents('data/migration_personas.json'));"`
3. Verificar permisos de escritura en `data/`

### Datos duplicados

El script usa `INSERT OR IGNORE`, así que:
- Personas con mismo `(nombre, apellido)` se saltan
- Roles con mismo `id` se saltan
- Persona_roles duplicados se saltan

Si necesitas re-importar, borrá los datos primero o eliminá el archivo `.imported` para reintentar.

---

## Nueva Migración en el Futuro

Para migrar datos de cualquier nueva tabla:

1. **Agregar tabla a `db_export.php`:**
   ```php
   $export['tables']['nueva_tabla'] = $db->query("SELECT * FROM nueva_tabla")->fetchAll();
   ```

2. **Agregar import a `db_import.php`:**
   ```php
   if (!empty($export['tables']['nueva_tabla'])) {
       $stmt = $db->prepare("INSERT OR IGNORE INTO nueva_tabla (...) VALUES (...)");
       foreach ($export['tables']['nueva_tabla'] as $row) {
           $stmt->execute([...]);
       }
   }
   ```

3. **Exportar, commitear, pushear**

---

## Referencias

- Plan 12 (v26.12): `docs/plan/12_v26.12_Personas.md`
- Plan 11 (v26.11): `docs/plan/11_v26.11.md` (protocolo de migración original)
