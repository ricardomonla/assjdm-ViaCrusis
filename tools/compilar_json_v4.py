#!/usr/bin/env python3
"""
Compilador v4.0 → guion_completo.json
Lee directamente de los archivos audios/subs/XXX_v4.0.md (fuente autoritativa)
e incluye el campo `idp` en cada cue para el sistema de perfiles Director.

Uso: python3 tools/compilar_json_v4.py
"""

import os
import re
import json
import glob

SUBS_DIR = os.path.join(os.path.dirname(__file__), '..', 'audios', 'subs')
OUT_FILE = os.path.join(SUBS_DIR, 'guion_completo.json')

def parse_time_mark(mark_str):
    """Parse [XXX.HH.MM.SS] → seconds float"""
    # Formato: [101.00.00.14] → track.HH.MM.SS
    parts = mark_str.split('.')
    if len(parts) < 4:
        return 0.0
    # parts[0] = track ID, parts[1] = HH, parts[2] = MM, parts[3] = SS
    hh = int(parts[1])
    mm = int(parts[2])
    ss = int(parts[3])
    return hh * 3600 + mm * 60 + ss

def parse_v4_md(filepath):
    """Parse a v4.0.md file, extract subtitles with IDP."""
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    # Build character map from section 1
    char_map = {}  # IDP → NOMBRE
    in_personajes = False
    in_subtitulos = False
    subs = []

    for line in lines:
        line = line.strip()

        # Detect sections
        if line.startswith('## 1.'):
            in_personajes = True
            in_subtitulos = False
            continue
        if line.startswith('## 2.'):
            in_personajes = False
            in_subtitulos = True
            continue
        if line.startswith('## ') and not line.startswith('## 1.') and not line.startswith('## 2.'):
            in_personajes = False
            in_subtitulos = False
            continue

        # Parse personajes table
        if in_personajes and line.startswith('|'):
            cols = [c.strip() for c in line.split('|')]
            cols = [c for c in cols if c]
            if len(cols) >= 2 and cols[0].startswith('P') and cols[0] != 'IDP':
                idp = cols[0]
                nombre = cols[1]
                char_map[idp] = nombre

        # Parse subtitulos table
        if in_subtitulos and line.startswith('|'):
            cols = [c.strip() for c in line.split('|')]
            cols = [c for c in cols if c]
            if len(cols) >= 3 and cols[0].startswith('['):
                mark_str = cols[0].strip('[]')
                idp = cols[1]
                text = cols[2] if len(cols) > 2 else ''
                
                start_time = parse_time_mark(mark_str)
                character = char_map.get(idp, idp)

                subs.append({
                    "character": character,
                    "idp": idp,
                    "startTime": float(start_time),
                    "endTime": float(start_time) + 10.0,  # Placeholder, will be refined
                    "text": text
                })

    # Refine endTime: each cue ends when the next one starts
    for i in range(len(subs) - 1):
        next_start = subs[i + 1]['startTime']
        gap = next_start - subs[i]['startTime']
        if gap > 15.0:
            subs[i]['endTime'] = subs[i]['startTime'] + 15.0
        else:
            subs[i]['endTime'] = next_start

    return subs

def main():
    # Find all v4.0.md files
    pattern = os.path.join(SUBS_DIR, '*_v4.0.md')
    files = sorted(glob.glob(pattern))

    guion = {}
    total_cues = 0

    for filepath in files:
        basename = os.path.basename(filepath)
        # Extract track ID: "101_v4.0.md" → "101"
        track_id = basename.split('_')[0]
        
        # Skip non-numeric (e.g., "00_Personajes.md" won't match pattern)
        if not track_id.isdigit():
            continue

        subs = parse_v4_md(filepath)
        if subs:
            guion[track_id] = subs
            total_cues += len(subs)
            print(f"  ✓ {track_id}: {len(subs)} cues ({basename})")

    # Write JSON
    with open(OUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(guion, f, ensure_ascii=False, indent=2)

    print(f"\n✅ guion_completo.json compilado: {len(guion)} tracks, {total_cues} cues totales.")
    print(f"   Archivo: {OUT_FILE}")

if __name__ == '__main__':
    main()
