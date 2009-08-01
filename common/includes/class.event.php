<?php

class event
{
    function event()
    {
        trigger_error('The class "event" may only be invoked statically.', E_USER_ERROR);
    }

    function register($event, $callback)
    {
        if (is_array($callback))
        {
            if (is_object($callback[0]))
            {
                trigger_error('The supplied callback has to point to a static method.', E_USER_WARNING);
                return;
            }

            // store callbacks as 'object::function'
            $callback = $callback[0].'::'.$callback[1];
        }
        if (!strpos($callback, '::'))
        {
            trigger_error('The supplied callback "'.$callback.'" has to point to a static method.', E_USER_WARNING);
            return;
        }

        // we store the event callbacks reverse so you need one function for every event
        event::_put($callback, $event);
    }

    function call($event, &$object)
    {
        $cache = &event::_getCache();
        foreach ($cache as $callback => $c_event)
        {
            // if the callback registered to the calling event we'll try to use his callback
            if ($event == $c_event)
            {
                $cb = explode('::', $callback);
                if (is_callable($cb))
                {
                    if (is_object($object))
                    {
                        //call_user_func($cb, &$object);
                        call_user_func_array($cb, array(&$object));
                    }
                    else
                    {
                        call_user_func($cb, $object);
                    }
                }
                else
                {
                    trigger_error('The stored event handler "'.$c_event.'" is not callable (CB: "'.$callback.'").', E_USER_WARNING);
                }
            }
        }
    }

    function add($event)
    {

    }

    function init()
    {
//        $db = new DBQuery();
//        $db->execute('select rol_name, rol_descr from kb3_roles where rol_site=\''.KB_SITE."'");
//        while ($row = $db->getRow())
//        {
//            role::_put($row['rol_name'], $row['rol_descr']);
//        }
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

    function _put($key, $data)
    {
    	$cache = &event::_getCache();
    	$cache[$key] = $data;
    }

    function _get($key)
    {
    	$cache = &event::_getCache();

    	if (!isset($cache[$key]))
    	{
    	    return null;
    	}
    	return $cache[$key];
    }
}
?>