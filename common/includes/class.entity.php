<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 *
 * @package EDK
 */


/**
 * Base class for entities - pilot/corp/alliance/items
 *
 * Cache handlers are used to cache entities between uses.
 * @package EDK
 */
abstract class Entity extends Cacheable {
	/**
	 * Return the entity's ID.
	 *
	 * @return integer
	 */
	public function getID()
	{
		if($this->id) return $this->id;
		elseif($this->externalid)
		{
			$this->execQuery();
			return $this->id;
		}
		else return 0;
	}

	/**
	 * Return the entities name.
	 *
	 * @return string
	 */
	public function getName()
	{
		if(!$this->name) $this->execQuery();
		return $this->name;
	}

	/**
	 * Return the entity's CCP ID.
	 *
	 * @return integer
	 */

	abstract public function getExternalID();
	/**
	 * Return a URL for the image of this entity.
	 *
	 * @param integer $size The size in pixels of the image needed.
	 * @return string The URL for this entity's logo.
	 */
	abstract public function getPortraitURL($size = 64);
	/**
	 * Return a URL for the details page of this entity.
	 * 
	 * @return string The URL for this entity's details page.
	 */
	abstract public function getDetailsURL();
}
