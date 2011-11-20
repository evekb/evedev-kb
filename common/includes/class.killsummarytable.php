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
	protected $view = "";
	protected $system = array();

	/**
	 * @param KillList $klist
	 * @param KillList $llist
	 */
	function KillSummaryTable($klist = null, $llist = null)
	{
		$this->klist = $klist;
		$this->llist = $llist;
	}

	/**
	 * Set the level of detail to show in the summary
	 * @param boolean $verbose Set true to show more details.
	 */
	function setVerbose($verbose)
	{
		$this->verbose = $verbose;
	}

	function setFilter($filter)
	{
		$this->filter = $filter;
	}

	/**
	 * @param integer $weekno The week of the year to show.
	 */
	function setWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->weekno = 1;
		if($weekno >53) $this->weekno = 53;
		else $this->weekno = $weekno;
	}

	/**
	 * @param integer $monthno The month of the year to show.
	 */
	function setMonth($monthno)
	{
		$monthno = intval($monthno);
		if($monthno < 1) $this->monthno = 1;
		if($monthno > 12) $this->monthno = 12;
		else $this->monthno = $monthno;
	}

	/**
	 * @param integer $yearno The year to show.
	 */
	function setYear($yearno)
	{
		// 1970-2038 is the allowable range for the timestamp code used
		// Needs to be revisited in the next 30 years
		$yearno = intval($yearno);
		if($yearno < 1970) $this->yearno = 1970;
		if($yearno > 2038) $this->yearno = 2038;
		else $this->yearno = $yearno;
	}

	/**
	 * Set a starting week. Show all kills since then.
	 * @param integer $weekno The first week to show.
	 */
	function setStartWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->startweekno = 1;
		if($weekno >53) $this->startweekno = 53;
		else $this->startweekno = $weekno;
	}

	/**
	 * Set a starting date. Show all kills since then.
	 * @param string $timestamp The earliest date to show.
	 */
	function setStartDate($timestamp)
	{
		// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->startDate = $timestamp;
	}

	/**
	 * Set an ending date. Show all kills before then.
	 * @param string $timestamp The firlatest date to show.
	 */
	function setEndDate($timestamp)
	{
		// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->endDate = $timestamp;
	}

	/**
	 * Return SQL for date filter using currently set date limits
	 * @return string SQL date filter.
	 */
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

	/**
	 * @return integer
	 */
	function getTotalKills()
	{
		return $this->tkcount;
	}

	/**
	 * @return integer
	 */
	function getTotalRealKills()
	{
		return $this->trkcount;
	}

	/**
	 * @return integer
	 */
	function getTotalLosses()
	{
		return $this->tlcount;
	}

	/**
	 * @return integer
	 */
	function getTotalKillPoints()
	{
		return $this->tkpoints;
	}

	/**
	 * @return integer
	 */
	function getTotalLossPoints()
	{
		return $this->tlpoints;
	}

	/**
	 * @return float
	 */
	function getTotalKillISK()
	{
		return $this->tkisk;
	}

	/**
	 * @return float
	 */
	function getTotalLossISK()
	{
		return $this->tlisk;
	}

	function setView($string)
	{
		$this->view = $string;
	}

	/**
	 * Add a Pilot ID as an involved party.
	 * @param integer $pilot
	 */
	function addInvolvedPilot($pilot)
	{
		involved::add($this->inv_plt, $pilot);
	}

	/**
	 * Add a Corporation ID as an involved party.
	 * @param integer $corp
	 */
	function addInvolvedCorp($corp)
	{
		involved::add($this->inv_crp, $corp);
	}

	/**
	 * Add an Alliance ID as an involved party.
	 * @param integer $alliance
	 */
	function addInvolvedAlliance($alliance)
	{
		involved::add($this->inv_all, $alliance);
	}

	/**
	 * Add a SolarSystem or SolarSystem ID as an involved party.
	 * @param integer|SolarSystem $system
	 */
	function setSystem($system)
	{
		if(is_numeric($system)) $this->system[] = $system;
		else $this->system[] = $system->getID();
	}

	/**
	 * Fetch all kills.
	 */
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
					if(!in_array($key, array(2,3,11) )) $this->trkcount += $row['killcount'];
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
					if(!in_array($key, array(2,3,11) )) $this->trkcount += $row['killcount'];
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
					
					if(!in_array($key, array(-1,2,3,11) )) $this->trkcount += $row['killcount'];
				}
				$qry = DBFactory::getDBQuery();

				$qry->execute("SELECT plt_lpoints, plt_kpoints FROM kb3_pilots WHERE plt_id=".$this->inv_plt[0]);
				if($qry->recordCount())
				{
					$row = $qry->getRow();
					$this->tlpoints = $row['plt_lpoints'];
					$this->tkpoints = $row['plt_kpoints'];
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

		$invcount = count($this->inv_all) + count($this->inv_crp) + count($this->inv_plt);

		if($invcount == 0)
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
		else
		{
			$sql .= "INNER JOIN (";
			$involved = array();
			if ($this->inv_all)
			{
				$invsql = "SELECT ina_kll_id as kll_id FROM kb3_inv_all
					WHERE ina_all_id in (".implode(',', $this->inv_all).") ";
				if($startdate) $invsql .=" AND ina_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
				if($enddate) $invsql .=" AND ina_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
				$involved[] = $invsql;
			}
			if ($this->inv_crp)
			{
				$invsql = "SELECT inc_kll_id as kll_id FROM kb3_inv_crp
					WHERE inc_crp_id in (".implode(',', $this->inv_crp).") ";
				if($startdate) $invsql .=" AND inc_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
				if($enddate) $invsql .=" AND inc_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
				$involved[] = $invsql;
			}
			if ($this->inv_plt)
			{
				$invsql = "SELECT ind_kll_id as kll_id FROM kb3_inv_detail
					WHERE ind_plt_id in (".implode(',', $this->inv_plt).") ";
				if($startdate) $invsql .=" AND ind_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
				if($enddate) $invsql .=" AND ind_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
				$involved[] = $invsql;
			}
			$invtypecount = 0;
			if($this->inv_all) $invtypecount++;
			if($this->inv_crp) $invtypecount++;
			if($this->inv_plt) $invtypecount++;

			$sql .= "(".implode(") UNION (", $involved);
			if($invtypecount == 1) $sql .= " GROUP BY kll_id";
			$sql .= ") ) inv ON inv.kll_id = kll.kll_id ";
		}
		if($this->system)
		{
			$sql .= $sqlop." kll.kll_system_id = ".join(',', $this->system)." ";
			$sqlop = " AND ";
		}

		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry[$row['scl_class']]['kills'] = $row['knb'];
			$this->entry[$row['scl_class']]['kills_isk'] = $row['kisk'];
			$this->tkcount += $row['knb'];
			$this->tkisk += $row['kisk'];
		}

		// LEFT JOIN to kb3_inv_all or kb3_inv_crp if only one type of entity
		// otherwise LEFT JOIN to kb3_inv_detail
		$sql = 'SELECT count( kll_id) AS lnb, scl_id, scl_class,';
		$sql .= ' sum(kll_isk_loss) AS lisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
		$sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

		if ($this->inv_all)
		{
			if(!($this->inv_crp || $this->inv_plt))
			{
				$sql .= ' LEFT JOIN kb3_inv_all ina ON (kll.kll_id = ina.ina_kll_id AND kll.kll_all_id = ina.ina_all_id)';
			}
			else
			{
				$sql .= ' LEFT JOIN kb3_inv_detail ind ON (kll.kll_id = ind.ind_kll_id AND (kll.kll_all_id = ind.ind_all_id ';
				if($this->inv_crp)
				{
					$sql .= ' OR kll.kll_crp_id = ind.ind_crp_id';
				}
				if($this->inv_plt)
				{
					$sql .= ' OR kll.kll_victim_id = ind.ind_plt_id';
				}
				$sql .= ') )';
			}
		}
		elseif ($this->inv_crp)
		{
			if(!$this->inv_plt)
			{
				$sql .= ' LEFT JOIN kb3_inv_crp inc ON (kll.kll_id = inc.inc_kll_id AND kll.kll_crp_id = inc.inc_crp_id)';
			}
			else
			{
				$sql .= ' LEFT JOIN kb3_inv_detail ind ON (kll.kll_id = ind.ind_kll_id AND (kll.kll_crp_id = ind.ind_crp_id';
				$sql .= ' OR kll.kll_victim_id = ind.ind_plt_id))';
			}
		}

		$sqlop = ' WHERE ';
		if($this->setDateFilter())
		{
			$sql.= $sqlop.$this->setDateFilter();
			$sqlop = " AND ";
		}

		if($invcount)
		{
			if ($this->inv_all && !($this->inv_crp || $this->inv_plt))
			{
				$sql .= $sqlop.' ina.ina_kll_id IS NULL ';
				$sqlop = " AND ";
			}
			else if ($this->inv_crp && !($this->inv_plt || $this->inv_all))
			{
				$sql .= $sqlop.' inc.inc_kll_id IS NULL ';
				$sqlop = " AND ";
			}
			else if(!($this->inv_plt && !($this->inv_crp || $this->inv_all)))
			{
				$sql .= $sqlop.' ind.ind_kll_id IS NULL ';
				$sqlop = " AND ";
			}

			$invP = array();
			if ($this->inv_all)
			{
				$invP[] = 'kll.kll_all_id IN ( '.implode(',', $this->inv_all).' ) ';
			}
			if ($this->inv_crp)
			{
				$invP[] = 'kll.kll_crp_id IN ( '.implode(',', $this->inv_crp).' ) ';
			}
			if ($this->inv_plt)
			{
				$invP[] = 'kll.kll_victim_id IN ( '.implode(',', $this->inv_plt).' ) ';
			}
			if($invP)
			{
				$sql .= $sqlop." (".implode(' OR ', $invP).") ";
				$sqlop = " AND ";
			}
		}
		if($this->system)
		{
			$sql .= $sqlop." kll.kll_system_id = ".join(',', $this->system)." ";
		}
		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry[$row['scl_class']]['losses'] = $row['lnb'];
			$this->entry[$row['scl_class']]['losses_isk'] =  $row['lisk'];

			$this->tlcount += $row['lnb'];
			$this->tlisk += $row['lisk'];
		}
	}

	/**
	 * Generate the HTML for this summary table.
	 * @return string Valid HTML representing this summary table.
	 */
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
				if (!$row['scl_id']) {
					continue;
				}
				$entry[$row['scl_class']]['id'] = $row['scl_id'];
				$entry[$row['scl_class']]['kills'] = 0;
				$entry[$row['scl_class']]['kills_isk'] = 0;
				$entry[$row['scl_class']]['losses'] = 0;
				$entry[$row['scl_class']]['losses_isk'] = 0;
			}
			$sql = "SELECT shp_id, scl_class FROM kb3_ships ".
					"INNER JOIN kb3_ship_classes ON scl_id = shp_class";
			$qry->execute($sql);
			$shipscl = array();
			while($row = $qry->getRow()) {
				$shipscl[$row['shp_id']] = $row['scl_class'];
			}
			// kills
			while ($kill = $this->klist->getKill())
			{
				$classname = $shipscl[$kill->getVictimShipID()];
				$entry[$classname]['kills']++;
				$entry[$classname]['kills_isk'] += $kill->getISKLoss();
				$this->tkcount++;
				$this->tkisk += $kill->getISKLoss();
			}
			// losses
			while ($kill = $this->llist->getKill())
			{
				$classname = $shipscl[$kill->getVictimShipID()];
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

		// Don't count noobships.
		$num = count($entry) - 1;
		$summary = array();
		$count = 0;

		$args = edkURI::parseURI();
		if (edkURI::getArg('scl_id')) {
			foreach ($args as $key => $value) {
				if($value[0] == 'scl_id') {
					unset($args[$key]);
					break;
				}
			}
		}
		$qrystring = edkURI::build($args);
		$clearfilter = $qrystring;
		if(strpos($qrystring, '?') === false) {
			$qrystring .= "?";
		} else {
			$qrystring .= "&amp;";
		}

		foreach ($entry as $k => $v) {
			if($v['id'] == 3) continue;
			$v['break'] = 0;
			if(edkURI::getArg('scl_id') == $v['id']) {
				$v['hl'] = 1;
			} else {
				$v['hl'] = 0;
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

		if (edkURI::getArg('scl_id')){
			$smarty->assign('clearfilter',$clearfilter);
		}

		$this->html = $smarty->fetch(get_tpl('summarytable'));

		return $this->html;
	}
}
