<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/*
* This class handles the roles.
* Roles are the basic principle for limiting and
* granting access to certain functions and areas.
*
* In a mod auto-init you can call role::register()
* to register a (hardcoded) role with the system so the user
* doesnt have to create it.
* However, users also got the ability to create roles which are then stored
* inside the database.
* Both types of roles can be assigned to either titles or to users directly.
* @package EDK
*/

class role
{
	private static $roles;

	function role()
	{
		trigger_error('The class "role" may only be invoked statically.', E_USER_ERROR);
	}

	public static function register($role_name, $role_descr)
	{
		// store role as hardcoded
		role::_put($role_name, $role_descr, true);
	}

	public static function init()
	{
		if (!isset(self::$roles))
		{
			$qry = DBFactory::getDBQuery();;
			$qry->execute('select rol_id,rol_name, rol_descr from kb3_roles where rol_site=\''.KB_SITE."' order by rol_name");
			while ($row = $qry->getRow())
			{
				self::$roles['keys'][$row['rol_name']] = $row['rol_descr'];
				self::$roles['hard'][$row['rol_name']] = $row['rol_descr'];
			}
			role::register('admin', 'Basic Admin Role');
		}

	}

	// look if we should only return hardcoded roles
	public static function &get($hard = false)
	{
		$list = array();
		foreach (self::$roles['keys'] as $key => $value)
		{
			if (in_array($key, self::$roles['hard']))
			{
				if ($hard)
				{
					$list[$key] = $value;
				}
			}
			else
			{
				if (!$hard)
				{
					$list[$key] = $value;
				}
			}
		}
		return $list;
	}

	private static function _put($key, $data, $hard = false)
	{
		if ($hard)
		{
			self::$roles['hard'][$key] = $key;
			if (!isset(self::$roles['keys'][$key]))
			{
				// this indicates a hard role without a database entry
				// generate an identification number
				$id = abs(crc32($key));

				// insert it into the database
				$db = DBFactory::getDBQuery();;
				$db->execute('INSERT INTO `kb3_roles` VALUES("'.$id.'", "'.KB_SITE.'", "'.$key.'", "'.$data.'");');
			}
		}
		self::$roles['keys'][$key] = $data;
	}
}
