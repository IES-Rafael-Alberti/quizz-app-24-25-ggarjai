<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

session_start();

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        header('Location: /login.php');
        exit;
    }
}

function login(string $username, string $password): bool
{
    $pdo = DB::conn();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function logout(): void
{
    session_destroy();
}
