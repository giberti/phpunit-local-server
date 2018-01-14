<?php

namespace Giberti\PHPUnitLocalServer;

class LocalServerTestCase extends \PHPUnit\Framework\TestCase {

    /**
     * The location of PHP on this system
     *
     * @var string $phpBinary
     */
    static $phpBinary = 'php';

    /**
     * The hostname to use for this test
     *
     * @var string $hostname
     */
    static $hostname = 'localhost';

    /**
     * Holds references to the pid and port that was started
     *
     * @var array $server
     */
    static $server;

    /**
     * Fingerprint of the currently running server config
     *
     * @var string $fingerprint
     */
    static $fingerprint;

    /**
     * How many seconds to wait for an instance of the server to start
     *
     * @var int $serverStartTimeout
     */
    static $serverStartTimeout = 2;

    /**
     * Number of seconds to wait for a new server process to start
     *
     * @var int $serviceStartTimeout
     */
    static $processStartTimeout = 5;

    /**
     * Microseconds to sleep between checks to see if server has started
     *
     * 1000    =   1ms
     * 100000  = 100ms (0.1 seconds)
     * 1000000 =   1 seconds
     *
     * @var int $defaultSleep
     */
    static $defaultSleep = 100000;

    const COMMAND_TEMPLATE_DOCROOT = '%s -S %s:%d -t %s > /dev/null 2>&1 & echo $!';
    const COMMAND_TEMPLATE_ROUTER  = '%s -S %s:%d %s > /dev/null 2>&1 & echo $!';

    /**
     * Start a local server with the provided document root
     *
     * @param string $docroot The directory that should be used for the server
     * @param bool   $forceRestart True will restart the server, even if the configuration is the same
     *
     * @return bool
     * @throws \Exception
     */
    public static function createServerWithDocroot($docroot, $forceRestart = false) {

        return static::createServer(static::COMMAND_TEMPLATE_DOCROOT, $docroot, $forceRestart);
    }

    /**
     * Start a local server with the provided router file
     *
     * @param string $router The router file the server should use
     * @param bool   $forceRestart True will restart the server, even if the configuration is the same
     *
     * @return bool
     * @throws \Exception
     */
    public static function createServerWithRouter($router, $forceRestart = false) {
        if (!file_exists($router)) {
            throw new \Exception('Router file not found');
        }

        return static::createServer(static::COMMAND_TEMPLATE_ROUTER, $router, $forceRestart);
    }

    /**
     * Returns the base Url to use for local server requests
     *
     * @return string|null Url in the form of http://{hostname}:{port}
     */
    public function getLocalServerUrl() {
        if (!static::$server) {
            return null;
        }

        return 'http://' . static::$hostname . ':' . static::$server['port'];
    }

    /**
     * Start the PHP HTTP server
     *
     * @param string $template The COMMAND_TEMPLATE_* constant used to generate the start server command
     * @param string $param Either the router file or document route to use with this server
     * @param bool   $forceRestart True will restart the server, even if the configuration is the same
     *
     * @return bool True if the server started, otherwise an exception
     * @throws \Exception
     */
    private static function createServer($template, $param, $forceRestart = false) {
        $fingerprint = md5($template . $param);

        // Only restart the server if necessary
        if ($forceRestart) {
            static::destroyServer();
        } elseif (static::$server && static::isServerRunning()) {
            if (static::$fingerprint == $fingerprint) {

                return true;
            }
            static::destroyServer();
        }

        static $port;
        if (!$port) {
            $port = 8000;
        }

        $processStartTime = microtime(true);
        do {
            // Seek an unused port
            while (static::isPortAcceptingConnections($port)) {
                $port++;
            }

            // Start the server
            $command = sprintf($template, static::$phpBinary, static::$hostname, $port, $param);
            exec($command, $output);
            $serverStartTime = microtime(true);
            $pid = $output[0];

            // Wait for server to respond
            do {
                usleep(static::$defaultSleep);
                if (static::isPortAcceptingConnections($port)) {
                    // Server has started
                    static::$server = [
                        'pid'  => $pid,
                        'port' => $port,
                    ];
                }
                $serverElapsedTime = microtime(true) - $serverStartTime;
            } while ($serverElapsedTime < static::$serverStartTimeout && !static::$server);

            // Kill the process that was started
            if (!static::$server) {
                posix_kill($pid, 9);
                $port++;
            }

            $processElapsedTime = microtime(true) - $processStartTime;
        } while ($processElapsedTime < static::$processStartTimeout && !static::$server);

        // Throw an exception if the server did not start
        if (!static::$server) {
            throw new \Exception('Unable to start server for ' . $param);
        }

        static::$fingerprint = $fingerprint;
        return true;
    }

    /**
     * Check to see if the current server is healthy
     *
     * @return bool
     */
    private static function isServerRunning() {
        if (static::$server
            && posix_getpgid(static::$server['pid'])
            && static::isPortAcceptingConnections(static::$server['port'])
        ){
            return true;
        }

        return false;
    }

    /**
     * Attempts to make a connection to a service running on the port
     *
     * @param int $port
     *
     * @return bool True if something responds, false otherwise
     */
    private static function isPortAcceptingConnections($port) {
        clearstatcache();
        $socket = @fsockopen(static::$hostname, $port);
        if ($socket) {
            fclose($socket);

            return true;
        }

        return false;
    }

    /**
     * Removes the current server (if any)
     */
    protected static function destroyServer() {
        if (static::$server) {
            posix_kill(static::$server['pid'], 9);
            static::$server = null;
            static::$fingerprint = null;
        }
    }

    /**
     * Cleans up any remaining servers at the end of the test execution
     */
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        static::destroyServer();
    }
}
