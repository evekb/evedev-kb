<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * Hashed object caching class backed by redis
 * Extends the cache handler to handle any type of object. Instead of
 * using the given filename a key is used to create a hashed name.
 * @package EDK
 */
class CacheHandlerHashedRedis extends CacheHandlerHashed
{
	private static $maxage = 43200;
	
	/**
	 * Add a file to the cache.
	 *
	 * @param string $key The key for the required object.
	 * @param mixed$object The object to store.
	 * @param string $location Fetch from a particular location if needed. (identical to $key.$location)
	 *
	 * @return boolean true if successful, false if an error occurred.
	 */
	public static function put($key, $data, $location = '', $age = null)
	{
		global $redis;

		$data = serialize($data);
		if(is_null($age)) $age = self::$maxage;
		if($age && $age > 5) $age = $age * (105 - rand(0, 10))/100;
		$hash = self::hash($key.$location);
		$result = $redis->set($hash, $data, $age);
		if(!$result) $result = $redis->set($hash, $data, $age);
		//
		// record age separately
		$hash .= "age";
		$result = $redis->set($hash, time(), $age);
		if(!$result) $result = $redis->set($hash, time(), $age);

		return $result;
	}
	/**
	 * Get a file from the cache
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return mixed A copy of the stored object.
	 */
	public static function get($key, $location = '')
	{
		global $redis;

		$hash = self::hash($key.$location);
		return unserialize($redis->get($hash));
	}
	/**
	 * Return true if the file is in the cache.
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return boolean true if the stored object exists, false otherwise.
	 */
	public static function exists($key, $location = '')
	{
		global $redis;

		$hash = self::hash($key,$location);
		return $redis->get($hash) !== false;
	}
	/**
	 * Get the externally accessible address of the cached file.
	 *
	 * @return boolean false. There is no valid path to a redis object.
	 */
	public static function getExternal($key, $location = null)
	{
		return false;
	}
	/**
	 * Get the internally accessible address of the cached file.
	 *
	 * @return boolean false. There is no valid path to a redis object.
	 */
	public static function getInternal($key, $location = null)
	{
		return false;
	}
	/**
	 * Get the hash of the given $key.$location.
	 *
	 * @param string $key
	 * @param string $location
	 * @return string
	 */
	private static function hash($key, $location = '')
	{
		return md5($key.$location);
	}
	/**
	 * Remove a cached object
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return boolean true if removed or not in the cache, false on failure.
	 */
	public static function remove($key, $location = '')
	{
		global $redis;

		$hash = self::hash($key.$location);
		return $redis->delete($hash);
	}
	/**
	 * Return the age of the given cache file.
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return integerThe age in seconds of the stored object.
	 */
	public static function age($key, $location = '')
	{
		global $redis;

		$hash = self::hash($key.$location)."age";
		$age = $redis->get($hash);
		if($age === false) return false;
		return time() - (int)$age;
	}
	/**
	 * Set the default maximum age.
	 *
	 * @param integer $age The new default maximum age
	 */
	public static function setMaxAge($age = 0)
	{
		self::$maxage = $age;
	}
}
