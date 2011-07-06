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
	 * Return whether this object is cached.
	 *
	 * Uses $this->getID() as a key.
	 * @return boolean true if this object is cached.
	 */
	protected function isCached()
	{
		if(isset(self::$cache[get_class($this).(int)$this->getID()]))
			return true;
		else if(!self::$cachehandler) self::init();

		return self::$cachehandler->exists(get_class($this).(int)$this->getID());
	}

	/**
	 * Return a cached Cacheable.
	 *
	 * Uses $this->getID() as a key.
	 * @return Cacheable
	 */
	protected function getCache()
	{
		if(isset(self::$cache[get_class($this).(int)$this->getID()]))
			return self::$cache[get_class($this).(int)$this->getID()];
		else if(!self::$cachehandler) self::init();

		return self::$cachehandler->get(get_class($this).(int)$this->getID());
	}

	/**
	 * Cache a Cacheable.
	 *
	 * Uses $this->getID() as a key.
	 * @return boolean Returns true if this was successfully cached.
	 */
	protected function putCache()
	{
		if(!self::$cachehandler) self::init();
		self::$cache[get_class($this).(int)$this->getID()] = unserialize(serialize($this));

		return self::$cachehandler->put(
				get_class($this).(int)$this->getID(), $this);
	}

	/**
	 * Initialise the cachehandler.
	 *
	 * Sets a new cachehandler, choosing between memcache or filecache
	 * depending on killboard settings.
	 */
	private static function init()
	{
		if(defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true)
			self::$cachehandler = new CacheHandlerHashedMem();
		else self::$cachehandler = new CacheHandlerHashed();
	}

	/**
	 * Return the object's ID.
	 *
	 * This is used as a key to cache the object so must return a value
	 * without calling the cache.
	 *
	 * @return integer
	 */
	abstract public function getID();
}
