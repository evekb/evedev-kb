<?php
/*
 MIT License
 Copyright (c) 2013 Timo Rothenpieler

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * Implememnts apc user-cache into Pheal
 */
class PhealApcCache implements PhealCacheInterface
{
    /**
     * check if apc caching is available
     */
    public static function available()
    {
        if(extension_loaded("apc") && ini_get("apc.enabled") && php_sapi_name() != "cli")
            return true;
        return false;
    }

    /**
     * construct PhealApcCache.
     */
    public function __construct()
    {
        if(!extension_loaded("apc"))
            throw new PhealException("Tried to initialize apc user cache, but apc extension is missing.");

        if(!ini_get("apc.enabled"))
            throw new PhealException("Tried to initialize apc user cache, but apc extension is not enabled.");
    }

    /**
     * Calculate hash for given arguments.
     * @param int $userid
     * @param string $apikey
     * @param string $scope
     * @param string $name
     * @param array $args
     */
    protected static function calcHashKey($userid, $apikey, $scope, $name, $args)
    {
        return "PhealCache:" . hash("sha256", "::$userid:$apikey:$scope:$name::" . serialize($args) . "::");
    }

    /**
     * Calculate ttl for xml.
     * @param string $xml
     */
    protected static function calcCacheTimeout($xml)
    {
        $tz = date_default_timezone_get();
        date_default_timezone_set("UTC");

        @$xml = new SimpleXMLElement($xml);
        $dt = (int)strtotime($xml->cachedUntil);
        $time = time();

        date_default_timezone_set($tz);

        $result = $dt - $time;
        if($result <= 0) // Fall back to one hour
            $result = 3600;

        return $result;
    }

    /**
     * Load XML from cache
     * @param int $userid
     * @param string $apikey
     * @param string $scope
     * @param string $name
     * @param array $args
     */
    public function load($userid, $apikey, $scope, $name, $args)
    {
        $hashKey = PhealApcCache::calcHashKey($userid, $apikey, $scope, $name, $args);

        if(!apc_exists($hashKey))
            return false;

        return apc_fetch($hashKey);
    }

    /**
     * Save XML in cache
     * @param int $userid
     * @param string $apikey
     * @param string $scope
     * @param string $name
     * @param array $args
     * @param string $xml
     */
    public function save($userid, $apikey, $scope, $name, $args, $xml)
    {
        $hashKey = PhealApcCache::calcHashKey($userid, $apikey, $scope, $name, $args);

        apc_store($hashKey, $xml, PhealApcCache::calcCacheTimeout($xml));
    }
}
