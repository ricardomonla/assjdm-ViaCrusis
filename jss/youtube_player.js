/**
 * Reproductor de YouTube para VíaCrucis 2025
 * Controla el iframe de YouTube y la navegación por escenas
 */

(function() {
  'use strict';

  let player = null;
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
      height: '360',
      width: '640',
      videoId: ESCENAS_YOUTUBE.length > 0 ? ESCENAS_YOUTUBE[0].videoId : '',
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

    // Configurar selector de grupos
    setupGroupSelector();

    // Cargar escena desde hash si existe
    loadSceneFromHash();
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
    if (event.data === YT.PlayerState.PLAYING) {
      updateSceneSelectorFromTime();
      // Remover circulo de carga cuando el video comienza a reproducirse
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
  function populateSceneSelector(grupo, autoLoadFirst = false) {
    const selector = document.getElementById('selector-escenas');
    if (!selector) return;

    // Limpiar opciones existentes
    selector.innerHTML = '';

    // Filtrar escenas del grupo activo
    const escenasDelGrupo = ESCENAS_YOUTUBE.filter(e => getGrupoFromId(e.id) === grupo);

    if (escenasDelGrupo.length === 0) {
      selector.innerHTML = '<option value="">-- Sin escenas --</option>';
      return;
    }

    // Agregar escenas sin opción por defecto en blanco para forzar la primera
    escenasDelGrupo.forEach(escena => {
      const option = document.createElement('option');
      option.value = escena.id;
      option.textContent = `Escena ${escena.id} - ${escena.nombre}`;
      selector.appendChild(option);
    });

    // Auto cargar la primera escena si fue gatillado por un cambio manual del usuario en el combo
    if (autoLoadFirst && escenasDelGrupo.length > 0) {
      selector.value = escenasDelGrupo[0].id;
      setTimeout(() => loadScene(escenasDelGrupo[0].id), 200);
    }

    // Mantener evento de cambio (solo se configura una vez)
    if (!selector.dataset.configured) {
      selector.addEventListener('change', function() {
        const escenaId = this.value;
        if (escenaId) {
          loadScene(escenaId);
        }
      });
      selector.dataset.configured = 'true';
    }
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

    // Ocultar el mensaje de bienvenida (overlay) para revelar el video debajo
    const welcome = document.getElementById('welcome-message');
    if (welcome) welcome.style.display = 'none';

    // Cambiar de video si es necesario
    if (currentVideoId !== escena.videoId) {
      const wrapper = document.getElementById('video-wrapper');
      if (wrapper) wrapper.classList.remove('loaded'); // Mostrar spinner
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

    // Seleccionar grupo correspondiente en el dropdown
    activateGroupSelector(getGrupoFromId(escenaId));

    console.log('Cargada escena', escenaId, 'en', escena.timestamp + 's');
  }

  /**
   * Activa el grupo en el selector
   */
  function activateGroupSelector(grupo) {
    const selector = document.getElementById('selector-grupos');
    if (selector) {
      selector.value = grupo;
    }
  }

  /**
   * Actualiza el selector basado en el tiempo actual del video
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
      const escena = ESCENAS_YOUTUBE.find(e => e.id === escenaId);

      if (escena) {
        // Activar el grupo correspondiente primero, pero evitar autoloader porque la ruta hash manda
        const grupo = getGrupoFromId(escenaId);
        activateGroupSelector(grupo);
        populateSceneSelector(grupo, false);

        // Esperar a que el player esté listo y cargar
        setTimeout(() => loadScene(escenaId), 500);
      }
    }
  }

  // Inicializar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPlayer);
  } else {
    initPlayer();
  }

  // Exponer funciones globales para el Director y carga manual
  window.loadScene = loadScene;
  window.vcbyGetPlayer = function() { return player; };

})();
