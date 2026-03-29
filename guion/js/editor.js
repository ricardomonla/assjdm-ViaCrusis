// guion/js/editor.js

document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("script-canvas");
    const statusText = document.getElementById("save-status");
    let undoStack = [];
    let redoStack = [];
    let isEditing = false;

    function updateActionButtons() {
        const btnUndo = document.getElementById("btn-undo");
        const btnRedo = document.getElementById("btn-redo");
        if (btnUndo) btnUndo.disabled = undoStack.length === 0;
        if (btnRedo) btnRedo.disabled = redoStack.length === 0;
    }

    // Phase 2: Interceptar Double Clicks para Edición In-Place (WYSIWYG Ligero)
    canvas.addEventListener("dblclick", (e) => {
        // Encontrar el bloque de texto más cercano con permiso de edición
        const target = e.target.closest('.scene-heading, .action-block, .character-name, .parenthetical, .dialogue');
        
        if (target && !target.isContentEditable) {
            enterEditMode(target);
        }
    });

    function enterEditMode(el) {
        if(isEditing) return; // Prevent multiple concurrent edits fácilmente
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

        const onBlur = function() {
            // Guardar en la pila SI hubo cambio visual sustancial (ignora 'Escape')
            if (el.innerHTML !== originalText) {
                undoStack.push({ element: el, oldHtml: originalText, newHtml: el.innerHTML });
                redoStack = []; // El árbol temporal colapsa ante una nueva rama
                updateActionButtons();
            }
            exitEditMode(el);
            el.removeEventListener("blur", onBlur);
            el.removeEventListener("keydown", onKeyDown);
        };

        const onKeyDown = function(evt) {
            // Escape = Abortar (devuelve texto original y desencadena blur)
            if (evt.key === "Escape") {
                el.innerHTML = originalText;
                el.blur(); 
            }
            // Enter = Guardar si no es un bloque gigantesco (Action/Díalogo)
            if (evt.key === "Enter" && !el.classList.contains("action-block") && !el.classList.contains("dialogue")) {
                evt.preventDefault();
                el.blur();
            }
        };

        // Escuchadores
        el.addEventListener("blur", onBlur);
        el.addEventListener("keydown", onKeyDown);
    }

    function exitEditMode(el) {
        el.setAttribute("contenteditable", "false");
        statusText.innerText = undoStack.length > 0 ? "Cambios Locales Retenidos" : "Sincronizado. Modo Visual.";
        statusText.classList.remove("text-blue-600");
        statusText.classList.add(undoStack.length > 0 ? "text-orange-500" : "text-gray-500");
        isEditing = false;
        
        if (undoStack.length > 0) {
            document.getElementById('btn-export').classList.add('bg-black', 'pulse-animation');
        }
    }

    function undo() {
        if (undoStack.length === 0 || isEditing) return;
        const action = undoStack.pop();
        action.element.innerHTML = action.oldHtml;
        redoStack.push(action);
        updateActionButtons();
        visualFlash(action.element);
    }

    function redo() {
        if (redoStack.length === 0 || isEditing) return;
        const action = redoStack.pop();
        action.element.innerHTML = action.newHtml;
        undoStack.push(action);
        updateActionButtons();
        visualFlash(action.element);
    }

    function visualFlash(el) {
        // Expandir TODOS los acordeones padre (Escena y Grupo) si están cerrados
        let currentElement = el;
        while (currentElement) {
            const parentDetail = currentElement.closest('details');
            if (parentDetail) {
                if (!parentDetail.open) {
                    parentDetail.open = true;
                }
                currentElement = parentDetail.parentElement; // Subir al siguiente ancestro
            } else {
                break;
            }
        }

        // Centralizar la pantalla en el cambio (con un leve delay para permitir al DOM renderizar la apertura)
        setTimeout(() => {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('bg-yellow-200', 'transition-colors', 'duration-300');
        }, 50);

        setTimeout(() => el.classList.remove('bg-yellow-200'), 550);
    }

    // Teclas Globales
    document.addEventListener("keydown", (e) => {
        if (e.ctrlKey && e.key.toLowerCase() === 'z') { e.preventDefault(); undo(); }
        if (e.ctrlKey && e.key.toLowerCase() === 'y') { e.preventDefault(); redo(); }
    });

    const btnUndoBtn = document.getElementById("btn-undo");
    const btnRedoBtn = document.getElementById("btn-redo");
    if(btnUndoBtn) btnUndoBtn.addEventListener("click", undo);
    if(btnRedoBtn) btnRedoBtn.addEventListener("click", redo);

    // Fase 4: Conversor de AST a Markdown y Descarga Local
    function extractScriptMarkdown() {
        let mdParts = [];
        mdParts.push("# Guion Teatral — Vía Crucis 2026");
        mdParts.push("Versión 1.2 (Exportada desde Script Editor WYSIWYG)");
        mdParts.push("");

        // Snapshot states and recursively Open every branch to unlock .innerText API
        const allDetails = canvas.querySelectorAll('details');
        const originalStates = [];
        allDetails.forEach((d, i) => {
            originalStates[i] = d.open;
            d.open = true;
        });

        const tracks = canvas.querySelectorAll('details.track-details');
        
        tracks.forEach(track => {
            mdParts.push("---");
            mdParts.push("");
            
            // Reconstruir Título
            const sceneHeadingSpan = track.querySelector('span.track-title-text');
            mdParts.push(`## ${sceneHeadingSpan.innerText.trim()}`);
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
        
        // Restore DOM states cleanly
        allDetails.forEach((d, i) => {
            d.open = originalStates[i];
        });

        return mdParts.join("\n");
    }

    document.getElementById("btn-export").addEventListener("click", async () => {
        const timestamp = new Date().getTime();
        const filename = `Guion-vcby2026_Editado_${timestamp}.md`;
        const markdownContent = extractScriptMarkdown();
        const markdownBlob = new Blob([markdownContent], { type: "text/markdown" });
        
        const file = new File([markdownBlob], filename, { type: 'text/markdown' });

        // Intentar compartir por WhatsApp / App nativa si es posible
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            try {
                await navigator.share({
                    title: 'Guion Editado Vía Crucis 2026',
                    text: 'Te envío la versión editada del guion.',
                    files: [file]
                });
                statusText.innerText = "Sincronizado y Compartido.";
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error("Error compartmentiendo:", err);
                    triggerDownloadFallback(URL.createObjectURL(markdownBlob), filename);
                } else {
                    statusText.innerText = "Envío cancelado.";
                }
            }
        } else {
            // Fallback en PC antiguos sin Web Share API
            triggerDownloadFallback(URL.createObjectURL(markdownBlob), filename);
        }
        
        // Reset state
        statusText.classList.remove("text-orange-500");
        document.getElementById('btn-export').classList.remove('bg-black', 'pulse-animation');
    });

    function triggerDownloadFallback(url, filename) {
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        statusText.innerText = "Descarga Directa (Fallback).";
    }
});
