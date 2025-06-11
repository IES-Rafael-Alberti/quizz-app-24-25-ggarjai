<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
requireLogin();
require_once __DIR__ . '/../src/db.php';

$quizzes = DB::conn()->query('SELECT id, title, description FROM quizzes ORDER BY id');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar cuestionario</title>
    <link rel="stylesheet" href="/quiz.css">
</head>
<body>
<h1>Elige un cuestionario</h1>
<ul>
    <?php foreach ($quizzes as $q): ?>
        <li>
            <strong><?= htmlspecialchars($q['title']) ?></strong><br>
            <?= htmlspecialchars($q['description']) ?><br>
            <a href="/quiz.php?quiz_id=<?= $q['id'] ?>">Comenzar »</a><br>
        </li>
    <?php endforeach; ?>
</ul>
<a href="/history.php">📊 Mi historial</a> |
<a href="/stats.php">📈 Estadísticas globales</a> |
<a href="/logout.php">Salir</a>
</body>
</html>