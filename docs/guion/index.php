<!DOCTYPE html>
<html class="light" lang="en">
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
                Via Crucis - Editor Guion
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <span id="save-status" class="text-xs text-gray-500 font-label mr-2">Modo Visual...</span>
            <button id="btn-export" class="px-4 py-2 bg-primary text-on-primary font-label text-xs uppercase tracking-widest font-bold hover:bg-primary-dim transition-all shadow-sm">
                Guardar / Exportar
            </button>
        </div>
    </header>

    <!-- Lienzo Papel (Vellum) -->
    <main class="pt-24 pb-32 px-4 md:px-0 flex flex-col items-center min-h-screen">
        <article class="w-full max-w-[850px] bg-white digital-vellum-shadow min-h-[1100px] py-20 px-8 md:px-24 mb-12">
            <!-- El contenido dinámico va aquí, renderizado en script.js -->
            <div id="script-canvas" class="font-script text-[14px] leading-relaxed text-on-surface">
                <!-- Data Placeholder temporal -->
                <div class="scene-heading mt-8 mb-6 font-bold uppercase tracking-wide cursor-text p-1 hover:bg-gray-50 transition-colors">
                    EXT. CALVARIO - TARDE
                </div>
                <div class="action-block mb-6 cursor-text p-1 hover:bg-gray-50 transition-colors">
                    El viento sopla fuerte, levantando polvo sobre las cabezas de la muchedumbre.
                </div>
                
                <div class="character-block flex flex-col items-center mt-6">
                    <div class="w-full flex flex-col items-center group cursor-text p-1 hover:bg-gray-50 transition-colors">
                        <span class="character-name font-bold uppercase mb-1">NARRADOR</span>
                        <span class="parenthetical italic text-[13px] mb-1">(con voz sollozante)</span>
                        <p class="dialogue text-center w-[85%] md:w-[70%]">
                            Aquí es donde el rey de los Judíos encontraría su destino, en la cima más árida...
                        </p>
                    </div>
                </div>
            </div>
        </article>
    </main>

    <!-- Motor JavaScript In-Place -->
    <script src="js/editor.js"></script>
</body>
</html>
