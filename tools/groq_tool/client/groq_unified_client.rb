# frozen_string_literal: true
#
# client/groq_unified_client.rb
# Cliente unificado para Groq API con rotación de keys
#

require_relative '../api_key_rotator/api_key_rotator'
require 'net/http'
require 'json'
require 'uri'
require 'ostruct'

class GroqUnifiedClient
  def initialize
    @rotator = ApiRotator
  end

  # Método principal para chat/completions
  def chat(model:, system_prompt:, user_prompt:, temperature: 0.5, max_tokens: nil, stream: false)
    uri = URI("https://api.groq.com/openai/v1/chat/completions")

    data = execute_with_rotation do |key|
      req = Net::HTTP::Post.new(uri)
      req["Authorization"] = "Bearer #{key}"
      req["Content-Type"] = "application/json"

      body = {
        model: model,
        messages: [
          { role: "system", content: system_prompt },
          { role: "user", content: user_prompt }
        ],
        temperature: temperature
      }

      body[:max_tokens] = max_tokens if max_tokens

      req.body = body.to_json

      Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
        http.read_timeout = 180
        http.request(req)
      end
    end

    # Extraer contenido de la respuesta
    if data.is_a?(Hash)
      data.dig("choices", 0, "message", "content") || "⚠️ Sin respuesta"
    else
      raise "Error en API: #{data.inspect}"
    end
  end

  # Método para transcripción de audio
  def transcribe(file_path, model: "whisper-large-v3", language: "es", prompt: nil)
    unless File.exist?(file_path)
      raise "Archivo no encontrado: #{file_path}"
    end

    uri = URI("https://api.groq.com/openai/v1/audio/transcriptions")

    data = execute_with_rotation do |key|
      req = Net::HTTP::Post.new(uri)
      req["Authorization"] = "Bearer #{key}"

      form_data = [
        ['model', model],
        ['prompt', prompt],
        ['response_format', 'verbose_json'],
        ['language', language],
        ['file', File.open(file_path, 'rb'), { filename: File.basename(file_path) }]
      ]
      form_data.delete_at(1) unless prompt # Eliminar prompt si es nil

      req.set_form(form_data, 'multipart/form-data')

      Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
        http.read_timeout = 300 # 5 min para audio largo
        http.request(req)
      end
    end

    data
  end

  # Método genérico para llamadas directas a la API
  def raw_request(endpoint:, model:, payload:)
    uri = URI("https://api.groq.com/openai/v1#{endpoint}")

    execute_with_rotation do |key|
      req = Net::HTTP::Post.new(uri)
      req["Authorization"] = "Bearer #{key}"
      req["Content-Type"] = "application/json"
      req.body = payload.merge(model: model).to_json

      Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
        http.read_timeout = 180
        http.request(req)
      end
    end
  end

  private

  # Ejecuta con rotación automática de API keys
  def execute_with_rotation(&block)
    @rotator.execute_with_rotation(&block)
  end
end
