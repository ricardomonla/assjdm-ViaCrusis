# Configuración del Servidor — ViaCrucis

## Problema: Permisos de Git para Webhook

El webhook de GitHub ejecuta `deploy.php` como usuario `www-data`, pero el repositorio git es dueño de `root`. Esto causa:
```
fatal: detected dubious ownership in repository
error: cannot open '.git/FETCH_HEAD': Permission denied
```

## Solución Definitiva (una única vez)

**Acceder como root al servidor y ejecutar:**

### 1. Cambiar owner del repositorio

```bash
# Cambiar dueño del repo a www-data
chown -R www-data:www-data /var/www/vcby

# Cambiar dueño de la DB a www-data
chown -R www-data:www-data /var/www/vcby-data
```

### 2. Configurar sudoers para www-data

```bash
# Editar sudoers
visudo

# Agregar esta línea al final (después de la línea de root):
www-data ALL=(ALL) NOPASSWD: /usr/bin/chown, /usr/bin/git, /usr/bin/php
```

### 3. Verificar configuración

```bash
# Verificar que www-data puede ejecutar git
sudo -u www-data git -C /var/www/vcby status

# Verificar que puede hacer chown
sudo -u www-data chown www-data:www-data /var/www/vcby/test && rm /var/www/vcby/test

# Probar deploy.php manualmente
sudo -u www-data php /var/www/vcby/deploy.php
```

## Alternativa: Sin cambiar owner (menos recomendado)

Si no podés cambiar el owner del repositorio, al menos permití que `www-data` escriba en `.git`:

```bash
# Como root:
chmod -R 775 /var/www/vcby/.git
chown -R root:www-data /var/www/vcby/.git
```

## Verificación Post-Configuración

Después de configurar, hacer push a GitHub debería:

1. Disparar el webhook
2. `deploy.php` ejecuta `git pull` exitosamente
3. `db_import.php` migra la DB
4. Los cambios están en producción automáticamente

**Verificar logs:**
```bash
tail -f /var/www/vcby/deploy.log
tail -f /var/www/vcby/data/import.log
```

## Comandos de Diagnóstico

```bash
# Ver dueño actual del repo
ls -la /var/www/vcby/
ls -la /var/www/vcby/.git/

# Ver si www-data existe
id www-data

# Ver logs de error de PHP
tail -20 /var/log/php8-fpm/error.log
# o
tail -20 /var/log/nginx/error.log

# Probar webhook manualmente
curl -X POST https://rmonla.duckdns.org/vcby/deploy.php
```

## Notas

- Esta configuración se hace **UNA SOLA VEZ**
- Después, todos los deploys son automáticos vía webhook
- No se necesita SSH para deploys futuros
- El webhook solo funciona con push a `main`
