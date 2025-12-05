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

# Función para incrementar la versión en el nombre del archivo
increment_version() {
    local folder="$1"
    shopt -s nullglob
    for file in "$folder"/*.{mp3,m4a,mp4}; do
        [ -e "$file" ] || continue
        BASENAME=$(basename -- "$file")
        
        if [[ $BASENAME =~ ^(.*_v)([0-9]{4})(\..*)?$ ]]; then
            PREFIX="${BASH_REMATCH[1]}"
            VERSION="${BASH_REMATCH[2]}"
            EXTENSION="${BASH_REMATCH[3]}"
            
            NEW_VERSION=$((10#$VERSION + 1))
            NEW_VERSION=$(printf "%04d" "$NEW_VERSION")
            
            NEW_BASENAME="${PREFIX}${NEW_VERSION}${EXTENSION}"
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
    echo "5. Incrementar versión en nombres"
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

# Configurar carpetas
configure_folders() {
    echo "Configuración actual:"
    echo "1. Carpeta MP3 origen: $dirMP3s"
    echo "2. Carpeta M4A origen: $dirM4As"
    echo "3. Carpeta videos destino: $dirMP4_OUT"
    echo "4. Carpeta MP3 destino: $dirMP3_OUT"
    echo "5. Carpeta audio normalizado: $dirNORMALIZED"
    echo "6. Imagen para videos: $default_img"
    echo "7. Volver al menú principal"
    
    read -p "Seleccione qué desea cambiar (1-7): " config_option
    
    case $config_option in
        1) read -p "Nueva carpeta MP3 origen: " dirMP3s ;;
        2) read -p "Nueva carpeta M4A origen: " dirM4As ;;
        3) read -p "Nueva carpeta videos destino: " dirMP4_OUT ;;
        4) read -p "Nueva carpeta MP3 destino: " dirMP3_OUT ;;
        5) read -p "Nueva carpeta audio normalizado: " dirNORMALIZED ;;
        6) read -p "Nueva imagen para videos: " default_img ;;
        7) return ;;
        *) echo "Opción no válida"; sleep 1 ;;
    esac
    
    mkdir -p "$dirMP3s" "$dirM4As" "$dirMP4_OUT" "$dirMP3_OUT" "$dirNORMALIZED"
}

# Mostrar configuración
show_config() {
    echo "Configuración actual:"
    echo "------------------------------------"
    echo "Carpeta MP3 origen: $dirMP3s"
    echo "Carpeta M4A origen: $dirM4As"
    echo "Carpeta videos destino: $dirMP4_OUT"
    echo "Carpeta MP3 destino: $dirMP3_OUT"
    echo "Carpeta audio normalizado: $dirNORMALIZED"
    echo "Imagen para videos: $default_img"
    echo "------------------------------------"
    read -p "Presione Enter para continuar..."
}

# Limpiar nombres de archivos
clean_filenames() {
    echo "Limpiando nombres de archivos..."
    for folder in "$dirMP3s" "$dirM4As" "$dirMP4_OUT" "$dirMP3_OUT" "$dirNORMALIZED"; do
        [ -d "$folder" ] || continue
        shopt -s nullglob
        for file in "$folder"/*.{mp3,m4a,mp4}; do
            [ -e "$file" ] || continue
            BASENAME=$(basename -- "$file")
            CLEAN_BASENAME=$(clean_filename "$BASENAME")
            if [ "$BASENAME" != "$CLEAN_BASENAME" ]; then
                mv "$file" "$folder/$CLEAN_BASENAME"
                echo "Renombrado: $BASENAME -> $CLEAN_BASENAME"
            fi
        done
    done
    echo "Limpieza completada."
    read -p "Presione Enter para continuar..."
}

# Convertir MP3 a MP4
convert_mp3_to_mp4() {
    mkdir -p "$dirMP4_OUT"
    shopt -s nullglob
    mp3_files=("$dirMP3s"/*.mp3)
    
    if [ ${#mp3_files[@]} -eq 0 ]; then
        echo "No se encontraron archivos MP3 en $dirMP3s"
        read -p "Presione Enter para continuar..."
        return
    fi
    
    for archORI in "${mp3_files[@]}"; do
        BASENAME=$(basename -- "$archORI" .mp3)
        CLEAN_BASENAME=$(clean_filename "$BASENAME")
        archDST="$dirMP4_OUT/$CLEAN_BASENAME.mp4"
        
        echo "Procesando: $CLEAN_BASENAME.mp3"
        ffmpeg -loop 1 -i "$default_img" -i "$archORI" \
               -vf "scale=trunc(iw/2)*2:trunc(ih/2)*2" \
               -c:v libx264 -c:a aac -b:a 192k \
               -shortest -pix_fmt yuv420p "$archDST"
    done
    
    echo "Conversión MP3 a MP4 completada."
    read -p "Presione Enter para continuar..."
}

# Convertir M4A a MP3
convert_m4a_to_mp3() {
    mkdir -p "$dirMP3_OUT"
    shopt -s nullglob
    m4a_files=("$dirM4As"/*.m4a)
    
    if [ ${#m4a_files[@]} -eq 0 ]; then
        echo "No se encontraron archivos M4A en $dirM4As"
        read -p "Presione Enter para continuar..."
        return
    fi
    
    for archORI in "${m4a_files[@]}"; do
        BASENAME=$(basename -- "$archORI" .m4a)
        CLEAN_BASENAME=$(clean_filename "$BASENAME")
        archDST="$dirMP3_OUT/$CLEAN_BASENAME.mp3"
        
        echo "Procesando: $CLEAN_BASENAME.m4a"
        ffmpeg -i "$archORI" -acodec libmp3lame -q:a 2 "$archDST"
    done
    
    echo "Conversión M4A a MP3 completada."
    read -p "Presione Enter para continuar..."
}

# Normalizar volumen de MP3 (EBU R128)
normalize_audio() {
    mkdir -p "$dirNORMALIZED"
    shopt -s nullglob
    mp3_files=("$dirMP3s"/*.mp3)
    
    if [ ${#mp3_files[@]} -eq 0 ]; then
        echo "No se encontraron archivos MP3 en $dirMP3s"
        read -p "Presione Enter para continuar..."
        return
    fi
    
    for archORI in "${mp3_files[@]}"; do
        BASENAME=$(basename -- "$archORI" .mp3)
        CLEAN_BASENAME=$(clean_filename "$BASENAME")
        archDST="$dirNORMALIZED/$CLEAN_BASENAME.mp3"
        
        echo "Normalizando: $CLEAN_BASENAME.mp3"
        ffmpeg -i "$archORI" -af loudnorm=I=-16:LRA=11:TP=-1.5 -ar 44100 "$archDST"
    done
    
    echo "Normalización completada."
    read -p "Presione Enter para continuar..."
}


# Menú principal
while true; do
    show_menu
    read -p "Seleccione una opción (1-8): " opcion
    case $opcion in
        1)
            echo "Función de conversión MP3 a MP4 aún no implementada."
            read -p "Presione Enter para continuar..."
            ;;
        2)
            echo "Función de conversión M4A a MP3 aún no implementada."
            read -p "Presione Enter para continuar..."
            ;;
        3)
            echo "Función de normalización aún no implementada."
            read -p "Presione Enter para continuar..."
            ;;
        4)
            clean_filenames
            ;;
        5)
            increment_version_menu
            ;;
        6)
            configure_folders
            ;;
        7)
            show_config
            ;;
        8)
            echo "Saliendo..."
            exit 0
            ;;
        *)
            echo "Opción no válida. Intente nuevamente."
            sleep 1
            ;;
    esac
done