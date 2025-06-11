<?php
require_once __DIR__ . '/../src/auth.php';
requireLogin();

$pdo = DB::conn();
$stmt = $pdo->prepare(
    'SELECT q.title, a.score, a.total, a.taken_at
     FROM attempts a
     JOIN quizzes q ON q.id = a.quiz_id
     WHERE a.user_id = ?
     ORDER BY a.taken_at DESC'
);
$stmt->execute([currentUser()['id']]);
?>
<h1>Mis intentos</h1>
<table border="1">
<tr><th>Quiz</th><th>Puntuación</th><th>Fecha</th></tr>
<?php foreach ($stmt as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td><?= $row['score'] ?> / <?= $row['total'] ?></td>
    <td><?= $row['taken_at'] ?></td>
</tr>
<?php endforeach; ?>
<a href="/quiz.php">⬅ Volver al quiz</a>