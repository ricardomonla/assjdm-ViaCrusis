php_file = "../../incs/versionLogs.php"
content = File.read(php_file)

# We want to remove everything from '26.6.40' down to the line before '26.5.0'
# and replace it with '26.6'
if content =~ /(    '26\.6\.40' => \[.*?\n)(    '26\.5\.0' => \[)/m
  new_chunk = <<~LOG
    '26.6' => [
        'date' => '2026-04-02',
        'changes' => [
            'HITO CRÍTICO: Compilación de subtítulos HITL completada al 100% (Pistas 101 a 403) en mega-JSON maestro.',
            'Implementación de Reproductor Web tipo Karaoke: autoscroll inteligente, UI teatral, historial dinámico y caché.',
            'Automatización IA: Pipeline estructurado de 5 fases usando Whisper (transcripción) y LLaMA 3.3 (deducción MD).',
            'Catálogo Literario: Consolidación semiótica de personajes (25 roles) e ingesta canónica en el script principal.',
            'UX y System Triage: Persistencia de volumen vía localStorage, corrección del footer sticky y CSS responsive.'
        ]
    ],
LOG
  content.sub!(/(    '26\.6\.40' => \[.*?\n)(    '26\.5\.0' => \[)/m, new_chunk + '\2')
  File.write(php_file, content)
  puts "Logs successfully cleaned!"
else
  puts "Failed to match the regex for 26.6.40 to 26.5.0"
end
