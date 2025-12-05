#!/bin/bash

# ======= COLORES =======
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[1;34m'
CYAN='\033[1;36m'
NC='\033[0m' # No Color

# ======= CONFIGURACIÓN =======
default_img="viacrucis.jpg"
dirMP3s="media"
dirM4As="media"
dirMP4_OUT="output_mp4"
dirMP3_OUT="output_mp3"
dirNORMALIZED="normalized_audio"

# ======= DEPENDENCIAS =======
if ! command -v ffmpeg &> /dev/null; then
    echo -e "${RED}Error:${NC} ffmpeg no está instalado."
    exit 1
fi

# ======= FUNCIONES =======

clean_filename() {
    local name="$1"
    name="${name// (128kbit_AAC)/}"
    name="${name// /_}"
    echo "$name"
}

increment_version() {
    local folder="$1"
    shopt -s nullglob
    for file in "$folder"/*.{mp3,m4a,mp4}; do
        [ -e "$file" ] || continue
        local BASENAME=$(basename -- "$file")

        if [[ $BASENAME =~ ^(.*_v)([0-9]{4})(\..*)?$ ]]; then
            local PREFIX="${BASH_REMATCH[1]}"
            local VERSION="${BASH_REMATCH[2]}"
            local EXTENSION="${BASH_REMATCH[3]}"
            local NEW_VERSION=$(printf "%04d" $((10#$VERSION + 1)))
            local NEW_BASENAME="${PREFIX}${NEW_VERSION}${EXTENSION}"
            mv "$file" "$folder/$NEW_BASENAME"
            echo -e "${GREEN}Versión incrementada:${NC} $BASENAME → $NEW_BASENAME"
        else
            echo -e "${YELLOW}Formato no válido (omitido):${NC} $BASENAME"
        fi
    done
}

show_menu() {
    clear
    echo -e "${CYAN}===================================="
    echo -e "  CONVERSOR DE AUDIO/VIDEO          "
    echo -e "====================================${NC}"
    echo -e "${BLUE}1.${NC} Convertir MP3 a MP4 (con imagen)"
    echo -e "${BLUE}2.${NC} Convertir M4A a MP3"
    echo -e "${BLUE}3.${NC} Normalizar volumen de MP3 (loudnorm)"
    echo -e "${BLUE}4.${NC} Limpiar nombres de archivos"
    echo -e "${BLUE}5.${NC} Incrementar versión en nombres"
    echo -e "${BLUE}6.${NC} Configurar carpetas"
    echo -e "${BLUE}7.${NC} Ver configuración actual"
    echo -e "${BLUE}8.${NC} Salir"
    echo -e "${CYAN}====================================${NC}"
}

increment_version_menu() {
    echo -e "${YELLOW}¿En qué carpeta desea incrementar la versión?${NC}"
    echo -e "1. MP3 origen ($dirMP3s)"
    echo -e "2. M4A origen ($dirM4As)"
    echo -e "3. Videos destino ($dirMP4_OUT)"
    echo -e "4. MP3 destino ($dirMP3_OUT)"
    echo -e "5. Audio normalizado ($dirNORMALIZED)"
    echo -e "6. Volver al menú principal"

    read -p "Seleccione una opción (1-6): " folder_option

    case $folder_option in
        1) increment_version "$dirMP3s" ;;
        2) increment_version "$dirM4As" ;;
        3) increment_version "$dirMP4_OUT" ;;
        4) increment_version "$dirMP3_OUT" ;;
        5) increment_version "$dirNORMALIZED" ;;
        6) return ;;
        *) echo -e "${RED}Opción no válida${NC}"; sleep 1 ;;
    esac

    read -p "Presione Enter para continuar..."
}

configure_folders() {
    echo -e "${CYAN}Configuración actual:${NC}"
    echo "1. MP3 origen: $dirMP3s"
    echo "2. M4A origen: $dirM4As"
    echo "3. Videos destino: $dirMP4_OUT"
    echo "4. MP3 destino: $dirMP3_OUT"
    echo "5. Audio normalizado: $dirNORMALIZED"
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
        *) echo -e "${RED}Opción no válida${NC}"; sleep 1 ;;
    esac

    mkdir -p "$dirMP3s" "$dirM4As" "$dirMP4_OUT" "$dirMP3_OUT" "$dirNORMALIZED"
}

show_config() {
    echo -e "${CYAN}Configuración actual:${NC}"
    echo "------------------------------------"
    echo "MP3 origen: $dirMP3s"
    echo "M4A origen: $dirM4As"
    echo "Videos destino: $dirMP4_OUT"
    echo "MP3 destino: $dirMP3_OUT"
    echo "Audio normalizado: $dirNORMALIZED"
    echo "Imagen: $default_img"
    echo "------------------------------------"
    read -p "Presione Enter para continuar..."
}

clean_filenames() {
    echo -e "${YELLOW}Limpiando nombres de archivos...${NC}"
    for folder in "$dirMP3s" "$dirM4As" "$dirMP4_OUT" "$dirMP3_OUT" "$dirNORMALIZED"; do
        [ -d "$folder" ] || continue
        shopt -s nullglob
        for file in "$folder"/*.{mp3,m4a,mp4}; do
            [ -e "$file" ] || continue
            local new_name=$(clean_filename "$(basename "$file")")
            mv "$file" "$folder/$new_name"
            echo -e "${GREEN}Renombrado:${NC} $file → $new_name"
        done
    done
    read -p "Presione Enter para continuar..."
}

# Aquí podés agregar más funciones según el menú

# ======= BUCLE PRINCIPAL =======
while true; do
    show_menu
    read -p "Seleccione una opción (1-8): " opcion

    case $opcion in
        1) echo "Función 1 no implementada aún."; read -p "Enter para continuar..." ;;
        2) echo "Función 2 no implementada aún."; read -p "Enter para continuar..." ;;
        3) echo "Función 3 no implementada aún."; read -p "Enter para continuar..." ;;
        4) clean_filenames ;;
        5) increment_version_menu ;;
        6) configure_folders ;;
        7) show_config ;;
        8) echo -e "${CYAN}¡Hasta luego!${NC}"; exit 0 ;;
        *) echo -e "${RED}Opción inválida${NC}"; sleep 1 ;;
    esac
done
