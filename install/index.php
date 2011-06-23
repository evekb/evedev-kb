<?php
/**
 * @package EDK
 */

@error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', 1);
include_once('../common/smarty/Smarty.class.php');

//May be a bit overkill to use smarty here, but this way the html is in the template
$smarty = new Smarty();
$smarty->template_dir = './templates';
//as much as i don't want it, the compiled templates directory is needed
$smarty->compile_dir = '../cache/templates_c';
$cacheWriteable = true;
if(!file_exists('../cache'))
{
	// If we can create it then it's writeable.
	if(!mkdir('../cache')) $cacheWriteable = false;
	else if(!mkdir('../cache/templates_c')) $cacheWriteable = false;
}
else if(!is_writeable('../cache'))
{
	if(!chmod('../cache', 755)) $cacheWriteable = false;
	else if(!mkdir('../cache/templates_c')) $cacheWriteable = false;
}
else if(!file_exists('../cache/templates_c'))
{
	// If we can create it then it's writeable.
	if(!mkdir('../cache/templates_c')) $cacheWriteable = false;
}
else if(!is_writeable('../cache/templates_c'))
{
	if(chmod('../cache/templates_c', 755)) $cacheWriteable = false;
}
if(!cacheWriteable)
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
	<meta http-equiv="content-type" content="text/html; charset=UTF8">
	<title>EVE Development Network Killboard Install Script</title>
	<link rel="stylesheet" type="text/css" href="common.css">
	<link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body style="background-color:#222222; height: 100%">
		<p>The cache directory is not writeable. [killboard]/cache/ must exist and be writeable to install the killboard.</p>
		<p>Make sure the cache directory exists and permissions are recursively set to 755 or 777.
    </body>
</html>
<?php
/**
 * @package EDK
 */

die;
}
$installrunning = true;
session_start();

if (!isset($_SESSION['state']))
{
	$_SESSION['state'] = 1;
}
elseif (isset($_GET['step']) && $step = intval($_GET['step']))
{
	$_SESSION['state'] = $step;
}

//set the smarty stuff, and render
$smarty->assign('date', date("Y"));
$smarty->assign('stepnumber', $_SESSION['state']);
$smarty->assign('inst_locked', file_exists('install.lock'));
$smarty->display('index.tpl');

//won't load the page parts unless the lockfile's gone
if(!file_exists('install.lock'))
{ 
	include('install_step'.$_SESSION['state'].'.php');
}

$smarty->display('index_bottom.tpl');

