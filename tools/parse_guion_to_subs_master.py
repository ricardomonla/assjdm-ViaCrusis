import os
import json

def parse_time(time_str):
    parts = time_str.split(':')
    if len(parts) == 2:
        return int(parts[0]) * 60 + int(parts[1])
    elif len(parts) == 3:
        return int(parts[0]) * 3600 + int(parts[1]) * 60 + int(parts[2])
    return 0

def convert_md_to_json():
    md_path = '../guion/Guion-vcby.md'
    media_dir = '../audios/media'
    out_file = '../audios/subs/guion_completo.json'
    
    os.makedirs(os.path.dirname(out_file), exist_ok=True)
    
    media_files = {}
    if os.path.exists(media_dir):
        for f in os.listdir(media_dir):
            if f.endswith('.mp3'):
                base_id = f.split('_')[0]
                media_files[base_id] = f.replace('.mp3', '')
    
    with open(md_path, 'r', encoding='utf-8') as file:
        lines = file.readlines()
        
    guion_maestro = {}
        
    current_audio_id = None
    current_subs = []
    
    current_char = None
    current_time = 0
    current_text = []
    
    def save_current_sub():
        nonlocal current_char, current_time, current_text, current_subs
        if current_char and current_text:
            text_str = "<br>".join(current_text).strip()
            # Clean up
            text_str = text_str.replace('Gracias por ver el video.', '').replace('Gracias por ver el video', '')
            text_str = text_str.replace('---???---', '').strip()
            if text_str:
                current_subs.append({
                    "character": current_char,
                    "startTime": current_time,
                    "endTime": current_time + 10.0,
                    "text": text_str
                })
        current_char = None
        current_text = []

    def save_audio():
        nonlocal current_audio_id, current_subs, guion_maestro
        if current_audio_id and current_subs:
            for i in range(len(current_subs) - 1):
                end_time = current_subs[i+1]['startTime']
                if end_time - current_subs[i]['startTime'] > 15.0:
                    current_subs[i]['endTime'] = current_subs[i]['startTime'] + 15.0
                else:
                    current_subs[i]['endTime'] = end_time
            
            file_name = media_files.get(current_audio_id, current_audio_id)
            guion_maestro[current_audio_id] = current_subs
            
        current_audio_id = None
        current_subs = []

    for line in lines:
        line = line.strip()
        
        if line.startswith('## Audio '):
            save_current_sub()
            save_audio()
            parts = line.split(':', 1)
            raw_id = parts[0].replace('## Audio', '').strip()
            current_audio_id = raw_id
            continue
            
        if not current_audio_id:
            continue
            
        if line.startswith('**') and '`[' in line and ']`' in line:
            save_current_sub()
            try:
                char_part, time_part = line.split('`[')
                current_char = char_part.replace('**', '').strip()
                time_str = time_part.split(']`')[0].strip()
                current_time = parse_time(time_str)
                
                rest = line.split(']`', 1)[1].strip()
                if rest:
                    current_text.append(rest)
            except Exception as e:
                pass
            continue
            
        if line.startswith('---') or line.startswith('>'):
            save_current_sub()
            continue
            
        if line and current_char is not None:
            current_text.append(line)

    save_current_sub()
    save_audio()
    
    with open(out_file, 'w', encoding='utf-8') as f:
        json.dump(guion_maestro, f, ensure_ascii=False, indent=2)
    print(f"✨ Creado guion_completo.json con {len(guion_maestro)} bloques de audio.")

if __name__ == '__main__':
    convert_md_to_json()
