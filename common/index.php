<?php

/**
 * The EVE-Development Network Killboard
 * based on eve-killboard.net created by rig0r
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

// many ppl had issues with pear and relative paths
include_once('kbconfig.php');
// If there is no config then redirect to the install folder.
if(!defined('KB_SITE'))
{
	$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
	$url = substr($url, 0, strrpos($url,'/')+1)."install/";
	header("Location: ".$url);
	die;
}
require_once('common/includes/globals.php');
require_once('common/includes/php_compat.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.apicache.php');
require_once('common/includes/class.killboard.php');
require_once('common/includes/class.page.php');
require_once('common/includes/class.event.php');
require_once('common/includes/class.roles.php');
#require_once('common/includes/class.titles.php');
require_once('common/includes/class.user.php');
require_once('common/includes/class.session.php');
require_once('common/includes/class.cache.php');
require_once('common/includes/class.involvedloader.php');
require_once('common/smarty/Smarty.class.php');

// smarty doesnt like it
@set_magic_quotes_runtime(0);

// remove some chars from the request string to avoid 'hacking'-attempts
$page = str_replace('.', '', $_GET['a']);
$page = str_replace('/', '', $page);
if ($page == '' || $page == 'index')
{
    $page = 'home';
}

// check for the igb
if (substr($_SERVER['HTTP_USER_AGENT'], 0, 15) == 'EVE-minibrowser')
{
    define('IS_IGB', true);
    if (!isset($_GET['a']))
    {
        $page = 'igb';
    }
}
else
{
    define('IS_IGB', false);
}

// load the config from the database
$config = new Config(KB_SITE);
$ApiCache = new ApiCache(KB_SITE);
define('KB_HOST', config::get('cfg_kbhost'));
define('MAIN_SITE', config::get('cfg_mainsite'));
define('THEME_URL', config::get('cfg_kbhost').'/themes/'.config::get('theme_name'));
define('IMG_URL', config::get('cfg_img'));
define('KB_TITLE', config::get('cfg_kbtitle'));

// pilot id not fully implemented yet.
if (0 && config::get('cfg_pilotid'))
{
	define('PILOT_ID', intval(config::get('cfg_pilotid')) );
	define('CORP_ID', 0);
	define('ALLIANCE_ID', 0);
}
elseif (config::get('cfg_corpid'))
{
	define('PILOT_ID', 0);
	define('CORP_ID', intval(config::get('cfg_corpid')));
	define('ALLIANCE_ID', 0);
}
else
{
	define('PILOT_ID', 0);
	define('CORP_ID', 0);
	define('ALLIANCE_ID', intval(config::get('cfg_allianceid')));
}

// setting up smarty and feed it with some config
$smarty = new Smarty();
if(is_dir('./themes/'.config::get('theme_name').'/templates'))
	$smarty->template_dir = './themes/'.config::get('theme_name').'/templates';
else $smarty->template_dir = './themes/default/templates';

$smarty->compile_dir = './cache/templates_c';
$smarty->cache_dir = './cache/data';
$smarty->assign('theme_url', THEME_URL);
$smarty->assign('style', config::get('style_name'));
$smarty->assign('img_url', IMG_URL);
$smarty->assign('kb_host', KB_HOST);
$smarty->assign_by_ref('config', $config);

// set up titles/roles
role::init();
#title::init();

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
// Enable page caching if set.
if(config::get('cache_enabled')) define('KB_CACHE', 1);
else define('KB_CACHE', 0);

if(config::get('DBUpdate') < LASTEST_DB_UPDATE)
{
	// Check db is installed.
	if(config::get('cfg_kbhost'))
	{
		$url = preg_replace('/^http:\/\//','',KB_HOST."/upgrade/");
		// Remove any erroneous double slashes
		$url = preg_replace('/\/+/','/',$url);
		header('Location: http://'.$url);
		die;
	}
	// Should not be able to reach this point but have this just in case
	else
	{
		$html = "<html><head><title>Board not configured</title></head>";
		$html .= "<body>Killboard configuration not found. Go to ";
		// Make URL from current script URL
		$url = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
		$url = substr($url, 0, strrpos($url, '/',1)).'/install/';
		// Make sure no double slash has crept in.
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
	// Check DB is up to date.
    $page = 'admin/'.$page;
}

// old modcode for loading settings
if (substr($page, 0, 9) == 'settings_')
{
    $settingsPage = true;
}
else
{
    $settingsPage = false;
}
$mods_active = explode(',', config::get('mods_active'));
$modOverrides = false;
$modconflicts = array();
foreach ($mods_active as $mod)
{
    // load all active modules which need initialization
    if (file_exists('mods/'.$mod.'/init.php'))
    {
        include('mods/'.$mod.'/init.php');
    }
    if (file_exists('mods/'.$mod.'/'.$page.'.php'))
    {
		$modconflicts[] = $mod;
		/*
		if ($modOverrides)
        {
            die('Error: Two or more of the mods you have activated are conflicting');
        }
		 *
		 */
        $modOverrides = true;
        $modOverride = $mod;
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
}
$none = '';
event::call('mods_initialised', $none);
if (!$settingsPage && !file_exists('common/'.$page.'.php') && !$modOverrides)
{
    $page = 'home';
}
// Serve feeds to feed fetchers.
if(strpos($_SERVER['HTTP_USER_AGENT'], 'EDK Feedfetcher') !== false) $page = 'feed';
cache::check($page);
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
?>