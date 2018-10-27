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
     * The password to use for testing.
     *
     * @var string
     */
    protected $password;

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
        $this->password = 'apowerfulthingindeed';
        $this->publicKeyPath = '/path/to/undead-parish';
        $this->privateKeyPath = '/path/to/quelaags-domain';
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
     * Creates an instance of the Frampt Client.
     *
     * @return \DefrostedTuna\Frampt\Client
     */
    protected function createFramptClient() : Client
    {
        $framptClient = new Client($this->server);

        return $framptClient;
    }

    /**
     * Helper for creating mocks of native PHP functions.
     *
     * @param string $function
     * @param mixed $return
     *
     * @return void
     */
    protected function mockNative($function, ...$return) : void
    {
        PHPMockery::mock('DefrostedTuna\Frampt', $function)
            ->andReturn(...$return);
    }

    /**
     * This tests checks to make sure that the functionality for connecting to,
     * and authenticating with a server works properly. In this case, we are
     * attempting to authenticate with the server using a given password.
     *
     * @return void
     *
     * @test
     */
    public function it_can_authenticate_with_a_password() : void
    {
        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_password', true);
        $this->mockNative('ssh2_disconnect', true);

        $this->assertTrue($framptClient->authenticateWithPassword(
            $this->username,
            $this->password
        ));
    }

    /**
     * This test will make sure that an exception is thrown when the client
     * fails to connect and authenticate successfully with a given server.
     *
     * @return void
     *
     * @test
     */
    public function it_throws_an_exception_when_it_fails_to_authenticate_with_a_password() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Unable to authenticate with the server using plain password.'
        );

        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_password', false);
        $this->mockNative('ssh2_disconnect', true);

        $framptClient->authenticateWithPassword(
            $this->username,
            $this->password
        );
    }

    /**
     * This tests checks to make sure that the functionality for connecting to,
     * and authenticating with a server works properly. In this case, we are
     * attempting to authenticate with the server using a public ssh key.
     *
     * @return void
     *
     * @test
     */
    public function it_can_authenticate_with_a_public_key() : void
    {
        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_pubkey_file', true);
        $this->mockNative('ssh2_disconnect', true);

        $this->assertTrue($framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        ));
    }

    /**
     * This test will make sure that an exception is thrown when the client
     * fails to connect and authenticate successfully with a given server.
     *
     * @return void
     *
     * @test
     */
    public function it_throws_an_exception_when_it_fails_to_authenticate_with_a_public_key() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Unable to authenticate with the server using public ssh key.'
        );

        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_pubkey_file', false);
        $this->mockNative('ssh2_disconnect', true);

        $framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );
    }

    /**
     * Testing a private function here. This will make sure an exception is
     * thrown before attempting to connect to a sever that is unreachable.
     *
     * @return void
     *
     * @test
     */
    public function it_will_throw_an_exception_if_the_server_is_not_present_or_is_unreachable() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Server is unreachable.'
        );

        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', false);

        $framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );
    }

    /**
     * Testing a private function here. This will make sure an exception is
     * thrown if the connection attempt to the server was unsuccessful.
     *
     * @return void
     *
     * @test
     */
    public function it_will_throw_an_exception_if_the_client_cannot_connect_to_a_given_server() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Unable to connect to server.'
        );

        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', false);

        $framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );
    }

    /**
     * The way we're going to so this is by throwing an exception.
     * When the first authentication attempt goes through, it will
     * Set all of the required parameters. After the first connection,
     * We'll try to connect again via a different authentication method.
     * Once we try to connect, the client will see that there is already
     * a connection present, and it will trigger the disconnect function.
     * This is where we will forcibly return false and trigger an exception.
     * When the exception is fired, that's when we'll know we triggered the
     * disconnect method successfully and in the proper place as well.
     *
     * @return void
     *
     * @test
     */
    public function it_will_disconnect_from_an_existing_server_before_attempting_to_connect() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Unable to disconnect from server.'
        );

        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_pubkey_file', false);
        $this->mockNative('ssh2_disconnect', false);

        $framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );

        // Attempt to authenticate a second time, triggering the disconnect.
        // In turn, this is what will trigger the exception as well.
        $framptClient->authenticateWithPassword(
            $this->username,
            $this->password
        );
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

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_pubkey_file', true);
        $this->mockNative('ssh2_disconnect', true);

        $framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );

        $this->assertTrue($framptClient->disconnect());
        $this->assertFalse($framptClient->getAuthenticated());
    }

    /**
     * Ensure that the server connection can be disconnected,
     * even if there is no connection present on the client instance.
     *
     * @return void
     *
     * @test
     */
    public function it_will_allow_a_disconnect_if_there_is_no_connection_present() : void
    {
        $framptClient = $this->createFramptClient();

        $this->assertTrue($framptClient->disconnect());
    }

    /**
     * Ensure that the server connection throws an exception if any
     * errors were found during the disconnection process.
     *
     * @return void
     *
     * @test
     */
    public function it_will_throw_an_exception_if_it_fails_to_disconnect() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Unable to disconnect from server.'
        );

        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_pubkey_file', true);
        $this->mockNative('ssh2_disconnect', false);

        $framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );

        $framptClient->disconnect();
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
     * The client instance should be able to return the authentication status.
     *
     * @return void
     *
     * @test
     */
    public function it_can_get_the_authenticated_property() : void
    {
        $framptClient = $this->createFramptClient();

        $this->mockNative('fsockopen', true);
        $this->mockNative('ssh2_connect', true);
        $this->mockNative('ssh2_auth_pubkey_file', true);
        $this->mockNative('ssh2_disconnect', true);

        $framptClient->authenticateWithPublicKey(
            $this->username,
            $this->publicKeyPath,
            $this->privateKeyPath
        );

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

        $this->mockNative('ssh2_exec', true);
        $this->mockNative('stream_set_blocking', true);
        $this->mockNative('stream_get_contents', 'hollow');

        $output = $framptClient->runCommand('echo $HOLLOWING');

        $this->assertEquals('hollow', $output);
    }

    /**
     * When a command is run, we should throw an exception when it fails.
     *
     * @return void
     *
     * @test
     */
    public function it_will_throw_an_exception_when_it_fails_to_run_a_command() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Unable to process command on the remote server.'
        );

        $framptClient = $this->createFramptClient();

        $this->mockNative('ssh2_exec', false);

        $framptClient->runCommand('echo $HOLLOWING');
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

        $this->mockNative('ssh2_exec', true);
        $this->mockNative('stream_set_blocking', true);
        $this->mockNative('stream_get_contents', 'hollow', '10');

        $output1 = $framptClient->runCommand('echo $HOLLOWING');
        $output2 = $framptClient->runCommand('echo $HUMANITY');

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

        $this->mockNative('ssh2_exec', true);
        $this->mockNative('stream_set_blocking', true);
        $this->mockNative('stream_get_contents', 'hollow');

        $framptClient->runCommand('echo $HOLLOWING');

        // We need to escape the '$' so that PHP
        // does not interpret it as a variable.
        $this->assertEquals(
            "Frampt Command: echo \$HOLLOWING\nhollow",
            $framptClient->getStreamOutput()
        );
    }
}
