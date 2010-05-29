<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class KillSummaryTable
{
	function KillSummaryTable($klist = null, $llist = null)
	{
		$this->klist_ = $klist;
		$this->llist_ = $llist;
		$this->verbose_ = false;
		$this->filter_ = true;
		$this->inv_plt_ = array();
		$this->inv_crp_ = array();
		$this->inv_all_ = array();
		$this->html_ = '';
	}
	//! Stub to handle deprecated code.
	function setBreak($break)
	{
	}

	function setVerbose($verbose)
	{
		$this->verbose_ = $verbose;
	}

	function setFilter($filter)
	{
		$this->filter_ = $filter;
	}

	function setWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->weekno_ = 1;
		if($weekno >53) $this->weekno_ = 53;
		else $this->weekno_ = $weekno;
	}

	function setMonth($monthno)
	{
		$monthno = intval($monthno);
		if($monthno < 1) $this->monthno_ = 1;
		if($monthno > 12) $this->monthno_ = 12;
		else $this->monthno_ = $monthno;
	}

	function setYear($yearno)
	{
	// 1970-2038 is the allowable range for the timestamp code used
	// Needs to be revisited in the next 30 years
		$yearno = intval($yearno);
		if($yearno < 1970) $this->yearno_ = 1970;
		if($yearno > 2038) $this->yearno_ = 2038;
		else $this->yearno_ = $yearno;
	}

	function setStartWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->startweekno_ = 1;
		if($weekno >53) $this->startweekno_ = 53;
		else $this->startweekno_ = $weekno;
	}

	function setStartDate($timestamp)
	{
	// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->startDate_ = $timestamp;
	}

	function setEndDate($timestamp)
	{
	// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->endDate_ = $timestamp;
	}

	// Return SQL for date filter using currently set date limits
	function setDateFilter()
	{
		$qstartdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
		$qenddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);
		if($qstartdate) $sql .= " kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' ";
		if($qstartdate && $qenddate) $sql .= " AND ";
		if($qenddate) $sql .= " kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' ";
		return $sql;
	}

	function getTotalKills()
	{
		return $this->tkcount_;
	}

	function getTotalLosses()
	{
		return $this->tlcount_;
	}

	function getTotalKillPoints()
	{
		return $this->tkpoints_;
	}

	function getTotalLossPoints()
	{
		return $this->tlpoints_;
	}

	function getTotalKillISK()
	{
		return $this->tkisk_;
	}

	function getTotalLossISK()
	{
		return $this->tlisk_;
	}

	function setView($string)
	{
		$this->view_ = $string;
	}

	function addInvolvedPilot($pilot)
	{
		if(is_numeric($pilot)) $this->inv_plt_[] = $pilot;
		else $this->inv_plt_[] = $pilot->getID();
	}

	function addInvolvedCorp($corp)
	{
		if(is_numeric($corp)) $this->inv_crp_[] = $corp;
		else $this->inv_crp_[] = $corp->getID();
	}

	function addInvolvedAlliance($alliance)
	{
		if(is_numeric($alliance)) $this->inv_all_[] = $alliance;
		else $this->inv_all_[] = $alliance->getID();
	}

	function setSystem($system)
	{
		if(is_numeric($system)) $this->system_[] = $system;
		else $this->system_[] = $system->getID();
	}

	// do it faster, baby!
	function getkills()
	{
		if ($this->mixedinvolved_)
		{
			echo 'mode not supported<br>';
			exit;
		}
		if($this->setDateFilter() == "" && empty($this->system_))
		{
			if( count($this->inv_all_) == 1 && !$this->inv_crp_ && !$this->inv_plt_)
			{
				$allsum = new allianceSummary($this->inv_all_[0]);
				$summary = $allsum->getSummary();
				foreach($summary as $key => $row)
				{
					$this->entry_[$row['class_name']] = array('id' => $key,
						'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
						'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

					$this->tkcount_ += $row['killcount'];
					$this->tkisk_ += $row['killisk'];
					$this->tlcount_ += $row['losscount'];
					$this->tlisk_ += $row['lossisk'];
				}
				return;
			}
			elseif( count($this->inv_crp_) == 1 && !$this->inv_all_ && !$this->inv_plt_)
			{
				$crpsum = new corpSummary($this->inv_crp_[0]);
				$summary = $crpsum->getSummary();
				foreach($summary as $key => $row)
				{
					$this->entry_[$row['class_name']] = array('id' => $key,
						'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
						'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

					$this->tkcount_ += $row['killcount'];
					$this->tkisk_ += $row['killisk'];
					$this->tlcount_ += $row['losscount'];
					$this->tlisk_ += $row['lossisk'];
				}
				return;
			}
			elseif( count($this->inv_plt_) == 1 && !$this->inv_all_ && !$this->inv_crp_)
			{
				$pltsum = new pilotSummary($this->inv_plt_[0]);
				$summary = $pltsum->getSummary();
				foreach($summary as $key => $row)
				{
					$this->entry_[$row['class_name']] = array('id' => $key,
						'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
						'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

					$this->tkcount_ += $row['killcount'];
					$this->tkisk_ += $row['killisk'];
					$this->tlcount_ += $row['losscount'];
					$this->tlisk_ += $row['lossisk'];
				}
				return;
			}
		}

		$this->entry_ = array();
		// as there is no way to do this elegantly in sql
		// i'll keep it in php
		$sql = "select scl_id, scl_class from kb3_ship_classes
               where scl_class not in ('Drone','Unknown') order by scl_class";
		$startdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
		$enddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);

		$qry = DBFactory::getDBQuery();;
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry_[$row['scl_class']] = array('id' => $row['scl_id'],
				'kills' => 0, 'kills_isk' => 0,
				'losses' => 0, 'losses_isk' => 0);
		}

		$sql = 'SELECT count(kll.kll_id) AS knb, scl_id, scl_class,';
		$sql .= ' sum(kll_isk_loss) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
		$sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';


		$sqlop = " WHERE ";
		if ($this->inv_all_)
		{
			$sql .= " INNER JOIN kb3_inv_all inv ON (inv.ina_kll_id = kll.kll_id)
				".$sqlop."inv.ina_all_id in (".implode(',', $this->inv_all_)." ) ";
			if($startdate) $sql .=" AND inv.ina_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
			if($enddate) $sql .=" AND inv.ina_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			$sqlop = " AND ";
		}
		elseif ($this->inv_crp_)
		{
			$sql .= " INNER JOIN kb3_inv_crp inv ON (inv.inc_kll_id = kll.kll_id)
				".$sqlop."inv.inc_crp_id in (".implode(',', $this->inv_crp_)." ) ";
			if($startdate) $sql .=" AND inv.inc_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
			if($enddate) $sql .=" AND inv.inc_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			$sqlop = " AND ";
		}
		elseif ($this->inv_plt_)
		{
			$sql .= " INNER JOIN kb3_inv_detail inv ON (inv.ind_kll_id = kll.kll_id)
					".$sqlop."inv.ind_plt_id in (".implode(',', $this->inv_plt_)." ) ";
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
			if($this->system_)
			{
				$sql .= $sqlop." kll.kll_system_id = ".join(',', $this->system_)." ";
				$sqlop = " AND ";
			}

		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = DBFactory::getDBQuery();;
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry_[$row['scl_class']]['kills'] = $row['knb'];
			$this->entry_[$row['scl_class']]['kills_isk'] = $row['kisk'];
			$this->tkcount_ += $row['knb'];
			$this->tkisk_ += $row['kisk'];
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

		if ($this->inv_all_)
		{
			$sql .= $sqlop.' kll.kll_all_id IN ( '.implode(',', $this->inv_all_).' ) ';
			$sql .= 'AND EXISTS (SELECT 1 FROM kb3_inv_all ina WHERE kll.kll_id = ina_kll_id AND ina.ina_all_id NOT IN ( '.implode(',', $this->inv_all_).' ) limit 0,1) ';
			$sqlop = " AND ";
		}
		elseif ($this->inv_crp_)
		{
			$sql .= $sqlop.' kll.kll_crp_id IN ( '.implode(',', $this->inv_crp_).' ) ';
			$sql .= 'AND EXISTS (SELECT 1 FROM kb3_inv_crp inc WHERE kll.kll_id = inc_kll_id AND inc.inc_crp_id NOT IN ( '.implode(',', $this->inv_crp_).' ) limit 0,1) ';
			$sqlop = " AND ";
		}
		elseif ($this->inv_plt_)
		{
			$sql .= $sqlop.' kll.kll_victim_id IN ( '.implode(',', $this->inv_plt_).' ) ';
			$sql .= ' AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind_kll_id AND ind.ind_plt_id NOT IN ( '.implode(',', $this->inv_plt_).' ) limit 0,1) ';
			$sqlop = " AND ";
		}
		if($this->system_)
		{
			$sql .= $sqlop." kll.kll_system_id = ".join(',', $this->system_)." ";
		}
		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = DBFactory::getDBQuery();;
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry_[$row['scl_class']]['losses'] = $row['lnb'];
			$this->entry_[$row['scl_class']]['losses_isk'] =  $row['lisk'];

			$this->tlcount_ += $row['lnb'];
			$this->tlisk_ += $row['lisk'];
		}
	}

	function generate()
	{
		if($this->html_ != '') return $this->html_;
		if ($this->klist_)
		{
			$entry = array();
			// build array
			$sql = "select scl_id, scl_class
                    from kb3_ship_classes
                   where scl_class not in ( 'Drone', 'Unknown' )
                  order by scl_class";

			$qry = DBFactory::getDBQuery();;
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
			while ($kill = $this->klist_->getKill())
			{
				$classname = $kill->getVictimShipClassName();
				$entry[$classname]['kills']++;
				$entry[$classname]['kills_isk'] += $kill->getISKLoss();
				$this->tkcount_++;
				$this->tkisk_ += $kill->getISKLoss();
			}
			// losses
			while ($kill = $this->llist_->getKill())
			{
				$classname = $kill->getVictimShipClassName();
				$entry[$classname]['losses']++;
				$entry[$classname]['losses_isk'] += $kill->getISKLoss();
				$this->tlcount_++;
				$this->tlisk_ += $kill->getISKLoss();
			}
		}
		else
		{
			$this->getkills();
			$entry = &$this->entry_;
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
			if($count && $this->break_ && $count%$this->break_ == 0) $v['break'] = 1;
			else $v['break'] = 0;
			if($_GET['scl_id'] && $_GET['scl_id'] == $v['id']) $v['hl'] = 1;
			else $v['hl'] = 0;
			$qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", $_SERVER['QUERY_STRING']);
			$qrystring = preg_replace("/&page=([0-9]?[0-9])/", "", $qrystring);
			$qrystring = preg_replace("/&/", "&amp;", $qrystring);
			if ($this->view_)
			{
				$qrystring .= '&amp;view='.$this->view_;
			}
			$v['qry'] = $qrystring;
			$v['kisk'] = round($v['kills_isk']/1000000, 2);
			$v['lisk'] = round($v['losses_isk']/1000000, 2);
			$v['name'] = $k;

			$summary[] = $v;

			$this->tkcount_ += $kcount;
			$this->tkisk_ += $kisk;
			$this->tkpoints_ += $kpoints;
			$this->tlcount_ += $lcount;
			$this->tlisk_ += $lisk;
			$this->tlpoints_ += $lpoints;
			$count++;

		}
		global $smarty;
		$smarty->assign('summary', $summary);
		$smarty->assign('count', $num);
		$smarty->assign('verbose', $this->verbose_);
		$smarty->assign('filter', $this->filter_);
		$smarty->assign('losses', 1);

		if (config::get('summarytable_summary'))
		{
			$smarty->assign('summarysummary', 1);
			if (config::get('summarytable_efficiency'))
				$smarty->assign('efficiency', round($this->tkisk_ / (($this->tkisk_ + $this->tlisk_) == 0 ? 1 : ($this->tkisk_ + $this->tlisk_)) * 100, 2));
			else $smarty->assign('efficiency', 0);
			$smarty->assign('kiskB', round($this->tkisk_/1000000000, 2));
			$smarty->assign('liskB', round($this->tlisk_/1000000000, 2));
			$smarty->assign('kiskM', round($this->tkisk_/1000000, 2));
			$smarty->assign('liskM', round($this->tlisk_/1000000, 2));
			$smarty->assign('kcount', $this->tkcount_);
			$smarty->assign('lcount', $this->tlcount_);
		}

		if ($_GET['scl_id'] != "")
		{
			$qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", '?'.$_SERVER['QUERY_STRING']);
			$qrystring = preg_replace("/&/", "&amp;", $qrystring);
			$smarty->assign('clearfilter',$qrystring);
		}

		$this->html_ = $smarty->fetch(get_tpl('summarytable'));

		return $this->html_;
	}
}
?>