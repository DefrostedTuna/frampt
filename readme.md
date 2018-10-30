# Frampt

## Overview

Frampt is a simple, lightweight SSH client designed to provide an elegant, yet easy to use API for common tasks via an SSH connection. The premise is simple; it allows a user to connect to a remote server, run commands, and send or receive files. That's pretty much it. No fancy bells and whistles. Originally an idea to help with managing remote servers autonomously, it made sense to create and share a package so that others may benefit from its uses.

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

// Run a command on the remote server.
$frampt->runCommand('mkdir /some-directory');

// Retreive the output from the commands that have been run.
$streamOutput = $frampt->getStreamOutput();

// Clear the stream output.
$frampt->clearStreamOutput();

// Retreive the output from the commands that have been run for the entire session.
$sessionOutput = $frampt->getSessionOutput();

// Send receive a file.
$frampt->sendFile(
    '/path/to/local/file.txt',
    '/path/to/remote/file.txt',
    0644 // Optional permissions.
);

// Receive receive a file.
$frampt->receiveFile(
    '/path/to/remote/file.txt',
    '/path/to/local/file.txt'
);

// Disconnect manually, or when the class is destroyed.
$frampt->disconnect():
```

Commands may also be chained.

```php
use DefrostedTuna\Frampt\Client;

$frampt = new Client('www.example.com');

// Connect, run commands, and get the output.
$streamOutput = $frampt->authenticateWithPublicKey(
    'username',
    '/path/to/public/key/id_rsa.pub',
    '/path/to/private/key/id_rsa',
    'optional-passphrase'
)->runCommand('touch file.txt')
    ->runCommand('cp file.txt /some-directory/file.txt')
    ->getStreamOutput();

// Connect, run a command, and disconnect.
$frampt->authenticateWithPassword(
    'username',
    'password'
)->runCommand("echo 'Some text.' >> /some-directory/file.txt")->disconnect();

// Run a command, clear the output, run another command,
// and get the output of the second command only.
$frampt->runCommand("echo 'Some more text.' >> /some-directory/file.txt")
    ->clearStreamOutput()
    ->runCommand('cat /some-directory/file.txt')
    ->getStreamOutput();

// Run a command, disconnect from the server, and get the session output.
$frampt->runCommand('rm -rf /some-directory/file.txt')
    ->disconnect()
    ->getSessionOutput();
    
// Send a file and receive a file.
$frampt->sendFile(
    '/path/to/local/file.txt',
    '/path/to/remote/file.txt',
    0644
)->receiveFile(
    '/path/to/remote/file.txt',
    '/path/to/local/file.txt',
);
```

## API

```php
/**
 * Authenticate over SSH using a plain password.
 *
 * @param string $username
 * @param string $password
 *
 * @return \DefrostedTuna\Frampt\ClientInterface
 *
 * @throws \DefrostedTuna\Frampt\Exceptions\AuthenticationException
 */
public function authenticateWithPassword(
    string $username,
    string $password
) : ClientInterface;
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
 * @return \DefrostedTuna\Frampt\ClientInterface
 *
 * @throws \DefrostedTuna\Frampt\Exceptions\AuthenticationException
 */
public function authenticateWithPublicKey(
    string $username,
    string $publicKeyFile,
    string $privateKeyFile,
    string $passphrase = null
) : ClientInterface;
```

```php
/**
 * Disconnects from the remote server passed to the class instance.
 *
 * @return \DefrostedTuna\Frampt\ClientInterface
 *
 * @throws \DefrostedTuna\Frampt\Exceptions\ConnectionException
 */
public function disconnect() : ClientInterface;
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
 * Retrieves the output from each command run.
 *
 * @return string
 */
public function getStreamOutput() : string;
```

```php
/**
 * Retrieves the output from each command run during the session.
 *
 * @return string
 */
public function getSessionOutput() : string;
```

```php
/**
 * Clears the stream output for all previously run commands.
 *
 * @return ClientInterface
 */
public function clearStreamOutput() : ClientInterface;
```

```php
/**
 * Sets the command to be run on the given remote server instance.
 *
 * @param string $command
 *
 * @return \DefrostedTuna\Frampt\ClientInterface
 *
 * @throws \DefrostedTuna\Frampt\Exceptions\CommandException
 */
public function runCommand(string $command) : ClientInterface;
```

```php
/**
 * Sends a file to the remote server.
 *
 * @param string $localFile
 * @param string $remoteFile
 * @param int|null $permissions
 *
 * @return \DefrostedTuna\Frampt\ClientInterface
 *
 * @throws \DefrostedTuna\Frampt\Exceptions\CommandException
 */
public function sendFile(
    string $localFile,
    string $remoteFile,
    int $permissions = null
) : ClientInterface;
```

```php
/**
 * Receives a file from the remote server.
 *
 * @param string $remoteFile
 * @param string $localFile
 *
 * @return \DefrostedTuna\Frampt\ClientInterface
 *
 * @throws \DefrostedTuna\Frampt\Exceptions\CommandException
 */
public function receiveFile(
    string $remoteFile,
    string $localFile
) : ClientInterface;
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