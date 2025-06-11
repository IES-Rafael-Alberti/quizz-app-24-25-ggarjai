<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/auth.php';
requireLogin();
require_once __DIR__ . '/../../src/csrf.php';
require_once __DIR__ . '/../../src/db.php';
$pdo = DB::conn();

$quizId = (int)($_GET['quiz_id'] ?? 0);
if (!$quizId) { header('Location: index.php'); exit; }

/* ───── operaciones ───── */
/* ───── duplicar ───── */
if (isset($_GET['dup'])) {
    csrf_check($_GET['csrf'] ?? null);
    $pdo->exec('INSERT INTO questions
        (quiz_id,question_text,option_a,option_b,option_c,option_d,correct_option,sort_order)
        SELECT quiz_id,question_text,option_a,option_b,option_c,option_d,correct_option,
               sort_order+1
        FROM questions WHERE id=' . (int)$_GET['dup']);
}
if (isset($_GET['del'])) {
    csrf_check($_GET['csrf'] ?? null);
    $pdo->prepare('DELETE FROM questions WHERE id=?')->execute([$_GET['del']]);
}
/* ───── Mover ───── */
if (isset($_GET['up']) || isset($_GET['down'])) {
    csrf_check($_GET['csrf'] ?? null);
    $id = (int) ($_GET['up'] ?? $_GET['down']);
    $dir = isset($_GET['up']) ? -1 : 1;
    // intercambiar sort_order
    $pdo->beginTransaction();
    $cur = $pdo->prepare('SELECT sort_order FROM questions WHERE id=?')->execute([$id])
               ->fetchColumn();
    $adj = $pdo->prepare('SELECT id,sort_order FROM questions
                          WHERE quiz_id=? AND sort_order=?')->execute([$quizId, $cur+$dir])
               ->fetch(PDO::FETCH_ASSOC);
    if ($adj) {
        $pdo->prepare('UPDATE questions SET sort_order=? WHERE id=?')
            ->execute([$cur+$dir,$id]);
        $pdo->prepare('UPDATE questions SET sort_order=? WHERE id=?')
            ->execute([$cur,$adj['id']]);
    }
    $pdo->commit();
}



/* ───── Guardar/Actualizar ───── */
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check($_POST['csrf'] ?? null);
    $fields = [
        trim($_POST['question_text']),
        trim($_POST['option_a']),
        trim($_POST['option_b']),
        trim($_POST['option_c']),
        trim($_POST['option_d']),
        strtoupper(trim($_POST['correct_option'])),
        $quizId
    ];
    if ($_POST['id']) {
        $fields[] = (int)$_POST['id'];
        $pdo->prepare('UPDATE questions SET
            question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=?
            WHERE quiz_id=? AND id=?')->execute($fields);
    } else {
        $pdo->prepare('INSERT INTO questions
            (question_text,option_a,option_b,option_c,option_d,correct_option,quiz_id,sort_order)
            VALUES (?,?,?,?,?,?,?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM questions WHERE quiz_id=?))')
            ->execute([...$fields,$quizId]);
    }
    header("Location: question_form.php?quiz_id=$quizId");
    exit;
}
$csrf = csrf_token();


$stmt = $pdo->prepare('SELECT * FROM questions WHERE quiz_id=? ORDER BY sort_order');
$stmt->execute([$quizId]);
$qrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="admin.css">
<h1>Preguntas (Quiz #<?= $quizId ?>)</h1>

<!-- formulario alta/edición -->
<form method="post">
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <input type="hidden" name="id" value="">
    <textarea name="question_text" placeholder="Pregunta"></textarea><br>
    A <input name="option_a">
    B <input name="option_b">
    C <input name="option_c">
    D <input name="option_d"><br>
    Correcta <input name="correct_option" size="1"><br>
    <button>Guardar</button>
</form>

<table>
<tr><th>#</th><th>Pregunta</th><th>Acciones</th></tr>
<?php foreach ($qrows as $q): ?>
<tr>
    <td><?= $q['sort_order'] ?></td>
    <td><?= htmlspecialchars($q['question_text']) ?></td>
    <td>
        <a href="?quiz_id=<?= $quizId ?>&dup=<?= $q['id'] ?>&csrf=<?= $csrf ?>">📄 Duplicar</a> |
        <a href="?quiz_id=<?= $quizId ?>&up=<?= $q['id'] ?>&csrf=<?= $csrf ?>">⬆</a> |
        <a href="?quiz_id=<?= $quizId ?>&down=<?= $q['id'] ?>&csrf=<?= $csrf ?>">⬇</a> |
        <a href="?quiz_id=<?= $quizId ?>&del=<?= $q['id'] ?>&csrf=<?= $csrf ?>"
                onclick="return confirm('¿Borrar?')">🗑️</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<p><a href="index.php">⬅ Volver a quizzes</a></p>