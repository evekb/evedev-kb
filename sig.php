<?php
// parse url and populate GPR
$url_args = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], 'sig.php')+8);
$args = explode('/', $url_args);
$_GET['a'] = 'sig';
$_REQUEST['a'] = 'sig';

$_GET['i'] = $args[0];
$_REQUEST['i'] = $args[0];

$_GET['s'] = $args[1];
$_REQUEST['s'] = $args[1];

include('index.php');
?>