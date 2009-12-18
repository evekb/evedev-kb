<?php
include_once('../common/smarty/Smarty.class.php');

//May be a bit overkill to use smarty here, but this way the html is in the template
$smarty = new Smarty();
$smarty->template_dir = './templates';
//as much as i don't want it, the compiled templates directory is needed
$smarty->compile_dir = './templates_c';

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
?>
