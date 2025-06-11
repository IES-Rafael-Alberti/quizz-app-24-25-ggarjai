<?php
require_once __DIR__ . '/../src/csrf.php';         // MOD
require_once __DIR__ . '/../src/auth.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null); 
    if (login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /quiz.php');
        exit;
    }
    $msg = 'Credenciales inválidas';
}
$csrf = csrf_token();


?>
<form method="post">
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <h1>Login</h1>
    <?php if ($msg) echo "<p style='color:red'>$msg</p>"; ?>
    <input name="username" placeholder="Usuario"><br>
    <input name="password" type="password" placeholder="Contraseña"><br>
    <button>Entrar</button>
    <a href="/register.php">Registrarse</a>
</form>