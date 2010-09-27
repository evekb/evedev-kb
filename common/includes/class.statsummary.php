<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


//! Base class for storing summary statistics.
abstract class statSummary
{
	protected $executed = false;
	protected $summary = array();
	//! Get the complete summary for this alliance.

	//! \return an array of ship id by kill/loss count/isk.
	public function getSummary()
	{
		if(!$this->executed) $this->execute();
		return $this->summary;
	}
	//! Return total ISK killed.
	public function getTotalKillISK()
	{
		if(!$this->executed) $this->execute();
		foreach($this->summary as $value)
			$totalisk .= $value['killisk'];
		return $totalisk;
	}
	//! Return total ISK lost.
	public function getTotalLossISK()
	{
		if(!$this->executed) $this->execute();
		foreach($this->summary as $value)
			$totalisk .= $value['lossisk'];
		return $totalisk;
	}
	//! Return the number of kills for the given ship class.
	public function getKillCount($shp_class)
	{
		if(!$this->executed) $this->execute();
		return intval($this->summary[$ship_class]['killcount']);
	}
	//! Return the ISK value of kills for the given ship class.
	public function getKillISK($shp_class)
	{
		if(!$this->executed) $this->execute();
		return intval($this->summary[$ship_class]['killisk']);
	}
	//! Return the number of losses for the given ship class.
	public function getLossCount($shp_class)
	{
		if(!$this->executed) $this->execute();
		return intval($this->summary[$ship_class]['losscount']);
	}
	//! Return the ISK value of losses for the given ship class.
	public function getLossISK($shp_class)
	{
		if(!$this->executed) $this->execute();
		return intval($this->summary[$ship_class]['lossisk']);
	}
	abstract public static function addKill($id);
	abstract public static function delKill($id);
	abstract public static function update($kill, $difference);
}
