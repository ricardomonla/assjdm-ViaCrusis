#!/usr/bin/env python3
"""
Transcribe todos los MP3 del Via Crucis usando la API externa de Groq (whisper-large-v3).
Esto evita sobrecargar la memoria y CPU de tu ordenador.
Genera archivos .txt individuales en docs/guion/transcripciones/
"""

import os
import sys
import re
from pathlib import Path
import requests
import json

# Configuración
MEDIA_DIR = Path(__file__).parent.parent / "media"
OUTPUT_DIR = Path(__file__).parent.parent / "docs" / "guion" / "transcripciones"
API_URL = "https://api.groq.com/openai/v1/audio/transcriptions"

# Llave API leída de la configuración local de OPENTerm / OpenCode
API_KEY = "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" # Reemplazar de ~/.local/share/opencode/auth.json

def format_timestamp(seconds):
    """Convierte segundos a formato [MM:SS]"""
    minutes = int(seconds // 60)
    secs = int(seconds % 60)
    return f"[{minutes:02d}:{secs:02d}]"

def get_audio_title(filename):
    """Extrae el título limpio del nombre de archivo."""
    match = re.match(r'^(\d{3})_v\d{4}_(.+)\.mp3$', filename)
    if match:
        order = match.group(1)
        title = match.group(2).replace('_', ' ')
        return order, title
    return None, filename

def transcribe_file_groq(audio_path):
    """Transcribe usando la API de Groq en lugar de CPU local."""
    with open(audio_path, "rb") as file:
        response = requests.post(
            API_URL,
            headers={"Authorization": f"Bearer {API_KEY}"},
            files={"file": (audio_path.name, file, "audio/mpeg")},
            data={
                "model": "whisper-large-v3",
                "temperature": "0",
                "response_format": "verbose_json",
                "language": "es"
            }
        )
    
    if response.status_code != 200:
        raise Exception(f"Error de API Groq (HTTP {response.status_code}): {response.text}")
    
    data = response.json()
    lines = []
    
    if "segments" in data:
        for segment in data["segments"]:
            ts_start = format_timestamp(segment["start"])
            text = segment["text"].strip()
            if text:
                lines.append(f"{ts_start} {text}")
    else:
        # Fallback de seguridad
        lines.append(f"[00:00] {data.get('text', '').strip()}")
        
    duration = data.get("duration", 0.0)
    language = data.get("language", "es")
    
    return lines, duration, language

def main():
    # Crear directorio de salida
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    
    # Filtrar archivos específicos si se pasan como argumento
    target_files = None
    if len(sys.argv) > 1:
        target_files = sys.argv[1:]
    
    # Listar archivos MP3
    mp3_files = sorted(MEDIA_DIR.glob("*.mp3"))
    if target_files:
        mp3_files = [f for f in mp3_files if f.name in target_files or f.stem.startswith(tuple(target_files))]
    
    if not mp3_files:
        print("❌ No se encontraron archivos MP3")
        return
    
    print(f"📢 Utilizando API Externa de Groq (Modelo whisper-large-v3)")
    print(f"   (0 carga en la CPU. Procesamiento rápido en servidor)\n")
    
    total = len(mp3_files)
    for i, mp3 in enumerate(mp3_files, 1):
        order, title = get_audio_title(mp3.name)
        if not order:
            continue
            
        output_file = OUTPUT_DIR / f"{order}_{title.replace(' ', '_')}.txt"
        
        # Saltar si ya fue transcrito
        if output_file.exists():
            print(f"⏭️  [{i}/{total}] {order} {title} — ya transcrito")
            continue
        
        print(f"🎙️  [{i}/{total}] Enviando a servidor: {order} {title} ({mp3.stat().st_size / 1024 / 1024:.1f} MB)... ", end="", flush=True)
        
        try:
            lines, duration, language = transcribe_file_groq(mp3)
            
            # Guardar transcripción
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(f"# {order} — {title}\n")
                f.write(f"# Duración: {duration:.1f}s\n")
                f.write(f"# Idioma reportado: {language}\n")
                f.write(f"# Archivo: {mp3.name}\n")
                f.write(f"# Transcripción vía Groq API\n")
                f.write(f"#\n\n")
                for line in lines:
                    f.write(line + "\n")
            
            print(f"✅ {len(lines)} segmentos, {duration:.0f}s de audio → {output_file.name}")
            
        except Exception as e:
            print(f"\n   ❌ Error: {e}")
            # Si hay error de Rate Limit, detener u obviar
            if "rate limit" in str(e).lower() or "429" in str(e):
                print("   ⚠️ Límite de la API alcanzado. Espera un poco y vuelve a ejecutar el script.")
                break
    
    print(f"\n🏁 Transcripción completada. Archivos en: {OUTPUT_DIR}")

if __name__ == "__main__":
    # Necesita `requests`. Si no está, advertir:
    try:
        import requests
    except ImportError:
        print("❌ Instala la librería 'requests' primero ejecutando: pip install requests")
        sys.exit(1)
        
    main()
