<?php

namespace DefrostedTuna\Frampt;

interface ClientInterface
{
    /**
     * Authenticate over SSH using a plain password.
     *
     * @param string $username
     * @param string $password
     *
     * @return \DefrostedTuna\Frampt\ClientInterface
     *
     * @throws \Exception
     */
    public function authenticateWithPassword(
        string $username,
        string $password
    ) : ClientInterface;

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
     * @throws \Exception
     */
    public function authenticateWithPublicKey(
        string $username,
        string $publicKeyFile,
        string $privateKeyFile,
        string $passphrase = null
    ) : ClientInterface;

    /**
     * Disconnects from the remote server passed to the class instance.
     *
     * @return \DefrostedTuna\Frampt\ClientInterface
     */
    public function disconnect() : ClientInterface;

    /**
     * Retrieves the server property.
     *
     * @return string
     */
    public function getServer() : string;

    /**
     * Retrieves the authenticated property.
     *
     * @return bool
     */
    public function getAuthenticated() : bool;

    /**
     * Retrieves the output from each command run.
     *
     * @return string
     */
    public function getStreamOutput() : string;

    /**
     * Retrieves the output from each command run during the session.
     *
     * @return string
     */
    public function getSessionOutput() : string;

    /**
     * Clears the stream output for all previously run commands.
     *
     * @return ClientInterface
     */
    public function clearStreamOutput() : ClientInterface;

    /**
     * Sets the command to be run on the given remote server instance.
     *
     * @param string $command
     *
     * @return \DefrostedTuna\Frampt\ClientInterface
     */
    public function runCommand(string $command) : ClientInterface;
}
