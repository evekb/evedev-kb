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
	/** @var array Array of target alliance IDs */
	private $alliances;
	/** @var array Array of target corporation IDs */
	private $corps;
	/** @var array Array of target region IDs */
	private $regions;
	/** @var array Array of target system IDs */
	private $systems;
	/** @var integer The contract ID */
	private $ctr_id = 0;
	/** @var array Array of ContractTargets */
	private $contracttargets;
	/** @var integer Which target we are currently viewing */
	private $contractpointer = 0;
	/** @var boolean Whether the backing query has been run */
	private $executed = false;
	/** @var KillList The list of kills */
	private $klist;
	/** @var KillList The list of losses */
	private $llist;
	/** @var string Contract name */
	private $ctr_name;
	/** @var string Contract start date */
	private $ctr_started;
	/** @var string Contract end date */
	private $ctr_ended;
	/** @var string Contract comment */
	private $ctr_comment;

	/**
	 * @param integer $ctr_id  The contract ID
	 */
	function Contract($ctr_id = 0)
	{
		$this->ctr_id = (int)$ctr_id;

		// overall kill/losslist
		$this->klist = new KillList();
		$this->llist = new KillList();
		involved::load($this->klist,'kill');
		involved::load($this->llist,'loss');
	}

	private function execQuery()
	{
		if ($this->executed)
			return;
		$qry = DBFactory::getDBQuery();
		// general
		$sql = "select * from kb3_contracts ctr
                where ctr.ctr_id = ".$this->ctr_id;

		if (!$qry->execute($sql))
			die($qry->getErrorMsg());
		$this->executed = true;

		$row = $qry->getRow();
		$this->ctr_name = $row['ctr_name'];
		$this->ctr_started = $row['ctr_started'];
		$this->ctr_ended = $row['ctr_ended'];
		$this->ctr_comment = $row['ctr_comment'];

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

		$this->contracttargets = array();
		$this->corps = array();
		$this->alliances = array();
		$this->regions = array();
		$this->systems = array();

		while ($carow = $caqry->getRow())
		{
			$contracttarget = new ContractTarget($this, $carow['ctd_crp_id'], $carow['ctd_all_id'], $carow['ctd_reg_id'], $carow['ctd_sys_id']);
			$this->contracttargets[] = $contracttarget;
			if ($carow['ctd_crp_id'])
			{
				$this->klist->addVictimCorp($carow['ctd_crp_id']);
				$this->llist->addInvolvedCorp($carow['ctd_crp_id']);
				$this->corps[] = $carow['ctd_crp_id'];
			}
			elseif ($carow['ctd_all_id'])
			{
				$this->klist->addVictimAlliance($carow['ctd_all_id']);
				$this->llist->addInvolvedAlliance($carow['ctd_all_id']);
				$this->alliances[] = $carow['ctd_all_id'];
			}
			elseif ($carow['ctd_reg_id'])
			{
				$this->klist->addRegion($carow['ctd_reg_id']);
				$this->llist->addRegion($carow['ctd_reg_id']);
				$this->regions[] = $carow['ctd_reg_id'];
			}
			elseif ($carow['ctd_sys_id'])
			{
				$this->klist->addSystem($carow['ctd_sys_id']);
				$this->llist->addSystem($carow['ctd_sys_id']);
				$this->systems[] = $carow['ctd_sys_id'];
			}
		}

		$this->klist->setStartDate($this->getStartDate());
		$this->llist->setStartDate($this->getStartDate());
		if ($this->getEndDate() != "")
		{
			$this->klist->setEndDate($this->getEndDate());
			$this->llist->setEndDate($this->getEndDate());
		}
	}

	/**
	 * @return integer The ID of this campaign.
	 */
	function getID()
	{
		return $this->ctr_id;
	}

	/**
	 * @return string The name of this campaign.
	 */
	function getName()
	{
		if (is_null($this->ctr_name)) {
			$this->execQuery();
		}
		return $this->ctr_name;
	}

	/**
	 * @return string Formatted string for start date
	 */
	function getStartDate()
	{
		if (is_null($this->ctr_started)) {
			$this->execQuery();
		}
		return $this->ctr_started;
	}

	/**
	 * @return string Formatted string for end date
	 */
	function getEndDate()
	{
		if (is_null($this->ctr_ended)) {
			$this->execQuery();
		}
		return $this->ctr_ended;
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
		if (is_null($this->ctr_comment)) {
			$this->execQuery();
		}
		return $this->ctr_comment;
	}

	/**
	 * Return an array of Corporations in this campaign.
	 * @return array
	 */
	function getCorps()
	{
		if (is_null($this->corps)) {
			$this->execQuery();
		}
		return $this->corps;
	}

	/**
	 * Return an array of Alliances in this campaign.
	 * @return array
	 */
	function getAlliances()
	{
		if (is_null($this->alliances)) {
			$this->execQuery();
		}
		return $this->alliances;
	}

	/**
	 * Return an array of Systems in this campaign.
	 * @return array
	 */
	function getSystems()
	{
		if (is_null($this->systems)) {
			$this->execQuery();
		}

		return $this->systems;
	}

	/**
	 * Return an array of Regions in this campaign.
	 * @return array
	 */
	function getRegions()
	{
		if (is_null($this->regions)) {
			$this->execQuery();
		}
		return $this->regions;
	}

	/**
	 * Return the count of kills for this campaign
	 * @return integer
	 */
	function getKills()
	{
		$this->execQuery();
		return $this->klist->getCount();
	}

	/**
	 * Return the count of losses for this campaign
	 * @return integer
	 */
	function getLosses()
	{
		$this->execQuery();
		return $this->llist->getCount();
	}

	/**
	 * Return the isk destroyed during this campaign
	 * @return float
	 */
	function getKillISK()
	{
		$this->execQuery();
		if (!$this->klist->getISK()) $this->klist->getAllKills();
		return $this->klist->getISK();
	}

	/**
	 * Return the isk lost during this campaign
	 * @return float
	 */
	function getLossISK()
	{
		$this->execQuery();
		if (!$this->llist->getISK()) $this->llist->getAllKills();
		return $this->llist->getISK();
	}

	/**
	 * Return the efficiency of this campaign as a percent.
	 * @return float
	 */
	function getEfficiency()
	{
		$this->execQuery();
		if ($this->klist->getISK())
			$efficiency = round($this->klist->getISK() / ($this->klist->getISK() + $this->llist->getISK()) * 100, 2);
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
		return $this->klist;
	}

	/**
	 * Return the KillList showing losses in this campaign.
	 * @return KillList
	 */
	function getLossList()
	{
		$this->execQuery();
		return $this->llist;
	}

	/**
	 * Get next ContractTarget
	 * @return ContractTarget
	 */
	function getContractTarget()
	{
		if ($this->contractpointer > 30)
			return null;

		$target = $this->contracttargets[$this->contractpointer];
		if ($target)
			$this->contractpointer++;
		return $target;
	}

	/**
	 *
	 * @param string $name Campaign name
	 * @param string $type unused
	 * @param string $startdate Campaign starting date.
	 * @param string $enddate Optional ending date for the campaign
	 * @param string $comment Optional comment for the campaign
	 */
	function add($name, $type, $startdate, $enddate = "", $comment = "")
	{
		$qry = DBFactory::getDBQuery();
		// null doesn't work inside the quotes.
		if ($enddate != "") $enddate = "'".$qry->escape($enddate." 23:59:59")."'";
		else $enddate = "null";

		if (!$this->ctr_id)
		{
			$sql = "insert into kb3_contracts values ( null, '".$qry->escape($name)."',
                                                   '".KB_SITE."', 1,
						   '".$qry->escape($startdate)." 00:00:00',
						   ".$enddate.",
						   '".$qry->escape($comment)."' )";
			$qry->execute($sql) or die($qry->getErrorMsg());
			$this->ctr_id = $qry->getInsertID();
		}
		else
		{
			$sql = "update kb3_contracts set ctr_name = '".$qry->escape($name)."',
			                 ctr_started = '".$qry->escape($startdate)." 00:00:00',
					 ctr_ended = ".$enddate.",
					 ctr_comment = '" . $qry->escape($comment) . "'
				     where ctr_id = ".$this->ctr_id;
			$qry->execute($sql) or die($qry->getErrorMsg());
			$this->ctr_id = $qry->getInsertID();
		}
	}

	/**
	 * Delete this campaign.
	 */
	function remove()
	{
		$qry = DBFactory::getDBQuery();

		$qry->execute("DELETE kb3_contracts, kb3_contract_details"
						." FROM kb3_contracts"
						." LEFT JOIN kb3_contract_details ON ctr_id = ctd_ctr_id"
						." WHERE ctr_id = ".$this->ctr_id);
	}

	/**
	 * Check that this campaign exists.
	 * @return boolean
	 */
	function validate()
	{
		$qry = DBFactory::getDBQuery();

		$qry->execute("select * from kb3_contracts
                       where ctr_id = ".$this->ctr_id."
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
					   WHERE ctr_id = {$this->ctr_id}");
	}
}
