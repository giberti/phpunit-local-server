<?php

use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class NoServerRunningTest extends LocalServerTestCase
{

    public function testGetServerUrl()
    {
        $this->assertNull($this->getLocalServerUrl(), 'Method should return `null` when server is not started');
    }
}
