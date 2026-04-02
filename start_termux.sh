#!/bin/bash

# Script de arranque automático para Termux (Android)
echo "==============================================="
echo "   ✝️ Via Crucis Barrio Yacampiz (VCBY) ✝️"
echo "        Iniciador de Modo Offline Móvil        "
echo "==============================================="
echo ""

echo "📥 [1/3] Sincronizando últimas actualizaciones..."
git pull origin main --quiet
if [ $? -eq 0 ]; then
    echo "  ✅ Código actualizado correctamente."
else
    echo "  ⚠️ No se pudo sincronizar (¿Sin internet?). Iniciando con caché offline..."
fi

echo "🌐 [2/3] Abriendo el navegador..."
# Termux tiene su propia herramienta para abrir enlaces en el celular
if command -v termux-open-url &> /dev/null; then
    termux-open-url "http://127.0.0.1:8080"
else
    echo "  (No se encontró disparador automático. Abre Chrome y entra a http://127.0.0.1:8080)"
fi

echo "🚀 [3/3] Levantando Servidor Local PHP..."
echo "  🔴 PRECAUCIÓN: No cierres esta pantalla negra."
echo "  🔴 Para APAGAR el sistema, presiona en tu teclado:"
echo "     Volumen Adelante/Abajo + Letra C  (Ctrl+C)"
echo "-----------------------------------------------"
echo ""

php -S 127.0.0.1:8080
