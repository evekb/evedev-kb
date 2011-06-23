<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php'); die(); }
$stoppage = true;
$smarty->assign('conflict', false);
$smarty->assign('length', false);

if (isset($_REQUEST['submit']))
{
	foreach ($_POST['set'] as $name => $value)
	{
		$_SESSION['sett'][$name] = $value;
	}
}
$uri = 'http://'.$_SERVER['HTTP_HOST'].str_replace('/install/index.php','', $_SERVER['SCRIPT_NAME']);
if (empty($_SESSION['sett']['adminpw']))
{
	$_SESSION['sett']['adminpw'] = '';
}

if (empty($_SESSION['sett']['title']))
{
	$_SESSION['sett']['title'] = '';
}
if (empty($_SESSION['sett']['site']))
{
	$_SESSION['sett']['site'] = chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90));
}
if (empty($_SESSION['sett']['host']))
{
	$_SESSION['sett']['host'] = $uri;
}
if (empty($_SESSION['sett']['img']))
{
	$_SESSION['sett']['img'] = $uri.'/img';
}

if (isset($_SESSION['sett']['adminpw']) && strlen($_SESSION['sett']['adminpw']) > 0 && isset($_SESSION['sett']['site']))
{
	$stoppage = false;
}
if (isset($_SESSION['sett']['site']))
{
	$_SESSION['sett']['site'] = preg_replace("/[^\w\d_-]*/", "", $_SESSION['sett']['site']);
	if(strlen($_SESSION['sett']['site']) > 12 || strlen($_SESSION['sett']['site']) < 1)
	{
		$smarty->assign('length', true);
		$stoppage = true;
	}
}
$settings = array();
$settings[] = array('descr' => 'Adminpassword', 'name' => 'adminpw', 'value' => $_SESSION['sett']['adminpw']);
$settings[] = array('descr' => 'Title', 'name' => 'title', 'value' => $_SESSION['sett']['title']);
$settings[] = array('descr' => 'Site', 'name' => 'site', 'value' => $_SESSION['sett']['site']);
$settings[] = array('descr' => 'Host', 'name' => 'host', 'value' => $_SESSION['sett']['host']);
$settings[] = array('descr' => 'IMG URL', 'name' => 'img', 'value' => $_SESSION['sett']['img']);

$smarty->assign('settings', $settings);
$smarty->assign('stoppage', $stoppage);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step6.tpl');
?>