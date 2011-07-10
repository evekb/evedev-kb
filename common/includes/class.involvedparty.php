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
	protected $pilotid_;
	protected $corpid_;
	protected $allianceid_;
	protected $secstatus_;
	protected $ship_;
	protected $weapon_;
	public $dmgdone_;

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

	function getPilotID()
	{
		return $this->pilotid_;
	}

	function getCorpID()
	{
		return $this->corpid_;
	}

	function getAllianceID()
	{
		return $this->allianceid_;
	}

	function getSecStatus()
	{
		return number_format($this->secstatus_, 1);
	}

	function getShip()
	{
		return $this->ship_;
	}

	function getWeapon()
	{
		return $this->weapon_;
	}

	function getDamageDone()
	{
		return $this->dmgdone_;
	}
}