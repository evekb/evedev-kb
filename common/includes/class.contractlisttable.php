<?php
/*
 * $Date: 2010-05-30 03:57:50 +1000 (Sun, 30 May 2010) $
 * $Revision: 711 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.contract.php $
 */

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
		$qry = DBFactory::getDBQuery();
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
					if(count(config::get('cfg_pilotid'))) $sql .= ' inner join kb3_inv_detail ind on ( kll.kll_id = ind.ind_kll_id ) ';
					if(count(config::get('cfg_corpid'))) $sql .= ' inner join kb3_inv_crp inc on ( kll.kll_id = inc.inc_kll_id ) ';
					if(count(config::get('cfg_allianceid'))) $sql .=' inner join kb3_inv_all ina on ( kll.kll_id = ina.ina_kll_id ) ';
				}
				if($contract->getStartDate())
				{
					$sql .= " WHERE kll.kll_timestamp >= '".$contract->getStartDate()."' ";
					if ((!$i && $contract->getCorps()) || ($i && count(config::get('cfg_corpid'))))
						$sql .= " AND inc.inc_timestamp >= '".$contract->getStartDate()."' ";
					if ((!$i && $contract->getAlliances()) && ($i && count(config::get('cfg_allianceid'))))
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
					if(count(config::get('cfg_allianceid'))) $tmp[] = 'kll.kll_all_id IN ('.implode(",", config::get('cfg_allianceid')).") ";
					if(count(config::get('cfg_corpid'))) $tmp[] = 'kll.kll_crp_id IN ('.implode(",", config::get('cfg_corpid')).") ";
					if(count(config::get('cfg_pilotid'))) $tmp[] = 'kll.kll_victim_id IN ('.implode(",", config::get('cfg_pilotid')).") ";

					if (count($tmp))
					{
						$sql .= $sqlwhereop.' (';
						$sql .= join(' OR ', $tmp);
						$sql .= ')';
						$sqlwhereop = ' AND ';
					}
					$tmp = array();
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
					$tmp = array();
					if(count(config::get('cfg_allianceid'))) $tmp[] = '  ina.ina_all_id IN ('.implode(",", config::get('cfg_allianceid')).") ";
					if(count(config::get('cfg_corpid'))) $tmp[] = ' inc.inc_crp_id IN ('.implode(",", config::get('cfg_corpid')).") ";
					if(count(config::get('cfg_pilotid'))) $tmp[] = ' ind.ind_plt_id IN ('.implode(",", config::get('cfg_pilotid')).") ";
					if (count($tmp))
					{
						$sql .= $sqlwhereop.' (';
						$sql .= join(' OR ', $tmp);
						$sql .= ')';
						$sqlwhereop = ' AND ';
					}
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
