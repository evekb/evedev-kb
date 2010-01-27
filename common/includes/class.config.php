<?php

class Config
{
	private static $configSite = null;
	private static $configCache = array();
	//! Set up the config for the given site.
	
	/*!
	 *  \param $site The site to configure for. Default is KB_SITE define.
	 */
	function Config($site = KB_SITE)
	{
		self::$configSite = $site;
		self::init();
	}

	function checkCheckbox($name)
	{
		if ($_POST[$name] == 'on')
		{
			self::set($name, '1');
			return true;
		}
		self::set($name, '0');
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
		$db->execute("SELECT * FROM kb3_config WHERE cfg_site='".self::$configSite."'");
		while ($row = $db->getRow())
		{
			if (substr($row['cfg_value'], 0, 2) == 'a:')
			{
				self::$configCache[$row['cfg_key']] = unserialize($row['cfg_value']);
			}
			else
			{
				self::$configCache[$row['cfg_key']] = stripslashes($row['cfg_value']);
			}
		}
		$config_init = true;
		
		if (self::get('post_password') === null)
		{
			// no config supplied, generate standard one
			self::set('theme_name', 'default');
			self::set('style_name', 'default');
			self::set('style_banner', 'default.jpg');
			self::set('post_password', 'CHANGEME');
			self::set('comment_password', 'CHANGEME');
			self::set('cfg_memcache', 0);
			self::set('cfg_memcache_server', 'memcached server address');
			self::set('cfg_memcache_port', 'memcached server port');
			//self::set('cache_dir', KB_QUERYCACHEDIR);
			//self::set('km_cache_dir', KB_CACHEDIR.'/mails');
			self::set('DBUpdate',LATEST_DB_UPDATE);
		}
	}

	function put($key, $data)
	{
		self::$configCache = $data;
	}

	function del($key)
	{
		if (isset(self::$configCache[$key]))
		{
			unset(self::$configCache[$key]);
		}

		$qry = new DBQuery(true);
		$qry->execute("DELETE FROM kb3_config WHERE cfg_key = '{$key}'
                       AND cfg_site = '".self::$configSite."'");
	}

	function set($key, $value)
	{
		// only update the database when the old value differs
		if (isset(self::$configCache[$key]))
		{
			if (self::$configCache[$key] === $value)
			{
				return;
			}
		}

		if (is_array($value))
		{
			self::$configCache[$key] = $value;
			$value = serialize($value);
		}
		else
		{
			self::$configCache[$key] = stripslashes($value);
		}
		$value = addslashes($value);

		$qry = new DBQuery(true);
		$sql = "INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) VALUES ('".self::$configSite."','{$key}','{$value}') ON DUPLICATE KEY UPDATE cfg_value = '{$value}'";
		$qry->execute($sql);
	}

	function &get($key)
	{
		if (!isset(self::$configCache[$key]))
		{
			return null;
		}
		return self::$configCache[$key];
	}

	function &getnumerical($key)
	{
		if (!isset(self::$configCache[$key]))
		{
			return null;
		}
		return self::$configCache[$key];
	}
}

