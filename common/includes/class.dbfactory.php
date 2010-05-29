<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


//! Factory class to create
class DBFactory
{
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