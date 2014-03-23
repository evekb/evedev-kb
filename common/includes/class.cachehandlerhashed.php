<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * Hashed object caching class
 * Extends the cache handler to handle any type of object. Instead of
 * using the given filename a key is used to create a hashed name.
 * @package EDK
 */
class CacheHandlerHashed extends CacheHandler
{
	/**
	 * Add a file to the cache.
	 *
	 * @param string $key The key for the required object.
	 * @param mixed $object The object to store.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return boolean true if successful, false if an error occurred.
	 */
	public static function put($key, $object, $location = null)
	{
		$path = self::getPathHashed($key, $location, true);

		$zp = gzopen(self::$internalroot."/".$path, "wb1");
		if (!$zp) {
			return false;
		}
		gzwrite($zp, serialize($object));
		return gzclose($zp);
	}
	/**
	 * Get a file from the cache
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return mixed A copy of the stored object.
	 */
	public static function get($key, $location = null)
	{
		$path = self::getPathHashed($key, $location, false);

		$zp = gzopen(self::$internalroot."/".$path, 'rb');
		$result = '';
		$tmp = '';
		while($tmp = @gzread($zp, 8192)) $result .= $tmp;
		gzclose($zp);

		if(!$result) return $result;
		return unserialize($result);
	}
	/**
	 * Return true if the file is in the cache.
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return boolean true if the stored object exists, false otherwise.
	 */
	public static function exists($key, $location = null)
	{
		$path = self::getPathHashed($key, $location, false);

		return is_file(self::$internalroot.'/'.$path);
	}
	/**
	 * Get the externally accessible address of the cached file.
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return boolean false. Valid URLs to an internal serialised object are not
	 * necessary.
	 */
	public static function getExternal($key, $location = null)
	{
		return false;
	}
	/**
	 * Get the internally accessible address of the cached file.
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return string The filesystem path to the serialised object.
	 */
	public static function getInternal($key, $location = null)
	{
		return self::$internalroot.'/'.self::getPathHashed($key, $location, false);
	}
	/**
	 * Get the path of the cached file.
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 * @return boolean $create Set false to not create the path if it does not exist.
	 *
	 * @return string The path to the serialised object.
	 */
	private static function getPathHashed($key, $location = null, $create = true)
	{
		$key = md5($key);

		return parent::getPath($key, $location, $create);
	}
	/**
	 * Remove a cached file
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 * @return boolean true if removed or not in the cache, false on failure.
	 */
	public static function remove($key, $location = null)
	{
		$dir = self::getPathHashed($key, $location, false);
		$path = self::$internalroot.'/'.$dir;

		if(!self::exists($key, $location)) return true;
		if(!unlink($path)) return false;
		self::removeDir(dirname($dir));

		return true;
	}
	/**
	 * Return the age of the given cache object.
	 *
	 * @param string $key The key for the required object.
	 * @param string $location Fetch from a particular location if needed.
	 *
	 *@return integer The age in seconds of the stored object.
	 */
	public static function age($key, $location = null)
	{
		if(!file_exists(self::getInternal($key, $location))) return false;

		return time() - filemtime(self::getInternal($key, $location));
	}
}
