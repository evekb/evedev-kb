<?php
/**
 * parse url and populate GPR
 * 
 * @package EDK
 */
$url_args = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], 'sig.php')+8);

if($_SERVER['PATH_INFO']) {
	$_SERVER['PATH_INFO'] = '/sig'.$_SERVER['PATH_INFO'];
}

$args = explode('/', $url_args);
$_GET['a'] = 'sig';
$_REQUEST['a'] = 'sig';

$_GET['i'] = $args[0];
$_REQUEST['i'] = $args[0];

$_GET['s'] = $args[1];
$_REQUEST['s'] = $args[1];
$_SERVER['QUERY_STRING'] = 'a=sig&i='.$args[0].'&s='.$args[1];

include('index.php');