<?php

use SsoPhp\Client;

require __DIR__ . '/../vendor/autoload.php';

$verbose = in_array("--verbose", $argv);

function write(string $msg): void
{
    echo $msg;
}

function writeln(string $msg = null): void
{
    write($msg ?? "");
    write(PHP_EOL);
}

function writeresponse(string $msg): void
{
    writeln('');
    writeln('  ↪ ' . $msg);
}

writeln("devvoh/sso-php example client");

writeln(str_repeat('-', 80));

if (!$verbose) {
    writeln("use --verbose to also output all server responses");
} else {
    writeln("verbose mode on, showing all server responses");
}

writeln(str_repeat('-', 80));

// We specifically disable secure mode so we can connect to an insecure http:// server url for example purposes
const SSO_CLIENT_SECURE_MODE = false;

// We disable E_USER_NOTICE loging since disabling secure mode /will/ trigger a notice that it's a BAD IDEA.
error_reporting(E_ALL & ~E_USER_NOTICE);

$client = new Client("secret", "client-token-goes-here", "http://127.0.0.1:9876/?action=");
// So don't do this on production EVER.

write("Connecting to server ({$client->getServerUrl()}connect)... ");

$response = $client->connect();

if ($verbose) {
    writeresponse($response->toJson());
}

if ($response->isError()) {
    writeln("Could not connect to server.");
    exit(1);
}

writeln("Connected!");

writeln(str_repeat('-', 80));

write("Register new user? [y/N] ");
$register = trim(fgets(STDIN));

if (strtolower($register) === 'y') {
    writeln();

    write("New username: ");
    $user = trim(fgets(STDIN));

    write("New password: ");
    $pass = trim(fgets(STDIN));

    writeln();

    write("Register new user with context? [y/N] ");
    $registerWithContext = trim(fgets(STDIN));

    if (strtolower($registerWithContext) === 'y') {
        $response = $client->registerUserWithContext($user, $pass, ['example' => 1]);
    } else {
        $response = $client->registerUser($user, $pass);
    }

    if ($verbose) {
        writeresponse($response->toJson());
    }

    if ($response->isError()) {
        writeln("Could not register user.");
        exit(1);
    }

    writeln("User registered.");
}

writeln(str_repeat("-", 80));

writeln("Log in now...");
writeln();

write("Username (user for example): ");
$user = trim(fgets(STDIN));

write("Password (pass for example): ");
$pass = trim(fgets(STDIN));

writeln();
write("Logging in with {$user}:{$pass}@{$client->getServerUrl()}loginUser... ");

$response = $client->loginUser($user, $pass);

if ($verbose) {
    writeresponse($response->toJson());
}

if ($response->isError()) {
    writeln("Could not log in.");
    exit(1);
}

writeln("Logged in as '{$response->getFromMetadata('username')}'.");

$token = $response->getFromData('token');

writeln(str_repeat('-', 80));

write("Validating token ({$client->getServerUrl()}validateToken)... ");

$response = $client->validateToken($user, $token);

if ($verbose) {
    writeresponse($response->toJson());
}

if ($response->isError()) {
    writeln("Could not validate token.");
    exit(1);
}

writeln("Token validated.");

writeln(str_repeat('-', 80));

$currentContext = $response->getFromMetadata('context');

if ($currentContext !== null) {
    write("Current context: ");
    writeln(json_encode($currentContext));
    writeln();

    write("Hit enter to update the user's context...");
    fgets(STDIN);
    writeln();

    if (isset($currentContext['example'])) {
        $currentContext['example'] = (int)$currentContext['example'] + 1;
    } else {
        $currentContext['example'] = 1;
    }

    $response = $client->updateUserContext($user, $currentContext);

    if ($response->isError()) {
        writeln("Could not update user context.");
        exit(1);
    } else {
        write("Updated user context: ");
        writeln(json_encode($response->getFromMetadata('context')));
    }

    if ($verbose) {
        writeresponse($response->toJson());
    }

    writeln(str_repeat('-', 80));
}

write("Hit enter to log out. Now you can check token_storage.json. The token will disappear after logging out...");
fgets(STDIN);

write("Logging out ({$client->getServerUrl()}revokeToken)... ");

$response = $client->revokeToken($user, $token);

if ($verbose) {
    writeresponse($response->toJson());
}

if ($response->isError()) {
    writeln("Could not log out.");
    exit(1);
}

writeln("Logged out.");

writeln(str_repeat('-', 80));

write("Validating token after logging out... ");

$response = $client->validateToken($user, $token);

if ($verbose) {
    writeresponse($response->toJson());
}

if ($response->isSuccess()) {
    writeln("Token was not revoked, this is bad.");
    exit(1);
}

if ($response->getErrorCode() !== SsoPhp\Response\ResponseErrors::VALIDATE_TOKEN_FAILED) {
    writeln("Token invalid, but not for the right reason:");
    writeln("  Message: " . $response->getErrorMessage());
    writeln("     Code: " . $response->getErrorCode());
    writeln();
}

writeln("Token invalidated, logged out.");

writeln(str_repeat('-', 80));

write("Delete this user? [y/N] ");
$delete = trim(fgets(STDIN));

if (strtolower($delete) === 'y') {
    $response = $client->deleteUser($user);

    if ($verbose) {
        writeresponse($response->toJson());
    }

    if ($response->isError()) {
        writeln("Could not delete user.");
        exit(1);
    }

    writeln("Deleted user.");
}
