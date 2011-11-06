<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 *
 * @package EDK
 */


/**
 * Cache objects between page loads.
 */
abstract class Cacheable {
	/** @var CacheHandlerHashed */
	private static $cachehandler = null;
	/** @var array */
	private static $cache = array();

	/**
	 * Create or fetch a new Cacheable object.
	 *
	 * @param string $classname A valid Cacheable class.
	 * @param integer $id The id of the object to create/retrieve.
	 * @return mixed
	 */
	public static function factory($classname, $id)
	{
		if(!self::$cachehandler) {
			self::init();
		}

		if (isset(self::$cache[$classname.$id])) {
			return self::$cache[$classname.$id];
		} else if (class_exists('Config', false) && !config::get('cfg_objcache')) {
			return new $classname($id);
		} else if (self::$cachehandler->exists($classname.$id)) {
			return self::$cachehandler->get($classname.$id);
		} else {
			return new $classname($id);
		}

	}

	/**
	 * Get a cached object by ID
	 * @param integer $id
	 * @return Cacheable Returns an object of the called class type for the
	 * given $id
	 */
	public static function getByID($id)
	{
		return Cacheable::factory(get_called_class(), (int) $id);
	}

	/**
	 * Return whether this object is cached.
	 *
	 * Uses $this->getID() as a key.
	 * @return boolean true if this object is cached.
	 */
	protected function isCached()
	{
		if(isset(self::$cache[get_class($this).$this->getID()])) {
			return true;
		}

		if (!config::get('cfg_objcache')) {
			return false;
		}

		if(!self::$cachehandler) {
			self::init();
		}
		return self::$cachehandler->exists(get_class($this).$this->getID());
	}

	/**
	 * Return a cached Cacheable.
	 *
	 * Uses $this->getID() as a key.
	 * @return Cacheable
	 */
	protected function getCache()
	{
		if(isset(self::$cache[get_class($this).$this->getID()])) {
			return self::$cache[get_class($this).$this->getID()];
		}

		if (!config::get('cfg_objcache')) {
			return false;
		}

		if(!self::$cachehandler) {
			self::init();
		}
		return self::$cachehandler->get(get_class($this).$this->getID());
	}

	/**
	 * Cache a Cacheable.
	 *
	 * Uses $this->getID() as a key.
	 * @return boolean Returns true if this was successfully cached.
	 */
	protected function putCache()
	{
		// The unserialize/serialize is used to make a deep copy
		self::$cache[get_class($this).$this->getID()] = unserialize(serialize($this));

		if (!config::get('cfg_objcache')) {
			return false;
		}

		if(!self::$cachehandler) {
			self::init();
		}
		return self::$cachehandler->put(
				get_class($this).$this->getID(), $this);
	}

	/**
	 * Delete the cached version of an object.
	 *
	 * @param Cacheable $obj
	 * @return boolean True on success.
	 */
	 public static function delCache($obj)
	 {
		unset(self::$cache[get_class($obj).$obj->getID()]);

		if (!config::get('cfg_objcache')) {
			return true;
		}
		return self::$cachehandler->remove(
				get_class($obj).$obj->getID());
	 }
	/**
	 * Initialise the cachehandler.
	 *
	 * Sets a new cachehandler, choosing between memcache or filecache
	 * depending on killboard settings.
	 */
	private static function init()
	{
		if(defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true) {
			self::$cachehandler = new CacheHandlerHashedMem();
		} else {
			self::$cachehandler = new CacheHandlerHashed();
		}
	}

	/**
	 * Return the object's ID.
	 *
	 * This is used as a key to cache the object so must return a value
	 * without calling the cache.
	 *
	 * @return integer
	 */
	abstract protected function getID();
}
