<?php

declare(strict_types=1);

// XAMPP default MySQL settings are usually:
// host: localhost
// user: root
// password: (empty)
// database: e1_personal_record_db

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $config = [
        'host' => 'localhost',
        'dbName' => 'e1_personal_record_db',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ];

    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbName'] . ';charset=' . $config['charset'];

    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
