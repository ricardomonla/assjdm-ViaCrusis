<?php
$guionVersionLogs = [
    '1.3' => [
        'date' => '2026-03-29',
        'changes' => [
            'fix(UX): Se aseguró que los títulos de pistas (Audio Tracks) sean estrictamente NO editables para proteger la integridad del esquema.',
            'feat(API): Integradada API Web Share nativa permitiendo compartir el archivo .md final exportado directamente por WhatsApp en dispositivos móviles.',
        ]
    ],
    '1.2' => [
        'date' => '2026-03-29',
        'changes' => [
            'feat(UX): Auto-despliegue de Acordeones Anidados y Auto-Scroll suave durante el Deshacer/Rehacer',
            'feat(UX): Implementado ecosistema de Comandos Deshacer/Rehacer y memoria local de editores (Fase 5)',
            'feat(UX): Modificada semántica de Guion a "Audio XXX" e inicio de Acordeones colapsados por defecto',
        ]
    ],
    '1.1' => [
        'date' => '2026-03-29',
        'changes' => [
            'feat(UX): Implementada persistencia Exportación Inversa DOM-to-MD (Fase 4)',
            'fix(guion): Corregida numeración de tracks de Resurrección (401-403) arrastrada del transcript',
            'feat(UX): Parseo estructural anidado por Grupos y Escenas (Acordeones)',
        ]
    ],
    '1.0' => [
        'date' => '2026-03-28',
        'changes' => [
            'Prototipado visual con Stitch (Aspecto Cinematográfico - Tema Digital Vellum).',
            'Desarrollo del motor Frontend interactivo y Edición In-Place (Vanilla JS).',
            'Parseo de Datos asíncrono desde el Repositorio de Backend.',
        ]
    ]
];

uksort($guionVersionLogs, 'version_compare');
$latestGuionVersion = array_key_last($guionVersionLogs);
$latestGuionDetails = $guionVersionLogs[$latestGuionVersion];
?>
