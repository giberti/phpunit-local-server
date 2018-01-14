<?php

use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class OptimizationTest extends LocalServerTestCase
{

    public function testReuseExistingServer()
    {
        static::createServerWithDocroot('./tests/localhost');
        $serverUrl = $this->getLocalServerUrl() . '/';
        $urlParts = parse_url($serverUrl);
        $port = $urlParts['port'];
        $this->assertGreaterThanOrEqual(8000, $port, 'The port was outside the expected range');

        static::createServerWithDocroot('./tests/localhost');
        $serverUrl = $this->getLocalServerUrl() . '/';
        $urlParts = parse_url($serverUrl);
        $this->assertEquals($port, $urlParts['port'], 'Creating the same server should return the same port');
    }

    public function testForceRestartOfServer()
    {
        static::createServerWithDocroot('./tests/localhost', true);
        $serverUrl = $this->getLocalServerUrl() . '/';
        $urlParts = parse_url($serverUrl);
        $port = $urlParts['port'];
        $this->assertGreaterThanOrEqual(8000, $port, 'The port was outside the expected range');

        static::createServerWithDocroot('./tests/localhost', true);
        $serverUrl = $this->getLocalServerUrl() . '/';
        $urlParts = parse_url($serverUrl);
        $this->assertGreaterThan($port, $urlParts['port'], 'Forcing a restart should increment the port');
    }

    public function testImpliedRestartOfServer()
    {
        static::createServerWithRouter('./tests/localhost/router.php');
        $serverUrl = $this->getLocalServerUrl() . '/';
        $urlParts = parse_url($serverUrl);
        $port = $urlParts['port'];
        $this->assertGreaterThanOrEqual(8000, $port, 'The port was outside the expected range');

        static::createServerWithRouter('./tests/localhost/a.php');
        $serverUrl = $this->getLocalServerUrl() . '/';
        $urlParts = parse_url($serverUrl);
        $this->assertGreaterThan($port, $urlParts['port'], 'A configuration change should restart the server and increment the port');
    }


    public function tearDown()
    {
        parent::tearDown();
        static::destroyServer();
    }
}
