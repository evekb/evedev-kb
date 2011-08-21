<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * Base class for storing summary statistics.
 * @package EDK
 */
abstract class statSummary
{
	/** @var boolean */
	protected $executed = false;
	/** @var array */
	protected $summary = array();
	/**
	 * Get the complete summary for this alliance.
	 *
	 * @return array an array of ship id by kill/loss count/isk.
	 */
	public function getSummary()
	{
		if(!$this->executed) {
			$this->execute();
		}
		return $this->summary;
	}
	/**
	 * Return total ISK killed.
	 *
	 * @return float
	 */
	public function getTotalKillISK()
	{
		if(!$this->executed) {
			$this->execute();
		}
		foreach($this->summary as $value) {
			$totalisk .= $value['killisk'];
		}
		return $totalisk;
	}
	/**
	 * Return total ISK lost.
	 *
	 * @return float
	 */
	public function getTotalLossISK()
	{
		if(!$this->executed) {
			$this->execute();
		}
		foreach($this->summary as $value) {
			$totalisk .= $value['lossisk'];
		}
		return $totalisk;
	}
	/**
	 * Return the number of kills for the given ship class.
	 *
	 * @param integer $shp_class
	 * @return integer
	 */
	public function getKillCount($shp_class)
	{
		if(!$this->executed) {
			$this->execute();
		}
		return $this->summary[$ship_class]['killcount'];
	}
	/**
	 * Return the ISK value of kills for the given ship class.
	 *
	 * @param integer $shp_class
	 * @return float
	 */
	public function getKillISK($shp_class)
	{
		if(!$this->executed) {
			$this->execute();
		}
		return $this->summary[$ship_class]['killisk'];
	}
	/**
	 * Return the number of losses for the given ship class.
	 *
	 * @param integer $shp_class
	 * @return integer
	 */
	public function getLossCount($shp_class)
	{
		if(!$this->executed) {
			$this->execute();
		}
		return $this->summary[$ship_class]['losscount'];
	}
	/**
	 * Return the ISK value of losses for the given ship class.
	 *
	 * @param integer $shp_class
	 * @return float
	 */
	public function getLossISK($shp_class)
	{
		if(!$this->executed) {
			$this->execute();
		}
		return $this->summary[$ship_class]['lossisk'];
	}
	/**
	 * Add a Kill and its value to the summary.
	 *
	 * @param Kill $kill
	 */
	abstract public static function addKill($kill);
	/**
	 * Delete a Kill and remove its value from the summary.
	 *
	 * @param Kill $kill
	 */
	abstract public static function delKill($kill);
	/**
	 * Update the summary table when a kill value changes.
	 *
	 * @param Kill $kill
	 * @param float $difference
	 */
	abstract public static function update($kill, $difference);
}
