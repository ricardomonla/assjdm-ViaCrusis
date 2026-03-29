// docs/guion/js/parser.js

async function loadScript(filename) {
    const canvas = document.getElementById("script-canvas");
    const statusText = document.getElementById("save-status");
    
    canvas.innerHTML = "<p class='text-center opacity-50 mt-10'>Parseando Markdown a Formato Cinematográfico...</p>";
    statusText.innerText = "Cargando...";

    try {
        // En un entorno PHP real, esto podría venir inyectado o por AJAX.
        // Aquí leemos mediante fetch ya que el .md está en la misma carpeta.
        const response = await fetch(filename);
        if(!response.ok) throw new Error("File not found");
        
        const markdown = await response.text();
        const html = parseMarkdownToVellum(markdown);
        
        canvas.innerHTML = html;
        statusText.innerText = "Sincronizado. Modo Visual.";
    } catch (error) {
        console.error("Error loading script:", error);
        canvas.innerHTML = `<p class='text-red-500 text-center mt-10'>Error al cargar ${filename} (Asegúrate de estar en un servidor web).</p>`;
        statusText.innerText = "Error Lectura";
    }
}

function parseMarkdownToVellum(markdown) {
    const lines = markdown.split('\n');
    let html = '';
    
    let inCharacterBlock = false;
    let dialogueBuffer = [];
    let currentCharacter = "";
    let currentParenthetical = "";

    function flushCharacter() {
        if (inCharacterBlock) {
             html += `
                <div class="character-block flex flex-col items-center mt-6">
                    <div class="w-full flex flex-col items-center group cursor-text p-1 hover:bg-gray-50 transition-colors">
                        <span class="character-name font-bold uppercase mb-1">${currentCharacter}</span>
                        ${currentParenthetical ? `<span class="parenthetical italic text-[13px] mb-1">${currentParenthetical}</span>` : ''}
                        <p class="dialogue text-center w-[85%] md:w-[70%]">
                            ${dialogueBuffer.join('<br>')}
                        </p>
                    </div>
                </div>`;
            inCharacterBlock = false;
            dialogueBuffer = [];
            currentCharacter = "";
            currentParenthetical = "";
        }
    }

    for (let i = 0; i < lines.length; i++) {
        let line = lines[i].trim();
        
        // Saltos horizontales o vacíos puros
        if (line.startsWith('---')) {
            flushCharacter();
            continue;
        }

        // Identificar Escenas (Títulos H1, H2, etc)
        if (line.startsWith('#')) {
            flushCharacter();
            let sceneName = line.replace(/^#+\s*/, '').trim();
            html += `<div class="scene-heading mt-8 mb-6 font-bold uppercase tracking-wide cursor-text p-1 hover:bg-gray-50 transition-colors">${sceneName}</div>`;
            continue;
        }

        // Bloques de Notas o Action Blocks forzados
        if (line.startsWith('>')) {
            flushCharacter();
            html += `<div class="action-block mb-6 cursor-text p-1 hover:bg-gray-50 transition-colors text-gray-500 italic">${line.substring(1).trim()}</div>`;
            continue;
        }

        // Personaje + Timestamp: **NARRADOR** `[00:00]`
        if (line.startsWith('**') && line.includes('**')) {
            flushCharacter();
            
            let nameMatch = line.match(/\*\*(.*?)\*\*/);
            let timeMatch = line.match(/`\[(.*?)\]`/);
            
            if (nameMatch) {
                currentCharacter = nameMatch[1];
                currentParenthetical = timeMatch ? `(${timeMatch[1]})` : '';
                inCharacterBlock = true;
                
                // Extraer ruido como ---???--- si existe en la misma línea
                let restOfLine = line.replace(/\*\*(.*?)\*\*/, '').replace(/`\[(.*?)\]`/, '').replace(/---\?\?\?---/g, '').trim();
                
                if (restOfLine.length > 0) {
                    dialogueBuffer.push(restOfLine);
                }
                continue;
            }
        }

        // Texto huérfano (Action Blocks implícitos)
        if (line.length > 0 && !inCharacterBlock) {
             html += `<div class="action-block mb-6 cursor-text p-1 hover:bg-gray-50 transition-colors">${line}</div>`;
             continue;
        }

        // Acumular diálogo si estamos en bloque de personaje
        if (inCharacterBlock) {
            if (line.length > 0) {
                dialogueBuffer.push(line);
            } else {
                // Línea vacía rompe el bloque de personaje (estilo Screenplay)
                flushCharacter();
            }
        }
    }
    
    // Flush the last one if EOF reached
    flushCharacter();
    
    return html;
}

// Iniciar Parseo al cargar el DOM
document.addEventListener("DOMContentLoaded", () => {
    // Si queremos cargar el real: 
    loadScript('Guion-vcby2026_v1.1.md');
});
