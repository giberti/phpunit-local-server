<?php

$status = isset($_GET['status']) ? (int) $_GET['status'] : 200;
header('HTTP/1.1 ' . $status);
echo $status;
