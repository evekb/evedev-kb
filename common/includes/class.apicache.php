<?php
class ApiCache
{
	private static $cache = array();

	function ApiCache($site)
	{
		ApiCache::init();
	}

	public static function checkCheckbox($name)
	{
		if ($_POST[$name] == 'on')
		{
			config::set($name, '1');
			return true;
		}
		config::set($name, '0');
		return false;
	}

	public static function init()
	{
		static $ApiCache_init = false;

		if ($ApiCache_init)
		{
			return;
		}

		$db = DBFactory::getDBQuery(true);;
		$db->execute('select * from kb3_apicache where cfg_site=\''.KB_SITE."'");
		while ($row = $db->getRow())
		{
			if (substr($row['cfg_value'], 0, 2) == 'a:')
			{
				$row['cfg_value'] = unserialize($row['cfg_value']);
			}
			self::$cache[$row['cfg_key']] = $row['cfg_value'];
		}
		$ApiCache_init = true;
	}

	public static function put($key, $data)
	{
		self::$cache[$key] = $data;
	}

	public static function del($key)
	{
		if (isset(self::$cache[$key]))
		{
			unset(self::$cache[$key]);
		}

		$qry = DBFactory::getDBQuery();;
		$qry->execute("delete from kb3_apicache where cfg_key = '".$key."'
        		       and cfg_site = '".KB_SITE."'");
	}

	public static function set($key, $value)
	{
		// only update the database when the old value differs
		if (isset(self::$cache[$key]))
		{
			if (self::$cache[$key] === $value)
			{
				return;
			}
		}

		if (is_array($value))
		{
			self::$cache[$key] = $value;
			$value = serialize($value);
		}
		else
		{
			self::$cache[$key] = stripslashes($value);
		}
		$value = addslashes($value);

		$qry = DBFactory::getDBQuery();;
		$sql = "INSERT INTO kb3_apicache (cfg_site, cfg_key, cfg_value) VALUES ('".
			KB_SITE."','".$key."','".$value.
			"') ON DUPLICATE KEY UPDATE cfg_value = '".$value."'";
		$qry->execute($sql);
	}

	public static function &get($key)
	{
		if (!isset(self::$cache[$key]))
		{
			return null;
		}
		return stripslashes(self::$cache[$key]);
	}
}
