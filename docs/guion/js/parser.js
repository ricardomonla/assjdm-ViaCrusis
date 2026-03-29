// docs/guion/js/parser.js

const GROUPS = {
    '0': { name: 'Desfile', icon: '🎭' },
    '1': { name: 'La Pasión', icon: '⛪' },
    '2': { name: 'Calvario', icon: '✝️' },
    '3': { name: 'Crucifixión', icon: '🕊️' },
    '4': { name: 'La Resurrección', icon: '🌅' }
};

async function loadScript(filename) {
    const canvas = document.getElementById("script-canvas");
    const statusText = document.getElementById("save-status");
    
    canvas.innerHTML = "<p class='text-center opacity-50 mt-10'>Parseando Markdown a Formato Cinematográfico...</p>";
    statusText.innerText = "Cargando...";

    try {
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

    // Agrupaciones Globales para Acordeones ("Desplegables")
    let currentGroupId = null;
    let inTrackBlock = false;

    function flushCharacter() {
        if (inCharacterBlock) {
             html += `
                <div class="character-block flex flex-col items-center mt-6">
                    <div class="w-full flex flex-col items-center group cursor-text p-1 hover:bg-gray-50 transition-colors">
                        <span class="character-name font-bold uppercase mb-1">${currentCharacter}</span>
                        ${currentParenthetical ? `<span class="parenthetical italic text-[13px] mb-1">${currentParenthetical}</span>` : ''}
                        <p class="dialogue text-center w-[85%] md:w-[70%] text-balance">
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
    
    function closeTrackBlock() {
        flushCharacter();
        if(inTrackBlock) {
            html += `</div></details>`; // Close track Details
            inTrackBlock = false;
        }
    }
    
    function closeGroupBlock() {
        closeTrackBlock();
        if(currentGroupId !== null) {
            html += `</div></details>`; // Close Group Details
            currentGroupId = null;
        }
    }

    for (let i = 0; i < lines.length; i++) {
        let line = lines[i].trim();
        
        // --- Separadores horizontales ---
        if (line.startsWith('---')) {
            flushCharacter();
            continue;
        }

        // --- H1 Principal ---
        if (line.startsWith('#') && !line.startsWith('##')) {
            flushCharacter();
            let mainTitle = line.replace(/^#\s*/, '').trim();
            html += `<h1 class="text-center font-bold text-2xl uppercase mb-10 pb-4 border-b">${mainTitle}</h1>`;
            continue;
        }

        // --- Tracks (Escenas) ---
        if (line.startsWith('## ')) {
            closeTrackBlock();
            
            // Expected '## Track 000: Desfile...'
            let trackMatch = line.match(/Track\s+(\d{3})/i);
            let trackId = trackMatch ? trackMatch[1] : null;
            
            // Detectar cambio de grupo principal
            if (trackId !== null) {
                let groupPrefix = trackId.substring(0, 1);
                if (groupPrefix !== currentGroupId) {
                    closeGroupBlock();
                    currentGroupId = groupPrefix;
                    let groupDef = GROUPS[groupPrefix] || {name: 'Otros', icon: '📁'};
                    
                    html += `
                    <details class="group-details mb-6" ${groupPrefix === '0' || groupPrefix === '1' ? 'open' : ''}>
                        <summary class="group-summary flex items-center gap-3 p-3 bg-[#e2e3d9] font-bold text-lg uppercase tracking-wider cursor-pointer rounded-t hover:bg-[#d9dbcf] transition-all">
                            <span>${groupDef.icon}</span> ${groupDef.name}
                        </summary>
                        <div class="group-content p-4 border-x border-b border-[#e2e3d9] bg-[#fbf9f4]/50 rounded-b shadow-sm">
                    `;
                }
            } else if (currentGroupId === null) {
                // Failsafe if not using strict Track XXX
                html += `<div class="group-content p-4">`;
            }

            // Iniciar Acordeón de Track
            let trackName = line.replace(/^##\s*/, '').trim();
            html += `
            <details class="track-details mb-4 border border-[#e2e3d9] rounded" open>
                <summary class="scene-heading bg-white font-bold uppercase tracking-wide cursor-pointer p-3 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm opacity-50">movie</span>
                    ${trackName}
                </summary>
                <div class="track-inner-content p-4 bg-white/60">
            `;
            inTrackBlock = true;
            continue;
        }

        // --- Notas / Bloques Acotativos ---
        if (line.startsWith('>')) {
            flushCharacter();
            html += `<div class="action-block mb-6 cursor-text p-2 hover:bg-gray-50 transition-colors text-gray-500 italic bg-gray-50/50 rounded">${line.substring(1).trim()}</div>`;
            continue;
        }

        // --- Personaje + Diálogo ---
        if (line.startsWith('**') && line.includes('**')) {
            flushCharacter();
            
            let nameMatch = line.match(/\*\*(.*?)\*\*/);
            let timeMatch = line.match(/`\[(.*?)\]`/);
            
            if (nameMatch) {
                currentCharacter = nameMatch[1];
                currentParenthetical = timeMatch ? `(${timeMatch[1]})` : '';
                inCharacterBlock = true;
                
                let restOfLine = line.replace(/\*\*(.*?)\*\*/, '').replace(/`\[(.*?)\]`/, '').replace(/---\?\?\?---/g, '').trim();
                
                if (restOfLine.length > 0) {
                    dialogueBuffer.push(restOfLine);
                }
                continue;
            }
        }

        // --- Acción general (Action Blocks) ---
        if (line.length > 0 && !inCharacterBlock) {
             html += `<div class="action-block mb-6 cursor-text p-1 hover:bg-gray-50 transition-colors">${line}</div>`;
             continue;
        }

        // --- Acumular texto a Diálogo ---
        if (inCharacterBlock) {
            if (line.length > 0) {
                dialogueBuffer.push(line);
            } else {
                flushCharacter();
            }
        }
    }
    
    closeGroupBlock(); // Cerrar todo al final
    
    return html;
}

document.addEventListener("DOMContentLoaded", () => {
    loadScript('Guion-vcby2026_v1.1.md');
});
