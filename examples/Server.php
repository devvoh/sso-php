<?php

use SsoPhp\Server;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/ExampleProvider.php';

$headers = getallheaders();

$server = new Server(
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
    case 'registerUser':
        echo $server->registerUser()->toJson();
        return;
    case 'deleteUser':
        echo $server->deleteUser()->toJson();
        return;
    case 'loginUser':
        echo $server->loginUser()->toJson();
        return;
    case 'validateToken':
        echo $server->validateToken()->toJson();
        return;
    case 'revokeToken':
        echo $server->revokeToken()->toJson();
        return;
    case 'registerUserWithContext':
        echo $server->registerUserWithContext()->toJson();
        return;
    case 'updateUserContext':
        echo $server->updateUserContext()->toJson();
        return;
}

