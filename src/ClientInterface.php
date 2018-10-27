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
     * @return bool
     *
     * @throws \Exception
     */
    public function authenticateWithPassword(string $username, string $password) : bool;

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

    /**
     * Disconnects from the remote server passed to the class instance.
     *
     * @return bool
     */
    public function disconnect() : bool;

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
     * Retrieves the output from each command run during the instance.
     *
     * @return string
     */
    public function getStreamOutput() : string;

    /**
     * Sets the command to be run on the given remote server instance.
     *
     * @param string $command
     *
     * @return string
     */
    public function runCommand(string $command) : string;
}
