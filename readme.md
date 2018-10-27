# Frampt

## Overview

Frampt is a simple, lightweight SSH client designed to provide an elegant, yet easy to use API for common tasks via an SSH connection. The premise is simple; it allows a user to connect to a remote server and run commands. That's pretty much it. No fancy bells and whistles. Originally an idea to help with managing remote servers autonomously, it made sense to create and share a package so that others may benefit from it's uses.

## Requirements

Frampt is lightweight and the only requirement is PHP 7.1.0 or newer.

## Installation

Frampt can be easily installed via composer.

```
composer require defrostedtuna/frampt
```

## Usage

Using Frampt is simple. Here's an example to show the basics.

```php
use DefrostedTuna\Frampt\Client;

$frampt = new Client('www.example.com');

$frampt->authenticateWithPassword(
    'username',
    'password'
);

// Or with an ssh key.
$frampt->authenticateWithPublicKey(
    'username',
    '/path/to/public/key/id_rsa.pub',
    '/path/to/private/key/id_rsa',
    'optional-passphrase'
);

// Run a single command, or multiple back to back.
$frampt->runCommand('mkdir /some-directory');
$frampt->runCommand('cp file.txt /some-directory/file.txt');

// Disconnect manually, or when the class is destroyed.
$frampt->disconnect():

// Retrieve the output from the console on the remote server.
$output = $frampt->getStreamOutput();
```

## API

```php
/**
 * Authenticate over SSH using a plain password.
 *
 * @param string $username
 * @param string $password
 *
 * @return bool
 *
 * @throws \Exception
 */
public function authenticateWithPassword(string $username, string $password) : bool;
```

```php
/**
 * Authenticate over SSH using a public key.
 *
 * @param string $username
 * @param string $publicKeyFile,
 * @param string $privateKeyFile,
 * @param string $passphrase = null
 *
 * @return bool
 *
 * @throws \Exception
 */
public function authenticateWithPublicKey(
    string $username,
    string $publicKeyFile,
    string $privateKeyFile,
    string $passphrase = null
) : bool;
```

```php
/**
 * Disconnects from the remote server passed to the class instance.
 *
 * @return bool
 */
public function disconnect() : bool;
```

```php
/**
 * Retrieves the server property.
 *
 * @return string
 */
public function getServer() : string;
```

```php
/**
 * Retrieves the authenticated property.
 *
 * @return bool
 */
public function getAuthenticated() : bool;
```

```php
/**
 * Retrieves the output from each command run during the instance.
 *
 * @return string
 */
public function getStreamOutput() : string;
```

```php
/**
 * Sets the command to be run on the given remote server instance.
 *
 * @param string $command
 *
 * @return string
 */
public function runCommand(string $command) : string;
```

## Testing

Tests have been included and can be run via PHPUnit.

```
vendor/bin/phpunit
```

For convenience, Frampt includes a Docker container in which tests can be run from. This saves developers the hassle of needing to install any dependencies on their local machine.

To run the test suite using Docker, the dependencies can be installed via the `composer:latest` Docker image.

```
docker run --rm -v $(pwd):/app composer:latest install --no-interaction --no-suggest
```

Once the Composer dependencies are installed, the Docker container can be created and the tests run.

```
docker-compose up -d
docker-compose exec package vendor/bin/phpunit --coverage-text
```