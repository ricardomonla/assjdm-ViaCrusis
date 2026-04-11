/**
 * Reproductor de YouTube para VíaCrucis 2025
 * Controla el iframe de YouTube y la navegación por escenas
 */

(function() {
  'use strict';

  let player = null;
  let playerReady = false;
  let apiReady = false;
  let currentVideoId = null;
  let grupoActivo = null;

  // Aplanar la base de datos inyectada al formato legacy esperado
  let ESCENAS_YOUTUBE = [];
  if (typeof window.VCBY_SCENES_DB !== 'undefined') {
    window.VCBY_SCENES_DB.forEach(group => {
      group.audios.forEach(audio => {
        if (audio.youtube_video_id) {
            ESCENAS_YOUTUBE.push({
                id: String(audio.scene_id),
                nombre: audio.title,
                videoId: audio.youtube_video_id,
                timestamp: audio.youtube_timestamp
            });
        }
      });
    });
  }

  // Exponer globalmente para el Director inline
  window.ESCENAS_YOUTUBE = ESCENAS_YOUTUBE;

  /**
   * Inicializa la carga de la API de YouTube
   */
  function initPlayer() {
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  }

  /**
   * Callback llamado por la API de YouTube cuando está lista
   */
  window.onYouTubeIframeAPIReady = function() {
    apiReady = true;
    console.log('YouTube API lista');

    // Configurar selector de grupos (no necesita player)
    setupGroupSelector();

    // Cargar escena desde hash si existe
    loadSceneFromHash();
  };

  /**
   * Crea el player. Solo se llama cuando el wrapper es visible.
   */
  function ensurePlayer(videoId, startSeconds) {
    if (player && playerReady) {
      // Ya existe, cargar video
      if (currentVideoId !== videoId) {
        currentVideoId = videoId;
        player.loadVideoById({ videoId: videoId, startSeconds: startSeconds });
      } else {
        player.seekTo(startSeconds, true);
        player.playVideo();
      }
      return;
    }

    if (player && !playerReady) {
      // Player creándose, esperar
      return;
    }

    // Crear player por primera vez (wrapper ya visible)
    currentVideoId = videoId;
    player = new YT.Player('youtube-player', {
      height: '360',
      width: '640',
      videoId: videoId,
      playerVars: {
        'playsinline': 1,
        'rel': 0,
        'modestbranding': 1,
        'start': startSeconds
      },
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
      }
    });
  }

  /**
   * Cuando el reproductor está listo
   */
  function onPlayerReady(event) {
    playerReady = true;
    console.log('Reproductor listo');
  }

  /**
   * Cuando cambia el estado del reproductor
   */
  function onPlayerStateChange(event) {
    if (event.data === YT.PlayerState.PLAYING) {
      updateSceneSelectorFromTime();
      const wrapper = document.getElementById('video-wrapper');
      if (wrapper) wrapper.classList.add('loaded');
    }
  }

  /**
   * Configura el selector de grupos
   */
  function setupGroupSelector() {
    const selector = document.getElementById('selector-grupos');
    if (!selector) return;

    selector.addEventListener('change', function() {
      grupoActivo = this.value;
      populateSceneSelector(grupoActivo, true);
    });
  }

  /**
   * Llena el selector con las escenas del grupo seleccionado
   */
  function populateSceneSelector(grupo, autoLoadFirst) {
    const selector = document.getElementById('selector-escenas');
    if (!selector) return;

    selector.innerHTML = '';

    const escenasDelGrupo = ESCENAS_YOUTUBE.filter(e => getGrupoFromId(e.id) === grupo);

    if (escenasDelGrupo.length === 0) {
      selector.innerHTML = '<option value="">-- Sin escenas --</option>';
      return;
    }

    escenasDelGrupo.forEach(escena => {
      const option = document.createElement('option');
      option.value = escena.id;
      option.textContent = `Escena ${escena.id} - ${escena.nombre}`;
      selector.appendChild(option);
    });

    if (autoLoadFirst && escenasDelGrupo.length > 0) {
      selector.value = escenasDelGrupo[0].id;
      setTimeout(() => loadScene(escenasDelGrupo[0].id), 200);
    }

    if (!selector.dataset.configured) {
      selector.addEventListener('change', function() {
        if (this.value) loadScene(this.value);
      });
      selector.dataset.configured = 'true';
    }
  }

  /**
   * Obtiene el grupo (0XX, 1XX, etc.) desde el ID de escena
   */
  function getGrupoFromId(escenaId) {
    return escenaId.charAt(0) + 'XX';
  }

  /**
   * Carga una escena específica
   */
  function loadScene(escenaId) {
    const escena = ESCENAS_YOUTUBE.find(e => e.id === escenaId);
    if (!escena) {
      console.error('Escena no encontrada:', escenaId);
      return;
    }

    if (!apiReady) {
      console.error('API de YouTube no lista');
      return;
    }

    // 1. Ocultar welcome, mostrar wrapper
    const welcome = document.getElementById('welcome-message');
    const wrapper = document.getElementById('video-wrapper');
    if (welcome) welcome.style.display = 'none';
    if (wrapper) {
      wrapper.style.display = 'block';
      wrapper.classList.remove('loaded');
    }

    // 2. Crear o usar el player (wrapper ya visible)
    setTimeout(() => {
      ensurePlayer(escena.videoId, escena.timestamp);
    }, 100);

    // 3. Actualizar UI
    window.location.hash = 'escena-' + escenaId;
    activateGroupSelector(getGrupoFromId(escenaId));
    console.log('Cargada escena', escenaId, 'en', escena.timestamp + 's');
  }

  /**
   * Activa el grupo en el selector
   */
  function activateGroupSelector(grupo) {
    const selector = document.getElementById('selector-grupos');
    if (selector) selector.value = grupo;
  }

  /**
   * Actualiza el selector basado en el tiempo actual del video
   */
  function updateSceneSelectorFromTime() {
    if (!player || !playerReady || !player.getCurrentTime) return;

    const currentTime = player.getCurrentTime();
    const currentVideo = player.getVideoData().video_id;
    const escenasDelVideo = ESCENAS_YOUTUBE.filter(e => e.videoId === currentVideo);
    let escenaActual = null;

    for (const escena of escenasDelVideo) {
      if (currentTime >= escena.timestamp) escenaActual = escena;
      else break;
    }

    if (escenaActual) {
      const selector = document.getElementById('selector-escenas');
      if (selector && selector.value !== escenaActual.id) {
        selector.value = escenaActual.id;
      }
    }
  }

  /**
   * Carga escena desde URL hash
   */
  function loadSceneFromHash() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#escena-')) {
      const escenaId = hash.substring(8);
      const escena = ESCENAS_YOUTUBE.find(e => e.id === escenaId);
      if (escena) {
        const grupo = getGrupoFromId(escenaId);
        activateGroupSelector(grupo);
        populateSceneSelector(grupo, false);
        setTimeout(() => loadScene(escenaId), 500);
      }
    }
  }

  // Inicializar
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPlayer);
  } else {
    initPlayer();
  }

  // Exponer globales para Director
  window.loadScene = loadScene;
  window.vcbyGetPlayer = function() { return player; };

})();
