<?php

use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class ConfigurationFailureTest extends LocalServerTestCase
{

    // Lowering the defaults to fail tests faster
    public static $serverStartTimeout  = 1;
    public static $processStartTimeout = 2;

    // Local cache to reset server between tests
    private static $cachedValues;


    public function testTimeoutChangeWorks()
    {
        static::$serverStartTimeout  = 3;
        static::$processStartTimeout = 3;
        $startTime                   = microtime(true);
        $e                           = null;
        try {
            static::createServerWithDocroot('./tests/invalid-path');
        } catch (Exception $e) {
            // expected
        }
        $this->assertInstanceOf(\Exception::class, $e, 'Timeout failed to raise exception');
        $elapsed = microtime(true) - $startTime;
        $this->assertGreaterThan(3, $elapsed, 'Timeout adjustment incorrect');
    }

    public function testInvalidDocroot()
    {
        $this->expectException(Exception::class);
        static::createServerWithDocroot('./tests/invalid-path');
    }

    public function testInvalidRouter()
    {
        $this->expectException(Exception::class);
        static::createServerWithRouter('./invalid-router.php');
    }

    public function testInvalidHostname()
    {
        $this->expectException(Exception::class);
        static::$hostname = 'invalid host name';
        static::createServerWithDocroot('./tests/localhost');
    }

    public function setup()
    {
        parent::setup();

        static::$cachedValues = [
            'hostname'  => static::$hostname,
            'phpBinary' => static::$phpBinary,

            'serverStartTimeout'  => static::$serverStartTimeout,
            'processStartTimeout' => static::$processStartTimeout,
        ];
    }

    public function tearDown()
    {
        parent::tearDown();

        static::$hostname  = static::$cachedValues['hostname'];
        static::$phpBinary = static::$cachedValues['phpBinary'];

        static::$serverStartTimeout  = static::$cachedValues['serverStartTimeout'];
        static::$processStartTimeout = static::$cachedValues['processStartTimeout'];

        static::destroyServer();
    }
}
