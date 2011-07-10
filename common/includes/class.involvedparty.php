<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class InvolvedParty
{
	/** var integer */
	protected $pilotid_;
	/** var integer */
	protected $corpid_;
	/** var integer */
	protected $allianceid_;
	/** var float */
	protected $secstatus_;
	/** var Ship */
	protected $ship_;
	/** var Item */
	protected $weapon_;
	/** var integer */
	public $dmgdone_;

	/**
	 * @param integer $pilotid
	 * @param integer $corpid
	 * @param integer $allianceid
	 * @param flat $secstatus
	 * @param Ship $ship
	 * @param Item $weapon
	 * @param integer $dmgdone 
	 */
	function InvolvedParty($pilotid, $corpid, $allianceid, $secstatus, $ship, $weapon, $dmgdone = 0)
	{
		$this->pilotid_ = $pilotid;
		$this->corpid_ = $corpid;
		$this->allianceid_ = $allianceid;
		$this->secstatus_ = $secstatus;
		$this->ship_ = $ship;
		$this->weapon_ = $weapon;
		$this->dmgdone_ = $dmgdone;
	}

	/**
	 * @return integer
	 */
	function getPilotID()
	{
		return $this->pilotid_;
	}

	/**
	 * @return integer
	 */
	function getCorpID()
	{
		return $this->corpid_;
	}

	/**
	 * @return integer
	 */
	function getAllianceID()
	{
		return $this->allianceid_;
	}

	/**
	 * @return float
	 */
	function getSecStatus()
	{
		return number_format($this->secstatus_, 1);
	}

	/**
	 * @return Ship
	 */
	function getShip()
	{
		return $this->ship_;
	}

	/**
	 * @return Item
	 */
	function getWeapon()
	{
		return $this->weapon_;
	}

	/**
	 * @return integer
	 */
	function getDamageDone()
	{
		return $this->dmgdone_;
	}
}