#!/usr/bin/env python3
"""
Transcribe todos los MP3 del Via Crucis usando faster-whisper.
Genera archivos .txt individuales en docs/guion/transcripciones/
"""

import os
import sys
import re
from pathlib import Path
from faster_whisper import WhisperModel

# Configuración
MEDIA_DIR = Path(__file__).parent.parent / "media"
OUTPUT_DIR = Path(__file__).parent.parent / "docs" / "guion" / "transcripciones"
MODEL_SIZE = "medium"  # Buen balance velocidad/precisión para español en CPU
LANGUAGE = "es"

def format_timestamp(seconds):
    """Convierte segundos a formato [MM:SS]"""
    minutes = int(seconds // 60)
    secs = int(seconds % 60)
    return f"[{minutes:02d}:{secs:02d}]"

def transcribe_file(model, audio_path):
    """Transcribe un archivo MP3 y retorna el texto con timestamps."""
    segments, info = model.transcribe(
        str(audio_path),
        language=LANGUAGE,
        beam_size=5,
        word_timestamps=False,
        vad_filter=True,
    )
    
    lines = []
    for segment in segments:
        ts = format_timestamp(segment.start)
        text = segment.text.strip()
        if text:
            lines.append(f"{ts} {text}")
    
    return lines, info

def get_audio_title(filename):
    """Extrae el título limpio del nombre de archivo."""
    match = re.match(r'^(\d{3})_v\d{4}_(.+)\.mp3$', filename)
    if match:
        order = match.group(1)
        title = match.group(2).replace('_', ' ')
        return order, title
    return None, filename

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
    
    print(f"📢 Cargando modelo '{MODEL_SIZE}'...")
    print(f"   (Primera vez descarga ~3GB, luego usa cache)")
    model = WhisperModel(MODEL_SIZE, device="cpu", compute_type="int8")
    print(f"✅ Modelo cargado\n")
    
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
        
        print(f"🎙️  [{i}/{total}] Transcribiendo: {order} {title}...")
        
        try:
            lines, info = transcribe_file(model, mp3)
            
            # Guardar transcripción
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(f"# {order} — {title}\n")
                f.write(f"# Duración: {info.duration:.1f}s\n")
                f.write(f"# Idioma detectado: {info.language} ({info.language_probability:.0%})\n")
                f.write(f"# Archivo: {mp3.name}\n")
                f.write(f"#\n\n")
                for line in lines:
                    f.write(line + "\n")
            
            duration = info.duration
            print(f"   ✅ {len(lines)} segmentos, {duration:.0f}s de audio → {output_file.name}")
            
        except Exception as e:
            print(f"   ❌ Error: {e}")
    
    print(f"\n🏁 Transcripción completada. Archivos en: {OUTPUT_DIR}")

if __name__ == "__main__":
    main()
