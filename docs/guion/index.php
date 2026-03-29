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
            <button id="btn-export" class="px-4 py-2 bg-primary text-on-primary font-label text-xs uppercase tracking-widest font-bold hover:bg-primary-dim transition-all shadow-sm rounded">
                Guardar / Exportar
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
