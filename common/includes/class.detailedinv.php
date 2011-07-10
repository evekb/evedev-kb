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
class DetailedInv extends InvolvedParty
{
	private $pilot_;
	private $corp_;
	private $alliance_;
	
	function DetailedInv($pilot, $secstatus, $corp, $alliance, $ship, $weapon, $dmgdone = 0)
	{
		$this->pilot_ = $pilot;
		$this->secstatus_ = $secstatus;
		$this->corp_ = $corp;
		$this->alliance_ = $alliance;
		$this->ship_ = $ship;
		$this->weapon_ = $weapon;
		$this->dmgdone_ = $dmgdone;
	}

	function getPilot()
	{
		return $this->pilot_;
	}

	function getPilotID()
	{
		return $this->pilot_->getID();
	}

	function getCorp()
	{
		return $this->corp_;
	}

	function getCorpID()
	{
		return $this->corp_->getID();
	}

	function getAlliance()
	{
		return $this->alliance_;
	}

	function getAllianceID()
	{
		return $this->alliance_->getID();
	}

}
