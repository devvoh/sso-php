<?php

use SsoPhp\Server;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/ExampleProvider.php';

$headers = getallheaders();

$server = new Server(
    $headers['client_secret'],
    $headers['client_token'],
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
        echo $server->connect()->toJson();
        return;
    case 'register':
        echo $server->register()->toJson();
        return;
    case 'registerWithContext':
        echo $server->registerWithContext()->toJson();
        return;
    case 'login':
        echo $server->login()->toJson();
        return;
    case 'validateToken':
        echo $server->validateToken()->toJson();
        return;
    case 'logout':
        echo $server->logout()->toJson();
        return;
    case 'updateContext':
        echo $server->updateContext()->toJson();
        return;
}

