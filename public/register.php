<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/csrf.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') $errors[] = 'Todos los campos son obligatorios';
    if (!$errors) {
        $pdo = DB::conn();
        $hash = password_hash($p, PASSWORD_DEFAULT); // (Fuente: PHP manual – password_hash)
        try {
            $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)')
                ->execute([$u, $hash]);
            header('Location: /login.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Usuario existente';
        }
    }
}
$csrf = csrf_token();


?>
<form method="post">
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <h1>Registro</h1>
    <?php foreach ($errors as $e) echo "<p style='color:red'>$e</p>"; ?>
    <input name="username" placeholder="Usuario"><br>
    <input name="password" type="password" placeholder="Contraseña"><br>
    <button>Crear cuenta</button>
    <a href="/login.php">Ya tengo cuenta</a>
</form>