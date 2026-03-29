#!/usr/bin/env python3
"""
Etiqueta personajes en las transcripciones delegando el procesamiento al script 'reutilizable' Ruby:
`tools/groq_client.rb` 
¡Es un enfoque híbrido espectacular!
Genera los guiones formateados en la carpeta docs/guion/etiquetados/
"""

import os
import sys
import subprocess
from pathlib import Path
import json

# Configuración de Rutas
INPUT_DIR = Path(__file__).parent.parent / "docs" / "guion" / "transcripciones"
OUTPUT_DIR = Path(__file__).parent.parent / "docs" / "guion" / "etiquetados"

SYSTEM_PROMPT = """
Eres un asistente experto en dramaturgia y transcripción teatral.
Tu tarea es leer la siguiente transcripción de un audio del Via Crucis, y reescribirla asignando cada línea de texto al personaje que la dice.

Reglas:
1. Mantén la marca de tiempo exacta al inicio de cada línea.
2. Identifica quién es el interlocutor según el contexto (JESÚS, PILATOS, PUEBLO, NARRADOR, SOLDADO, JUDAS, CAIFÁS, PEDRO, MARÍA, etc.).
3. Formatea la salida exclusivamente así: '[00:00] PERSONAJE: Lo que dijo el personaje...'.
4. No resumas ni elimines ningún texto. Todo lo hablado debe estar presente.
5. No agregues preámbulos. Solo responde con el texto formateado.
"""

def procesar_texto_usando_ruby(texto_crudo):
    """
    Invoca al sub-proceso Ruby 'groq_client.rb' que contiene la lógica
    del LLM, la conexión de HTTP y la rotación de API Keys.
    """
    script_ruby = Path(__file__).parent / "api_key_rotator" / "api_key_rotator.rb"
    
    paylod = {
        "system": SYSTEM_PROMPT,
        "user": texto_crudo
    }
    
    # Inicia el proceso de Ruby, enviándole el contenido a través de su 'entrada estándar' (STDIN)
    proceso = subprocess.Popen(
        ["ruby", str(script_ruby)],
        stdin=subprocess.PIPE,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE, # Conserva warnings 
        text=True
    )
    
    # Nos comunicamos y esperamos el retorno
    stdout_salida, stderr_salida = proceso.communicate(input=json.dumps(paylod))
    
    # Si el script en Ruby pintó advertencias de rotaciones, muestralas al usuario desde Python
    if stderr_salida:
        print(stderr_salida.strip())
        
    # Si falló la ejecución del binario en sí mismo
    if proceso.returncode != 0:
        raise Exception(f"El proceso Ruby falló horriblemente con código {proceso.returncode}. Detalles: {stdout_salida.strip()} {stderr_salida.strip()}")
        
    # Procesar JSON de STDOUT del script en Ruby
    try:
        resultado = json.loads(stdout_salida)
        if resultado.get("success"):
            return resultado["data"]
        else:
            raise Exception(f"Ruby Script reportó fallo controlado: {resultado.get('error')}")
    except json.JSONDecodeError:
        raise Exception(f"Dato corrupto devuelto desde el Subproceso Ruby: {stdout_salida[:100]}")


def main():
    if not INPUT_DIR.exists():
        print("❌ No se encontraron transcripciones.")
        return
        
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    
    txt_files = sorted(INPUT_DIR.glob("*.txt"))
    if not txt_files:
        print("❌ La carpeta de transcripciones está vacía.")
        return

    print("📢 Iniciando etiquetado automático delegando IA a Cliente Ruby (groq_client.rb)\n")    

    apis_path = Path(__file__).parent / "api_key_rotator" / "apis.json"
    hint = "Sin pista configurada"
    if apis_path.exists():
        try:
            with open(apis_path) as fa:
                data = json.load(fa)
                hint = data.get("_hint", hint)
        except Exception:
            pass
            
    candado_path = Path(__file__).parent / "api_key_rotator" / ".candado.key"
    import time
    candado_abierto = False
    if candado_path.exists():
        if (time.time() - candado_path.stat().st_mtime) < 3600:
            candado_abierto = True
            print("🔓 Candado detectado. Operando sin requerir contraseña por 1 hora.")
            
    if not candado_abierto and not os.environ.get("GROQ_ROTATOR_PASS"):
        import getpass
        pwd = getpass.getpass(f"🔑 Ingresa la frase secreta de rotación (Pista: {hint}): ")
        # Esto será heredado por el subproceso Popen de Ruby
        os.environ["GROQ_ROTATOR_PASS"] = pwd
        # Cerrar el candado temporalmente para el resto de scripts
        with open(candado_path, "w") as f:
            f.write(pwd)
        candado_path.chmod(0o600)
        print("🔓 Candado cerrado en tu equipo. Válido por 1 hora.")
        
    total = len(txt_files)
    
    for i, file_path in enumerate(txt_files, 1):
        output_file = OUTPUT_DIR / file_path.name
        
        if output_file.exists():
            print(f"⏭️  [{i}/{total}] {file_path.name} — Ya etiquetado")
            continue
            
        print(f"🤖 [{i}/{total}] Analizando personajes en: {file_path.name}...", end="", flush=True)
        
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                raw_text = f.read()
                
            # Procesar usando EL SCRIPT RUTILIZABLE DE RUBY
            texto_listo = procesar_texto_usando_ruby(raw_text)
            
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(texto_listo.strip() + "\n")
                
            print(f" ✅ -> /etiquetados/")
            
        except Exception as e:
            print(f"\n   ❌ Error procesando {file_path.name}: {e}")
            break

    print(f"\n🏁 Proceso finalizado. Puedes encontrar los guiones en: {OUTPUT_DIR}")

if __name__ == "__main__":
    main()
