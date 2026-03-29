#!/usr/bin/env python3
import os
import re
import glob
from collections import defaultdict

# Rutas
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ETIQUETADOS_DIR = os.path.join(BASE_DIR, "docs", "guion", "etiquetados")
GUION_FILE = os.path.join(BASE_DIR, "docs", "guion", "guion-viacrusis-2026.md")
PERSONAJES_FILE = os.path.join(BASE_DIR, "docs", "guion", "personajes.md")

# Regex para parsear [00:00] PERSONAJE: Texto
LINE_REGEX = re.compile(r"^\[(\d{2}:\d{2})\]\s+([^:]+):\s+(.*)$")

def main():
    print("📢 Iniciando compilación del Guion Final y lista de Personajes...\n")
    
    archivos = sorted(glob.glob(os.path.join(ETIQUETADOS_DIR, "*.txt")))
    
    personajes_apariciones = defaultdict(set)
    guion_content = []
    
    guion_content.append("# Guion Teatral — Vía Crucis 2026\n")
    guion_content.append("> **Nota**: Este guion fue transcrito y etiquetado automáticamente. Los tiempos (timestamps) pertenecen al track de audio original.\n\n")
    
    count_lineas = 0
    
    for filepath in archivos:
        filename = os.path.basename(filepath)
        # Extraer número de escena y título. Ejemplo: "103_La_Última_Cena.txt"
        match_nombre = re.match(r"^(\d+)_?(.*)\.txt$", filename)
        if match_nombre:
            escena_num = match_nombre.group(1)
            escena_titulo = match_nombre.group(2).replace("_", " ")
        else:
            escena_num = "???"
            escena_titulo = filename.replace(".txt", "")
            
        guion_content.append(f"## Track {escena_num}: {escena_titulo}\n")
        
        with open(filepath, "r", encoding="utf-8") as f:
            lineas = f.readlines()
            
        ultimo_personaje = None
        
        for linea in lineas:
            linea = linea.strip()
            if not linea:
                continue
                
            m = LINE_REGEX.match(linea)
            if m:
                tiempo, personaje, dialogo = m.groups()
                personaje = personaje.strip().upper()
                
                # Registrar aparición de personaje
                personajes_apariciones[personaje].add(f"{escena_num} ({escena_titulo})")
                
                # Formato de guion
                if personaje != ultimo_personaje:
                    guion_content.append(f"\n**{personaje}** `[{tiempo}]`")
                    ultimo_personaje = personaje
                
                guion_content.append(f"{dialogo}")
                count_lineas += 1
            else:
                # Línea sin formato estricto (probablemente ruido o mal parseada)
                guion_content.append(f"> {linea}")
                
        guion_content.append("\n---\n")
        
    # Escribir Guion Completo
    with open(GUION_FILE, "w", encoding="utf-8") as f:
        f.write("\n".join(guion_content))
    print(f"✅ Guion compilado exitosamente: {GUION_FILE} ({count_lineas} diálogos)")

    # Escribir Lista de Personajes
    with open(PERSONAJES_FILE, "w", encoding="utf-8") as f:
        f.write("# Reparto de Personajes — Vía Crucis 2026\n\n")
        f.write("| Personaje | Total Apariciones | Tracks / Escenas donde aparece |\n")
        f.write("|:---|:---:|:---|\n")
        
        # Ordenar personajes alfabéticamente (filtrando narrador a veces)
        for personaje in sorted(personajes_apariciones.keys()):
            escenas = sorted(list(personajes_apariciones[personaje]))
            escenas_str = "<br>• ".join(escenas)
            f.write(f"| **{personaje}** | {len(escenas)} | • {escenas_str} |\n")
            
    print(f"✅ Lista de personajes exportada: {PERSONAJES_FILE} ({len(personajes_apariciones)} personajes detectados)")

if __name__ == "__main__":
    main()
