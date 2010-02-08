<?php
// cached query class will be loaded additionally once we received the config
// see common/index.php for details
require_once('common/includes/class.db.php');
require_once('common/includes/class.dbfactory.php');
require_once('common/includes/class.config.php');

if(!defined('DB_TYPE')) define('DB_TYPE', 'mysqli');
require_once('common/includes/class.db.php');

$conn = new DBConnection;
$value = (float) mysqli_get_server_info($conn->id_);

if ($value < 5)
{
	die("EDK 3 requires MySQL version 5.0+. Your version is ".$value);
}

if(!isset($config)) $config = new Config(KB_SITE);
// DB_HALTONERROR may have been defined externally for sensitive operations.
if(!defined('DB_HALTONERROR')) define('DB_HALTONERROR', (bool)config::get('cfg_sqlhalt'));
define('DB_USE_QCACHE', (bool)config::get('cfg_qcache'));

if (((bool)config::get('cfg_memcache')) == true && !strstr($_SERVER['REQUEST_URI'], "admin"))
{
	require_once('common/includes/class.dbmemcache.php');
	if(!method_exists(Memcache, 'pconnect'))
	{
		$boardMessage = "ERROR: Unable to connect to memcached server, disabling memcached. Please check your settings (server, port) and make sure the memcached server is running";
		define("DB_USE_MEMCACHE", false);
	}
	else
	{
		$mc = new Memcache();
		if(!@$mc->pconnect(config::get('cfg_memcache_server'), config::get('cfg_memcache_port')))
		{
			$boardMessage = "ERROR: Unable to connect to memcached server, disabling memcached. Please check your settings (server, port) and make sure the memcached server is running";
			define("DB_USE_MEMCACHE", false);
		}
		else define("DB_USE_MEMCACHE", true);
	}
} else
{
	define("DB_USE_MEMCACHE", false);
}

if (DB_USE_QCACHE)
{
// the object overloading system will switch to cached queries now
	require_once('common/includes/class.dbcache.php');
}

