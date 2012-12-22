<?php
/**
 * Factory class to create dbquery objects.
 * @package EDK
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
		return new DBNormalQuery();
	}
}