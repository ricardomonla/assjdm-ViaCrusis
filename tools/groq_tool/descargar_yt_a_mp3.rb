#!/usr/bin/env ruby
# Archivo: tools/groq_tool/descargar_yt_a_mp3.rb

require 'fileutils'

module DescargarYTaMP3
  def self.obtener_audio(url, output_dir)
    FileUtils.mkdir_p(output_dir)
    
    # Extraer ID del video para caché inteligente
    video_id = if url.include?("v=")
                 url.split("v=").last.split("&").first
               elsif url.include?("youtu.be/")
                 url.split("/").last.split("?").first
               else
                 "video_#{rand(10000)}"
               end
               
    audio_final = File.join(output_dir, "#{video_id}.mp3")
    
    if File.exist?(audio_final) && File.size(audio_final) > 10000
      puts "1️⃣  [CACHÉ] Audio '#{video_id}' ya existe. Saltando descarga."
      return audio_final
    end

    tool_tmp_dir = File.join(File.dirname(__FILE__), "tmp")
    FileUtils.mkdir_p(tool_tmp_dir)
    
    # Validar dependencia yt-dlp usando la carpeta tmp de la herramienta
    yt_bin = File.join(tool_tmp_dir, "yt-dlp")
    yt_dlp_cmd = system("command -v yt-dlp > /dev/null 2>&1") ? "yt-dlp" : yt_bin
    
    if yt_dlp_cmd == yt_bin && !File.exist?(yt_bin)
      puts "   ⏬ Instalando motor local de yt-dlp dentro de la tool..."
      system("wget -q https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -O '#{yt_bin}'")
      system("chmod a+rx '#{yt_bin}'")
    end

    puts "1️⃣  Descargando audio original con yt-dlp..."
    
    temp_path = File.join(output_dir, "audio_bruto")
    system("#{yt_dlp_cmd} -f 'bestaudio/best' --extract-audio --audio-format mp3 -o '#{temp_path}.%(ext)s' '#{url}' --quiet --no-warnings")

    descargado = Dir.glob("#{temp_path}.*").first
    if descargado && File.exist?(descargado)
      File.rename(descargado, audio_final)
      puts "   ✅ Audio MP3 listo: #{audio_final}"
      return audio_final
    else
      raise "Error crítico: falló la descarga de YouTube."
    end
  end
end

if __FILE__ == $0 && ARGV.length > 0
  DescargarYTaMP3.obtener_audio(ARGV[0], ARGV[1] || File.join(File.dirname(__FILE__), "tmp", "audios_yt"))
end
