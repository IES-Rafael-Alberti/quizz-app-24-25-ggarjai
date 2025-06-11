<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
requireLogin();
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/csrf.php';
csrf_check($_POST['csrf'] ?? null);

$pdo    = DB::conn();
$quizId = (int) ($_POST['quiz_id'] ?? 0);
if ($quizId <= 0) { header('Location: /quizzes.php'); exit; }

// ───── traer respuestas correctas ─────
$stmt = $pdo->prepare(
    'SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option
     FROM questions WHERE quiz_id = ? ORDER BY sort_order'
);
$stmt->execute([$quizId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ───── validar formulario ─────
foreach ($rows as $row) {
    if (!isset($_POST['q'.$row['id']])) {
        header("Location: /quiz.php?quiz_id=$quizId&err=incompleto");
        exit;
    }
}
// ───── calcular puntuación ─────
$score = 0;
$total = count($rows);

foreach ($rows as &$row) {
    $userAns = strtoupper($_POST['q'.$row['id']]);
    $row['user'] = $userAns;
    $row['is_correct'] = $userAns === $row['correct_option'];
    if ($row['is_correct']) { $score++; }
}

// ───── registrar intento ─────
$pdo->prepare(
    'INSERT INTO attempts (user_id, quiz_id, score, total) VALUES (?, ?, ?, ?)'
)->execute([currentUser()['id'], $quizId, $score, $total]);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resultado</title>
<link rel="stylesheet" href="/quiz.css">
</head>
<body>
<h1>Resultado: <?= $score ?> / <?= $total ?></h1>

<table border="1">
<tr><th>#</th><th>Pregunta</th><th>Tu respuesta</th><th>Correcta</th></tr>
<?php foreach ($rows as $i => $r): ?>
<tr style="background:<?= $r['is_correct'] ? '#c8f7c5' : '#f7c5c5' ?>;">
    <td><?= $i+1 ?></td>
    <td><?= htmlspecialchars($r['question_text']) ?></td>
    <td><?= $r['user'] ?></td>
    <td><?= $r['correct_option'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<p>
<a href="/quiz.php?quiz_id=<?= $quizId ?>">Volver a intentar</a> |
<a href="/quizzes.php">Elegir otro cuestionario</a> |
<a href="/history.php">Mi historial</a>
</p>
</body>
</html>