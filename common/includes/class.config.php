<?php

require_once('common/includes/class.registry.php');

class Config
{
    function Config($site)
    {
        config::init();
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
        global $config_init;

        if ($config_init)
        {
            return;
        }

        $db = new DBQuery(true);
        $db->execute('select * from kb3_config where cfg_site=\''.KB_SITE."'");
        $config = &config::_getCache();
        while ($row = $db->getRow())
        {
            if (substr($row['cfg_value'], 0, 2) == 'a:')
            {
                $config[$row['cfg_key']] = unserialize($row['cfg_value']);
            }
            else
            {
                $config[$row['cfg_key']] = stripslashes($row['cfg_value']);
            }
        }
        $config_init = true;

        $defaults = array();
        $defaults['summarytable_rowcount'] = 8;
        $defaults['killcount'] = 50;
        $defaults['kill_points'] = 1;
        $defaults['least_active'] = 0;
        registry::set('config_defaults', $defaults);

        if (config::get('post_password') === null)
        {
            // no config supplied, generate standard one
            config::set('style_name', 'default');
            config::set('style_banner', 'default.jpg');
            config::set('post_password', 'CHANGEME');
			config::set('comment_password', 'CHANGEME');
			config::set('cfg_memcache', 0);
			config::set('cfg_memcache_server', 'memcached server address');
			config::set('cfg_memcache_port', 'memcached server port');
			config::set('cache_dir', 'cache/cache');
			config::set('km_cache_dir', 'cache/mails');
        }
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
    	$cache = &config::_getCache();
    	$cache[$key] = $data;
    }

    function del($key)
    {
    	$cache = &config::_getCache();
    	if (isset($cache[$key]))
    	{
    	    unset($cache[$key]);
    	}

        $qry = new DBQuery();
        $qry->execute("delete from kb3_config where cfg_key = '".$key."'
        		       and cfg_site = '".KB_SITE."'");
    }

    function set($key, $value)
    {
    	$cache = &config::_getCache();

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
        $qry->execute("select cfg_value from kb3_config
                       where cfg_key = '".$key."' and cfg_site = '".KB_SITE."'");
        if ($qry->recordCount())
        {
            $sql = "update kb3_config set cfg_value = '".$value."'
                    where cfg_site = '".KB_SITE."' and cfg_key = '".$key."'";
        }
        else
        {
            $sql = "insert into kb3_config values ('".KB_SITE."','".$key."','".$value."')";
        }
        $qry->execute($sql);
    }

    function &get($key)
    {
    	$cache = &config::_getCache();

    	if (!isset($cache[$key]))
    	{
    	    return config::defaultval($key);
    	}
    	return $cache[$key];
    }

	function &getnumerical($key)
    {
    	$cache = &config::_getCache();

    	if (!isset($cache[$key]))
    	{
    	    return config::defaultval($key);
    	}
    	return $cache[$key];
    }

    function defaultval($key)
    {
        // add important upgrade configs here, they will return the default if not set
        // they will be shown as set but take no space in the database
        $defaults = registry::get('config_defaults');

        if (!isset($defaults[$key]))
        {
            return null;
        }
        return $defaults[$key];
    }
}