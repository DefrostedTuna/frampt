<?php

namespace DefrostedTuna\Frampt;

use DefrostedTuna\Frampt\Exceptions\{
    AuthenticationException,
    CommandException,
    ConnectionException
};

class Client implements ClientInterface
{
    /**
     * IP or Hostname of the server to connect to.
     *
     * @var string
     */
    protected $server;

    /**
     * The instance of the connection to the server.
     *
     * @var resource
     */
    protected $connection;

    /**
     * Whether or not the session is authenticated.
     *
     * @var bool
     */
    protected $authenticated;

    /**
     * The output that is returned from the previously run command.
     *
     * @var string
     */
    protected $streamOutput;

    /**
     * The output that is returned from each command run during session.
     *
     * @var string
     */
    protected $sessionOutput;

    /**
     * Sets the remote server that is being connected to.
     *
     * @param string $server
     */
    public function __construct(string $server)
    {
        $this->server = $server;
    }

    /**
     * Disconnects from the remote server instance when the class is destroyed.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Opens a socket to a specified server to make sure the server is online.
     *
     * @return bool
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\ConnectionException
     */
    protected function verifyServerIsReachable() : bool
    {
        $socket = fsockopen($this->server, 22, $errno, $errstr, 15);

        if (! $socket) {
            throw new ConnectionException('Server is unreachable.');
        }

        return true;
    }

    /**
     * Connects to the remote server passed to the class instance.
     *
     * @return bool
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\ConnectionException
     */
    protected function connect() : bool
    {
        // Disconnect if there is already an established connection.
        if ($this->connection) {
            $this->disconnect();
        }

        // This will open a socket to make sure the server exists.
        // If the server does not exist or is unreachable, then
        // an exception will be thrown that ends the script.
        $this->verifyServerIsReachable();

        // Connect to the server with the native PHP library.
        // This will return a resource upon success, or it
        // will return false if errors are encountered.
        $connection = ssh2_connect($this->server);

        if (! $connection) {
            throw new ConnectionException('Unable to connect to server.');
        }

        $this->connection = $connection;

        return true;
    }

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
    ) : ClientInterface {
        $this->connect();

        // Authenticate with the server using a plain password method.
        // This will return a boolean value based upon the success.
        $auth = ssh2_auth_password(
            $this->connection,
            $username,
            $password
        );

        if (! $auth) {
            throw new AuthenticationException(
                'Unable to authenticate with the server using plain password.'
            );
        }

        $this->authenticated = $auth;

        return $this;
    }

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
    ) : ClientInterface {
        $this->connect();

        // Authenticate with the server using a public ssh key method.
        // This will return a boolean value based upon the success.
        $auth = ssh2_auth_pubkey_file(
            $this->connection,
            $username,
            $publicKeyFile,
            $privateKeyFile,
            $passphrase
        );

        if (! $auth) {
            throw new AuthenticationException(
                'Unable to authenticate with the server using public ssh key.'
            );
        }

        $this->authenticated = $auth;

        return $this;
    }

    /**
     * Disconnects from the remote server passed to the class instance.
     *
     * @return \DefrostedTuna\Frampt\ClientInterface
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\ConnectionException
     */
    public function disconnect() : ClientInterface
    {
        // Only attempt a disconnect if a connection is present.
        if ($this->connection) {
            // This will return a boolean value based on upon success.
            $disconnect = ssh2_disconnect($this->connection);

            if (! $disconnect) {
                throw new ConnectionException('Unable to disconnect from server.');
            }

            // Reset the values to their state before authentication.
            $this->connection = null;
            $this->authenticated = null;
        }

        return $this;
    }

    /**
     * Retrieves the server property.
     *
     * @return string
     */
    public function getServer() : string
    {
        return $this->server;
    }

    /**
     * Retrieves the authenticated property.
     *
     * @return bool
     */
    public function getAuthenticated() : bool
    {
        return (bool) $this->authenticated;
    }

    /**
     * Retrieves the output from each command run during the instance.
     *
     * @return string
     */
    public function getStreamOutput() : string
    {
        return $this->streamOutput ?: '';
    }

    /**
     * Retrieves the output from each command run during the session.
     *
     * @return string
     */
    public function getSessionOutput() : string
    {
        return $this->sessionOutput ?: '';
    }

    /**
     * Clears the stream output for all previously run commands.
     *
     * @return ClientInterface
     */
    public function clearStreamOutput() : ClientInterface
    {
        $this->streamOutput = null;

        return $this;
    }

    /**
     * Concatenates the output of the commands that have been run.
     *
     * @param string $output
     *
     * @return ClientInterface
     */
    protected function concatenateStreamOutput(string $output) : ClientInterface
    {
        $this->streamOutput .= $output;
        $this->concatenateSessionOutput($output);

        return $this;
    }

    /**
     * Concatenates the output of the commands that have been run.
     *
     * @param string $output
     *
     * @return \DefrostedTuna\Frampt\ClientInterface
     */
    protected function concatenateSessionOutput(string $output) : ClientInterface
    {
        $this->sessionOutput .= $output;

        return $this;
    }

    /**
     * Sets the command to be run on the given remote server instance.
     *
     * @param string $command
     *
     * @return \DefrostedTuna\Frampt\ClientInterface
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\CommandException
     */
    public function runCommand(string $command) : ClientInterface
    {
        $this->concatenateSessionOutput("Frampt Command: {$command}\n");

        $output = $this->executeCommand($command);

        $this->concatenateStreamOutput($output);

        return $this;
    }

    /**
     * Runs the command on the remote server instance.
     *
     * @param $command
     *
     * @return string
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\CommandException
     */
    protected function executeCommand($command) : string
    {
        $stream = ssh2_exec($this->connection, $command);

        if (! $stream) {
            throw new CommandException(
                'Unable to process command on the remote server.'
            );
        }

        stream_set_blocking($stream, true);

        return stream_get_contents($stream);
    }

    /**
     * Sends a file to the remote server.
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int|null $permissions
     *
     * @return ClientInterface
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\CommandException
     */
    public function sendFile(
        string $localFile,
        string $remoteFile,
        int $permissions = null
    ) : ClientInterface {
        $sent = ssh2_scp_send(
            $this->connection,
            $localFile,
            $remoteFile,
            $permissions
        );

        if (! $sent) {
            throw new CommandException(
                'Unable to send file to remote server.'
            );
        }

        return $this;
    }

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
    ) : ClientInterface {
        $received = ssh2_scp_recv($this->connection, $remoteFile, $localFile);

        if (! $received) {
            throw new CommandException(
                'Unable to receive file to remote server.'
            );
        }

        return $this;
    }
}
