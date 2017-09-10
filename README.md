# SSO-PHP
Sso-php is a minimalist single sign-on client/server written in PHP.

## Requirements

- PHP 5.6, PHP 7.x
- Composer

## Installation

sso-php can be installed by using [Composer](http://getcomposer.org/). Simply run:

`composer require devvoh/sso-php`

## Information

The Server package should be installed on whatever account management application you want. It should support the 
requests the client will send.

It's possible to use routing for this (`http://x.dev/sso/connect`, `http://x.dev/sso/login`, etc.) or a GET parameter 
(`http://x.dev/sso.php?action=connect`, `http://x.dev/sso.php?action=login`).

Most of the functionality specific for your situation will need to be provided in the form of an implementation of 
`\SsoPhp\Server\ProviderInterface`. There's an example provider in `/examples`.

## Usage - General

sso-php only provides login logic. No user profiles or anything else. So for a working setup, we need the following:

- A server with user accounts (username, password are needed for sso-php) and token storage.
- A url to send our client's requests to (see Information above)

When this is all set up, we can make requests from our client application.

## Usage - Internal mode

Internal mode means it's all handled by the sso-php client and server without any outside functionality.

We start a Client instance:  
`$client = new \SsoPhp\Client("client-secret", "client-token-goes-here", "http://x.dev/sso/");`

We then connect to see if the server's available and our client-id/token is correct.

`$response = $client->connect();`

For the response objects, the [JSend](https://labs.omniti.com/labs/jsend) specification is used.

To log in a user, allow them to fill in their user/password combo and then call `login()`:

`$response = $client->login($username, $password);`

If the login attempt is successful, the return value of `$client->generateToken($username)` is returned in 
`$response["data"]["token"]`. A representation of the user (as defined by the provider's 
`getUserFromUsername($username)`) is returned in `$response["data"]["user"]`. What's in there is up to the
implementation. If you want to validate the token from time to time, make sure to store the user and 
token data somewhere client-side so you can make subsequent calls. If you plan to handle the login logic on the client 
side, then this is all you need.

To validate an existing token:

`$response = $client->validateToken($username, $token);`

This will return the user representation in `$response["data"]["user"]` like `login(...)` does.

And to log a user out:

`$response = $client->logout($username, $token);`

That's it. Token validity (expiry times, ttl, multiple logins from different browsers, etc.) is not something sso-php
is responsible for. That's all up to you and can (and should) be done in your `\SsoPhp\Server\ProviderInterface`
implementation. You can make it as extravagant or simple as you want.

## Usage - External mode

External mode means login and registration is done on an external site (usually the account management application).

We start a Client instance:  
`$client = new \SsoPhp\Client("client-id", "client-token-goes-here", "http://x.dev/sso/");`

We can then generate the required urls:

```php
$response = $client->generateLoginUrl();
$loginUrl = $response["data"]["url"];
  
$response    = $client->generateRegisterUrl();
$registerUrl = $response["data"]["url"];
```

How you handle login/registration and then get the tokens back to the Client is up to you. From that point, you can
simply `validateToken()` with the code mentioned above.