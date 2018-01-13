<?php

$status = isset($_GET['status']) ? $_GET['status'] : 200;
header('HTTP/1.1 ' . $status);
