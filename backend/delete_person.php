<?php

declare(strict_types=1);

require_once __DIR__ . '/crud_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo 'Invalid person ID.';
    exit;
}

try {
    delete_person((int)$id);
    header('Location: view_persons.php?deleted=1');
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error deleting person.';
    exit;
}

?>
