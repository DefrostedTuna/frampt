<?php

namespace DefrostedTuna\Frampt;

interface ClientInterface
{
    /**
     * Connects to the remote server passed to the class instance.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function connect() : bool;

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
     * Retrieves the username property.
     *
     * @return string
     */
    public function getUsername() : string;

    /**
     * Retrieves the public ssh key path.
     *
     * @return string
     */
    public function getPublicKeyPath() : string;

    /**
     * Retrieves the private ssh key path.
     *
     * @return string
     */
    public function getPrivateKeyPath() : string;

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
