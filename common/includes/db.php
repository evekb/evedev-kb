<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// cached query class will be loaded additionally once we received the config
// see common/index.php for details
if(!defined('DB_HOST'))
{
	trigger_error("Database has not been configured.", E_USER_ERROR);
	die("Database has not been configured. Exiting.");
}

$value = (float) mysqli_get_server_info(DBConnection::id());

if ($value < 5)
{
	die("EDK 3 requires MySQL version 5.0+. Your version is ".$value);
}

// Check if caching has been configured already.
if(defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true)
{
	if(!defined('DB_MEMCACHE_SERVER') || !defined('DB_MEMCACHE_PORT'))
		die("DB_MEMCACHE_SERVER and DB_MEMCACHE_PORT not defined. Memcache not started.");

	$mc = new Memcache();
	if(!@$mc->pconnect(DB_MEMCACHE_SERVER, DB_MEMCACHE_PORT))
		die("ERROR: Unable to connect to memcached server, disabling
			memcached. Please check your settings (server, port) and make
			sure the memcached server is running");
	else if(method_exists(Memcache, 'setCompressThreshold')) $mc->setCompressThreshold(10000, 0.2);
}
else if(defined('DB_USE_REDIS') && DB_USE_REDIS == true)
{
	if(!defined('DB_REDIS_SERVER') || !defined('DB_REDIS_PORT'))
		die("DB_REDIS_SERVER and DB_REDIS_PORT not defined. Redis not started.");

	$redis = new Redis();
	if(!@$redis->pconnect(DB_REDIS_SERVER, DB_REDIS_PORT))
		die("ERROR: Unable to connect to Redis server, disabling
			Redis. Please check your settings (server, port) and make
			sure the Redis server is running");
	else if(method_exists('Redis', 'select')) $redis->select(DB_REDIS_DB);
}
// If DB_USE_QCACHE is defined then it needs no further setup.
else if(defined('DB_USE_QCACHE'))
{ 
	if(!defined('DB_USE_MEMCACHE')) define('DB_USE_MEMCACHE', false);
	if(!defined('DB_USE_REDIS')) define('DB_USE_REDIS', false);
}
else
{
	if(!isset($config)) $config = new Config(KB_SITE);

	define('DB_USE_QCACHE', (bool)config::get('cfg_qcache'));

	if (!DB_USE_QCACHE)
	{
		if ((bool)config::get('cfg_memcache'))
		{
			if(!method_exists('Memcache', 'pconnect'))
			{
				$boardMessage = "ERROR: Memcache extension not installed. memcaching disabled.";
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
				if(method_exists('Memcache', 'setCompressThreshold')) $mc->setCompressThreshold(20000, 0.2);
			}
		}
		elseif ((bool)config::get('cfg_redis'))
		{
			if(!method_exists('Redis', 'pconnect'))
			{
				$boardMessage = "ERROR: Redis extension not installed. Redis disabled.";
				define("DB_USE_REDIS", false);
			}
			else
			{
				$redis = new Redis();
				if(!@$redis->pconnect(config::get('cfg_redis_server'), config::get('cfg_redis_port')))
				{
					$boardMessage = "ERROR: Unable to connect to Redis server, disabling Redis. Please check your settings (server, port, database) and make sure the Redis server is running";
					define("DB_USE_REDIS", false);
				}
				else
				{
					if(method_exists('Redis', 'select')) $redis->select((int) config::get('cfg_redis_db'));
					define("DB_USE_REDIS", true);
				}
			}
		} else {
			define("DB_USE_MEMCACHE", false);
			define("DB_USE_REDIS", false);
		}
	}
}

// DB_HALTONERROR may have been defined externally for sensitive operations.
if(!defined('DB_HALTONERROR')) define('DB_HALTONERROR', (bool)config::get('cfg_sqlhalt'));
