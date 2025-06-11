<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/auth.php';
requireLogin();
require_once __DIR__ . '/../../src/csrf.php';
require_once __DIR__ . '/../../src/db.php';
$pdo = DB::conn();

$id = (int) ($_GET['id'] ?? 0);
$data = ['title'=>'','description'=>''];

if ($id) {
    $data = $pdo->prepare('SELECT * FROM quizzes WHERE id = ?')
                ->execute([$id])->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $title = trim($_POST['title']);
    $desc  = trim($_POST['description']);
    if ($id) {
        $pdo->prepare('UPDATE quizzes SET title=?, description=? WHERE id=?')
            ->execute([$title,$desc,$id]);
    } else {
        $pdo->prepare('INSERT INTO quizzes (title,description) VALUES (?,?)')
            ->execute([$title,$desc]);
        $id = (int)$pdo->lastInsertId();
    }
    header("Location: question_form.php?quiz_id=$id");
    exit;
}
$csrf = csrf_token();


?>
<link rel="stylesheet" href="admin.css">
<h1><?= $id ? 'Editar' : 'Nuevo' ?> Quiz</h1>
<form method="post">
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    Título:<br><input name="title" value="<?= htmlspecialchars($data['title']) ?>"><br>
    Descripción:<br><textarea name="description"><?= htmlspecialchars($data['description']) ?></textarea><br>
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <button>Guardar</button>
</form>
<a href="index.php">⬅ Volver</a>