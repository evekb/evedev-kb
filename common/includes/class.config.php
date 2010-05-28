<?php
/*
 * $Id $
 */


class Config
{
	private static $configSite = KB_SITE;
	private static $configCache = array();
	private static $configCacheGlobal = array();
	private static $qry = null;
	private static $initialised = false;
	//! Set up the config for the given site.
	
	/*!
	 *  \param $site The site to configure for. Default is KB_SITE define.
	 */
	function Config($site = KB_SITE)
	{
		self::$configSite = $site;
		self::init();
	}

	public static function checkCheckbox($name)
	{
		if (!self::$initialised) self::init();

		if ($_POST[$name] == 'on')
		{
			self::set($name, '1');
			return true;
		}
		self::set($name, '0');
		return false;
	}

	public static function init()
	{
		if (self::$initialised) return;

		self::$qry = DBFactory::getDBQuery();

		// If a super KB is defined then fetch its settings first.
		if(defined('SUPERKB_SITE'))
		{
			self::$qry->execute("SELECT * FROM kb3_config WHERE cfg_site='".SUPERKB_SITE."'");
			while ($row = self::$qry->getRow())
			{
				if (substr($row['cfg_value'], 0, 2) == 'a:')
				{
					self::$configCacheGlobal[$row['cfg_key']] = unserialize($row['cfg_value']);
					self::$configCache[$row['cfg_key']] = unserialize($row['cfg_value']);
				}
				else
				{
					self::$configCacheGlobal[$row['cfg_key']] = stripslashes($row['cfg_value']);
					self::$configCache[$row['cfg_key']] = stripslashes($row['cfg_value']);
				}
			}
		}
		self::$qry->execute("SELECT * FROM kb3_config WHERE cfg_site='".self::$configSite."'");
		if(!self::$qry->recordCount()) self::setDefaults();
		while ($row = self::$qry->getRow())
		{
			// If this board is set up with a super admin then restrict global changes.
			if(defined('SUPERKB_SITE') && isset(self::$configCacheGlobal[$row['cfg_key']])) continue;

			if (substr($row['cfg_value'], 0, 2) == 'a:')
			{
				self::$configCache[$row['cfg_key']] = unserialize($row['cfg_value']);
			}
			else
			{
				self::$configCache[$row['cfg_key']] = stripslashes($row['cfg_value']);
			}
		}
		self::$initialised = true;
	}


	public static function put($key, $data)
	{
		if (!self::$initialised) self::init();

		if(defined('SUPERKB_SITE') && isset(self::$configCacheGlobal[$key])) 
			return false;

		self::$configCache[$key] = $data;
	}

	public static function del($key, $global = false)
	{
		if (!self::$initialised) self::init();

		if( isset(self::$configCacheGlobal[$key]))
		{
			if(!$global) return false;
			else
			{
				unset(self::$configCacheGlobal[$key]);

				self::$qry->execute("DELETE FROM kb3_config WHERE cfg_key = '{$key}'
						   AND cfg_site = '".SUPERKB_SITE."'");
			}
		}
		if (isset(self::$configCache[$key])) unset(self::$configCache[$key]);

		self::$qry->execute("DELETE FROM kb3_config WHERE cfg_key = '{$key}'
                       AND cfg_site = '".self::$configSite."'");
	}

	public static function set($key, $value, $global = false)
	{
		if (!self::$initialised) self::init();

		if(!$global && isset(self::$configCacheGlobal[$key])) return;

		if($global && defined('SUPERKB_SITE'))
		{
			// only update the database when the old value differs
			if (isset(self::$configCacheGlobal[$key])
				&& self::$configCacheGlobal[$key] === $value) return;

			if (is_array($value))
			{
				self::$configCacheGlobal[$key] = $value;
				$value = serialize($value);
			}
			else
			{
				self::$configCacheGlobal[$key] = stripslashes($value);
			}
			$value = self::$qry->escape($value);

			$sql = "INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) VALUES ('".
				SUPERKB_SITE."','{$key}','{$value}') ON DUPLICATE KEY UPDATE cfg_value = '{$value}'";
			self::$qry->execute($sql);

			return;
		}

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
		$value = self::$qry->escape($value);

		$sql = "INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) VALUES ('".
			self::$configSite."','{$key}','{$value}') ON DUPLICATE KEY UPDATE cfg_value = '{$value}'";
		self::$qry->execute($sql);

	}

	public static function get($key)
	{
		if (!self::$initialised) self::init();

		if (isset(self::$configCacheGlobal[$key]))
			return self::$configCacheGlobal[$key];

		if (!isset(self::$configCache[$key]))
			return null;

		return self::$configCache[$key];
	}

	private static function setDefaults()
	{
		$sql = "INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT '".self::$configSite."', cfg_key, cfg_value FROM kb3_config where cfg_site = ''";
		self::$qry->execute($sql);
	}
}

