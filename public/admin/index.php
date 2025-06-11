<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/auth.php';
requireLogin();
require_once __DIR__ . '/../../src/csrf.php';

require_once __DIR__ . '/../../src/db.php';
$pdo = DB::conn();

if (isset($_GET['del'])) {
    csrf_check($_GET['csrf'] ?? null);
    $pdo->prepare('DELETE FROM quizzes WHERE id = ?')
        ->execute([(int)$_GET['del']]);
    header('Location: index.php');
    exit;
}
$csrf = csrf_token();
$quizzes = $pdo->query('SELECT * FROM quizzes ORDER BY id DESC');
?>
<link rel="stylesheet" href="admin.css">
<h1>Quizzes</h1>
<a href="quiz_form.php">➕ Nuevo</a>
<table>
<tr><th>ID</th><th>Título</th><th>Acciones</th></tr>
<?php foreach ($quizzes as $q): ?>
<tr>
    <td><?= $q['id'] ?></td>
    <td><?= htmlspecialchars($q['title']) ?></td>
    <td>
    <a href="quiz_form.php?id=<?= $q['id'] ?>">✏️ Editar</a> |
    <a href="index.php?del=<?= $q['id'] ?>&csrf=<?= $csrf ?>"
        onclick="return confirm('¿Borrar?')">🗑️</a>
    <a href="question_form.php?quiz_id=<?= $q['id'] ?>">📄 Preguntas</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="/quiz.php">⬅ Volver</a>