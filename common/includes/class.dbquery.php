<?php
/**
 * $Date: 2010-05-29 14:46:12 +1000 (Sat, 29 May 2010) $
 * $Revision: 699 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.db.php $
 * @package EDK
 */

/**
 * @package EDK
 */
class DBQuery
{
	var $object;

	// php5 style object overloading
	// we internally load up the wanted object and reroute all
	// object actions to it
	function __construct($forceNormal = false)
	{
		$this->object = DBFactory::getDBQuery($forceNormal);
	}

	function __call($name, $args)
	{
		return call_user_func_array(array($this->object, $name), $args);
	}

	function __set($name, $value)
	{
		$this->object->$name = $value;
	}

	function __unset($name)
	{
		unset($this->object->$name);
	}

	function __isset($name)
	{
		return isset($this->object->$name);
	}

	function __get($name)
	{
		return $this->object->$name;
	}
}
