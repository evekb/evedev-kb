<?php
class ApiCache
{
    function ApiCache($site)
    {
        ApiCache::init();
    }

    function checkCheckbox($name)
    {
        if ($_POST[$name] == 'on')
        {
            config::set($name, '1');
            return true;
        }
        config::set($name, '0');
        return false;
    }

    function init()
    {
        global $ApiCache_init;

        if ($ApiCache_init)
        {
            return;
        }

        $db = new DBQuery(true);
        $db->execute('select * from kb3_apicache where cfg_site=\''.KB_SITE."'");
        $ApiCache = &ApiCache::_getCache();
        while ($row = $db->getRow())
        {
            if (substr($row['cfg_value'], 0, 2) == 'a:')
            {
                $row['cfg_value'] = unserialize($row['cfg_value']);
            }
            $ApiCache[$row['cfg_key']] = $row['cfg_value'];
        }
        $ApiCache_init = true;
    }

    function &_getCache()
    {
    	static $cache;

    	if (!isset($cache))
        {
    	    $cache = array();
    	}
    	return $cache;
    }

    function put($key, $data)
    {
    	$cache = &ApiCache::_getCache();
    	$cache[$key] = $data;
    }

    function del($key)
    {
    	$cache = &ApiCache::_getCache();
    	if (isset($cache[$key]))
    	{
    	    unset($cache[$key]);
    	}

        $qry = new DBQuery();
        $qry->execute("delete from kb3_apicache where cfg_key = '".$key."'
        		       and cfg_site = '".KB_SITE."'");
    }

    function set($key, $value)
    {
    	//$cache = &ApiCache::_getCache();

    	// only update the database when the old value differs
    	if (isset($cache[$key]))
    	{
    	    if ($cache[$key] === $value)
    	    {
    	        return;
    	    }
    	}

    	if (is_array($value))
    	{
    	    $cache[$key] = $value;
    	    $value = serialize($value);
    	}
    	else
    	{
    	    $cache[$key] = stripslashes($value);
        }
        $value = addslashes($value);

        $qry = new DBQuery();
        $qry->execute("select cfg_value from kb3_apicache
                       where cfg_key = '".$key."' and cfg_site = '".KB_SITE."'");
        if ($qry->recordCount())
        {
            $sql = "update kb3_apicache set cfg_value = '".$value."'
                    where cfg_site = '".KB_SITE."' and cfg_key = '".$key."'";
        }
        else
        {
            $sql = "insert into kb3_apicache values ('".KB_SITE."','".$key."','".$value."')";
        }
        $qry->execute($sql);
    }

    function &get($key)
    {
    	$cache = &ApiCache::_getCache();

    	if (!isset($cache[$key]))
    	{
    	    return ApiCache::defaultval($key);
    	}
    	return stripslashes($cache[$key]);
    }

	function &getnumerical($key)
    {
    	$cache = &ApiCache::_getCache();

    	if (!isset($cache[$key]))
    	{
    	    return ApiCache::defaultval($key);
    	}
    	return $cache[$key];
    }
    function defaultval($key)
    {
        // add important upgrade configs here, they will return the default if not set
        // they will be shown as set but take no space in the database
        //$defaults = array('summarytable_rowcount' => 8);
		//$defaults = array('killcount' => 50);
		
        if (!isset($defaults[$key]))
        {
            return null;
        }
        return $defaults[$key];
    }
}
?>
