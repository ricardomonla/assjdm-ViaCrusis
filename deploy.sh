#!/bin/bash
# deploy.sh — Script de deploy para ejecutar como root
# El webhook PHP llama a este script con sudo

set -e

cd /var/www/vcby

# Asegurar permisos correctos
chown -R www-data:www-data /var/www/vcby 2>/dev/null || true
chown -R www-data:www-data /var/www/vcby-data 2>/dev/null || true

# Git pull
git fetch origin main
git reset --hard origin/main

# Ejecutar migración si existe
if [ -f "data/db_import.php" ]; then
    sudo -u www-data php data/db_import.php
fi

echo "Deploy completado: $(date)"
