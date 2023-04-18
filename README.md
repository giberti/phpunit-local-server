# PHPUnit Local Server

Provides an HTTP server test case for PHPUnit. The server is powered by PHP's built-in server for testing of network related calls.

### Installing

This library requires PHP 7.3 or newer, including PHP 8.0, 8,1, and 8.2. It will run with PHPUnit versions 8 and 9.

```
composer require giberti/phpunit-local-server
```

### Usage

* Create a directory that will contain the code you want to execute
* Extend the `\Giberti\PHPUnitLocalServer\LocalServerTestCase` as if you were extending `\PHPUnit\Framework\TestCase`
* Start a server in the test method or for the entire class
* Make requests against the server

#### Usage Tips

* Whenever possible, re-use the existing server. Frequent restarts will slow down your tests.
* You can provide a different `php` binary by overriding the static `$phpBinary` property on the class.

##### A single test

Call either the `createServerWithDocroot()` or `createServerWithRouter()` helper method and then execute your test.

```php
use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class Test extends LocalServerTestCase
{
    public function testFoo() {
        static::createServerWithDocroot('./tests/localhost');
        $url = $this->getLocalServerUrl() . '/foo';

        $content = file_get_contents($url);

        $this->assertEquals('...', $content, 'Content mismatch');
    }
}
```

##### Several tests using the same configuration

To optimize performance of your tests, it's best to re-use the server whenever possible. To make this easier, simply start the server at the beginning of the class by defining a `setupBeforeClass()` method with your desired configuration.

```php
use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class Test extends LocalServerTestCase
{
    public static function setupBeforeClass() {
        static::createServerWithDocroot('./tests/localhost');
    }

    public function testFoo() {
        $url = $this->getLocalServer() . '/foo';
        $content = file_get_contents($url);

        $this->assertEquals('...', $content, 'Content mismatch');
    }

    public function testBar() {
        $url = $this->getLocalServer() . '/bar';
        $content = file_get_contents($url);

        $this->assertEquals('...', $content, 'Content mismatch');
    }
}
```

##### Modifying the server runtime version

It's possible to run the server under a different PHP runtime than the version running your test suite. This can help with testing your code under multiple versions of PHP. In the example below, the server will start with the PHP 7.3 and 8.1 executable in `/usr/local/bin/` on the host test system. Your path may be different.

```php
use Giberti\PHPUnitLocalServer\LocalServerTestCase;

class Test73 extends LocalServerTestCase
{
    static $phpBinary = '/usr/local/bin/php73';

    public function testFoo() {
        static::createServerWithDocroot('./tests/localhost');

        $url = $this->getLocalServer() . '/foo';
        $content = file_get_contents($url);

        $this->assertEquals('...', $content, 'Content mismatch');
    }
}

class Test81 extends LocalServerTestCase
{
    static $phpBinary = '/usr/local/bin/php81';

    public function testFoo() {
        static::createServerWithDocroot('./tests/localhost');

        $url = $this->getLocalServer() . '/foo';
        $content = file_get_contents($url);

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

```php
static::destroyServer();
```

#### public string LocalServerTestCase::getServerUrl(void)

The port for the server will _usually_ be `8000`, however, it is dynamically assigned in the event of a conflict. The safest way to access the host is to call the `getServerUrl()` method and use that as the root for any Url construction.

```php
$schemeHost = $this->getServerUrl();
$fullUrl    = $schemeHost . "/path/to/file/to/access";

echo $fullUrl; // http://localhost:8000/path/to/file/to/access
```
