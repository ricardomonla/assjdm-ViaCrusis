# Plan 01: Deploy Automático con GitHub Webhook

> **Estado**: ✅ Completado
> **Fecha**: 2026-03-28
> **Servidor**: `srvv-nginx-rm` (190.114.205.17)
> **Sitio**: `https://rmonla.duckdns.org/vcby/`

---

## Progreso General

```
██████████████████████████████ 100% — COMPLETADO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Preparar el servidor | ✅ Completado |
| 2 | Crear deploy.php | ✅ Completado |
| 3 | Configurar NGINX | ✅ Completado |
| 4 | Webhook en GitHub | ✅ Completado |
| 5 | Prueba end-to-end | ✅ Completado |

---

## Objetivo

Configurar deploy automático para que cada `git push` a `main` actualice el sitio en producción de forma instantánea (~2 seg), sin intervención manual.

## Flujo

```
git push → GitHub Webhook → POST /vcby/deploy.php → git pull → Sitio actualizado
```

---

## Fase 1: Preparar el servidor (SSH)

```
██████████████████████████████ 100%
```

- [x] Verificar estado del repo en `/var/www/vcby`
- [x] Confirmar deploy key SSH (`/root/.ssh/id_ed25519`)
- [x] Git pull de cambios pendientes (v26.2)
- [x] Instalar `sudo` (no venía en el LXC mínimo)
- [x] Configurar sudoers para `www-data`

**Acceso**: vía relay `ssh root@10.0.10.203 'pct exec 116 -- bash -c "COMANDO"'`

**Hallazgo**: SSH directo al LXC con password no funciona. Se accede vía `pct exec` desde srv-pmox3.

**Sudoers creado** (`/etc/sudoers.d/vcby-deploy`):
```
www-data ALL=(root) NOPASSWD: /usr/bin/git -C /var/www/vcby pull origin main
```

---

## Fase 2: Crear el script `deploy.php`

```
██████████████████████████████ 100%
```

- [x] Generar secret aleatorio (64 hex chars)
- [x] Crear `/var/www/vcby/deploy.php` con validación HMAC-SHA256
- [x] Crear log `/var/log/vcby-deploy.log` con permisos `www-data`
- [x] Verificar sintaxis PHP (`php -l`)
- [x] Probar `sudo git pull` como `www-data`

**Secret**: `3c9c2c7fd5d3c614c5b66e4997ea8e49debd41e763bc159f8ded369ea56ba376`

**Lógica del script**:
1. Valida método POST
2. Verifica firma HMAC-SHA256 de GitHub
3. Verifica que es push a `main`
4. Ejecuta `sudo git -C /var/www/vcby pull origin main`
5. Registra resultado en log

---

## Fase 3: Configurar NGINX (seguridad)

```
██████████████████████████████ 100%
```

- [x] Agregar location block para `deploy.php`
- [x] Restringir a IPs de GitHub (140.82.112.0/20, 185.199.108.0/22, 192.30.252.0/22, 143.55.64.0/20)
- [x] Validar config (`nginx -t`)
- [x] Recargar NGINX (`systemctl reload nginx`)

**Location agregado** en `/etc/nginx/sites-available/default`:
```nginx
location = /vcby/deploy.php {
    allow 140.82.112.0/20;
    allow 185.199.108.0/22;
    allow 192.30.252.0/22;
    allow 143.55.64.0/20;
    deny all;
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME /var/www/vcby/deploy.php;
}
```

---

## Fase 4: Configurar Webhook en GitHub

```
██████████████████████████████ 100%
```

- [x] Ir a `Settings → Webhooks → Add webhook`
- [x] Configurar Payload URL, Content type, Secret
- [x] Seleccionar evento `push`
- [x] Activar webhook

**Configuración aplicada**:

| Campo | Valor |
|:---|:---|
| Payload URL | `https://rmonla.duckdns.org/vcby/deploy.php` |
| Content type | `application/json` |
| Secret | (mismo que deploy.php) |
| Events | Solo `push` |
| Active | ✅ |

---

## Fase 5: Prueba end-to-end

```
██████████████████████████████ 100%
```

- [x] Hacer cambio de prueba (v26.3 en versionLogs.php)
- [x] Commit + push
- [x] Verificar log de deploy en servidor
- [x] Confirmar sitio actualizado

**Resultado del test** (2026-03-28 20:57:59 UTC):
```
[2026-03-28 20:57:59] Deploy OK | Branch: main | Exit: 0
Output: Updating da802a8..c09450f Fast-forward 3 files changed, 282 insertions(+)
```

**Tiempo push → deploy**: ~2 segundos ✅

---

## Resumen de archivos creados/modificados

| Archivo | Ubicación | Estado |
|:---|:---|:---|
| `deploy.php` | Servidor `/var/www/vcby/` | ✅ Creado |
| `vcby-deploy` | Servidor `/etc/sudoers.d/` | ✅ Creado |
| `vcby-deploy.log` | Servidor `/var/log/` | ✅ Creado |
| Config NGINX | Servidor `/etc/nginx/sites-available/default` | ✅ Modificado |
| Webhook | GitHub → Settings → Webhooks | ✅ Configurado |

## Seguridad

- ✅ Secret compartido con HMAC-SHA256 (GitHub firma cada petición)
- ✅ Restricción por IP de GitHub en NGINX
- ✅ `deploy.php` **NO está versionado** en el repo (contiene el secret)
- ✅ Sudo restringido a un solo comando específico
- ✅ Log de cada deploy para auditoría

## Rollback

Si algo falla después de un deploy:
```bash
ssh root@10.0.10.203 'pct exec 116 -- bash -c "
  cd /var/www/vcby
  git log --oneline -5
  git checkout <commit-anterior>
"'
```
