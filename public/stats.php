<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
requireLogin();
require_once __DIR__ . '/../src/db.php';
$pdo = DB::conn();

$stats = $pdo->query(
   'SELECT q.id, q.title,
           COUNT(a.id)             AS intentos,
           ROUND(AVG(a.score*1.0/a.total)*100,2) AS media_pct
    FROM quizzes q
    LEFT JOIN attempts a ON a.quiz_id = q.id
    GROUP BY q.id
    ORDER BY q.id'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8">
<title>Estadísticas</title>
<link rel="stylesheet" href="/quiz.css"></head><body>
<h1>Estadísticas globales</h1>
<table border="1">
<tr><th>ID</th><th>Título</th><th># intentos</th><th>Media %</th></tr>
<?php foreach ($stats as $s): ?>
<tr>
    <td><?= $s['id'] ?></td>
    <td><?= htmlspecialchars($s['title']) ?></td>
    <td><?= $s['intentos'] ?></td>
    <td><?= $s['media_pct'] ?? 0 ?></td>
</tr>
<?php endforeach; ?>
</table>
<p><a href="/quizzes.php">⬅ Volver a cuestionarios</a></p>
</body></html>