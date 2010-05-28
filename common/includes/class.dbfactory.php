<?php
/*
 * $Id $
 */


//! Factory class to create
class DBFactory
{
	public static function getDBQuery($forceNormal = false)
	{
		if (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true)
		{
			require_once('class.dbmemcache.php');
			return new DBMemCachedQuery($forceNormal);
		}
		else if (defined('DB_USE_QCACHE') && DB_USE_QCACHE == true)
		{
			require_once('class.dbcache.php');
			return new DBCachedQuery($forceNormal);
		}
		else
		{
			require_once('class.db.php');
			return new DBNormalQuery();
		}
	}
}