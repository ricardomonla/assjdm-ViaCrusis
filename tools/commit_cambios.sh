#!/bin/bash
# tools/commit_cambios.sh — Commit + Push de cambios del Director
# Invocado desde audios/save_changes.php
# Uso: bash tools/commit_cambios.sh "mensaje de commit"

set -e

REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_DIR"

MSG="${1:-Cambios del Director (auto-commit)}"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# IMPORTANTE: Sincronizar con GitHub antes de commitear
# Esto evita que un push desde otra PC sobreescriba cambios del Director
git stash --include-untracked 2>/dev/null || true
git pull --rebase origin main 2>&1 || true
git stash pop 2>/dev/null || true

# Agregar solo archivos de subtítulos y guion
git add audios/subs/*.md audios/subs/guion_completo.json 2>/dev/null || true

# Verificar si hay cambios staged
if git diff --cached --quiet; then
    echo '{"ok":false,"msg":"Sin cambios pendientes para commitear."}'
    exit 0
fi

# Commit con mensaje descriptivo
git commit -m "🎬 Director: $MSG ($TIMESTAMP)" --no-verify 2>&1

# Push a GitHub (deploy key con permisos de escritura)
git push origin main 2>&1 || echo '{"push_warning":"Push falló, commit local OK"}'

echo "{\"ok\":true,\"msg\":\"Commit + push realizado: $MSG\",\"ts\":\"$TIMESTAMP\"}"
