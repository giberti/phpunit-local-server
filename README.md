# PHPUnit Local Server

Provides an HTTP server test case for PHPUnit. The server is powered by PHP's built in server for testing of network related calls.

### Installing

```
composer require giberti/phpunit-local-server
```

### Usage

To use, extend `\Giberti\PHPUnitLocalServer\LocalServerTestCase` as if you were extending `\PHPUnit\Framework\TestCase`.

##### A single test

```php
use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class Test extends LocalServerTestCase
{

    public function testFoo() {
        static::createServerWithDocroot('./tests/localhost');
        $url = $this->getLocalServerUrl() . '/foo';

        $fh = fopen($url);
        $content = fread($fh, 1024);
        fclose($fh);

        $this->assertEquals('...', $content, 'Content mismatch');
    }
}
```

##### Several tests using the same configuration

```php
use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class Test extends LocalServerTestCase
{

    public static function setupBeforeClass() {
        static::createServerWithDocroot('./tests/localhost');
    }

    public function testFoo() {
        $url = $this->getLocalServer() . '/foo';
        $fh = fopen($this->getLocalServerUrl());
        $content = fread($fh, 1024);
        fclose($fh);

        $this->assertEquals('...', $content, 'Content mismatch');
    }

    public function testBar() {
        $url = $this->getLocalServer() . '/bar';
        $fh = fopen($this->getLocalServerUrl());
        $content = fread($fh, 1024);
        fclose($fh);

        $this->assertEquals('...', $content, 'Content mismatch');
    }
}
```


### Methods

The following methods are provided to interact with the local server.

#### public bool LocalServerTestCase::createServerWithDocroot(string $docroot)

Creates a local server using a document root.

```php
static::createServerWithDocroot('./path/to/site/files');
```

#### public bool LocalServerTestCase::createServerWithRouter(string $router)

Creates a local server using a router file. If you are using a framework, this is most likely the `index.php` file in your document route.

```php
static::createServerWithRouter('./path/to/router.php');
```

#### public void LocalServerTestCase::destroyServer(void)

Removes the local server. Useful to reset the session state. This is automatically called in the `tearDownAfterClass()` lifecycle method.

```
static::destroyServer()
```

#### public string LocalServerTestCase::getServerUrl(void)

The port for the server will _usually_ be `8000`, however, it is dynamically assigned in the event of a conflict. The safest way to access the host is to call the `getServerUrl()` method and use that as the root for any Url construction.

```php
$schemeHost = $this->getServerUrl();
$fullUrl    = $schemeHost . "/path/to/file/to/access";

echo $fullUrl; // http://localhost:8000/path/to/file/to/access
```