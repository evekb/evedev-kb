<?php

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
*
*/

class role
{
    function role()
    {
        trigger_error('The class "role" may only be invoked statically.', E_USER_ERROR);
    }

    function register($role_name, $role_descr)
    {
        // store role as hardcoded
        role::_put($role_name, $role_descr, true);
    }

    function init()
    {
        $db = new DBQuery();

        $db->execute('select rol_name, rol_descr from kb3_roles where rol_site=\''.KB_SITE."'");
        while ($row = $db->getRow())
        {
            role::_put($row['rol_name'], $row['rol_descr']);
        }
        role::register('admin', 'Basic Admin Role');
    }

    // look if we should only return hardcoded roles
    function &get($hard = false)
    {
        $cache = &role::_getCache();

        $list = array();
        foreach ($cache['keys'] as $key => $value)
        {
            if (in_array($key, $cache['hard']))
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

    function _put($key, $data, $hard = false)
    {
    	$cache = &role::_getCache();
    	if ($hard)
    	{
    	    $cache['hard'][$key] = $key;
    	    if (!isset($cache['keys'][$key]))
    	    {
    	        // this indicates a hard role without a database entry
    	        // generate an identification number
    	        $id = abs(crc32($key));

    	        // insert it into the database
	            $db = new DBQuery();
    	        $db->execute('INSERT INTO `kb3_roles` VALUES("'.$id.'", "'.KB_SITE.'", "'.$key.'", "'.$data.'");');
    	    }
    	}
        $cache['keys'][$key] = $data;
    }

    function _get($key)
    {
    	$cache = &role::_getCache();

    	if (!isset($cache['keys'][$key]))
    	{
    	    return null;
    	}
    	return $cache['keys'][$key];
    }

    function &_getCache()
    {
    	static $cache;

    	if (!isset($cache))
        {
    	    $cache['keys'] = array();
    	    $cache['hard'] = array();
    	}
    	return $cache;
    }
}
?>