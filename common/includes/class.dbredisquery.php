<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * mysqli Redis query class. Manages SQL queries to a MySQL DB using mysqli.
 * @package EDK
 */
class DBRedisQuery extends DBCachedQuery
{
    /**
     * Set up a mysqli cached query object with default values.
     *
     * @param boolean $nocache true to retrieve results directly from the db.
     */
    function __construct($nocache = false)
    {
        $this->nocache = $nocache;

        self::$cachehandler = new CacheHandlerHashedRedis();

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
