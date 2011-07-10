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
	/** @var Pilot */
	private $pilot_;
	/** @var Corporation */
	private $corp_;
	/** @var Alliance */
	private $alliance_;

	/**
	 * @param Pilot $pilot
	 * @param float $secstatus
	 * @param Corporation $corp
	 * @param Alliance $alliance
	 * @param Ship $ship
	 * @param Item $weapon
	 * @param integer $dmgdone
	 */
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

	/**
	 * @return Pilot
	 */
	function getPilot()
	{
		return $this->pilot_;
	}

	/**
	 * @return integer
	 */
	function getPilotID()
	{
		return $this->pilot_->getID();
	}

	/**
	 * @return Corporation
	 */
	function getCorp()
	{
		return $this->corp_;
	}

	/**
	 * @return integer
	 */
	function getCorpID()
	{
		return $this->corp_->getID();
	}

	/**
	 * @return Alliance
	 */
	function getAlliance()
	{
		return $this->alliance_;
	}

	/**
	 * @return integer
	 */
	function getAllianceID()
	{
		return $this->alliance_->getID();
	}

}
