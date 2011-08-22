<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * mysqli memcached query class. Manages SQL queries to a MySQL DB using mysqli.
 * @package EDK
 */
class DBMemcachedQuery extends DBCachedQuery
{
	/**
	 * Set up a mysqli cached query object with default values.
	 *
	 * @param boolean $nocache true to retrieve results directly from the db.
	 */
	function DBMemcachedQuery($nocache = false)
	{
		$this->nocache = $nocache;

		self::$cachehandler = new CacheHandlerHashedMem();

		if(is_null(self::$maxmem))
		{
			$tmp = @ini_get('memory_limit');
			$tmp = @str_replace('M', '000000', $tmp) * 0.8;
			self::$maxmem = @intval(str_replace('G', '000000000', $tmp) * 0.8);
			if(!self::$maxmem) {
				self::$maxmem = 128000000;
			}
		}
	}
}
