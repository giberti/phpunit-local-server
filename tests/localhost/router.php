<?php

$uri = $_SERVER['REQUEST_URI']; // expects: /echo-status/200
$uriParts = explode('/', $uri);
array_shift($uriParts);         // remove empty element at 0

if ('echo-status' !== $uriParts[0]) {

    // Error all the non echo-status requests
    header('HTTP/1.1 404');
    echo '404';

} else {

    // Hack the existing echo-status file
    $_GET['status'] = $uriParts[1];
    include 'echo-status.php';

}
