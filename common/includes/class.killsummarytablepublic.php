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
class KillSummaryTablePublic extends KillSummaryTable
{

	function KillSummaryTablePublic($klist = null)
	{
		$this->klist = $klist;
		$this->verbose = true;
	}

	// do it faster, baby!
	function getkills()
	{
		$this->entry = array();
		// as there is no way to do this elegant in sql
		// i'll keep it in php
		$sql = "select scl_id, scl_class from kb3_ship_classes
               where scl_class not in ('Drone','Unknown') order by scl_class";

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->entry[$row['scl_class']] = array('id' => $row['scl_id'],
				'kills' => 0, 'kills_isk' => 0);
		}
		$startdate = makeStartDate($this->weekno, $this->yearno, $this->monthno,
				$this->startweekno, $this->startDate);
		$enddate = makeEndDate($this->weekno, $this->yearno, $this->monthno,
				$this->endDate);

		$sql = 'SELECT count(kll.kll_id) AS knb, scl_id, scl_class,';

		$sql .= ' sum(kll_isk_loss) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';

		$sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

		if ($this->inv_crp) {
			$sql .= ' inner join kb3_inv_detail ind on ( ind.ind_crp_id in ( '.join(',',
							$this->inv_crp).' ) and kll.kll_id = ind.ind_kll_id ) ';
		} else if ($this->inv_all) {
			$sql .= ' inner join kb3_inv_detail ind on ( ind.ind_all_id in ( '.join(',',
							$this->inv_all).' ) and kll.kll_id = ind.ind_kll_id ) ';
		}
		$sqlop = " WHERE ";
		if ($this->system) {
			$sql .= $sqlop." kll.kll_system_id in ".join(',', $this->system)." ";
		}
		if ($startdate) {
			$sql .= $sqlop." kll.kll_timestamp >= '".gmdate('Y-m-d H:i', $startdate)."' ";
			$sqlop = " AND ";
		}
		if ($enddate) {
			$sql .= $sqlop." kll.kll_timestamp <= '".gmdate('Y-m-d H:i', $enddate)."' ";
		}
		$sql .= 'GROUP BY shp.shp_class';
		if ($this->inv_crp || $this->inv_all) {
			$sql .= ', kll.kll_id';
		}
		$sql .= ' order by scl.scl_class';

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->entry[$row['scl_class']]['kills'] = $row['knb'];
			$this->entry[$row['scl_class']]['kills_isk'] = $row['kisk'];
			$this->tkcount += $row['knb'];
			$this->tkisk += $row['kisk'];
		}
	}

	function generate()
	{
		if ($this->klist) {
			$entry = array();
			// build array
			$sql = "select scl_id, scl_class
                    from kb3_ship_classes
                   where scl_class not in ( 'Drone', 'Unknown' )
                  order by scl_class";

			$qry = DBFactory::getDBQuery();
			$qry->execute($sql) or die($qry->getErrorMsg());
			while ($row = $qry->getRow()) {
				if (!$row['scl_id']) {
					continue;
				}

				$entry[$row['scl_class']]['id'] = $row['scl_id'];
				$entry[$row['scl_class']]['kills'] = 0;
				$entry[$row['scl_class']]['kills_isk'] = 0;
			}

			while ($kill = $this->klist->getKill()) {
				$classname = $kill->getVictimShipClassName();
				$entry[$classname]['kills']++;
				$entry[$classname]['kills_isk'] += $kill->getISKLoss();
				$this->tkcount++;
				$this->tkisk += $kill->getISKLoss();
			}
		} else {
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
				if ($value[0] == 'scl_id') {
					unset($args[$key]);
					break;
				}
			}
		}
		$qrystring = edkURI::build($args);
		$clearfilter = $qrystring;
		if (strpos($qrystring, '?') === false) {
			$qrystring .= "?";
		} else {
			$qrystring .= "&amp;";
		}

		foreach ($entry as $k => $v) {
			if ($v['id'] == 3) {
				continue;
			}
			$v['break'] = 0;
			if ($_GET['scl_id'] && $_GET['scl_id'] == $v['id']) {
				$v['hl'] = 1;
			} else {
				$v['hl'] = 0;
			}
			$v['qry'] = $qrystring;
			$v['kisk'] = round($v['kills_isk'] / 1000000, 2);
			$v['name'] = $k;

			$summary[] = $v;

			$this->tkcount += $kcount;
			$this->tkisk += $kisk;
			$this->tkpoints += $kpoints;
			$count++;
		}
		global $smarty;
		$smarty->assign('summary', $summary);
		$smarty->assign('count', $num);
		$smarty->assign('verbose', $this->verbose);
		$smarty->assign('filter', $this->filter);

		if (config::get('summarytable_summary')) {
			$smarty->assign('summarysummary', 1);
			$smarty->assign('efficiency', 0);
			$smarty->assign('kiskB', round($this->tkisk / 1000000000, 2));
			$smarty->assign('kiskM', round($this->tkisk / 1000000, 2));
			$smarty->assign('kcount', $this->tkcount);
		}

		if (edkURI::getArg('scl_id')) {
			$smarty->assign('clearfilter', $clearfilter);
		}

		$html .= $smarty->fetch(get_tpl('summarytable'));

		return $html;
	}

}
