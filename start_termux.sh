#!/bin/bash

# Script de arranque automático para Termux (Android)
# Entrar al directorio donde vive el script (clave para que funcione si lo invocas desde afuera)
cd "$(dirname "$0")" || exit 1

echo "==============================================="
echo "   ✝️ Via Crucis Barrio Yacampiz (VCBY) ✝️"
echo "        Iniciador de Modo Offline Móvil        "
echo "==============================================="
echo ""

echo "📥 [1/4] Sincronizando últimas actualizaciones..."
git pull origin main --quiet
if [ $? -eq 0 ]; then
    echo "  ✅ Código actualizado correctamente."
else
    echo "  ⚠️ No se pudo sincronizar (¿Sin internet?). Iniciando con caché offline..."
fi

echo "📄 [2/4] Descargando guion actualizado del servidor..."
curl -sS --connect-timeout 5 --max-time 15 \
    "https://rmonla.duckdns.org/vcby/audios/subs/guion_completo.json" \
    -o audios/subs/guion_completo.json.tmp 2>/dev/null
if [ $? -eq 0 ] && [ -s audios/subs/guion_completo.json.tmp ]; then
    mv audios/subs/guion_completo.json.tmp audios/subs/guion_completo.json
    echo "  ✅ Guion descargado (última versión del Director)."
else
    rm -f audios/subs/guion_completo.json.tmp
    echo "  ⚠️ No se pudo descargar. Usando versión local."
fi

echo "🌐 [3/4] Abriendo el navegador..."
# Termux tiene su propia herramienta para abrir enlaces en el celular
if command -v termux-open-url &> /dev/null; then
    termux-open-url "http://127.0.0.1:8080"
else
    echo "  (No se encontró disparador automático. Abre Chrome y entra a http://127.0.0.1:8080)"
fi

echo "🚀 [4/4] Levantando Servidor Local PHP..."
echo "  🔴 PRECAUCIÓN: No cierres esta pantalla negra."
echo "  🔴 Para APAGAR el sistema, presiona en tu teclado:"
echo "     Volumen Adelante/Abajo + Letra C  (Ctrl+C)"
echo "-----------------------------------------------"
echo ""

php -S 127.0.0.1:8080
