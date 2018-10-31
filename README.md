# SSO-PHP
Sso-php is a minimalist single sign-on client/server written in PHP.

## Requirements

- PHP 7.1+
- `ext-curl`, `ext-json`, `ext-mbstring`
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
`\SsoPhp\Server\ProviderInterface`. There's an example provider, server and client in `/examples`.

## Usage - General

So out of date it needs to be rewritten.
