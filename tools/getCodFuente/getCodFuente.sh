#!/bin/bash

# Array de rutas de archivos
archivos=(
    "../../incs/audioFiles.php"
    "../../incs/footer.php"
    "../../incs/functions.php"
    "../../incs/header.php"
    "../../incs/index.php"
    "../../incs/kerberos.php"
    "../../incs/versionLogs.php"
    "../../index.php"
    "../../play.php"
    "../../serve.php"
)

# archivos=(
#     "../../css/style.css"
#     "../../index.php"
#     "../../play.php"
#     "../../serve.php"
# )

# Archivo de salida
output_file="codigo_fuente.md"

# Encabezado del archivo markdown
echo "# Código Fuente Recolectado" > "$output_file"
echo "" >> "$output_file"

# Procesar cada elemento del array
for ruta_archivo in "${archivos[@]}"; do
    # Verificar si el archivo existe
    if [ -f "$ruta_archivo" ]; then
        # Obtener extensión para determinar el lenguaje del bloque de código
        extension="${ruta_archivo##*.}"
        
        # Agregar título al markdown
        echo "## $ruta_archivo" >> "$output_file"
        echo "" >> "$output_file"
        
        # Agregar código entre bloques de markdown
        echo -e "\n\`\`\`$extension" >> "$output_file"
        cat "$ruta_archivo" >> "$output_file"
        echo -e "\n\`\`\`" >> "$output_file"
        echo "" >> "$output_file"
    else
        echo "Advertencia: El archivo $ruta_archivo no existe y será omitido." >&2
    fi
done

echo "Proceso completado. El código fuente se ha guardado en $output_file"