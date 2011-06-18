<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


/**
 * Factory class to create dbquery objects.
 */
class DBFactory
{
	/**
	 * Create and return a db query object.
	 *
	 * @param boolean $forceNormal true to disable cached queries
	 * 
	 * @return DBBaseQuery
	 */
	public static function getDBQuery($forceNormal = false)
	{
		if (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true)
		{
			return new DBMemCachedQuery($forceNormal);
		}
		else if (defined('DB_USE_QCACHE') && DB_USE_QCACHE == true)
		{
			return new DBCachedQuery($forceNormal);
		}
		else
		{
			return new DBNormalQuery();
		}
	}
}