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

    // Botón de exportar
    document.getElementById("btn-export").addEventListener("click", () => {
        alert("Integración del Guardado / Re-escritura (Fase 4) en cola...");
        statusText.innerText = "Sincronizado. Modo Visual.";
        statusText.classList.remove("text-orange-500");
    });
});
