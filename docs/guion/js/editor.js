// docs/guion/js/editor.js

document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("script-canvas");
    const statusText = document.getElementById("save-status");
    let isEditing = false;

    // Phase 2: Interceptar Double Clicks para Edición In-Place (WYSIWYG Ligero)
    canvas.addEventListener("dblclick", (e) => {
        // Encontrar el bloque de texto más cercano con permiso de edición
        const target = e.target.closest('.scene-heading, .action-block, .character-name, .parenthetical, .dialogue');
        
        if (target && !target.isContentEditable) {
            enterEditMode(target);
        }
    });

    function enterEditMode(el) {
        if(isEditing) return; // Prevent multiple concurrent edits easily
        isEditing = true;
        
        const originalText = el.innerHTML;
        el.setAttribute("contenteditable", "true");
        statusText.innerText = "Modo Edición Activado...";
        statusText.classList.add("text-blue-600");

        // Set cursor at the end of the text naturally
        const range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        el.focus();

        // Salir al perder el foco
        el.addEventListener("blur", function onBlur() {
            exitEditMode(el);
            el.removeEventListener("blur", onBlur);
        });

        // Manejar teclas rápidas de salida
        el.addEventListener("keydown", function onKeyDown(evt) {
            // Escape = Abortar
            if (evt.key === "Escape") {
                el.innerHTML = originalText;
                el.blur(); // Triggers blur which calls exit
            }
            // Enter = Guardar si no es un bloque de Acción/Díalogo muy extenso
            if (evt.key === "Enter" && !el.classList.contains("action-block") && !el.classList.contains("dialogue")) {
                evt.preventDefault();
                el.blur();
            }
        });
    }

    function exitEditMode(el) {
        el.setAttribute("contenteditable", "false");
        statusText.innerText = "Cambios Locales Retenidos (Sin exportar)";
        statusText.classList.remove("text-blue-600");
        statusText.classList.add("text-orange-500");
        isEditing = false;
        
        // Fase 4 simulación (marcar que el DOM está sucio y debe guardarse)
        document.getElementById('btn-export').classList.add('bg-black', 'pulse-animation');
    }

    // Fase 4: Conversor de AST a Markdown y Descarga Local
    function extractScriptMarkdown() {
        let mdParts = [];
        mdParts.push("# Guion Teatral — Vía Crucis 2026");
        mdParts.push("Versión 1.2 (Exportada desde Script Editor WYSIWYG)");
        mdParts.push("");

        const tracks = canvas.querySelectorAll('details.track-details');
        
        tracks.forEach(track => {
            mdParts.push("---");
            mdParts.push("");
            
            // Reconstruir Título
            const summaryClone = track.querySelector('summary.scene-heading').cloneNode(true);
            const icon = summaryClone.querySelector('span.material-symbols-outlined');
            if (icon) icon.remove();
            
            mdParts.push(`## ${summaryClone.innerText.trim()}`);
            mdParts.push("");
            
            // Reconstruir Contenido Dinámico
            const innerContent = track.querySelector('.track-inner-content');
            innerContent.childNodes.forEach(node => {
                if (node.nodeType === 1) { // Element Node
                    if (node.classList.contains('action-block')) {
                        let text = node.innerText.trim();
                        // Revertir a nota direction block (>) si era itálica/gris
                        if (node.classList.contains('italic') && node.classList.contains('text-gray-500')) {
                            mdParts.push(`> ${text}\n`);
                        } else {
                            mdParts.push(`${text}\n`);
                        }
                    } else if (node.classList.contains('character-block')) {
                        const charName = node.querySelector('.character-name')?.innerText.trim() || '';
                        
                        // Parsear Parenthetical (00:00) a Metadata `[00:00]`
                        const parenthSpan = node.querySelector('.parenthetical');
                        let timestampStr = '';
                        if (parenthSpan) {
                             let textParen = parenthSpan.innerText.trim().replace(/^\(|\)$/g, ''); 
                             timestampStr = ` \`[${textParen}]\``;
                        }
                        
                        const dialogueP = node.querySelector('.dialogue');
                        let dialogueTxt = dialogueP ? dialogueP.innerText.trim() : '';
                        
                        if (charName) {
                            mdParts.push(`**${charName}**${timestampStr}`);
                            if (dialogueTxt) mdParts.push(dialogueTxt);
                            mdParts.push("");
                        }
                    }
                }
            });
        });
        
        return mdParts.join("\n");
    }

    document.getElementById("btn-export").addEventListener("click", () => {
        const markdownBlob = new Blob([extractScriptMarkdown()], { type: "text/markdown" });
        const url = URL.createObjectURL(markdownBlob);
        
        const a = document.createElement("a");
        a.href = url;
        a.download = `Guion-vcby2026_Editado_${new Date().getTime()}.md`;
        
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        // Reset state
        statusText.innerText = "Sincronizado. Descarga Iniciada.";
        statusText.classList.remove("text-orange-500");
        document.getElementById('btn-export').classList.remove('bg-black', 'pulse-animation');
    });
});
