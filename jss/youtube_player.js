/**
 * Reproductor de YouTube para VíaCrucis 2025
 * Controla el iframe de YouTube y la navegación por escenas
 */

(function() {
  'use strict';

  let player = null;
  let currentVideoId = null;

  /**
   * Inicializa el reproductor de YouTube
   */
  function initPlayer() {
    // Cargar API de YouTube
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  }

  /**
   * Callback llamado por la API de YouTube cuando está lista
   */
  window.onYouTubeIframeAPIReady = function() {
    const playerContainer = document.getElementById('youtube-player');
    if (!playerContainer) return;

    player = new YT.Player('youtube-player', {
      height: '100%',
      width: '100%',
      videoId: '',
      playerVars: {
        'playsinline': 1,
        'rel': 0,
        'modestbranding': 1
      },
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
      }
    });

    // Llenar el selector de escenas
    populateSceneSelector();
  };

  /**
   * Cuando el reproductor está listo
   */
  function onPlayerReady(event) {
    console.log('Reproductor listo');
  }

  /**
   * Cuando cambia el estado del reproductor
   */
  function onPlayerStateChange(event) {
    // Opcional: actualizar el select cuando el video llega a cierta escena
    if (event.data === YT.PlayerState.PLAYING) {
      updateSceneSelectorFromTime();
    }
  }

  /**
   * Llena el selector con las escenas disponibles
   */
  function populateSceneSelector() {
    const selector = document.getElementById('selector-escenas');
    if (!selector) return;

    // Limpiar opciones existentes
    selector.innerHTML = '';

    // Agrupar por videos
    const grupos = {
      '0XX': { label: '🎬 Intro / Previa', escenas: [] },
      '1XX': { label: '✝️ Primera Parte', escenas: [] },
      '2XX': { label: '🙏 Segunda Parte', escenas: [] },
      '3XX': { label: '🕊️ Tercera Parte', escenas: [] }
    };

    ESCENAS_YOUTUBE.forEach(escena => {
      const grupo = getGrupoFromId(escena.id);
      if (grupos[grupo]) {
        grupos[grupo].escenas.push(escena);
      }
    });

    // Agregar opciones al selector
    for (const [grupoKey, grupo] of Object.entries(grupos)) {
      if (grupo.escenas.length === 0) continue;

      // Optgroup para cada sección
      const optgroup = document.createElement('optgroup');
      optgroup.label = grupo.label;

      grupo.escenas.forEach(escena => {
        const option = document.createElement('option');
        option.value = escena.id;
        option.textContent = `Escena ${escena.id} - ${escena.nombre}`;
        optgroup.appendChild(option);
      });

      selector.appendChild(optgroup);
    }

    // Evento de cambio
    selector.addEventListener('change', function() {
      const escenaId = this.value;
      if (escenaId) {
        loadScene(escenaId);
      }
    });
  }

  /**
   * Obtiene el grupo (0XX, 1XX, etc.) desde el ID de escena
   */
  function getGrupoFromId(escenaId) {
    const primerDigito = escenaId.charAt(0);
    return primerDigito + 'XX';
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

    if (!player || !player.loadVideoById) {
      console.error('Reproductor no listo');
      return;
    }

    // Cambiar de video si es necesario
    if (currentVideoId !== escena.videoId) {
      currentVideoId = escena.videoId;
      player.loadVideoById({
        videoId: escena.videoId,
        startSeconds: escena.timestamp
      });
    } else {
      // Mismo video, solo saltar al timestamp
      player.seekTo(escena.timestamp, true);
      player.playVideo();
    }

    // Actualizar URL con hash para compartir
    window.location.hash = 'escena-' + escenaId;

    console.log('Cargada escena', escenaId, 'en', escena.timestamp + 's');
  }

  /**
   * Actualiza el selector basado en el tiempo actual del video
   * (opcional, para sincronización bidireccional)
   */
  function updateSceneSelectorFromTime() {
    if (!player || !player.getCurrentTime) return;

    const currentTime = player.getCurrentTime();
    const currentVideo = player.getVideoData().video_id;

    // Encontrar la escena actual
    const escenasDelVideo = ESCENAS_YOUTUBE.filter(e => e.videoId === currentVideo);
    let escenaActual = null;

    for (const escena of escenasDelVideo) {
      if (currentTime >= escena.timestamp) {
        escenaActual = escena;
      } else {
        break;
      }
    }

    if (escenaActual) {
      const selector = document.getElementById('selector-escenas');
      if (selector && selector.value !== escenaActual.id) {
        selector.value = escenaActual.id;
      }
    }
  }

  /**
   * Carga escena desde URL hash (para compartir enlaces)
   */
  function loadSceneFromHash() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#escena-')) {
      const escenaId = hash.substring(8);
      // Esperar a que el player esté listo
      setTimeout(() => loadScene(escenaId), 1000);
    }
  }

  // Inicializar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initPlayer();
      loadSceneFromHash();
    });
  } else {
    initPlayer();
    loadSceneFromHash();
  }

  // Exponer función global para carga manual
  window.loadScene = loadScene;

})();
