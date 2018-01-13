<?php

$uri = $_SERVER['REQUEST_URI'];
$uriParts = explode('/', $uri);
array_shift($uriParts); // remove empty first element

if ('echo-status' !== $uriParts[0]) {

    // Error all the non echo-status requests
    header('HTTP/1.1 404');

} else {

    // Hack the existing echo-status file
    $_GET['status'] = $uriParts[1];
    include 'echo-status.php';
}
