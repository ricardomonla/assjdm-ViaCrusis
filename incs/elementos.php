<?php
/**
 * elementos.php
 * Ubicación centralizada de la estructura documental y de medios (Guion y Audios)
 * para garantizar la compatibilidad entre todos los módulos del Via Crucis (/audios, /guion).
 */

function getMediaGroupsStructure() {
    return [
        '0' => [
            'name' => 'Desfile',
            'icon' => '🎭',
            'audios' => [
                [
                    'id' => '000_v2502',
                    'order' => '000',
                    'version' => '2502',
                    'title' => 'Desfile Reflección Final',
                    'display_name' => '000 Desfile Reflección Final',
                    'filename' => '000_v2502.mp3'
                ],
                [
                    'id' => '001_v2504',
                    'order' => '001',
                    'version' => '2504',
                    'title' => 'Desfile Pueblo',
                    'display_name' => '001 Desfile Pueblo',
                    'filename' => '001_v2504.mp3'
                ],
                [
                    'id' => '002_v2502',
                    'order' => '002',
                    'version' => '2502',
                    'title' => 'Desfile Apóstoles',
                    'display_name' => '002 Desfile Apóstoles',
                    'filename' => '002_v2502.mp3'
                ],
                [
                    'id' => '003_v2502',
                    'order' => '003',
                    'version' => '2502',
                    'title' => 'Desfile Pilatos y Soldados',
                    'display_name' => '003 Desfile Pilatos y Soldados',
                    'filename' => '003_v2502.mp3'
                ],
                [
                    'id' => '004_v2503',
                    'order' => '004',
                    'version' => '2503',
                    'title' => 'Desfile Curas',
                    'display_name' => '004 Desfile Curas',
                    'filename' => '004_v2503.mp3'
                ],
                [
                    'id' => '005_v2502',
                    'order' => '005',
                    'version' => '2502',
                    'title' => 'Desfile Herodes y Bailarinas',
                    'display_name' => '005 Desfile Herodes y Bailarinas',
                    'filename' => '005_v2502.mp3'
                ],
                [
                    'id' => '006_v2504',
                    'order' => '006',
                    'version' => '2504',
                    'title' => 'Desfile Diablo Suspenso',
                    'display_name' => '006 Desfile Diablo Suspenso',
                    'filename' => '006_v2504.mp3'
                ],
            ]
        ],
        '1' => [
            'name' => 'La Pasión',
            'icon' => '⛪',
            'audios' => [
                [
                    'id' => '101_v2503',
                    'order' => '101',
                    'version' => '2503',
                    'title' => 'La entrada de Jesús en Jerusalén',
                    'display_name' => '101 La entrada de Jesús en Jerusalén',
                    'filename' => '101_v2503.mp3'
                ],
                [
                    'id' => '102_v2504',
                    'order' => '102',
                    'version' => '2504',
                    'title' => 'El Trato de Judas y Caifás',
                    'display_name' => '102 El Trato de Judas y Caifás',
                    'filename' => '102_v2504.mp3'
                ],
                [
                    'id' => '103_v2510',
                    'order' => '103',
                    'version' => '2510',
                    'title' => 'La Última Cena + Monedas de Judas',
                    'display_name' => '103 La Última Cena + Monedas de Judas',
                    'filename' => '103_v2510.mp3'
                ],
                [
                    'id' => '104_v2503',
                    'order' => '104',
                    'version' => '2503',
                    'title' => 'La oración en el Monte de los Olivos',
                    'display_name' => '104 La oración en el Monte de los Olivos',
                    'filename' => '104_v2503.mp3'
                ],
                [
                    'id' => '105_v2504',
                    'order' => '105',
                    'version' => '2504',
                    'title' => 'La Entrega',
                    'display_name' => '105 La Entrega',
                    'filename' => '105_v2504.mp3'
                ],
                [
                    'id' => '106_v2504',
                    'order' => '106',
                    'version' => '2504',
                    'title' => 'Las negaciones de Pedro',
                    'display_name' => '106 Las negaciones de Pedro',
                    'filename' => '106_v2504.mp3'
                ],
                [
                    'id' => '107_v2503',
                    'order' => '107',
                    'version' => '2503',
                    'title' => 'El juicio en el Sanedrín',
                    'display_name' => '107 El juicio en el Sanedrín',
                    'filename' => '107_v2503.mp3'
                ],
                [
                    'id' => '108_v2504',
                    'order' => '108',
                    'version' => '2504',
                    'title' => 'La Culpa de Judas',
                    'display_name' => '108 La Culpa de Judas',
                    'filename' => '108_v2504.mp3'
                ],
                [
                    'id' => '109_v2503',
                    'order' => '109',
                    'version' => '2503',
                    'title' => 'El lavado de las manos de Pilatos',
                    'display_name' => '109 El lavado de las manos de Pilatos',
                    'filename' => '109_v2503.mp3'
                ],
                [
                    'id' => '110_v2504',
                    'order' => '110',
                    'version' => '2504',
                    'title' => 'Jesús Ante Herodes',
                    'display_name' => '110 Jesús Ante Herodes',
                    'filename' => '110_v2504.mp3'
                ],
                [
                    'id' => '111_v2503',
                    'order' => '111',
                    'version' => '2503',
                    'title' => '1ºE Jesús es condenado a muerte',
                    'display_name' => '111 1ºE Jesús es condenado a muerte',
                    'filename' => '111_v2503.mp3'
                ],
            ]
        ],
        '2' => [
            'name' => 'Calvario',
            'icon' => '✝️',
            'audios' => [
                [
                    'id' => '201_v2593',
                    'order' => '201',
                    'version' => '2593',
                    'title' => '2ºE Jesús carga con la cruz',
                    'display_name' => '201 2ºE Jesús carga con la cruz',
                    'filename' => '201_v2593.mp3'
                ],
                [
                    'id' => '202_v2503',
                    'order' => '202',
                    'version' => '2503',
                    'title' => '3ºE Jesús cae por primera vez',
                    'display_name' => '202 3ºE Jesús cae por primera vez',
                    'filename' => '202_v2503.mp3'
                ],
                [
                    'id' => '203_v2503',
                    'order' => '203',
                    'version' => '2503',
                    'title' => '4ºE Jesús se encuentra con su madre',
                    'display_name' => '203 4ºE Jesús se encuentra con su madre',
                    'filename' => '203_v2503.mp3'
                ],
                [
                    'id' => '204_v2503',
                    'order' => '204',
                    'version' => '2503',
                    'title' => '5ºE Simón de Cirene ayuda a Jesús a llevar la cruz',
                    'display_name' => '204 5ºE Simón de Cirene ayuda a Jesús a llevar la cruz',
                    'filename' => '204_v2503.mp3'
                ],
                [
                    'id' => '205_v2503',
                    'order' => '205',
                    'version' => '2503',
                    'title' => '6ºE La Verónica enjuga el rostro de Jesús',
                    'display_name' => '205 6ºE La Verónica enjuga el rostro de Jesús',
                    'filename' => '205_v2503.mp3'
                ],
                [
                    'id' => '206_v2503',
                    'order' => '206',
                    'version' => '2503',
                    'title' => '7ºE Jesús cae por segunda vez',
                    'display_name' => '206 7ºE Jesús cae por segunda vez',
                    'filename' => '206_v2503.mp3'
                ],
                [
                    'id' => '207_v2504',
                    'order' => '207',
                    'version' => '2504',
                    'title' => '8ºE Jesús consuela a las mujeres de Jerusalén',
                    'display_name' => '207 8ºE Jesús consuela a las mujeres de Jerusalén',
                    'filename' => '207_v2504.mp3'
                ],
            ]
        ],
        '3' => [
            'name' => 'Crucifixión',
            'icon' => '🕊️',
            'audios' => [
                [
                    'id' => '301_v2503',
                    'order' => '301',
                    'version' => '2503',
                    'title' => '9ºE Jesús cae por tercera vez',
                    'display_name' => '301 9ºE Jesús cae por tercera vez',
                    'filename' => '301_v2503.mp3'
                ],
                [
                    'id' => '302_v2503',
                    'order' => '302',
                    'version' => '2503',
                    'title' => '10ºE Jesús es despojado de sus vestiduras',
                    'display_name' => '302 10ºE Jesús es despojado de sus vestiduras',
                    'filename' => '302_v2503.mp3'
                ],
                [
                    'id' => '303_v2503',
                    'order' => '303',
                    'version' => '2503',
                    'title' => '11ºE Jesús es clavado en la cruz',
                    'display_name' => '303 11ºE Jesús es clavado en la cruz',
                    'filename' => '303_v2503.mp3'
                ],
                [
                    'id' => '304_v2505',
                    'order' => '304',
                    'version' => '2505',
                    'title' => '12ºE Jesús muere en la cruz',
                    'display_name' => '304 12ºE Jesús muere en la cruz',
                    'filename' => '304_v2505.mp3'
                ],
                [
                    'id' => '305_v2503',
                    'order' => '305',
                    'version' => '2503',
                    'title' => '13ºE Jesús es bajado de la cruz y entregado a su madre',
                    'display_name' => '305 13ºE Jesús es bajado de la cruz y entregado a su madre',
                    'filename' => '305_v2503.mp3'
                ],
                [
                    'id' => '306_v2503',
                    'order' => '306',
                    'version' => '2503',
                    'title' => 'Jesús es llevado por las calles',
                    'display_name' => '306 Jesús es llevado por las calles',
                    'filename' => '306_v2503.mp3'
                ],
            ]
        ],
        '4' => [
            'name' => 'La Resurrección',
            'icon' => '🌅',
            'audios' => [
                [
                    'id' => '401_v2504',
                    'order' => '401',
                    'version' => '2504',
                    'title' => '14ºE Jesús es colocado en el sepulcro',
                    'display_name' => '401 14ºE Jesús es colocado en el sepulcro',
                    'filename' => '401_v2504.mp3'
                ],
                [
                    'id' => '402_v2504',
                    'order' => '402',
                    'version' => '2504',
                    'title' => 'El Sepulcro',
                    'display_name' => '402 El Sepulcro',
                    'filename' => '402_v2504.mp3'
                ],
                [
                    'id' => '403_v2503',
                    'order' => '403',
                    'version' => '2503',
                    'title' => 'La Resurección',
                    'display_name' => '403 La Resurección',
                    'filename' => '403_v2503.mp3'
                ],
            ]
        ],
    ];
}
