<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Class to cache API information.
 * @package EDK
 */
class ApiCache
{
	private static $cache = array();
	private static $configSite = KB_SITE;
	private static $initialised = false;

	/**
	 * @param string $site ID for the site.
	 */
	function ApiCache($site = KB_SITE)
	{
		self::$configSite = $site;
		ApiCache::init();
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public static function checkCheckbox($name)
	{
		if($_POST[$name] == 'on')
		{
			config::set($name, '1');
			return true;
		}
		config::set($name, '0');
		return false;
	}

	/**
	 * Initialise the object and connect to the db
	 */
	public static function init()
	{
		if(self::$initialised) return;

		$db = DBFactory::getDBQuery();
		$db->execute("select * from kb3_apicache where cfg_site='" . self::$configSite . "'");
		while($row = $db->getRow())
		{
			if(substr($row['cfg_value'], 0, 2) == 'a:')
			{
				$row['cfg_value'] = unserialize($row['cfg_value']);
			}
			self::$cache[$row['cfg_key']] = $row['cfg_value'];
		}
		self::$initialised = true;
	}

	/**
	 * Store an object temporarily.
	 *
	 * Will not save the object to the db.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public static function put($key, $value)
	{
		if(!self::$initialised) self::init();

		self::$cache[$key] = $value;
	}

	/**
	 * Remove an object from the API cache.
	 *
	 * @param string $key
	 */
	public static function del($key)
	{
		if(!self::$initialised) self::init();

		if(isset(self::$cache[$key]))
		{
			unset(self::$cache[$key]);
		}

		$qry = DBFactory::getDBQuery();
		$qry->execute("delete from kb3_apicache where cfg_key = '" . $key . "'
        		       and cfg_site = '" . self::$configSite . "'");
	}

	/**
	 * Store an object in the db.
	 *
	 * Will save the object to the db.
	 *
	 * @param string $key
	 * @param string $data
	 */
	public static function set($key, $value)
	{
		if(!self::$initialised) self::init();

		// only update the database when the old value differs
		if(isset(self::$cache[$key]))
		{
			if(self::$cache[$key] === $value)
			{
				return;
			}
		}

		if(is_array($value))
		{
			self::$cache[$key] = $value;
			$value = serialize($value);
		}
		else
		{
			self::$cache[$key] = stripslashes($value);
		}
		$value = addslashes($value);

		$qry = DBFactory::getDBQuery();
		$sql = "INSERT INTO kb3_apicache (cfg_site, cfg_key, cfg_value) VALUES ('" .
			self::$configSite . "','" . $key . "','" . $value .
			"') ON DUPLICATE KEY UPDATE cfg_value = '" . $value . "'";
		$qry->execute($sql);
	}

	/**
	 * Return a cached string.
	 *
	 * @param string $key
	 * @return string
	 */
	public static function &get($key)
	{
		if(!self::$initialised) self::init();

		if(!isset(self::$cache[$key]))
		{
			return null;
		}
		return stripslashes(self::$cache[$key]);
	}
}
