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
     * @throws \DefrostedTuna\Frampt\Exceptions\AuthenticationException
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
     * @throws \DefrostedTuna\Frampt\Exceptions\AuthenticationException
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
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\ConnectionException
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
     *
     * @throws \DefrostedTuna\Frampt\Exceptions\CommandException
     */
    public function runCommand(string $command) : ClientInterface;

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
}
