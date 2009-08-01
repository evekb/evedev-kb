<?php
//debut Mod PHPBB
// Gestion de la page 
require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');
session_start();
if (strstr(config::get("mods_active"),'apiuser')===false){} else
{
	if (!isset($_SESSION['phpOK']))
		header('location: checkphpbb.php');

	if (isset($_SESSION['phpOK']) && $_SESSION['phpOK']==0 && $_GET['a']<>'error')
	{
	header('location: index.php?a=error');
	exit();
	}
}
//fin Mod PHPBB
?>