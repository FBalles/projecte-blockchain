<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $env = parse_ini_file(__DIR__ . '/../.env');
        $pdo = new PDO(
            "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
            $env['DB_USER'],
            $env['DB_PASS'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

function env(string $key): string
{
    static $vars = null;
    if ($vars === null) {
        $vars = parse_ini_file(__DIR__ . '/../.env');
    }
    return $vars[$key] ?? '';
}

function view(string $template, array $data = []): void
{
    extract($data);
    ob_start();
    require __DIR__ . "/Views/{$template}";
    $content = ob_get_clean();
    require __DIR__ . '/Views/layout.php';
}