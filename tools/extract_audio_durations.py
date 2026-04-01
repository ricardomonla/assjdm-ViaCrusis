import os
import json
import subprocess

media_folder = '../audios/media/'
output_file = '../audios/subs/audio_durations.json'

durations = {}

for f in sorted(os.listdir(media_folder)):
    if f.endswith('.mp3'):
        path = os.path.join(media_folder, f)
        base_id = f.split('_')[0]
        try:
            cmd = ['ffprobe', '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', path]
            result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
            duration = float(result.stdout.strip())
            durations[base_id] = duration
            print(f"[{base_id}] -> {duration:.2f} s")
        except Exception as e:
            print(f"Error reading {f}: {e}")

with open(output_file, 'w', encoding='utf-8') as out:
    json.dump(durations, out, indent=2)

print(f"Saved {len(durations)} durations to {output_file}")
