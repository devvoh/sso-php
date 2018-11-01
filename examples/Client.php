<?php

require __DIR__ . '/../vendor/autoload.php';

$verbose = in_array("--verbose", $argv);

function write(string $msg): void
{
    echo $msg;
}

function writeln(string $msg): void
{
    write($msg);
    write(PHP_EOL);
}

writeln("devvoh/sso-php example client");
if (!$verbose) {
    writeln("use --verbose to also output all server responses");
} else {
    writeln("verbose mode on, showing all server responses");
}
writeln("");


$client = new \SsoPhp\Client("secret", "client-token-goes-here", "http://127.0.0.1:9876/?action=");

write("Connecting to server ({$client->getServerUrl()}connect)... ");

$response = $client->connect();

if ($verbose) {
    var_dump($response);
}

if ($response['status'] !== 'success') {
    writeln("Could not connect to server.");
    exit(1);
}

writeln("Connected!");

write("Register new user? [y/N] ");
$register = trim(fgets(STDIN));

if (strtolower($register) === 'y') {
    write("New username: ");
    $user = trim(fgets(STDIN));

    write("New password: ");
    $pass = trim(fgets(STDIN));

    $response = $client->register($user, $pass, ['example' => 'yes']);

    if ($verbose) {
        var_dump($response);
    }

    if ($response['status'] !== 'success') {
        writeln("Could not register user.");
        exit(1);
    }

    writeln('User registered.');
}

writeln("Log in now...");

write("Username (user for example): ");
$user = trim(fgets(STDIN));

write("Password (pass for example): ");
$pass = trim(fgets(STDIN));

write("Logging in with {$user}:{$pass}@{$client->getServerUrl()}login... ");

$response = $client->login($user, $pass);

if ($verbose) {
    var_dump($response);
}

if ($response['status'] !== 'success') {
    writeln("Could not log in.");
    exit(1);
}

writeln("Logged in as '{$response['data']['metadata']['username']}'.");

$token = $response['data']['token'];

write("Validating token ({$client->getServerUrl()}validateToken)... ");

$response = $client->validateToken($user, $token);

if ($verbose) {
    var_dump($response);
}

if ($response['status'] !== 'success') {
    writeln("Could not validate token.");
    exit(1);
}

writeln("Token validated.");

writeln("Hit enter to log out. This is where you can check token_storage.json. The token will disappear after logging out...");
fgets(STDIN);

write("Logging out ({$client->getServerUrl()}logout)... ");

$response = $client->logout($user, $token);

if ($verbose) {
    var_dump($response);
}

if ($response['status'] !== 'success') {
    writeln("Could not log out.");
    exit(1);
}

writeln("Logged out.");

write("Validating token after logging out... ");

$response = $client->validateToken($user, $token);

if ($verbose) {
    var_dump($response);
}

if ($response['status'] === 'success') {
    writeln("Token was not revoked, this is bad.");
    exit(1);
}

writeln("Token invalidated, logged out.");
