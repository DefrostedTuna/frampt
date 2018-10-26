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
     * The username in which to use when logging into the server.
     *
     * @var string
     */
    protected $username;

    /**
     * The path where the public key is stored.
     *
     * @var string
     */
    protected $publicKeyPath;

    /**
     * The path where the private key is stored.
     *
     * @var string
     */
    protected $privateKeyPath;

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
     * @param string $username
     * @param string $publicKeyPath
     * @param string $privateKeyPath
     */
    public function __construct(
        string $server,
        string $username,
        string $publicKeyPath,
        string $privateKeyPath
    ) {
        $this->server = $server;
        $this->username = $username;
        $this->publicKeyPath = $publicKeyPath;
        $this->privateKeyPath = $privateKeyPath;
    }

    /**
     * Disconnects from the remote server instance when the class is destroyed.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Connects to the remote server passed to the class instance.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function connect() : bool
    {
        try {
            $connection = ssh2_connect($this->server);

            $this->connection = $connection;

            return $this->authenticate();
        } catch (\Exception $e) {
            throw new $e;
        }
    }

    /**
     * Disconnects from the remote server passed to the class instance.
     *
     * @return bool
     */
    public function disconnect() : bool
    {
        return ssh2_disconnect($this->connection);
    }

    /**
     * Authenticates the remote server connection instance via ssh key.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function authenticate() : bool
    {
        try {
            $auth = ssh2_auth_pubkey_file(
                $this->connection,
                $this->username,
                $this->publicKeyPath,
                $this->privateKeyPath
            );

            $this->authenticated = $auth;

            return $this->authenticated;
        } catch (\Exception $e) {
            throw new $e;
        }
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
     * Retrieves the username property.
     *
     * @return string
     */
    public function getUsername() : string
    {
        return $this->username;
    }

    /**
     * Retrieves the public ssh key path.
     *
     * @return string
     */
    public function getPublicKeyPath() : string
    {
        return $this->publicKeyPath;
    }

    /**
     * Retrieves the private ssh key path.
     *
     * @return string
     */
    public function getPrivateKeyPath() : string
    {
        return $this->privateKeyPath;
    }

    /**
     * Retrieves the authenticated property.
     *
     * @return bool
     */
    public function getAuthenticated() : bool
    {
        return $this->authenticated;
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
     */
    protected function executeCommand($command) : string
    {
        $stream = ssh2_exec($this->connection, $command);

        stream_set_blocking($stream, true);

        return stream_get_contents($stream);
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
}
