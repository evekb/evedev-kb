<?php
class registry
{
    function &_getCache()
    {
        static $cache;

        if (!isset($cache))
        {
            $cache = array();
        }
        return $cache;
    }

    function set($key, $data)
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
    }

    function &get($key)
    {
        $cache = &config::_getCache();

        if (!isset($cache[$key]))
        {
            return null;
        }
        return $cache[$key];
    }
}