<?php

namespace DefrostedTuna\Frampt;

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
     * The output that is returned from each command run during session.
     *
     * @var string
     */
    protected $streamOutput;

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
     * @throws \Exception
     */
    protected function verifyServerIsReachable() : bool
    {
        $socket = fsockopen($this->server, 22, $errno, $errstr, 15);

        if (! $socket) {
            throw new \Exception('Server is unreachable.');
        }

        return true;
    }

    /**
     * Connects to the remote server passed to the class instance.
     *
     * @return bool
     *
     * @throws \Exception
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
            throw new \Exception('Unable to connect to server.');
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
     * @return bool
     *
     * @throws \Exception
     */
    public function authenticateWithPassword(string $username, string $password) : bool
    {
        $this->connect();

        // Authenticate with the server using a plain password method.
        // This will return a boolean value based upon the success.
        $auth = ssh2_auth_password(
            $this->connection,
            $username,
            $password
        );

        if (! $auth) {
            throw new \Exception(
                'Unable to authenticate with the server using plain password.'
            );
        }

        $this->authenticated = $auth;

        return $this->authenticated;
    }

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
    ) : bool {
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
            throw new \Exception(
                'Unable to authenticate with the server using public ssh key.'
            );
        }

        $this->authenticated = $auth;

        return $this->authenticated;
    }

    /**
     * Disconnects from the remote server passed to the class instance.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function disconnect() : bool
    {
        // Only attempt a disconnect if a connection is present.
        if ($this->connection) {
            // This will return a boolean value based on upon success.
            $disconnect = ssh2_disconnect($this->connection);

            if (! $disconnect) {
                throw new \Exception('Unable to disconnect from server.');
            }

            // Reset the values to their state before authentication.
            $this->connection = null;
            $this->authenticated = null;
        }

        return true;
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
        return !! $this->authenticated;
    }

    /**
     * Retrieves the output from each command run during the instance.
     *
     * @return string
     */
    public function getStreamOutput() : string
    {
        return $this->streamOutput;
    }

    /**
     * Concatenates the output of the commands that have been run.
     *
     * @param string $output
     *
     * @return void
     */
    protected function concatenateStreamOutput(string $output) : void
    {
        $this->streamOutput .= $output;
    }

    /**
     * Sets the command to be run on the given remote server instance.
     *
     * @param string $command
     *
     * @return string
     */
    public function runCommand(string $command) : string
    {
        $this->concatenateStreamOutput("Frampt Command: {$command}\n");

        $output = $this->executeCommand($command);

        $this->concatenateStreamOutput($output);

        return $output;
    }

    /**
     * Runs the command on the remote server instance.
     *
     * @param $command
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function executeCommand($command) : string
    {
        $stream = ssh2_exec($this->connection, $command);

        if (! $stream) {
            throw new \Exception(
                'Unable to process command on the remote server.'
            );
        }

        stream_set_blocking($stream, true);

        return stream_get_contents($stream);
    }
}
