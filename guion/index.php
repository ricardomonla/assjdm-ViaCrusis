<?php require_once 'incs/versionLogs.php'; ?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Via Crucis - Script Editor Ligero</title>
    <!-- Tailwind ligthweight CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Tipografías Cinematográficas -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@400;500;600&family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="css/style.css" rel="stylesheet"/>
    
    <!-- Diseño base exportado de la IA Stitch -->
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "surface-container-lowest": "#ffffff",
              "background": "#fbf9f4",
              "primary": "#5f5e5e",
              "primary-dim": "#535252",
              "on-primary": "#faf7f6",
              "surface-container-low": "#f5f4ed",
              "on-surface": "#31332c",
            },
            fontFamily: {
              "headline": ["Space Grotesk"],
              "body": ["Space Grotesk"],
              "label": ["Inter"],
              "script": ["Courier Prime", "monospace"]
            }
          }
        }
      }
    </script>
</head>
<body class="bg-background font-body text-on-surface antialiased">
    <!-- Barra Superior -->
    <header class="fixed top-0 w-full z-50 bg-[#fbf9f4]/80 backdrop-blur-md flex justify-between items-center px-6 h-16 w-full border-b border-[#f5f4ed]">
        <div class="flex items-center gap-4">
            <h1 class="font-headline font-bold text-lg text-[#2D2D2D] uppercase tracking-widest">
                Via Crucis - Editor Guion <span class="text-xs text-gray-400 font-normal normal-case tracking-normal ml-2 hover:text-blue-500 cursor-pointer" title="Ver Historial de Cambios">v<?= htmlspecialchars($latestGuionVersion) ?></span>
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <span id="save-status" class="text-xs text-gray-500 font-label mr-2">Modo Visual...</span>
            <button id="btn-undo" title="Deshacer (Ctrl+Z)" disabled class="disabled:opacity-30 disabled:cursor-not-allowed px-3 py-2 bg-[#e2e3d9] text-[#5f5e5e] rounded hover:bg-[#d0d1c4] transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm align-middle">undo</span>
            </button>
            <button id="btn-redo" title="Rehacer (Ctrl+Y)" disabled class="disabled:opacity-30 disabled:cursor-not-allowed px-3 py-2 bg-[#e2e3d9] text-[#5f5e5e] rounded hover:bg-[#d0d1c4] transition-all shadow-sm mr-2">
                <span class="material-symbols-outlined text-sm align-middle">redo</span>
            </button>
            <button id="btn-export" title="Compartir a WhatsApp" class="p-2 bg-[#25D366] text-white hover:bg-[#20b858] transition-all shadow-sm rounded-full flex items-center justify-center border border-[#1da850]">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- Lienzo Papel (Vellum) -->
    <main class="pt-24 pb-32 px-4 md:px-0 flex flex-col items-center min-h-screen">
        <article class="w-full max-w-[850px] bg-white digital-vellum-shadow min-h-[1100px] py-20 px-8 md:px-24 mb-12">
            <!-- El contenido dinámico se carga vía parser.js (Markdown) -->
            <div id="script-canvas" class="font-script text-[14px] leading-relaxed text-on-surface pb-32">
                <!-- Se inyectará el contenido aquí -->
            </div>
        </article>
    </main>

    <!-- Motor JavaScript In-Place -->
    <script src="js/editor.js"></script>
    <script src="js/parser.js"></script>
</body>
</html>
