<?php
require_once("db.php");
require_once("class.killlist.php");
require_once("class.graph.php");
require_once("class.pagesplitter.php");

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

	function add($name, $type, $startdate, $enddate = "")
	{
		$qry = DBFactory::getDBQuery();;
		if ($type == "campaign") $campaign = 1;
		else $campaign = 0;
		if ($enddate != "") $enddate = "'".$enddate." 23:59:59'";
		else $enddate = "null";

		if (!$this->ctr_id_)
		{
			$sql = "insert into kb3_contracts values ( null, '".slashfix($name)."',
                                                   '".KB_SITE."', ".$campaign.",
						   '".$startdate." 00:00:00',
						   ".$enddate." )";
			$qry->execute($sql) or die($qry->getErrorMsg());
			$this->ctr_id_ = $qry->getInsertID();
		}
		else
		{
			$sql = "update kb3_contracts set ctr_name = '".slashfix($name)."',
			                 ctr_started = '".$startdate." 00:00:00',
					 ctr_ended = ".$enddate."
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
}

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
			$qry = DBFactory::getDBQuery();;
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
		$qry = DBFactory::getDBQuery();;
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
		$qry = DBFactory::getDBQuery();;
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

class ContractList
{
	public $qry_ = null;
	private $active_ = "both";
	private $contractcounter_ = 1;

	function ContractList()
	{
		$this->qry_ = DBFactory::getDBQuery();;
	}

	function execQuery()
	{
		if ($this->qry_->executed())
			return;

		$sql = "select ctr.ctr_id, ctr.ctr_started, ctr.ctr_ended, ctr.ctr_name
                from kb3_contracts ctr
               where ctr.ctr_site = '".KB_SITE."'";
		if ($this->active_ == "yes")
			$sql .= " and ( ctr_ended is null or now() <= ctr_ended )";
		elseif ($this->active_ == "no")
			$sql .= " and ( now() >= ctr_ended )";
/*
		if ($this->campaigns_)
			$sql .= " and ctr.ctr_campaign = 1";
		else
			$sql .= " and ctr.ctr_campaign = 0";
*/
		$sql .= " order by ctr_ended, ctr_started desc";
		// if ( $this->limit_ )
		// $sql .= " limit ".( $this->page_ / $this->limit_ ).", ".$this->limit_;
		$this->qry_ = DBFactory::getDBQuery();;
		$this->qry_->execute($sql) or die($this->qry_->getErrorMsg());
	}

	function setActive($active)
	{
		$this->active_ = $active;
	}

	function setCampaigns($campaigns)
	{
		$this->campaigns_ = $campaigns;
	}

	function setLimit($limit)
	{
		$this->limit_ = $limit;
	}

	function setPage($page)
	{
		$this->page_ = $page;
		$this->offset_ = ($page * $this->limit_) - $this->limit_;
	}

	function getContract()
	{
	// echo "off: ".$this->offset_."<br>";
	// echo "cnt: ".$this->contractcounter_."<br>";
	// echo "limit: ".$this->limit_."<br>";
		$this->execQuery();
		if ($this->offset_ && $this->contractcounter_ < $this->offset_)
		{
			for ($i = 0; $i < $this->offset_; $i++)
			{
				$row = $this->qry_->getRow();
				$this->contractcounter_++;
			}
		}
		if ($this->limit_ && ($this->contractcounter_ - $this->offset_) > $this->limit_)
			return null;

		$row = $this->qry_->getRow();
		if ($row)
		{
			$this->contractcounter_++;
			return new Contract($row['ctr_id']);
		}
		else
			return null;
	}

	function getCount()
	{
		$this->execQuery();
		return $this->qry_->recordCount();
	}

	function getActive()
	{
		return $this->active_;
	}

	public function rewind()
	{
		$this->contractcounter_ = 1;
		$this->qry_->rewind();
	}
}

class ContractListTable
{
	private $contractlist = null;
	private $paginate = null;

	function ContractListTable($contractlist)
	{
		$this->contractlist = $contractlist;
	}

	function paginate($paginate, $page = 1)
	{
		if (!$page) $page = 1;
		$this->paginate = $paginate;
		$this->contractlist->setLimit($paginate);
		$this->contractlist->setPage($page);
	}

	function getTableStats()
	{
		$qry = DBFactory::getDBQuery();;
		while ($contract = $this->contractlist->getContract())
		{
		// generate all neccessary objects within the contract
			$contract->execQuery();


			for ($i = 0; $i < 2; $i++)
			{
				$sql = 'select count(kll_id) AS ships, sum(kll_isk_loss) as isk from (';

				if($i) $invcount = count($contract->getAlliances()) + count($contract->getCorps());
				else $invcount = 0;
				if($invcount > 1) $sql .= 'select distinct kll_id, kll_isk_loss FROM kb3_kills kll ';
				else $sql .= 'select kll_id, kll_isk_loss FROM kb3_kills kll ';

				if ($contract->getRegions())
				{
					$sql .= ' inner join kb3_systems sys on ( sys.sys_id = kll.kll_system_id )
							inner join kb3_constellations con
							on ( con.con_id = sys.sys_con_id
							and con.con_reg_id in ( '.join(',', $contract->getRegions()).' ) )';
				}
				if(!$i)
				{
					if ($contract->getCorps() )
					{
						$sql .= ' inner join kb3_inv_crp inc on ( kll.kll_id = inc.inc_kll_id ) ';
					}
					if ($contract->getAlliances() )
					{
						$sql .= ' inner join kb3_inv_all ina on ( kll.kll_id = ina.ina_kll_id ) ';
					}
				}
				else
				{
					if(PILOT_ID) $sql .= ' inner join kb3_inv_detail ind on ( kll.kll_id = ind.ind_kll_id ) ';
					else if(CORP_ID) $sql .= ' inner join kb3_inv_crp inc on ( kll.kll_id = inc.inc_kll_id ) ';
					else if(ALLIANCE_ID) $sql .=' inner join kb3_inv_all ina on ( kll.kll_id = ina.ina_kll_id ) ';
				}
				if($contract->getStartDate())
				{
					$sql .= " WHERE kll.kll_timestamp >= '".$contract->getStartDate()."' ";
					if ($i && $contract->getCorps() )
						$sql .= " AND inc.inc_timestamp >= '".$contract->getStartDate()."' ";
					if ($i && $contract->getAlliances() )
						$sql .= " AND ina.ina_timestamp >= '".$contract->getStartDate()."' ";
					$sqlwhereop = ' AND ';
				}
				else $sqlwhereop = ' WHERE ';

				$tmp = array();
				if(!$i)
				{
					if ($contract->getCorps())
					{
						$tmp[] = 'inc.inc_crp_id in ( '.join(',', $contract->getCorps()).')';
					}
					if ($contract->getAlliances())
					{
						$tmp[] = 'ina.ina_all_id in ( '.join(',', $contract->getAlliances()).')';
					}
					if (count($tmp))
					{
						$sql .= $sqlwhereop.' (';
						$sql .= join(' or ', $tmp);
						$sql .= ')';
						$sqlwhereop = ' AND ';
					}
					$tmp = array();
					if(PILOT_ID) $sql .= 'AND kll.kll_victim_id = '.PILOT_ID." ";
					else if(CORP_ID) $sql .= 'AND kll.kll_crp_id = '.CORP_ID." ";
					else if(ALLIANCE_ID) $sql .= ' AND kll.kll_all_id = '.ALLIANCE_ID." ";
				}
				else
				{
					if ($contract->getCorps())
					{
						$tmp[] = 'kll.kll_crp_id in ( '.join(',', $contract->getCorps()).' )';
					}
					if ($contract->getAlliances())
					{
						$tmp[] = 'kll.kll_all_id in ( '.join(',', $contract->getAlliances()).' )';
					}
					if (count($tmp))
					{
						$sql .= $sqlwhereop.' (';
						$sql .= join(' or ', $tmp);
						$sql .= ')';
						$sqlwhereop = ' AND ';
					}
					if(PILOT_ID) $sql .= ' AND ind.ind_plt_id = '.PILOT_ID." ";
					else if(CORP_ID) $sql .= ' AND inc.inc_crp_id = '.CORP_ID." ";
					else if(ALLIANCE_ID) $sql .= '  AND ina.ina_all_id = '.ALLIANCE_ID." ";
				}
				if ($contract->getSystems())
				{
					$sql .= $sqlwhereop.' kll.kll_system_id in ( '.join(',', $contract->getSystems()).')';
				}
				$sql .= ') as kb3_shadow';
				$sql .= " /* contract: getTableStats */";
				$result = $qry->execute($sql);
				$row = $qry->getRow($result);

				if ($i == 0)
				{
					$ldata = array('losses' => $row['ships'], 'lossisk' => $row['isk'] / 1000 );
				}
				else
				{
					$kdata = array('kills' => $row['ships'], 'killisk' => $row['isk'] / 1000 );
				}
			}
			if ($kdata['killisk'])
			{
				$efficiency = round($kdata['killisk'] / ($kdata['killisk']+$ldata['lossisk']) *100, 2);
			}
			else
			{
				$efficiency = 0;
			}
			$bar = new BarGraph($efficiency, 100, 75);

			$tbldata[] = array_merge(array('name' => $contract->getName(), 'startdate' => $contract->getStartDate(), 'bar' => $bar->generate(),
				'enddate' => $contract->getEndDate(), 'efficiency' => $efficiency, 'id' => $contract->getID()), $kdata, $ldata);
		}
		$this->contractlist->rewind();
		return $tbldata;
	}

	function generate()
	{
		if ($table = $this->getTableStats())
		{
			global $smarty;

			$smarty->assign('contract_getactive', $this->contractlist->getActive());
			$smarty->assignByRef('contracts', $table);
			$pagesplitter = new PageSplitter($this->contractlist->getCount(), 10);

			return $smarty->fetch(get_tpl('contractlisttable')).$pagesplitter->generate();
		}
	}
}
