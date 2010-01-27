<?php

require_once('common/includes/class.registry.php');

class Config
{
	//! Set up the config for the given site.

	/*!
	 *  \param $site The site to configure for. Default is KB_SITE define.
	 */
	function Config($site = KB_SITE)
	{
		$configSite = &Config::_getSite();
		$configSite = $site;
		Config::init();
	}
	//! Return the site used for this configuration.

	/*!
	 * \returns String containing the site used for this configuration.
	 */
	function &_getSite()
	{
		static $site;
		if(!isset($site)) $site = KB_SITE;
		return $site;
	}

	function checkCheckbox($name)
	{
		if ($_POST[$name] == 'on')
		{
			Config::set($name, '1');
			return true;
		}
		Config::set($name, '0');
		return false;
	}

	function init()
	{
		global $config_init;

		if ($config_init)
		{
			return;
		}

		$db = new DBQuery();
		$db->execute("SELECT * FROM kb3_config WHERE cfg_site='".Config::_getSite()."'");
		$config = &Config::_getCache();
		while ($row = $db->getRow())
		{
			if (substr($row['cfg_value'], 0, 2) == 'a:')
			{
				$config[$row['cfg_key']] = unserialize($row['cfg_value']);
			}
			else
			{
				$config[$row['cfg_key']] = stripslashes($row['cfg_value']);
			}
		}
		$config_init = true;

		$defaults = array();
		$defaults['killcount'] = 50;
		$defaults['kill_points'] = 1;
		$defaults['least_active'] = 0;
		registry::set('config_defaults', $defaults);

		if (Config::get('post_password') === null)
		{
			// no config supplied, generate standard one
			Config::set('theme_name', 'default');
			Config::set('style_name', 'default');
			Config::set('style_banner', 'default.jpg');
			Config::set('post_password', 'CHANGEME');
			Config::set('comment_password', 'CHANGEME');
			Config::set('cfg_memcache', 0);
			Config::set('cfg_memcache_server', 'memcached server address');
			Config::set('cfg_memcache_port', 'memcached server port');
//            Config::set('cache_dir', KB_QUERYCACHEDIR);
//            Config::set('km_cache_dir', KB_CACHEDIR.'/mails');
			Config::set('DBUpdate',LATEST_DB_UPDATE);
		}
	}

	function &_getCache()
	{
		static $cache;

		if (!isset($cache))
		{
			$cache = array();
		}
		return $cache;
	}

	function put($key, $data)
	{
		$cache = &Config::_getCache();
		$cache[$key] = $data;
	}

	function del($key)
	{
		$cache = &Config::_getCache();
		if (isset($cache[$key]))
		{
			unset($cache[$key]);
		}

		$qry = new DBQuery(true);
		$qry->execute("DELETE FROM kb3_config WHERE cfg_key = '".$key."'
                       AND cfg_site = '".Config::_getSite()."'");
	}

	function set($key, $value)
	{
		$cache = &Config::_getCache();

		// only update the database when the old value differs
		if (isset($cache[$key]))
		{
			if ($cache[$key] === $value)
			{
				return;
			}
		}

		if (is_array($value))
		{
			$cache[$key] = $value;
			$value = serialize($value);
		}
		else
		{
			$cache[$key] = stripslashes($value);
		}
		$value = addslashes($value);

		$qry = new DBQuery(true);
		$sql = "INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) VALUES ('".
				Config::_getSite()."','".$key."','".$value.
				"') ON DUPLICATE KEY UPDATE cfg_value = '".$value."'";
		$qry->execute($sql);
	}

	function &get($key)
	{
		$cache = &Config::_getCache();

		if (!isset($cache[$key]))
		{
			return Config::defaultval($key);
		}
		return $cache[$key];
	}

	function &getnumerical($key)
	{
		$cache = &Config::_getCache();

		if (!isset($cache[$key]))
		{
			return Config::defaultval($key);
		}
		return $cache[$key];
	}

	function defaultval($key)
	{
		// add important upgrade configs here, they will return the default if not set
		// they will be shown as set but take no space in the database
		$defaults = registry::get('config_defaults');

		if (!isset($defaults[$key]))
		{
			return null;
		}
		return $defaults[$key];
	}
}
