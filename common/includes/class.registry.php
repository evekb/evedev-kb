<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class registry
{
	private static $registryCache = array();

	function set($key, $data)
	{
		self::$registryCache[$key] = $data;
	}

	function del($key)
	{
		if (isset(self::$registryCache[$key]))
		{
			unset(self::$registryCache[$key]);
		}
	}

	function &get($key)
	{
		if (!isset(self::$registryCache[$key]))
		{
			return null;
		}
		return self::$registryCache[$key];
	}
}
