# Plan 01: Deploy Automático con GitHub Webhook

> **Estado**: 📋 Planificado
> **Fecha**: 2026-03-28
> **Servidor**: `srvv-nginx-rm` (190.114.205.17)
> **Sitio**: `https://rmonla.duckdns.org/vcby/`

---

## Objetivo

Configurar deploy automático para que cada `git push` a `main` actualice el sitio en producción de forma instantánea (~2 seg), sin intervención manual.

## Flujo

```
git push → GitHub Webhook → POST /vcby/deploy.php → git pull → Sitio actualizado
```

---

## Fases de Implementación

### Fase 1: Preparar el servidor (SSH)

**Conectar al servidor:**
```bash
ssh -p 7022 root@190.114.205.17
```

#### 1.1 — Verificar estado actual del repo en el servidor
```bash
cd /var/www/vcby
git remote -v
git status
git branch
```
> Confirmar que el repo usa la deploy key SSH y está en la rama `main`.

#### 1.2 — Configurar permisos para `www-data`
PHP se ejecuta como el usuario `www-data`. Este usuario necesita poder ejecutar `git pull` en el directorio del proyecto.

```bash
# Dar propiedad del directorio al usuario www-data
chown -R www-data:www-data /var/www/vcby

# Verificar que www-data puede hacer git pull
su - www-data -s /bin/bash -c "cd /var/www/vcby && git pull"
```

> **Nota**: Si la deploy key SSH está en `/root/.ssh/`, hay que copiarla o configurar SSH para `www-data`. Alternativa: usar un wrapper con `sudo`.

#### 1.3 — Alternativa: Usar sudo sin password para git pull
Si `www-data` no tiene la deploy key, configurar sudo:

```bash
# Crear archivo sudoers específico
echo 'www-data ALL=(root) NOPASSWD: /usr/bin/git -C /var/www/vcby pull' > /etc/sudoers.d/vcby-deploy
chmod 440 /etc/sudoers.d/vcby-deploy

# Verificar sintaxis
visudo -c
```

---

### Fase 2: Crear el script `deploy.php`

#### 2.1 — Generar un secret aleatorio
```bash
# En la máquina local o el servidor
openssl rand -hex 32
```
> Guardar el valor generado. Se usará tanto en GitHub como en el script.

#### 2.2 — Crear `/var/www/vcby/deploy.php`

```php
<?php
/**
 * GitHub Webhook — Auto Deploy
 * Recibe notificaciones de push y ejecuta git pull.
 */

// ===== CONFIGURACIÓN =====
$secret    = 'AQUI_EL_SECRET_GENERADO';  // Mismo que en GitHub
$logFile   = '/var/log/vcby-deploy.log';
$repoDir   = '/var/www/vcby';
$branch    = 'main';

// ===== VALIDACIONES =====

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Verificar firma de GitHub
$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (empty($signature)) {
    http_response_code(403);
    die('No signature');
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    die('Invalid signature');
}

// Verificar que es un push a main
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? '';

if ($ref !== 'refs/heads/' . $branch) {
    http_response_code(200);
    die('Ignored: not ' . $branch);
}

// ===== EJECUTAR DEPLOY =====
$timestamp = date('Y-m-d H:i:s');
$output    = [];
$returnVar = 0;

// Opción A: Si www-data tiene la deploy key
// exec("cd $repoDir && git pull origin $branch 2>&1", $output, $returnVar);

// Opción B: Si usamos sudo
exec("sudo git -C $repoDir pull origin $branch 2>&1", $output, $returnVar);

// ===== LOG =====
$logEntry = sprintf(
    "[%s] Deploy %s | Branch: %s | Exit: %d | Output: %s\n",
    $timestamp,
    $returnVar === 0 ? 'OK' : 'FAIL',
    $branch,
    $returnVar,
    implode(' ', $output)
);

file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Respuesta a GitHub
http_response_code($returnVar === 0 ? 200 : 500);
echo $returnVar === 0 ? 'Deploy OK' : 'Deploy FAILED';
```

#### 2.3 — Crear archivo de log con permisos
```bash
touch /var/log/vcby-deploy.log
chown www-data:www-data /var/log/vcby-deploy.log
```

---

### Fase 3: Configurar NGINX (seguridad)

#### 3.1 — Restringir acceso al deploy.php
Agregar en la configuración de NGINX del sitio para que `deploy.php` solo acepte peticiones de GitHub:

```nginx
# Bloquear acceso directo a deploy.php excepto desde GitHub
location = /vcby/deploy.php {
    # IPs de GitHub Webhooks (https://api.github.com/meta)
    allow 140.82.112.0/20;
    allow 185.199.108.0/22;
    allow 192.30.252.0/22;
    deny all;

    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

#### 3.2 — Recargar NGINX
```bash
nginx -t && systemctl reload nginx
```

---

### Fase 4: Configurar Webhook en GitHub

#### 4.1 — Ir a la configuración del repo
1. Abrir `https://github.com/ricardomonla/assjdm-ViaCrusis/settings/hooks`
2. Click **"Add webhook"**

#### 4.2 — Completar los campos
| Campo | Valor |
|:---|:---|
| **Payload URL** | `https://rmonla.duckdns.org/vcby/deploy.php` |
| **Content type** | `application/json` |
| **Secret** | El mismo secret generado en Fase 2.1 |
| **Events** | Solo `push` |
| **Active** | ✅ |

#### 4.3 — Guardar y verificar
GitHub enviará un ping automático. Verificar en "Recent Deliveries" que responde 200.

---

### Fase 5: Prueba end-to-end

#### 5.1 — Hacer un cambio de prueba
```bash
# En la máquina local
cd /home/rmonla/Documentos/GitHub/assjdm-ViaCrusis
# Hacer un cambio menor (ej: versión en versionLogs.php)
git add -A && git commit -m "test: deploy automático" && git push
```

#### 5.2 — Verificar en el servidor
```bash
# Revisar el log de deploy
ssh -p 7022 root@190.114.205.17 'cat /var/log/vcby-deploy.log'
```

#### 5.3 — Verificar en el navegador
Recargar `https://rmonla.duckdns.org/vcby/` y confirmar que muestra los cambios.

---

## Resumen de archivos a crear/modificar

| Archivo | Ubicación | Acción |
|:---|:---|:---|
| `deploy.php` | Servidor `/var/www/vcby/` | Crear |
| `vcby-deploy` | Servidor `/etc/sudoers.d/` | Crear (si se usa sudo) |
| `vcby-deploy.log` | Servidor `/var/log/` | Crear |
| Config NGINX | Servidor `/etc/nginx/sites-available/` | Modificar |
| Webhook | GitHub → Settings → Webhooks | Configurar |
| `.gitignore` | Repo local | Agregar `deploy.php` (no versionar el secret) |

## Seguridad

- ✅ Secret compartido con HMAC-SHA256 (GitHub firma cada petición)
- ✅ Restricción por IP de GitHub en NGINX
- ✅ `deploy.php` **NO se versiona** en el repo (contiene el secret)
- ✅ Sudo restringido a un solo comando
- ✅ Log de cada deploy para auditoría

## Rollback

Si algo falla después de un deploy:
```bash
ssh -p 7022 root@190.114.205.17
cd /var/www/vcby
git log --oneline -5          # Ver commits recientes
git checkout <commit-anterior> # Volver a un commit específico
```
