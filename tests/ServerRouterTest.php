<?php

use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class ServerRouterTest extends LocalServerTestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::createServerWithRouter('./tests/localhost/router.php');
    }

    public static function getPaths()
    {
        return [
            'ok 200'           => [
                '/echo-status/200',
                200,
            ],
            'moved 301'        => [
                '/echo-status/301',
                301,
            ],
            'moved 302'        => [
                '/echo-status/302',
                302,
            ],
            'bad request 400'  => [
                '/echo-status/400',
                400,
            ],
            'unauthorized 401' => [
                '/echo-status/401',
                401,
            ],
            'not found 404'    => [
                '/echo-status/404',
                404,
            ],
            'server error 500' => [
                '/echo-status/500',
                500,
            ],
        ];
    }

    /**
     * @dataProvider getPaths
     *
     * @param string $path
     * @param string $expectedStatus
     */
    public function testFetchValidFiles($path, $expectedStatus)
    {
        $url = $this->getLocalServerUrl() . $path;
        $result = @file_get_contents($url);
        if ($expectedStatus >= 400) {
            $this->assertFalse($result);
        } else {
            $this->assertEquals($expectedStatus, $result);
        }
    }
}
