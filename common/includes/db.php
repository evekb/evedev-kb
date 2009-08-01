<?php
// cached query class will be loaded additionally once we received the config
// see common/index.php for details
require_once('common/includes/class.db.php');
require_once('common/includes/class.config.php');

if(!defined('DB_TYPE')) define('DB_TYPE', 'mysql');
if(DB_TYPE == 'mysqli' and function_exists('mysqli_connect')) define('DB_TYPE_USED', 'mysqli');
else define('DB_TYPE_USED', 'mysql');
if(DB_TYPE_USED == 'mysqli') require_once('common/includes/class.db.mysqli.php');

// get mysql server info and store it in a define so we know if its
// safe to use subquerys or not. (mysqli only works on 4.1+)
if(DB_TYPE_USED == 'mysqli')
{
	$conn = new DBConnection_mysqli;
	$value = (float) mysqli_get_server_info($conn->id_);
}
else
{
	$conn = new DBConnection;
	$value = (float) mysql_get_server_info($conn->id_);
}

if ($value >= 4.1)
{
	define('KB_MYSQL41', true);
}
else
{
	die("EDK 2.0 requires MySQL version 4.1+. Your version is ".$value);
	define('KB_MYSQL41', false);
}

if(!isset($config)) $config = new Config(KB_SITE);
// DB_HALTONERROR may have been defined externally for sensitive operations.
if(!defined('DB_HALTONERROR')) define('DB_HALTONERROR', (bool)config::get('cfg_sqlhalt'));
define('DB_USE_QCACHE', (bool)config::get('cfg_qcache'));

if (((bool)config::get('cfg_memcache')) == true && !strstr($_SERVER['REQUEST_URI'], "admin"))
{
    // mysqli version already loaded
    if(DB_TYPE_USED != 'mysqli') require_once('common/includes/class.db_memcache.php');
    $mc = new Memcache();
    if(!@$mc->pconnect(config::get('cfg_memcache_server'), config::get('cfg_memcache_port'))) {
       print "ERROR: Unable to connect to memcached server, disabling memcached. Please check your settings (server, port) and make sure the memcached server is running";
       define("DB_USE_MEMCACHE", false);
    } else {
       define("DB_USE_MEMCACHE", true);
    }
} else {
    define("DB_USE_MEMCACHE", false);
}

// mysqli version already loaded
if (DB_USE_QCACHE && DB_TYPE_USED != 'mysqli')
{
    // the object overloading system will switch to cached queries now
    require_once('common/includes/class.db_cache.php');
}
if (!$dir = config::get('cache_dir'))
{
    $dir = 'cache/data';
}
define('KB_CACHEDIR', $dir);

?>