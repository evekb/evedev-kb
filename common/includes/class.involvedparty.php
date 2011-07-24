<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * A simple wrapper for involved party information.
 * @package EDK
 */
class InvolvedParty
{
	/** var integer */
	protected $pilotid;
	/** var integer */
	protected $corpid;
	/** var integer */
	protected $allianceid;
	/** var float */
	protected $secstatus;
	/** var integer */
	protected $shipid;
	/** var integer */
	protected $weaponid;
	/** var integer */
	protected $dmgdone;

	/**
	 * @param integer $pilotid
	 * @param integer $corpid
	 * @param integer $allianceid
	 * @param float $secstatus
	 * @param integer $ship
	 * @param integer $weapon
	 * @param integer $dmgdone 
	 */
	function InvolvedParty($pilotid, $corpid, $allianceid, $secstatus, $shipid,
			$weaponid, $dmgdone = 0)
	{
		$this->pilotid = (int)$pilotid;
		$this->corpid = (int)$corpid;
		$this->allianceid = (int)$allianceid;
		$this->secstatus = (float)$secstatus;
		$this->shipid = (int)$shipid;
		$this->weaponid = (int)$weaponid;
		$this->dmgdone = (int)$dmgdone;
	}

	/**
	 * @return integer
	 */
	function getPilotID()
	{
		return $this->pilotid;
	}

	/**
	 * @return integer
	 */
	function getCorpID()
	{
		return $this->corpid;
	}

	/**
	 * @return integer
	 */
	function getAllianceID()
	{
		return $this->allianceid;
	}

	/**
	 * @return integer
	 */
	function getShipID()
	{
		return $this->shipid;
	}

	/**
	 * @return integer
	 */
	function getWeaponID()
	{
		return $this->weaponid;
	}

	/**
	 * @deprecated
	 * @return Pilot
	 */
	function getPilot()
	{
		return Cacheable::factory('Pilot', $this->pilotid);
	}

	/**
	 * @deprecated
	 * @return Corporation
	 */
	function getCorp()
	{
		return Cacheable::factory('Corporation', $this->corpid);
	}

	/**
	 * @deprecated
	 * @return Alliance
	 */
	function getAlliance()
	{
		return Cacheable::factory('Alliance', $this->allianceid);
	}

	/**
	 * @deprecated
	 * @return Ship
	 */
	function getShip()
	{
		return Cacheable::factory('Ship', $this->shipid);
	}

	/**
	 * @deprecated
	 * @return Item
	 */
	function getWeapon()
	{
		return Cacheable::factory('Item', $this->weaponid);
	}

	/**
	 * @return float
	 */
	function getSecStatus()
	{
		return number_format($this->secstatus, 1);
	}

	/**
	 * @return integer
	 */
	function getDamageDone()
	{
		return $this->dmgdone;
	}
}