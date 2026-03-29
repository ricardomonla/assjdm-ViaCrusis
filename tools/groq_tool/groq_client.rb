#!/usr/bin/env ruby
# Archivo: tools/groq_tools/groq_client.rb
# Cliente abstraído de IA. Funciona sobre la arquitectura del rotador de llaves.

require_relative 'api_key_rotator/api_key_rotator'
require 'net/http'
require 'json'
require 'uri'

module GroqClient
  @total_tokens = 0
  @total_audio_seconds = 0.0
  @api_calls = 0

  class << self
    attr_accessor :total_tokens, :total_audio_seconds, :api_calls
  end

  def self.chat(system_prompt, user_prompt, model="llama-3.3-70b-versatile")
    uri = URI("https://api.groq.com/openai/v1/chat/completions")
    
    data = execute_with_rotation do |key|
      req = Net::HTTP::Post.new(uri)
      req["Authorization"] = "Bearer #{key}"
      req["Content-Type"] = "application/json"
      req.body = {
        model: model,
        messages: [
          { role: "system", content: system_prompt },
          { role: "user", content: user_prompt }
        ],
        temperature: 0.2
      }.to_json

      Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
        http.request(req)
      end
    end
    
    @api_calls += 1
    @total_tokens += data.dig("usage", "total_tokens") || 0
    
    data.dig("choices", 0, "message", "content")
  end

  def self.transcribe(file_path, prompt="", model="whisper-large-v3")
    uri = URI("https://api.groq.com/openai/v1/audio/transcriptions")
    
    unless File.exist?(file_path)
      raise "Archivo de audio no encontrado: #{file_path}"
    end

    data = execute_with_rotation do |key|
      req = Net::HTTP::Post.new(uri)
      req["Authorization"] = "Bearer #{key}"
      
      form_data = [
        ['model', model],
        ['prompt', prompt],
        ['response_format', 'verbose_json'],
        ['file', File.open(file_path, 'rb'), { filename: File.basename(file_path) }]
      ]
      req.set_form(form_data, 'multipart/form-data')

      Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
        http.request(req)
      end
    end
    
    @api_calls += 1
    @total_audio_seconds += data["duration"].to_f || 0.0
    
    data
  end
end
