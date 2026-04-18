<?php
// Índice principal del sistema ViaCrusis
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/incs/versionLogs.php';
ensureSchema();
$latestVersion = $latestVersion ?? '26.12';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css?v=<?= $latestVersion ?>">
    <title>ViaCrusis - BY2026</title>
    <style>
        .main-menu {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .main-menu h1 {
            text-align: center;
            color: #5d4e37;
            margin-bottom: 30px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: #f9f5f0;
            border: 2px solid #5d4e37;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #5d4e37;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(93,78,55,0.2);
        }
        .menu-card .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .menu-card h3 {
            margin: 10px 0;
            color: #5d4e37;
        }
        .menu-card p {
            color: #8b7355;
            font-size: 14px;
        }
        .version-info {
            text-align: center;
            margin-top: 40px;
            color: #8b7355;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Via Crusis<br>Barrio Yacampiz - 2026</h1>
    </header>

    <main class="main-menu">
        <h1>🎭 Sistema ViaCrusis</h1>

        <div class="menu-grid">
            <a href="audios/" class="menu-card">
                <div class="icon">🎵</div>
                <h3>Audios</h3>
                <p>Escenas y bandas de audio</p>
            </a>

            <a href="personas/" class="menu-card">
                <div class="icon">👥</div>
                <h3>Personas</h3>
                <p>Participantes y roles</p>
            </a>

            <a href="videos/" class="menu-card">
                <div class="icon">🎬</div>
                <h3>Videos</h3>
                <p>Ensayos y presentaciones</p>
            </a>
        </div>

        <div class="version-info">
            Versión: <?= $latestVersion ?> |
            <?php
            $personasCount = 0;
            try {
                $db = getDB();
                $personasCount = (int)$db->query("SELECT COUNT(*) FROM personas")->fetchColumn();
            } catch (Exception $e) {
                // Fallback para modo offline (Termux)
            }
            ?>
            Personas registradas: <?= $personasCount ?>
        </div>
    </main>
</body>
</html>
