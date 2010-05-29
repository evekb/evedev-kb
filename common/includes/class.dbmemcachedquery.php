<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

require_once 'class.dbcache.php';

//! mysqli memcached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBMemcachedQuery extends DBCachedQuery
{
	//! Set up a mysqli cached query object with default values.
	function DBMemcachedQuery($nocache = false)
	{
		$this->nocache = $nocache;

		self::$cachehandler = new CacheHandlerHashedMem();

		if(is_null(self::$maxmem))
		{
			self::$maxmem = @ini_get('memory_limit');
			self::$maxmem = @str_replace('M', '000000', self::$maxmem) * 0.8;
			self::$maxmem = @intval(str_replace('M', '000000000', self::$maxmem) * 0.8);
			if(!self::$maxmem) self::$maxmem = 128000000;
		}
	}
}
