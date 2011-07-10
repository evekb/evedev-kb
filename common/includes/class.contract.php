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
class Contract
{
	private $alliances = array();
	private $corps = array();
	private $regions = array();
	private $systems = array();
	private $ctr_id_ = 0;
	private $contracttargets_ = array();
	private $contractpointer_ = 0;
	private $executed = false;
	private $klist_ = null;
	private $llist_ = null;
	private $ctr_name_ = '';
	private $ctr_started_ = '';
	private $ctr_ended_ = '';
	private $ctr_comment_ = '';

	function Contract($ctr_id = 0)
	{
		$this->ctr_id_ = intval($ctr_id);

		// overall kill/losslist
		$this->klist_ = new KillList();
		$this->llist_ = new KillList();
		involved::load($this->klist_,'kill');
		involved::load($this->llist_,'loss');
	}

	function execQuery()
	{
		if ($this->executed)
			return;
		$qry = DBFactory::getDBQuery();
		// general
		$sql = "select * from kb3_contracts ctr
                where ctr.ctr_id = ".$this->ctr_id_;

		if (!$qry->execute($sql))
			die($qry->getErrorMsg());
		$this->executed = true;

		$row = $qry->getRow();
		$this->ctr_name_ = $row['ctr_name'];
		$this->ctr_started_ = $row['ctr_started'];
		$this->ctr_ended_ = $row['ctr_ended'];
		$this->ctr_comment_ = $row['ctr_comment'];

		// get corps & alliances for contract
		$sql = "select ctd.ctd_crp_id, ctd.ctd_all_id, ctd.ctd_reg_id, ctd.ctd_sys_id
                from kb3_contract_details ctd
                where ctd.ctd_ctr_id = ".$row['ctr_id']."
	            order by 3, 2, 1 -- get corps & alliances for contract";

		$caqry = DBFactory::getDBQuery();
		if (!$caqry->execute($sql))
		{
			check_contracts();
			$caqry->execute($sql);
		}

		while ($carow = $caqry->getRow())
		{
			$contracttarget = new ContractTarget($this, $carow['ctd_crp_id'], $carow['ctd_all_id'], $carow['ctd_reg_id'], $carow['ctd_sys_id']);
			array_push($this->contracttargets_, $contracttarget);
			if ($carow['ctd_crp_id'])
			{
				$this->klist_->addVictimCorp($carow['ctd_crp_id']);
				$this->llist_->addInvolvedCorp($carow['ctd_crp_id']);
				$this->corps[] = $carow['ctd_crp_id'];
			}
			elseif ($carow['ctd_all_id'])
			{
				$this->klist_->addVictimAlliance($carow['ctd_all_id']);
				$this->llist_->addInvolvedAlliance($carow['ctd_all_id']);
				$this->alliances[] = $carow['ctd_all_id'];
			}
			elseif ($carow['ctd_reg_id'])
			{
				$this->klist_->addRegion($carow['ctd_reg_id']);
				$this->llist_->addRegion($carow['ctd_reg_id']);
				$this->regions[] = $carow['ctd_reg_id'];
			}
			elseif ($carow['ctd_sys_id'])
			{
				$this->klist_->addSystem($carow['ctd_sys_id']);
				$this->llist_->addSystem($carow['ctd_sys_id']);
				$this->systems[] = $carow['ctd_sys_id'];
			}
		}

		$this->klist_->setStartDate($this->getStartDate());
		$this->llist_->setStartDate($this->getStartDate());
		if ($this->getEndDate() != "")
		{
			$this->klist_->setEndDate($this->getEndDate());
			$this->llist_->setEndDate($this->getEndDate());
		}
	}

	/**
	 * @return integer
	 */
	function getID()
	{
		return $this->ctr_id_;
	}

	/**
	 * @return string
	 */
	function getName()
	{
		$this->execQuery();
		return $this->ctr_name_;
	}

	/**
	 * @return string Formatted string for start date
	 */
	function getStartDate()
	{
		$this->execQuery();
		return $this->ctr_started_;
	}

	/**
	 * @return string Formatted string for end date
	 */
	function getEndDate()
	{
		$this->execQuery();
		return $this->ctr_ended_;
	}

	/**
	 * How long this campaign has run for.
	 * For current campaigns this returns the length so far.
	 * @return integer The length of this campaign.
	 */
	function getRunTime()
	{
		if (!$datet = $this->getEndDate())
		{
			$datet = 'now';
		}

		$diff = strtotime($datet) - strtotime($this->getStartDate());
		return floor($diff/86400);
	}

	/**
	 * @deprecated
	 * @return boolean
	 */
	function getCampaign()
	{
		return true;
	}

	/**
	 * Returns a comment string for this campaign.
	 * @return string
	 */
	function getComment()
	{
		$this->execQuery();
		return $this->ctr_comment_;
	}

	/**
	 * Return an array of Corporations in this campaign.
	 * @return array
	 */
	function getCorps()
	{
		$this->execQuery();
		return $this->corps;
	}

	/**
	 * Return an array of Alliances in this campaign.
	 * @return array
	 */
	function getAlliances()
	{
		$this->execQuery();
		return $this->alliances;
	}

	/**
	 * Return an array of Systems in this campaign.
	 * @return array
	 */
	function getSystems()
	{
		$this->execQuery();
		return $this->systems;
	}

	/**
	 * Return an array of Regions in this campaign.
	 * @return array
	 */
	function getRegions()
	{
		$this->execQuery();
		return $this->regions;
	}

	/**
	 * Return the count of kills for this campaign
	 * @return integer
	 */
	function getKills()
	{
		$this->execQuery();
		return $this->klist_->getCount();
	}

	/**
	 * Return the count of losses for this campaign
	 * @return integer
	 */
	function getLosses()
	{
		$this->execQuery();
		return $this->llist_->getCount();
	}

	/**
	 * Return the isk destroyed during this campaign
	 * @return float
	 */
	function getKillISK()
	{
		$this->execQuery();
		if (!$this->klist_->getISK()) $this->klist_->getAllKills();
		return $this->klist_->getISK();
	}

	/**
	 * Return the isk lost during this campaign
	 * @return float
	 */
	function getLossISK()
	{
		$this->execQuery();
		if (!$this->llist_->getISK()) $this->llist_->getAllKills();
		return $this->llist_->getISK();
	}

	/**
	 * Return the efficiency of this campaign as a percent.
	 * @return float
	 */
	function getEfficiency()
	{
		$this->execQuery();
		if ($this->klist_->getISK())
			$efficiency = round($this->klist_->getISK() / ($this->klist_->getISK() + $this->llist_->getISK()) * 100, 2);
		else
			$efficiency = 0;

		return $efficiency;
	}

	/**
	 * Return the KillList showing kills in this campaign.
	 * @return KillList
	 */
	function getKillList()
	{
		$this->execQuery();
		return $this->klist_;
	}

	/**
	 * Return the KillList showing losses in this campaign.
	 * @return KillList
	 */
	function getLossList()
	{
		$this->execQuery();
		return $this->llist_;
	}

	/**
	 * Get next ContractTarget
	 * @return ContractTarget
	 */
	function getContractTarget()
	{
		if ($this->contractpointer_ > 30)
			return null;

		$target = $this->contracttargets_[$this->contractpointer_];
		if ($target)
			$this->contractpointer_++;
		return $target;
	}

	/**
	 *
	 * @param string $name
	 * @param string $type
	 * @param string $startdate
	 * @param string $enddate
	 * @param string $comment
	 */
	function add($name, $type, $startdate, $enddate = "", $comment = "")
	{
		$qry = DBFactory::getDBQuery();
		if ($type == "campaign") $campaign = 1;
		else $campaign = 0;
		// null doesn't work inside the quotes.
		if ($enddate != "") $enddate = "'".$qry->escape($enddate." 23:59:59")."'";
		else $enddate = "null";

		if (!$this->ctr_id_)
		{
			$sql = "insert into kb3_contracts values ( null, '".$qry->escape($name)."',
                                                   '".KB_SITE."', ".$campaign.",
						   '".$qry->escape($startdate)." 00:00:00',
						   ".$enddate.",
						   '".$qry->escape($comment)."' )";
			$qry->execute($sql) or die($qry->getErrorMsg());
			$this->ctr_id_ = $qry->getInsertID();
		}
		else
		{
			$sql = "update kb3_contracts set ctr_name = '".$qry->escape($name)."',
			                 ctr_started = '".$qry->escape($startdate)." 00:00:00',
					 ctr_ended = ".$enddate.",
					 ctr_comment = '" . $qry->escape($comment) . "'
				     where ctr_id = ".$this->ctr_id_;
			$qry->execute($sql) or die($qry->getErrorMsg());
			$this->ctr_id_ = $qry->getInsertID();
		}
	}

	/**
	 * Delete this campaign.
	 */
	function remove()
	{
		$qry = DBFactory::getDBQuery();

		$qry->execute("delete from kb3_contracts
                       where ctr_id = ".$this->ctr_id_);

		$qry->execute("delete from kb3_contract_details
                       where ctd_ctr_id = ".$this->ctr_id_);
	}

	/**
	 * Check that this campaign exists.
	 * @return boolean
	 */
	function validate()
	{
		$qry = DBFactory::getDBQuery();

		$qry->execute("select * from kb3_contracts
                       where ctr_id = ".$this->ctr_id_."
		         and ctr_site = '".KB_SITE."'");
		return ($qry->recordCount() > 0);
	}

	/**
	 * Set a string describing this campaign.
	 * @param string $comment
	 */
	function setComment($comment)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("UPDATE kb3_contracts
					   SET ctr_comment = '" . $qry->escape($comment) . "'
					   WHERE ctr_id = {$this->ctr_id_}");
	}
}
