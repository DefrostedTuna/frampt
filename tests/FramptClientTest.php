<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use phpmock\mockery\PHPMockery;
use DefrostedTuna\Frampt\Client;

class FramptClientTest extends TestCase
{
    /**
     * The server to use for testing.
     *
     * @var string
     */
    protected $server;

    /**
     * The username to use for testing.
     *
     * @var string
     */
    protected $username;

    /**
     * The public ssh key path to use for testing.
     *
     * @var string
     */
    protected $publicKeyPath;

    /**
     * The private ssh key path to use for testing.
     *
     * @var string
     */
    protected $privateKeyPath;

    /**
     * The logic to be performed before each test is run.
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->server = 'kiln-of-the-first-flame.test';
        $this->username = 'gwyn';
        $this->publicKeyPath = '/path/to/undead-parish';
        $this->privateKeyPath = '/path/to/quelaags-domain';

        $this->setRequiredMocks();
    }

    /**
     * The logic to be performed after finishing each test.
     *
     * @return void
     */
    public function tearDown() : void
    {
        Mockery::close();
    }

    /**
     * Mock the native PHP ssh2 functions so that we don't
     * have to reach out to an actual server to perform testing.
     *
     * @return void
     */
    protected function setRequiredMocks() : void
    {
        PHPMockery::mock('DefrostedTuna\Frampt', 'ssh2_connect')
            ->andReturn(true);
        PHPMockery::mock('DefrostedTuna\Frampt', 'ssh2_auth_pubkey_file')
            ->andReturn(true);
        PHPMockery::mock('DefrostedTuna\Frampt', 'ssh2_disconnect')
            ->andReturn(true);
    }

    /**
     * Creates an instance of the Frampt Client.
     *
     * @return \DefrostedTuna\Frampt\Client
     */
    protected function createFramptClient() : Client
    {
        $framptClient = new Client(
            $this->server,
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );

        return $framptClient;
    }

    /**
     * This tests checks to make sure that the functionality for connecting to
     * a server works properly. By association, it also tests to make sure that
     * it can successfully authenticate, and that it can disconnect as well.
     *
     * @return void
     *
     * @test
     */
    public function connects_to_a_given_server() : void
    {
        $framptClient = $this->createFramptClient();

        $this->assertTrue($framptClient->connect());
    }

    /**
     * Ensure that the server connection can be disconnected manually.
     *
     * @return void
     *
     * @test
     */
    public function it_can_manually_disconnect_from_a_server() : void
    {
        $framptClient = $this->createFramptClient();

        $this->assertTrue($framptClient->disconnect());
    }

    /**
     * The client instance should be able to return the server being used.
     *
     * @return void
     *
     * @test
     */
    public function it_can_get_the_server_property() : void
    {
        $framptClient = $this->createFramptClient();

        $this->assertEquals($this->server, $framptClient->getServer());
    }

    /**
     * The client instance should be able to return the username being used.
     *
     * @return void
     *
     * @test
     */
    public function it_can_get_the_username_property() : void
    {
        $framptClient = $this->createFramptClient();

        $this->assertEquals($this->username, $framptClient->getUsername());
    }

    /**
     * The client instance should be able to return the public key path.
     *
     * @return void
     *
     * @test
     */
    public function it_can_get_the_public_key_path_property() : void
    {
        $framptClient = $this->createFramptClient();

        $this->assertEquals(
            $this->publicKeyPath,
            $framptClient->getPublicKeyPath()
        );
    }

    /**
     * The client instance should be able to return the private key path.
     *
     * @return void
     *
     * @test
     */
    public function it_can_get_the_private_key_path_property() : void
    {
        $framptClient = $this->createFramptClient();

        $this->assertEquals(
            $this->privateKeyPath,
            $framptClient->getPrivateKeyPath()
        );
    }

    /**
     * The client instance should be able to return the authentication status.
     *
     * @return void
     *
     * @test
     */
    public function it_can_get_the_authenticated_property() : void
    {
        $framptClient = $this->createFramptClient();

        // Connect so that it will authenticate.
        $framptClient->connect();

        $this->assertTrue($framptClient->getAuthenticated());
    }

    /**
     * The client should be able to run a command on the remote server.
     *
     * @return void
     *
     * @test
     */
    public function it_can_run_a_command() : void
    {
        $framptClient = $this->createFramptClient();

        // Mock the execution of commands.
        PHPMockery::mock('DefrostedTuna\Frampt', 'ssh2_exec');
        PHPMockery::mock('DefrostedTuna\Frampt', 'stream_set_blocking');
        PHPMockery::mock('DefrostedTuna\Frampt', 'stream_get_contents')
            ->andReturn('hollow');

        $framptClient->connect();
        $output = $framptClient->runCommand('echo $HOLLOWING');
        $framptClient->disconnect();

        $this->assertEquals('hollow', $output);
    }

    /**
     * The client should be able to run multiple commands
     * back to back before closing the connection.
     *
     * @return void
     *
     * @test
     */
    public function it_can_run_a_series_of_commands() : void
    {
        $framptClient = $this->createFramptClient();

        // Mock the execution of commands.
        PHPMockery::mock('DefrostedTuna\Frampt', 'ssh2_exec');
        PHPMockery::mock('DefrostedTuna\Frampt', 'stream_set_blocking');
        PHPMockery::mock('DefrostedTuna\Frampt', 'stream_get_contents')
            ->andReturn('hollow', '10');

        $framptClient->connect();
        $output1 = $framptClient->runCommand('echo $HOLLOWING');
        $output2 = $framptClient->runCommand('echo $HUMANITY');
        $framptClient->disconnect();

        $this->assertEquals('hollow', $output1);
        $this->assertEquals('10', $output2);
    }

    /**
     * The client instance should be able to retrieve the stream output.
     *
     * @return void
     *
     * @test
     */
    public function it_can_get_the_stream_output() : void
    {
        $framptClient = $this->createFramptClient();

        // Mock the execution of commands.
        PHPMockery::mock('DefrostedTuna\Frampt', 'ssh2_exec');
        PHPMockery::mock('DefrostedTuna\Frampt', 'stream_set_blocking');
        PHPMockery::mock('DefrostedTuna\Frampt', 'stream_get_contents')
            ->andReturn('hollow');

        $framptClient->connect();
        $framptClient->runCommand('echo $HOLLOWING');
        $framptClient->disconnect();

        // We need to escape the '$' so that PHP
        // does not interpret it as a variable.
        $this->assertEquals(
            "Frampt Command: echo \$HOLLOWING\nhollow",
            $framptClient->getStreamOutput()
        );
    }
}
