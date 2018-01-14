<?php

use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class ServerDocrootTest extends LocalServerTestCase {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        static::createServerWithDocroot('./tests/localhost');
    }

    public function getPaths() {
        return [
            'a valid file'     => ['/a.php', 200],
            'an invalid file'  => ['/z.php', 404],

            // Forced status page
            'ok 200'           => ['/echo-status.php?status=200', 200],
            'moved 301'        => ['/echo-status.php?status=301', 301],
            'moved 302'        => ['/echo-status.php?status=302', 302],
            'bad request 400'  => ['/echo-status.php?status=400', 400],
            'unauthorized 401' => ['/echo-status.php?status=401', 401],
            'not found 404'    => ['/echo-status.php?status=404', 404],
            'server error 500' => ['/echo-status.php?status=500', 500],
        ];
    }

    /**
     * @dataProvider getPaths
     *
     * @param string $path
     * @param string $expectedStatus
     */
    public function testFetchValidFiles($path, $expectedStatus) {
        $url = $this->getLocalServerUrl() . $path;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        $curlinfo = curl_getinfo($curl);
        curl_close($curl);

        $this->assertEquals($expectedStatus, $curlinfo['http_code'], 'Unexpected status code returned');
    }

}