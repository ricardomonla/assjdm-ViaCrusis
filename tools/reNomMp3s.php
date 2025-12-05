<?php
// Asegurar que se ejecuta desde la línea de comandos
if (php_sapi_name() !== "cli") {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}

// Array con los datos: [nombre_actual, nuevo_nombre, texto_metadato]
$archivos = [
    [
        "101v25-1_La_Entrada_en_Jerusalen.mp3", 
        "101v25-1_La_entrada_de_Jesús_en_Jerusalén.mp3", 
        "101v25-1 - La entrada de Jesús en Jerusalén"
    ],
    [
        "102v25-1_El_Trato_de_Judas_y_Caifás.mp3", 
        "102v25-1_El_Trato_de_Judas_y_Caifás.mp3", 
        "102v25-1 - El Trato de Judas y Caifás"
    ],
    [
        "103v25-6_La_Última_Cena+Monedas_Adeje16.mp3", 
        "103v25-6_La_Última_Cena.mp3", 
        "103v25-6 - La Última Cena"
    ],
    [
        "104v25-1_El_Monte_de_los_olivos.mp3", 
        "104v25-1_La_oración_en_el_Monte_de_los_Olivos.mp3", 
        "104v25-1 - La oración en el Monte de los Olivos"
    ],
    [
        "105v25-1_La_Entrega.mp3", 
        "105v25-1_La_Entrega.mp3", 
        "105v25-1 - La Entrega"
    ],
    [
        "106v25-1_La_Negación_de_Pedro.mp3", 
        "106v25-1_Las_negaciones_de_Pedro.mp3", 
        "106v25-1 - Las negaciones de Pedro"
    ],
    [
        "107v25-1_El_Juicio_en_San_Edrin.mp3", 
        "107v25-1_El_juicio_en_el_Sanedrín.mp3", 
        "107v25-1 - El juicio en el Sanedrín"
    ],
    [
        "108v25-1_La_Culpa_de_Judas.mp3", 
        "108v25-1_La_Culpa_de_Judas.mp3", 
        "108v25-1 - La Culpa de Judas"
    ],
    [
        "109v25-1_Jesús_Ante_Ponsio_Pilatos.mp3", 
        "109v25-1_El_lavado_de_las_manos_de_Pilatos.mp3", 
        "109v25-1 - El lavado de las manos de Pilatos"
    ],
    [
        "110v25-1_Jesús_Ante_Herodes.mp3", 
        "110v25-1_Jesús_Ante_Herodes.mp3", 
        "110v25-1 - Jesús Ante Herodes"
    ],
    [
        "111v25-1_1raE_Jesús_es_Condenado_a_Muerte.mp3", 
        "111v25-1_1ºE_Jesús_es_condenado_a_muerte.mp3", 
        "111v25-1 - 1ºE - Jesús es condenado a muerte"
    ],
    [
        "201-E02_Jesús_es_cargado_con_la_cruz.mp3", 
        "201v25-1_2ºE_Jesús_carga_con_la_cruz.mp3", 
        "201v25-1 - 2ºE - Jesús carga con la cruz"
    ],
    [
        "202-E03_Jesús_cae_por_primera_vez.mp3", 
        "202v25-1_3ºE_Jesús_cae_por_primera_vez.mp3", 
        "202v25-1 - 3ºE - Jesús cae por primera vez"
    ],
    [
        "203-E04_Jesús_encuentra_a_su_madre.mp3", 
        "203v25-1_4ºE_Jesús_se_encuentra_con_su_madre.mp3", 
        "203v25-1 - 4ºE - Jesús se encuentra con su madre"
    ],
    [
        "204-E05_Simón_de_Cirene_ayuda_a_Jesús.mp3", 
        "204v25-1_5ºE_Simón_de_Cirene_ayuda_a_Jesús_a_llevar_la_cruz.mp3", 
        "204v25-1 - 5ºE - Simón de Cirene ayuda a Jesús a llevar la cruz"
    ],
    [
        "205-E06_La_Verónica_enjuga_el_rostro_de_Jesús.mp3", 
        "205v25-1_6ºE_La_Verónica_enjuga_el_rostro_de_Jesús.mp3", 
        "205v25-1 - 6ºE - La Verónica enjuga el rostro de Jesús"
    ],
    [
        "206-E07_Jesús_cae_por_segunda_vez.mp3", 
        "206v25-1_7ºE_Jesús_cae_por_segunda_vez.mp3", 
        "206v25-1 - 7ºE - Jesús cae por segunda vez"
    ],
    [
        "207-E08_Jesús_consuela_a_las_santas_mujeres.mp3", 
        "207v25-1_8ºE_Jesús_consuela_a_las_mujeres_de_Jerusalén.mp3", 
        "207v25-1 - 8ºE - Jesús consuela a las mujeres de Jerusalén"
    ],
    [
        "301v2-E09_Jesús_Cae_por_Tercera_Vez.mp3", 
        "301v25-1_9ºE_Jesús_cae_por_tercera_vez.mp3", 
        "301v25-1 - 9ºE - Jesús cae por tercera vez"
    ],
    [
        "302v2-E10_Jesús_Despojado_de_sus_Vestiduras.mp3", 
        "302v25-1_10ºE_Jesús_es_despojado_de_sus_vestiduras.mp3", 
        "302v25-1 - 10ºE - Jesús es despojado de sus vestiduras"
    ],
    [
        "303v2-E11_Jesús_es_Clavado_en_la_Cruz.mp3", 
        "303v25-1_11ºE_Jesús_es_clavado_en_la_cruz.mp3", 
        "303v25-1 - 11ºE - Jesús es clavado en la cruz"
    ],
    [
        "304v2-E12_Jesús_Muere_en_la_Cruz.mp3", 
        "304v25-1_12ºE_Jesús_muere_en_la_cruz.mp3", 
        "304v25-1 - 12ºE - Jesús muere en la cruz"
    ],
    [
        "305v2-E13_Jesús_es_Bajado_de_la_Cruz.mp3", 
        "305v25-1_13ºE_Jesús_es_bajado_de_la_cruz_y_entregado_a_su_madre.mp3", 
        "305v25-1 - 13ºE - Jesús es bajado de la cruz y entregado a su madre"
    ],
    [
        "306v2-E14_El_Cuerpo_de_Jesús_Depositado_en_el_Sepulcro.mp3", 
        "306v25-1_14ºE_Jesús_es_colocado_en_el_sepulcro.mp3", 
        "306v25-1 - 14ºE - Jesús es colocado en el sepulcro"
    ],
    [
        "307v2-La_Puerta_de_Piedra.mp3", 
        "307v25-1_La_Puerta_de_Piedra.mp3", 
        "307v25-1 - La Puerta de Piedra"
    ],
    [
        "308v2-El_Sepulcro.mp3", 
        "308v25-1_El_Sepulcro.mp3", 
        "308v25-1 - El Sepulcro"
    ],
    [
        "309v2-El_Anuncio_de_la_Resurección.mp3", 
        "309v25-1_El_Anuncio_de_la_Resurección.mp3", 
        "309v25-1 - El Anuncio de la Resurección"
    ],
    [
        "199v25-1_TodoUnido.mp3", 
        "199v25-1_1ºP_Todo_UNIDO.mp3", 
        "199v25-1 - Todo UNIDO"
    ],
    [
        "300_TodoUnido.mp3", 
        "300v25-1_3ºP_Todo_UNIDO.mp3", 
        "300v25-1 - Todo UNIDO"
    ]
];


// Directorio donde están los archivos (ajustar si es necesario)
$directorio = __DIR__ . "/media/";

// Verifica si la librería getID3 está instalada
if (!class_exists("getID3")) {
    die("Error: Se requiere la librería getID3 para modificar metadatos.\nInstálala con: composer require james-heinrich/getid3\n");
}

require_once "vendor/autoload.php"; // Asegurar que getID3 está disponible

// Función para renombrar archivos y actualizar metadatos
function renombrarYActualizarMetadatos($directorio, $archivos) {
    $getID3 = new getID3;
    $tagWriter = new getid3_writetags();

    foreach ($archivos as $archivo) {
        list($nombreActual, $nuevoNombre, $nuevoTitulo) = $archivo;

        $rutaOriginal = $directorio . $nombreActual;
        $rutaNueva = $directorio . $nuevoNombre;

        // Verificar si el archivo original existe
        if (!file_exists($rutaOriginal)) {
            echo "No se encontró el archivo: $nombreActual\n";
            continue;
        }

        // Renombrar el archivo
        if (!rename($rutaOriginal, $rutaNueva)) {
            echo "Error al renombrar $nombreActual\n";
            continue;
        }

        echo "Archivo renombrado: $nombreActual -> $nuevoNombre\n";

        // Modificar metadatos (ID3)
        $tagWriter->filename = $rutaNueva;
        $tagWriter->tagformats = ['id3v2.3'];
        $tagWriter->overwrite_tags = true;
        $tagWriter->tag_encoding = 'UTF-8';
        $tagWriter->remove_other_tags = true;
        $tagWriter->tag_data = [
            "title" => [$nuevoTitulo]
        ];

        if ($tagWriter->WriteTags()) {
            echo "Metadatos actualizados para $nuevoNombre\n";
        } else {
            echo "Error al actualizar metadatos de $nuevoNombre\n";
        }
    }
}

// Ejecutar la función
renombrarYActualizarMetadatos($directorio, $archivos);
