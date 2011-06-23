<?php
/**
 * $Date: 2010-05-30 03:57:50 +1000 (Sun, 30 May 2010) $
 * $Revision: 711 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.contract.php $
 * @package EDK
 */

/**
 * @package EDK
 */
class ContractTarget
{
	function ContractTarget($contract, $crp_id, $all_id, $reg_id , $sys_id)
	{
		$this->contract_ = $contract;
		$this->crp_id_ = $crp_id;
		$this->all_id_ = $all_id;
		$this->reg_id_ = $reg_id;
		$this->sys_id_ = $sys_id;

		$this->klist_ = new KillList();
		$this->llist_ = new KillList();

		if ($this->crp_id_)
		{
			$this->type_ = "corp";
			$this->klist_->addVictimCorp($this->crp_id_);
			$this->llist_->addInvolvedCorp($this->crp_id_);
			$this->id_ = $this->crp_id_;
		}
		elseif ($this->all_id_)
		{
			$this->type_ = "alliance";
			$this->klist_->addVictimAlliance($this->all_id_);
			$this->llist_->addInvolvedAlliance($this->all_id_);
			$this->id_ = $this->all_id_;
		}
		elseif ($this->reg_id_)
		{
			$this->type_ = "region";
			$this->klist_->addRegion($this->reg_id_);
			$this->llist_->addRegion($this->reg_id_);
			$this->id_ = $this->reg_id_;
		}
		elseif ($this->sys_id_)
		{
			$this->type_ = "system";
			$this->klist_->addSystem($this->sys_id_);
			$this->llist_->addSystem($this->sys_id_);
			$this->id_ = $this->sys_id_;
		}

		involved::load($this->klist_,'kill');
		involved::load($this->llist_,'loss');

		$this->klist_->setStartDate($contract->getStartDate());
		$this->llist_->setStartDate($contract->getStartDate());
		if ($contract->getEndDate() != "")
		{
			$this->klist_->setEndDate($contract->getEndDate());
			$this->llist_->setEndDate($contract->getEndDate());
		}
	}

	function getID()
	{
		return $this->id_;
	}

	function getName()
	{
		if ($this->name_ == "")
		{
			$qry = DBFactory::getDBQuery();
			switch ($this->type_)
			{
				case "corp":
					$qry->execute("select crp_name as name from kb3_corps where crp_id = ".$this->crp_id_);
					break;
				case "alliance":
					$qry->execute("select all_name as name from kb3_alliances where all_id = ".$this->all_id_);
					break;
				case "region":
					$qry->execute("select reg_name as name from kb3_regions where reg_id = ".$this->reg_id_);
					break;
				case "system":
					$qry->execute("select sys_name as name from kb3_systems where sys_id = ".$this->sys_id_);
					break;
			}
			$row = $qry->getRow();
			$this->name_ = $row['name'];
		}
		return $this->name_;
	}

	function getType()
	{
		return $this->type_;
	}

	function getKillList()
	{
		return $this->klist_;
	}

	function getLossList()
	{
		return $this->llist_;
	}

	function getEfficiency()
	{
		if ($this->klist_->getISK())
			$efficiency = round($this->klist_->getISK() / ($this->klist_->getISK() + $this->llist_->getISK()) * 100, 2);
		else
			$efficiency = 0;

		return $efficiency;
	}

	function getKills()
	{
	}

	function getLosses()
	{
	}

	function add()
	{
		$qry = DBFactory::getDBQuery();
		$sql = "insert into kb3_contract_details
                     values ( ".$this->contract_->getID().",";
		switch ($this->type_)
		{
			case "corp":
				$sql .= $this->id_.", 0, 0, 0 )";
				break;
			case "alliance":
				$sql .= "0, ".$this->id_.", 0, 0 )";
				break;
			case "region":
				$sql .= "0, 0, ".$this->id_.",0 )";
				break;
			case "system":
				$sql .= "0, 0, 0, ".$this->id_." )";
				break;
		}
		$qry->execute($sql) or die($qry->getErrorMsg());
	}

	function remove()
	{
		$qry = DBFactory::getDBQuery();
		$sql = "delete from kb3_contract_details
                    where ctd_ctr_id = ".$this->contract_->getID();
		switch ($this->type_)
		{
			case "corp":
				$sql .= " and ctd_crp_id = ".$this->id_;
				break;
			case "alliance":
				$sql .= " and ctd_all_id = ".$this->id_;
				break;
			case "region":
				$sql .= " and ctd_reg_id = ".$this->id_;
				break;
			case "system":
				$sql .= " and ctd_sys_id = ".$this->id_;
				break;
		}
		$qry->execute($sql) or die($qry->getErrorMsg());
	}
}
