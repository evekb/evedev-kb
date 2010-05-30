<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
*/


class KillSummaryTable
{
	protected $klist = null;
	protected $llist = null;
	protected $verbose = false;
	protected $filter = true;
	protected $inv_plt = array();
	protected $inv_crp = array();
	protected $inv_all = array();
	protected $html = '';
	protected $monthno = 0;
	protected $weekno = 0;
	protected $yearno = 0;
	protected $startweekno = 0;
	protected $startDate = 0;
	protected $endDate = 0;
	protected $tkcount = 0;
	protected $tlcount = 0;
	protected $tkisk = 0;
	protected $tlisk = 0;
	protected $system = 0;
	protected $view = "";

	function KillSummaryTable($klist = null, $llist = null)
	{
		$this->klist = $klist;
		$this->llist = $llist;
	}
	//! Stub to handle deprecated code.
	function setBreak($break)
	{
	}

	function setVerbose($verbose)
	{
		$this->verbose = $verbose;
	}

	function setFilter($filter)
	{
		$this->filter = $filter;
	}

	function setWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->weekno = 1;
		if($weekno >53) $this->weekno = 53;
		else $this->weekno = $weekno;
	}

	function setMonth($monthno)
	{
		$monthno = intval($monthno);
		if($monthno < 1) $this->monthno = 1;
		if($monthno > 12) $this->monthno = 12;
		else $this->monthno = $monthno;
	}

	function setYear($yearno)
	{
		// 1970-2038 is the allowable range for the timestamp code used
		// Needs to be revisited in the next 30 years
		$yearno = intval($yearno);
		if($yearno < 1970) $this->yearno = 1970;
		if($yearno > 2038) $this->yearno = 2038;
		else $this->yearno = $yearno;
	}

	function setStartWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->startweekno = 1;
		if($weekno >53) $this->startweekno = 53;
		else $this->startweekno = $weekno;
	}

	function setStartDate($timestamp)
	{
		// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->startDate = $timestamp;
	}

	function setEndDate($timestamp)
	{
		// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->endDate = $timestamp;
	}

	// Return SQL for date filter using currently set date limits
	function setDateFilter()
	{
		$sql = '';
		$qstartdate = makeStartDate($this->weekno, $this->yearno, $this->monthno, $this->startweekno, $this->startDate);
		$qenddate = makeEndDate($this->weekno, $this->yearno, $this->monthno, $this->endDate);
		if($qstartdate) $sql .= " kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' ";
		if($qstartdate && $qenddate) $sql .= " AND ";
		if($qenddate) $sql .= " kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' ";
		return $sql;
	}

	function getTotalKills()
	{
		return $this->tkcount;
	}

	function getTotalLosses()
	{
		return $this->tlcount;
	}

	function getTotalKillPoints()
	{
		return $this->tkpoints;
	}

	function getTotalLossPoints()
	{
		return $this->tlpoints;
	}

	function getTotalKillISK()
	{
		return $this->tkisk;
	}

	function getTotalLossISK()
	{
		return $this->tlisk;
	}

	function setView($string)
	{
		$this->view = $string;
	}

	function addInvolvedPilot($pilot)
	{
		if(is_numeric($pilot)) $this->inv_plt[] = $pilot;
		else $this->inv_plt[] = $pilot->getID();
	}

	function addInvolvedCorp($corp)
	{
		if(is_numeric($corp)) $this->inv_crp[] = $corp;
		else $this->inv_crp[] = $corp->getID();
	}

	function addInvolvedAlliance($alliance)
	{
		if(is_numeric($alliance)) $this->inv_all[] = $alliance;
		else $this->inv_all[] = $alliance->getID();
	}

	function setSystem($system)
	{
		if(is_numeric($system)) $this->system[] = $system;
		else $this->system[] = $system->getID();
	}

	// do it faster, baby!
	function getkills()
	{
		if($this->setDateFilter() == "" && empty($this->system))
		{
			if( count($this->inv_all) == 1 && !$this->inv_crp && !$this->inv_plt)
			{
				$allsum = new allianceSummary($this->inv_all[0]);
				$summary = $allsum->getSummary();
				foreach($summary as $key => $row)
				{
					$this->entry[$row['class_name']] = array('id' => $key,
						'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
						'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

					$this->tkcount += $row['killcount'];
					$this->tkisk += $row['killisk'];
					$this->tlcount += $row['losscount'];
					$this->tlisk += $row['lossisk'];
				}
				return;
			}
			elseif( count($this->inv_crp) == 1 && !$this->inv_all && !$this->inv_plt)
			{
				$crpsum = new corpSummary($this->inv_crp[0]);
				$summary = $crpsum->getSummary();
				foreach($summary as $key => $row)
				{
					$this->entry[$row['class_name']] = array('id' => $key,
						'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
						'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

					$this->tkcount += $row['killcount'];
					$this->tkisk += $row['killisk'];
					$this->tlcount += $row['losscount'];
					$this->tlisk += $row['lossisk'];
				}
				return;
			}
			elseif( count($this->inv_plt) == 1 && !$this->inv_all && !$this->inv_crp)
			{
				$pltsum = new pilotSummary($this->inv_plt[0]);
				$summary = $pltsum->getSummary();
				foreach($summary as $key => $row)
				{
					$this->entry[$row['class_name']] = array('id' => $key,
						'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
						'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

					$this->tkcount += $row['killcount'];
					$this->tkisk += $row['killisk'];
					$this->tlcount += $row['losscount'];
					$this->tlisk += $row['lossisk'];
				}
				return;
			}
		}

		$this->entry = array();
		// as there is no way to do this elegantly in sql
		// i'll keep it in php
		$sql = "select scl_id, scl_class from kb3_ship_classes
               where scl_class not in ('Drone','Unknown') order by scl_class";
		$startdate = makeStartDate($this->weekno, $this->yearno, $this->monthno, $this->startweekno, $this->startDate);
		$enddate = makeEndDate($this->weekno, $this->yearno, $this->monthno, $this->endDate);

		$qry = DBFactory::getDBQuery();
		;
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry[$row['scl_class']] = array('id' => $row['scl_id'],
				'kills' => 0, 'kills_isk' => 0,
				'losses' => 0, 'losses_isk' => 0);
		}

		$sql = 'SELECT count(kll.kll_id) AS knb, scl_id, scl_class,';
		$sql .= ' sum(kll_isk_loss) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
		$sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';


		$sqlop = " WHERE ";
		if ($this->inv_all)
		{
			$sql .= " INNER JOIN kb3_inv_all inv ON (inv.ina_kll_id = kll.kll_id)
				".$sqlop."inv.ina_all_id in (".implode(',', $this->inv_all)." ) ";
			if($startdate) $sql .=" AND inv.ina_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
			if($enddate) $sql .=" AND inv.ina_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			$sqlop = " AND ";
		}
		elseif ($this->inv_crp)
		{
			$sql .= " INNER JOIN kb3_inv_crp inv ON (inv.inc_kll_id = kll.kll_id)
				".$sqlop."inv.inc_crp_id in (".implode(',', $this->inv_crp)." ) ";
			if($startdate) $sql .=" AND inv.inc_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
			if($enddate) $sql .=" AND inv.inc_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			$sqlop = " AND ";
		}
		elseif ($this->inv_plt)
		{
			$sql .= " INNER JOIN kb3_inv_detail inv ON (inv.ind_kll_id = kll.kll_id)
					".$sqlop."inv.ind_plt_id in (".implode(',', $this->inv_plt)." ) ";
			if($startdate) $sql .=" AND inv.ind_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
			if($enddate) $sql .=" AND inv.ind_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			$sqlop = " AND ";
		}
		else
		{
			if($startdate)
			{
				$sql .= $sqlop." kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
				$sqlop = " AND ";
			}
			if($enddate)
			{
				$sql .= $sqlop." kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
				$sqlop = " AND ";
			}
		}
		if($this->system)
		{
			$sql .= $sqlop." kll.kll_system_id = ".join(',', $this->system)." ";
			$sqlop = " AND ";
		}

		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = DBFactory::getDBQuery();
		;
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry[$row['scl_class']]['kills'] = $row['knb'];
			$this->entry[$row['scl_class']]['kills_isk'] = $row['kisk'];
			$this->tkcount += $row['knb'];
			$this->tkisk += $row['kisk'];
		}


		$sql = 'SELECT count( kll_id) AS lnb, scl_id, scl_class,';
		$sql .= ' sum(kll_isk_loss) AS lisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
		$sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

		$sqlop = ' WHERE ';
		if($this->setDateFilter())
		{
			$sql.= $sqlop.$this->setDateFilter();
			$sqlop = " AND ";
		}

		if ($this->inv_all)
		{
			$sql .= $sqlop.' kll.kll_all_id IN ( '.implode(',', $this->inv_all).' ) ';
			$sql .= 'AND EXISTS (SELECT 1 FROM kb3_inv_all ina WHERE kll.kll_id = ina_kll_id AND ina.ina_all_id NOT IN ( '.implode(',', $this->inv_all).' ) limit 0,1) ';
			$sqlop = " AND ";
		}
		elseif ($this->inv_crp)
		{
			$sql .= $sqlop.' kll.kll_crp_id IN ( '.implode(',', $this->inv_crp).' ) ';
			$sql .= 'AND EXISTS (SELECT 1 FROM kb3_inv_crp inc WHERE kll.kll_id = inc_kll_id AND inc.inc_crp_id NOT IN ( '.implode(',', $this->inv_crp).' ) limit 0,1) ';
			$sqlop = " AND ";
		}
		elseif ($this->inv_plt)
		{
			$sql .= $sqlop.' kll.kll_victim_id IN ( '.implode(',', $this->inv_plt).' ) ';
			$sql .= ' AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind_kll_id AND ind.ind_plt_id NOT IN ( '.implode(',', $this->inv_plt).' ) limit 0,1) ';
			$sqlop = " AND ";
		}
		if($this->system)
		{
			$sql .= $sqlop." kll.kll_system_id = ".join(',', $this->system)." ";
		}
		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = DBFactory::getDBQuery();
		;
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry[$row['scl_class']]['losses'] = $row['lnb'];
			$this->entry[$row['scl_class']]['losses_isk'] =  $row['lisk'];

			$this->tlcount += $row['lnb'];
			$this->tlisk += $row['lisk'];
		}
	}

	function generate()
	{
		if($this->html != '') return $this->html;
		if ($this->klist)
		{
			$entry = array();
			// build array
			$sql = "select scl_id, scl_class
                    from kb3_ship_classes
                   where scl_class not in ( 'Drone', 'Unknown' )
                  order by scl_class";

			$qry = DBFactory::getDBQuery();
			$qry->execute($sql) or die($qry->getErrorMsg());
			while ($row = $qry->getRow())
			{
				if (!$row['scl_id'])
					continue;

				$shipclass = new ShipClass($row['scl_id']);
				$shipclass->setName($row['scl_class']);

				$entry[$shipclass->getName()]['id'] = $row['scl_id'];
				$entry[$shipclass->getName()]['kills'] = 0;
				$entry[$shipclass->getName()]['kills_isk'] = 0;
				$entry[$shipclass->getName()]['losses'] = 0;
				$entry[$shipclass->getName()]['losses_isk'] = 0;
			}
			// kills
			while ($kill = $this->klist->getKill())
			{
				$classname = $kill->getVictimShipClassName();
				$entry[$classname]['kills']++;
				$entry[$classname]['kills_isk'] += $kill->getISKLoss();
				$this->tkcount++;
				$this->tkisk += $kill->getISKLoss();
			}
			// losses
			while ($kill = $this->llist->getKill())
			{
				$classname = $kill->getVictimShipClassName();
				$entry[$classname]['losses']++;
				$entry[$classname]['losses_isk'] += $kill->getISKLoss();
				$this->tlcount++;
				$this->tlisk += $kill->getISKLoss();
			}
		}
		else
		{
			$this->getkills();
			$entry = &$this->entry;
		}

		$odd = false;
		$prevdate = "";
		// Don't count noobships.
		$num = count($entry) - 1;
		$summary = array();
		$count = 0;
		foreach ($entry as $k => $v)
		{
			if($v['id'] == 3) continue;
			$v['break'] = 0;
			if(isset($_GET['scl_id']) && $_GET['scl_id'] == $v['id']) $v['hl'] = 1;
			else $v['hl'] = 0;
			$qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", $_SERVER['QUERY_STRING']);
			$qrystring = preg_replace("/&page=([0-9]?[0-9])/", "", $qrystring);
			$qrystring = preg_replace("/&/", "&amp;", $qrystring);
			if ($this->view)
			{
				$qrystring .= '&amp;view='.$this->view;
			}
			$v['qry'] = $qrystring;
			$v['kisk'] = round($v['kills_isk']/1000000, 2);
			$v['lisk'] = round($v['losses_isk']/1000000, 2);
			$v['name'] = $k;

			$summary[] = $v;

			$count++;

		}
		global $smarty;
		$smarty->assign('summary', $summary);
		$smarty->assign('count', $num);
		$smarty->assign('verbose', $this->verbose);
		$smarty->assign('filter', $this->filter);
		$smarty->assign('losses', 1);

		if (config::get('summarytable_summary'))
		{
			$smarty->assign('summarysummary', 1);
			if (config::get('summarytable_efficiency'))
				$smarty->assign('efficiency', round($this->tkisk / (($this->tkisk + $this->tlisk) == 0 ? 1 : ($this->tkisk + $this->tlisk)) * 100, 2));
			else $smarty->assign('efficiency', 0);
			$smarty->assign('kiskB', round($this->tkisk/1000000000, 2));
			$smarty->assign('liskB', round($this->tlisk/1000000000, 2));
			$smarty->assign('kiskM', round($this->tkisk/1000000, 2));
			$smarty->assign('liskM', round($this->tlisk/1000000, 2));
			$smarty->assign('kcount', $this->tkcount);
			$smarty->assign('lcount', $this->tlcount);
		}

		if (!empty($_GET['scl_id']))
		{
			$qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", '?'.$_SERVER['QUERY_STRING']);
			$qrystring = preg_replace("/&/", "&amp;", $qrystring);
			$smarty->assign('clearfilter',$qrystring);
		}

		$this->html = $smarty->fetch(get_tpl('summarytable'));

		return $this->html;
	}
}
