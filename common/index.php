<?php

/**
 * The EVE-Development Network Killboard
 * based on eve-killboard.net created by rig0r
 *
 * $Date$
 * $Revision$
 * $HeadURL$
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

// Start timing the killboard page.
$timeStarted = microtime(true);

@include_once('kbconfig.php');
// If there is no config then redirect to the install folder.
if(!defined('KB_SITE'))
{
	$html = "<html><head><title>Board not configured</title></head>";
	$html .= "<body>Killboard configuration not found. Go to ";
	$url = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
	$url = substr($url, 0, strrpos($url, '/',1)).'/install/';
	$url = preg_replace('/\/{2,}/','/',$url);
	$url = "http://".$url;
	$html .= "<a href='".$url."'>install</a> to install a new killboard";
	$html .= "</body></html>";
	die($html);
}
// Check the install folder is not accessible
else if(file_exists("install") && !file_exists("install/install.lock"))
{
	$html = "<html><head><title>Installation in progress</title></head>";
	$html .= "<body><p>Installation folder must be removed or locked to proceed.</p>";
	$html .= "<p>Go to <a href='".$url."'>Install</a> to install a new killboard.</p>";
	$html .= "</body></html>";
	die($html);
}
require_once('common/includes/globals.php');
if(isset($_GET['xajax'])) require_once('common/includes/xajax.functions.php');

// Set the default encoding to UTF-8
header('Content-Type: text/html; charset=UTF-8');

//edkloader::setRoot(getcwd());

// smarty doesnt like it
if(get_magic_quotes_runtime()) @set_magic_quotes_runtime(0);

// remove some chars from the request string to avoid 'hacking'-attempts
if(!isset($_GET['a'])) $_GET['a'] = 'home';
$page = str_replace('.', '', $_GET['a']);
$page = str_replace('/', '', $page);
if ($page == '' || $page == 'index')
{
	$page = 'home';
}
// Serve feeds to feed fetchers.
if(strpos($_SERVER['HTTP_USER_AGENT'], 'EDK Feedfetcher') !== false) $page = 'feed';

// Serve idfeeds to idfeed fetchers.
else if(strpos($_SERVER['HTTP_USER_AGENT'], 'EDK IDFeedfetcher') !== false) $page = 'idfeed';

// check for the igb
else if (strpos($_SERVER['HTTP_USER_AGENT'], 'EVE-IGB') !== FALSE)
{
	define('IS_IGB', true);
}
else
{
	define('IS_IGB', false);
}

// load the config from the database
$config = new Config();
if(!config::get('cfg_kbhost'))
{
	config::put('cfg_kbhost',
			"http://".$_SERVER['HTTP_HOST'].
			substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'],"/")));
}
if(!config::get('cfg_img'))
{
	config::put('cfg_img',
			config::get('cfg_kbhost')."/img");
}
define('KB_HOST', config::get('cfg_kbhost'));
define('MAIN_SITE', config::get('cfg_mainsite'));
define('IMG_URL', config::get('cfg_img'));
if(substr(IMG_URL, -4) == '/img') define('IMG_HOST', substr(IMG_URL, 0, strpos(IMG_URL, "/img")));
else  define('IMG_HOST', KB_HOST);
define('KB_TITLE', config::get('cfg_kbtitle'));

// set up themes.
if(isset($_GET['theme']))
{
	$themename = preg_replace('/[^0-9a-zA-Z-_]/','',$_GET['theme']);
	if(!is_dir("themes/".$themename))
	{
		$themename = config::get('theme_name');
		$stylename = config::get('style_name');
	}
	else
	{
		if(isset($_GET['style']))
		{
			$stylename = preg_replace('/[^0-9a-zA-Z-_]/','',$_GET['style']);
			if(!file_exists("themes/".$themename."/".$stylename.".css"))
				$stylename = config::get('style_name');
		}
		else $stylename = $themename;
	}
}
else
{
	$themename = config::get('theme_name');
	if(isset($_GET['style']))
	{
		$stylename = preg_replace('/[^0-9a-zA-Z-_]/','',$_GET['style']);
		if(!file_exists("themes/".$themename."/".$stylename.".css"))
			$stylename = config::get('style_name');
	}
	else $stylename = config::get('style_name');
}
define('THEME_URL', config::get('cfg_kbhost').'/themes/'.$themename);

// Ensure board owner is stored appropriately in the config.
// Should move to an update before release.
if(!is_array(config::get('cfg_pilotid')))
	if(config::get('cfg_pilotid')) config::set('cfg_pilotid',array(config::get('cfg_pilotid')));
	else config::set('cfg_pilotid',array());
if(!is_array(config::get('cfg_corpid'))) 
	if(config::get('cfg_corpid')) config::set('cfg_corpid',array(config::get('cfg_corpid')));
	else config::set('cfg_corpid',array());
if(!is_array(config::get('cfg_allianceid'))) 
	if(config::get('cfg_allianceid')) config::set('cfg_allianceid', array(config::get('cfg_allianceid')));
	else config::set('cfg_allianceid',array());

//Configure legacy defines.
if(config::get('cfg_allianceid'))
{
	$all = config::get('cfg_allianceid');
	define('PILOT_ID', 0);
	define('CORP_ID', 0);
	define('ALLIANCE_ID', $all[0] );
	unset($all);
}
elseif (config::get('cfg_corpid'))
{
	$crp = config::get('cfg_corpid');
	define('PILOT_ID', 0);
	define('CORP_ID', $crp[0] );
	define('ALLIANCE_ID', 0);
	unset($crp);
}
elseif (config::get('cfg_pilotid'))
{
	$plt = config::get('cfg_pilotid');
	define('PILOT_ID', $plt[0] );
	define('CORP_ID', 0);
	define('ALLIANCE_ID', 0);
	unset($plt);
}
else
{
	define('PILOT_ID', 0);
	define('CORP_ID', 0);
	define('ALLIANCE_ID', 0);
}

// set up titles/roles
role::init();
//title::init();

// start session management
session::init();

// reinforced management
if (config::get('auto_reinforced'))
{
	// first check if we are in reinforced
	if (config::get('is_reinforced'))
	{
		// every 1/x request we check for disabling RF
		if (rand(1, config::get('reinforced_rf_prob')) == 1)
		{
			cache::checkLoad();
		}
	}
	else
	{
		// reinforced not active
		// check for load and activate reinforced if needed
		if (rand(1, config::get('reinforced_prob')) == 1)
		{
			cache::checkLoad();
		}
	}
}

// Check if the database structure needs updating
if(config::get('DBUpdate') < LATEST_DB_UPDATE)
{
	// Check db is installed.
	if(config::get('cfg_kbhost'))
	{
		$url = preg_replace('/^http:\/\//','',KB_HOST."/update/");
		$url = preg_replace('/\/{2,}/','/',$url);
		header('Location: http://'.$url);
		die;
	}
	// Should not be able to reach this point but have this just in case
	else
	{
		$html = "<html><head><title>Board not configured</title></head>";
		$html .= "<body>Killboard configuration not found. Go to ";
		$url = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
		$url = substr($url, 0, strrpos($url, '/',1)).'/install/';
		$url = preg_replace('/\/+/','/',$url);
		$url = "http://".$url;
		$html .= "<a href='".$url."'>install</a> to install a new killboard";
		$html .= "</body></html>";
		die($html);
	}
}

// all admin files are now in the admin directory and preload the menu
if (substr($page, 0, 5) == 'admin')
{
	require_once('common/admin/admin_menu.php');
	$page = 'admin/'.$page;
}
else if(config::get('cfg_locked') && $page != 'login' && !session::isAdmin())
	$page = "locked";

// old modcode for loading settings
if (substr($page, 0, 9) == 'settings_')
{
	$settingsPage = true;
}
else
{
	$settingsPage = false;
}
if(file_exists("themes/".$themename."/init.php"))
	include_once("themes/".$themename."/init.php");
$mods_active = explode(',', config::get('mods_active'));
$modOverrides = false;
$modconflicts = array();

$modInfo = array();
foreach ($mods_active as $mod)
{
	// load all active modules which need initialization
	if (file_exists('mods/'.$mod.'/init.php'))
	{
		include('mods/'.$mod.'/init.php');
	}
	if(!isset($modInfo[$mod]))
		$modInfo[$mod] = array("name"=>$mod,
			"abstract"=>"Purpose unknown",
			"about"=>"");
	if (file_exists('mods/'.$mod.'/'.$page.'.php'))
	{
		$modconflicts[] = $mod;
		$modOverrides = true;
		$modOverride = $mod;
	}
}
if(count($modconflicts)>1)
{
	echo "<html><head></head><body>There are multiple active mods ".
			"for this page. Only one may be active at a time. All others ".
			"must be deactivated in the admin panel.<br>";
	foreach($modconflicts as $modname) echo $modname." <br> ";
	echo "</body>";
	die();
}

$none = '';
event::call('mods_initialised', $none);
if (!$settingsPage && !file_exists('common/'.$page.'.php') && !$modOverrides)
{
	$page = 'home';
}

cache::check($page);

// setting up smarty and feed it with some config
$smarty = new Smarty();
if(is_dir('./themes/'.$themename.'/templates'))
	$smarty->template_dir = './themes/'.$themename.'/templates';
else $smarty->template_dir = './themes/default/templates';

if(!is_dir(KB_CACHEDIR.'/templates_c/'.$themename))
	mkdir(KB_CACHEDIR.'/templates_c/'.$themename);
$smarty->compile_dir = KB_CACHEDIR.'/templates_c/'.$themename;

$smarty->cache_dir = KB_CACHEDIR.'/data';
$smarty->assign('theme_url', THEME_URL);
$smarty->assign('style', $stylename);
$smarty->assign('img_url', IMG_URL);
$smarty->assign('img_host', IMG_URL);
$smarty->assign('kb_host', KB_HOST);
$smarty->assignByRef('config', $config);
$smarty->assign('is_IGB', IS_IGB);

// Set the name of the board owner.
$owners = array();
if(config::get('cfg_allianceid'))
{
	foreach(config::get('cfg_allianceid') as $owner)
	{
		$alliance=new Alliance($owner);
		$owners[] = htmlentities($alliance->getName());
	}
	unset($alliance);
}
if (config::get('cfg_corpid'))
{
	foreach(config::get('cfg_corpid') as $owner)
	{
		$corp = new Corporation($owner);
		$owners[] = htmlentities($corp->getName());
	}
	unset($corp);
}
if (config::get('cfg_pilotid'))
{
	foreach(config::get('cfg_corpid') as $owner)
	{
		$pilot = new Pilot($owner);
		$owners[] = htmlentities($pilot->getName());
	}
	unset($pilot);
}
if(!$owners) $smarty->assign('kb_owner', false);
else $smarty->assign('kb_owner', implode(',', $owners));

// Show a system message on all pages if the init stage has generated any.
if(isset($boardMessage)) $smarty->assign('message', $boardMessage);
if ($settingsPage)
{
	if (!session::isAdmin())
	{
		header('Location: ?a=login');
		echo '<a href="?a=login">Login</a>';
		exit;
	}

	include('mods/'.substr($page, 9, strlen($page)-9).'/settings.php');
}
elseif ($modOverrides)
{
	include('mods/'.$modOverride.'/'.$page.'.php');
}
else
{
	include('common/'.$page.'.php');
}

cache::generate();
