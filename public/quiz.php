<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
requireLogin();
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/csrf.php';

$quizId = (int) ($_GET['quiz_id'] ?? $_POST['quiz_id'] ?? 0);
if ($quizId <= 0) {
    header('Location: /quizzes.php');
    exit;
}

$pdo = DB::conn();
$quizStmt = $pdo->prepare('SELECT * FROM quizzes WHERE id = ?');
$quizStmt->execute([$quizId]);
$quiz = $quizStmt->fetch(PDO::FETCH_ASSOC) ?: header('Location: /quizzes.php') && exit;

// ───── recuperar preguntas una sola vez + aleatorizar
$questionsStmt = $pdo->prepare('SELECT * FROM questions WHERE quiz_id = ? ORDER BY RANDOM()');
$questionsStmt->execute([$quizId]);
$questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);
$totalQuestions = count($questions);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quiz['title']) ?></title>
    <link rel="stylesheet" href="/quiz.css">
</head>
<body>
<p>Bienvenido, <?= htmlspecialchars(currentUser()['username']) ?> |
    <a href="/quizzes.php">Cambiar cuestionario</a> |
    <a href="/logout.php">Salir</a></p>

<form method="post" action="process.php">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="quiz_id" value="<?= $quizId ?>">
    <h1><?= htmlspecialchars($quiz['title']) ?></h1>

<?php foreach ($questions as $idx => $q): ?>
    <div class="question">
        <p><?= ($idx + 1) ?>. <?= htmlspecialchars($q['question_text']) ?></p>

        <?php foreach (['a','b','c','d'] as $letter):
                $opt = 'option_' . $letter; ?>
            <label>
                <input type="radio"
                    name="q<?= $q['id']; ?>"
                    value="<?= strtoupper($letter) ?>">
                <?= $letter ?>) <?= htmlspecialchars($q[$opt]) ?>
            </label>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
    <input type="submit" value="Enviar">
</form>

<script>
document.querySelector('form').addEventListener('submit', e => {
    const total     = <?= $totalQuestions ?>;
    const marcadas  = new Set(
        [...document.querySelectorAll('input[type=radio]:checked')]
            .map(i => i.name)
    );
    if (marcadas.size !== total) {
        alert('Debes responder todas las preguntas antes de enviar.');
        e.preventDefault();
    }
});
</script>
</body>
</html>