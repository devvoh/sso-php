<?php

require __DIR__ . '/../vendor/autoload.php';

function write(string $msg): void
{
    echo $msg;
}

function writeln(string $msg): void
{
    write($msg);
    write(PHP_EOL);
}

$client = new \SsoPhp\Client("secret", "client-token-goes-here", "http://127.0.0.1:9876/?action=");

write("Connecting to server ({$client->getServerUrl()}connect)... ");

if (!$client->connect()) {
    writeln("Could not connect to server.");
    exit(1);
}

writeln("Connected!");

write("Username (user for example): ");
$user = trim(fgets(STDIN));

write("Password (pass for example): ");
$pass = trim(fgets(STDIN));

write("Logging in with {$user}:{$pass}@{$client->getServerUrl()}login... ");

$response = $client->login($user, $pass);

if ($response['status'] !== 'success') {
    writeln("Could not log in.");
    exit(1);
}

writeln("Logged in as '{$response['data']['metadata']['username']}'.");

$token = $response['data']['token'];

write("Validating token ({$client->getServerUrl()}validateToken)... ");

$response = $client->validateToken('user', $token);

if ($response['status'] !== 'success') {
    writeln("Could not validate token.");
    exit(1);
}

writeln("Token validated.");

write("Logging out ({$client->getServerUrl()}logout)... ");

$response = $client->logout('user', $token);

if ($response['status'] !== 'success') {
    writeln("Could not log out.");
    exit(1);
}

writeln("Logged out.");

write("Validating token after logging out... ");

$response = $client->validateToken('user', $token);

if ($response['status'] === 'success') {
    writeln("Token was not revoked, this is bad.");
    exit(1);
}

writeln("Token invalidated, logged out.");
