<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
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
	private $campaign_ = 0;
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
		$qry = DBFactory::getDBQuery();;
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
		$this->campaign_ = ($row['ctr_campaign'] == "1");
		$this->ctr_comment_ = $row['ctr_comment'];

		// get corps & alliances for contract
		$sql = "select ctd.ctd_crp_id, ctd.ctd_all_id, ctd.ctd_reg_id, ctd.ctd_sys_id
                from kb3_contract_details ctd
                where ctd.ctd_ctr_id = ".$row['ctr_id']."
	            order by 3, 2, 1 -- get corps & alliances for contract";

		$caqry = DBFactory::getDBQuery();;
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

	function getID()
	{
		return $this->ctr_id_;
	}

	function getName()
	{
		$this->execQuery();
		return $this->ctr_name_;
	}

	function getStartDate()
	{
		$this->execQuery();
		return $this->ctr_started_;
	}

	function getEndDate()
	{
		$this->execQuery();
		return $this->ctr_ended_;
	}

	function getRunTime()
	{
		if (!$datet = $this->getEndDate())
		{
			$datet = 'now';
		}

		$diff = strtotime($datet) - strtotime($this->getStartDate());
		return floor($diff/86400);
	}

	function getCampaign()
	{
		$this->execQuery();
		return $this->campaign_;
	}
	
	function getComment()
	{
		$this->execQuery();
		return $this->ctr_comment_;
	}

	function getCorps()
	{
		$this->execQuery();
		return $this->corps;
	}

	function getAlliances()
	{
		$this->execQuery();
		return $this->alliances;
	}

	function getSystems()
	{
		$this->execQuery();
		return $this->systems;
	}

	function getRegions()
	{
		$this->execQuery();
		return $this->regions;
	}

	function getKills()
	{
		$this->execQuery();
		return $this->klist_->getCount();
	}

	function getLosses()
	{
		$this->execQuery();
		return $this->llist_->getCount();
	}

	function getKillISK()
	{
		$this->execQuery();
		if (!$this->klist_->getISK()) $this->klist_->getAllKills();
		return $this->klist_->getISK();
	}

	function getLossISK()
	{
		$this->execQuery();
		if (!$this->llist_->getISK()) $this->llist_->getAllKills();
		return $this->llist_->getISK();
	}

	function getEfficiency()
	{
		$this->execQuery();
		if ($this->klist_->getISK())
			$efficiency = round($this->klist_->getISK() / ($this->klist_->getISK() + $this->llist_->getISK()) * 100, 2);
		else
			$efficiency = 0;

		return $efficiency;
	}

	function getKillList()
	{
		$this->execQuery();
		return $this->klist_;
	}

	function getLossList()
	{
		$this->execQuery();
		return $this->llist_;
	}

	function getContractTarget()
	{
		if ($this->contractpointer_ > 30)
			return null;

		$target = $this->contracttargets_[$this->contractpointer_];
		if ($target)
			$this->contractpointer_++;
		return $target;
	}

	function add($name, $type, $startdate, $enddate = "", $comment = "")
	{
		$qry = DBFactory::getDBQuery();;
		if ($type == "campaign") $campaign = 1;
		else $campaign = 0;
		if ($enddate != "") $enddate = "'".$enddate." 23:59:59'";
		else $enddate = "null";

		if (!$this->ctr_id_)
		{
			$sql = "insert into kb3_contracts values ( null, '".$qry->escape($name)."',
                                                   '".KB_SITE."', ".$campaign.",
						   '".$qry->escape($startdate)." 00:00:00',
						   ".$qry->escape($enddate).",
						   '".$qry->escape($comment)."' )";
			$qry->execute($sql) or die($qry->getErrorMsg());
			$this->ctr_id_ = $qry->getInsertID();
		}
		else
		{
			$sql = "update kb3_contracts set ctr_name = '".$qry->escape($name)."',
			                 ctr_started = '".$qry->escape($startdate)." 00:00:00',
					 ctr_ended = ".$qry->escape($enddate).",
					 ctr_comment = '" . $qry->escape($comment) . "'
				     where ctr_id = ".$this->ctr_id_;
			$qry->execute($sql) or die($qry->getErrorMsg());
			$this->ctr_id_ = $qry->getInsertID();
		}
	}

	function remove()
	{
		$qry = DBFactory::getDBQuery();;

		$qry->execute("delete from kb3_contracts
                       where ctr_id = ".$this->ctr_id_);

		$qry->execute("delete from kb3_contract_details
                       where ctd_ctr_id = ".$this->ctr_id_);
	}

	function validate()
	{
		$qry = DBFactory::getDBQuery();;

		$qry->execute("select * from kb3_contracts
                       where ctr_id = ".$this->ctr_id_."
		         and ctr_site = '".KB_SITE."'");
		return ($qry->recordCount() > 0);
	}
	
	function setComment($comment)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("UPDATE kb3_contracts
					   SET ctr_comment = '" . $qry->escape($comment) . "'
					   WHERE ctr_id = {$this->ctr_id_}");
	}
}
