<?php

use SsoPhp\Server;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/ExampleProvider.php';

$server = new Server(
    'secret',
    'client-token-goes-here',
    new ExampleProvider()
);

header("content-type: application/json");

$action = $_GET['action'] ?? null;

if ($action === null) {
    echo json_encode(['status' => 'error']);
    return;
}

switch ($action) {
    case 'connect':
        echo json_encode($server->connect());
        return;
    case 'register':
        echo json_encode($server->register());
        return;
    case 'login':
        echo json_encode($server->login());
        return;
    case 'validateToken':
        echo json_encode($server->validateToken());
        return;
    case 'logout':
        echo json_encode($server->logout());
        return;
}

