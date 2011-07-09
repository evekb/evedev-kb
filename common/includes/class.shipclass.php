<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * Contains the attributes of a Ship Class.
 * @package EDK
 */
class ShipClass extends Cacheable
{
	private $executed = false;
	private $id = 0;
	private $name = '';
	private $value = null;
	private $points = null;

	function ShipClass($id = 0)
	{
		if (!$id) $id = 0;
		$this->id = intval($id);
	}

	/**
	 * Return the ID of this ship class object.
	 *
	 * @return integer The ID of this ship class.
	 */
	public function getID()
	{
		return $this->id;
	}
	/**
	 * Return the name of this ship class object.
	 *
	 * @return string The name of this ship class.
	 */
	public function getName()
	{
		if (!$this->name) $this->execQuery();
		return $this->name;
	}

	/**
	 * Get value for this ship class object in millions of ISK.
	 *
	 * @return float The M ISK value of this ship class
	 */
	public function getValue()
	{
		if (is_null($this->value)) $this->execQuery();
		return round($this->value / 1000000, 2);
	}
	/**
	 * Get value for this ship class object in ISK.
	 *
	 * @return float The ISK value of this ship class.
	 */
	public function getActualValue()
	{
		if (is_null($this->value)) $this->execQuery();
		return $this->value;
	}
	/**
	 * Get the point value of this ship class.
	 *
	 * @return integer
	 */
	public function getPoints()
	{
		if (is_null($this->points)) $this->execQuery();
		return $this->points;
	}
	/**
	 * Set the name of this ship class object.
	 *
	 * @param string $name The new name for this class.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	/**
	 * Set the value of this ship class object.
	 *
	 * @param string $name The new value for this object.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
	/**
	 * Return the URL to a colour coded value indicator image.
	 *
	 * @return string
	 */
	public function getValueIndicator()
	{
		$value = $this->getValue();

		if ($value >= 0 && $value <= 1)
			$color = "gray";
		elseif ($value > 1 && $value <= 15)
			$color = "blue";
		elseif ($value > 15 && $value <= 25)
			$color = "green";
		elseif ($value > 25 && $value <= 40)
			$color = "yellow";
		elseif ($value > 40 && $value <= 80)
			$color = "red";
		elseif ($value > 80 && $value <= 250)
			$color = "orange";
		elseif ($value > 250)
			$color = "purple";

		return IMG_URL."/ships/ship-".$color.".gif";
	}

	private function execQuery()
	{
		if (!$this->executed)
		{
			if ($this->isCached()) {
				$cache = $this->getCache();
				$this->name = $cache->name;
				$this->value = $cache->value;
				$this->points = $cache->points;
				$this->executed = true;
				return;
			}
			$sql = "SELECT * FROM kb3_ship_classes ".
  	         "WHERE scl_id = ".$this->id;

			$qry = DBFactory::getDBQuery();

			$qry->execute($sql);
			$row = $qry->getRow();

			$this->name = $row['scl_class'];
			$this->value = $row['scl_value'];
			$this->points = $row['scl_points'];
			$this->executed = true;
			$this->putCache();
		}
	}
}