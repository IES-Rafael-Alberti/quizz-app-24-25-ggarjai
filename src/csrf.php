<?php
declare(strict_types=1);

// MOD
function csrf_token(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): void
{
    if ($token !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(403);
        exit('CSRF token inválido');
    }
}