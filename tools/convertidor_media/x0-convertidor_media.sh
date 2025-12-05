#!/bin/bash

# Configuración por defecto
default_img="viacrucis.jpg"
dirMP3s="media"
dirM4As="media"
dirMP4_OUT="output_mp4"
dirMP3_OUT="output_mp3"
dirNORMALIZED="normalized_audio"

# Verificar si ffmpeg está instalado
if ! command -v ffmpeg &> /dev/null; then
    echo "Error: ffmpeg no está instalado. Instálalo antes de ejecutar este script."
    exit 1
fi

# Función para limpiar nombres de archivos
clean_filename() {
    local name="$1"
    name="${name// (128kbit_AAC)/}"  # Eliminar " (128kbit_AAC)"
    name="${name// /_}"              # Reemplazar espacios por "_"
    echo "$name"
}

# Función para incrementar SOLO los últimos 2 dígitos (ej: 101_v2509... → 101_v2510...)
increment_version() {
    local folder="$1"
    shopt -s nullglob
    for file in "$folder"/*.{mp3,m4a,mp4}; do
        [ -e "$file" ] || continue
        BASENAME=$(basename -- "$file")
        
        # Extraer prefijo (101_v25), versión (09), y resto del nombre
        if [[ $BASENAME =~ ^(.*_v25)([0-9]{2})(_.*\..*)$ ]]; then
            PREFIX="${BASH_REMATCH[1]}"   # Ej: "101_v25"
            VERSION="${BASH_REMATCH[2]}"  # Ej: "09"
            REST="${BASH_REMATCH[3]}"     # Ej: "_La_entrada_de_Jesús_en_Jerusalén.mp3"
            
            # Incrementar versión y formatear a 2 dígitos (09 → 10)
            NEW_VERSION=$((10#$VERSION + 1))
            NEW_VERSION=$(printf "%02d" "$NEW_VERSION")  # Asegura 2 dígitos (ej: 10, no 010)
            
            NEW_BASENAME="${PREFIX}${NEW_VERSION}${REST}"
            mv "$file" "$folder/$NEW_BASENAME"
            echo "Versión incrementada: $BASENAME → $NEW_BASENAME"
        else
            echo "Formato no válido (se omite): $BASENAME"
        fi
    done
}

# Mostrar menú
show_menu() {
    clear
    echo "===================================="
    echo "  CONVERSOR DE AUDIO/VIDEO          "
    echo "===================================="
    echo "1. Convertir MP3 a MP4 (con imagen)"
    echo "2. Convertir M4A a MP3"
    echo "3. Normalizar volumen de MP3 (loudnorm)"
    echo "4. Limpiar nombres de archivos"
    echo "5. Incrementar versión en nombres (v2509 → v2510)"
    echo "6. Configurar carpetas"
    echo "7. Ver configuración actual"
    echo "8. Salir"
    echo "===================================="
}

# Menú para incrementar versión
increment_version_menu() {
    echo "¿En qué carpeta desea incrementar la versión?"
    echo "1. Carpeta MP3 origen ($dirMP3s)"
    echo "2. Carpeta M4A origen ($dirM4As)"
    echo "3. Carpeta videos destino ($dirMP4_OUT)"
    echo "4. Carpeta MP3 destino ($dirMP3_OUT)"
    echo "5. Carpeta audio normalizado ($dirNORMALIZED)"
    echo "6. Volver al menú principal"
    
    read -p "Seleccione una opción (1-6): " folder_option
    
    case $folder_option in
        1) increment_version "$dirMP3s" ;;
        2) increment_version "$dirM4As" ;;
        3) increment_version "$dirMP4_OUT" ;;
        4) increment_version "$dirMP3_OUT" ;;
        5) increment_version "$dirNORMALIZED" ;;
        6) return ;;
        *) echo "Opción no válida"; sleep 1 ;;
    esac
    
    read -p "Presione Enter para continuar..."
}

# Resto de funciones (configure_folders, show_config, clean_filenames, convert_mp3_to_mp4, convert_m4a_to_mp3, normalize_audio)
# ... (las mismas que en el script anterior)

# Menú principal
while true; do
    show_menu
    read -p "Seleccione una opción (1-8): " option
    
    case $option in
        1) convert_mp3_to_mp4 ;;
        2) convert_m4a_to_mp3 ;;
        3) normalize_audio ;;
        4) clean_filenames ;;
        5) increment_version_menu ;;
        6) configure_folders ;;
        7) show_config ;;
        8) echo "Saliendo..."; exit 0 ;;
        *) echo "Opción no válida"; sleep 1 ;;
    esac
done