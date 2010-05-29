<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

require_once("class.killsummarytable.php");

class KillSummaryTablePublic extends KillSummaryTable
{
    function KillSummaryTablePublic($klist = null)
    {
        $this->klist_ = $klist;
        $this->verbose_ = true;
        $this->filter_ = true;
        $this->inv_crp_ = array();
        $this->inv_all_ = array();
    }

    // do it faster, baby!
    function getkills()
    {
        if ($this->mixedinvolved_)
        {
            echo 'mode not supported<br>';
            exit;
        }

        $this->entry_ = array();
        // as there is no way to do this elegant in sql
        // i'll keep it in php
        $sql = "select scl_id, scl_class from kb3_ship_classes
               where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = DBFactory::getDBQuery();;
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $this->entry_[$row['scl_class']] = array('id' => $row['scl_id'],
                                                     'kills' => 0, 'kills_isk' => 0);
        }
		$startdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
		$enddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);

        $sql = 'SELECT count(kll.kll_id) AS knb, scl_id, scl_class,';

		$sql .= ' sum(kll_isk_loss) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
		
        $sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

        if ($this->inv_crp_)
        {
            $sql .= ' inner join kb3_inv_detail ind on ( ind.ind_crp_id in ( '.join(',', $this->inv_crp_).' ) and kll.kll_id = ind.ind_kll_id ) ';
        }
        elseif ($this->inv_all_)
        {
            $sql .= ' inner join kb3_inv_detail ind on ( ind.ind_all_id in ( '.join(',', $this->inv_all_).' ) and kll.kll_id = ind.ind_kll_id ) ';
        }
		$sqlop = " WHERE ";
		if($this->system_)
		{
			$sql .= $sqlop." kll.kll_system_id in ".join(',', $this->system_)." ";
		}
		if($startdate)
		{
			$sql .= $sqlop." kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
			$sqlop = " AND ";
		}
		if($enddate) $sql .= $sqlop." kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
        $sql .= 'GROUP BY shp.shp_class';
		if($this->inv_crp_ || $this->inv_all_) $sql .= ', kll.kll_id';
		$sql .= ' order by scl.scl_class';

        $qry = DBFactory::getDBQuery();;
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $this->entry_[$row['scl_class']]['kills'] = $row['knb'];
            $this->entry_[$row['scl_class']]['kills_isk'] = $row['kisk'];
            $this->tkcount_ += $row['knb'];
            $this->tkisk_ += $row['kisk'];
        }
    }

    function generate()
    {
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
            }

            while ($kill = $this->klist_->getKill())
            {
                $classname = $kill->getVictimShipClassName();
                $entry[$classname]['kills']++;
                $entry[$classname]['kills_isk'] += $kill->getISKLoss();
                $this->tkcount_++;
                $this->tkisk_ += $kill->getISKLoss();
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
				$qrystring .= '&view='.$this->view_;
			}
			$v['url'] = $querystring;
			$v['kisk'] = round($v['kills_isk']/1000000, 2);
			$v['name'] = $k;

			$summary[] = $v;

			$this->tkcount_ += $kcount;
			$this->tkisk_ += $kisk;
			$this->tkpoints_ += $kpoints;
			$count++;
		}
		global $smarty;
		$smarty->assign('summary', $summary);
		$smarty->assign('count', $num);
		$smarty->assign('verbose', $this->verbose_);
		$smarty->assign('filter', $this->filter_);

		if (config::get('summarytable_summary'))
		{
			$smarty->assign('summarysummary', 1);
			$smarty->assign('efficiency', 0);
			$smarty->assign('kiskB', round($this->tkisk_/1000000000, 2));
			$smarty->assign('kiskM', round($this->tkisk_/1000000, 2));
			$smarty->assign('kcount', $this->tkcount_);
		}

		if ($_GET['scl_id'] != "")
		{
			$qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", '?'.$_SERVER['QUERY_STRING']);
			$qrystring = preg_replace("/&/", "&amp;", $qrystring);
			$smarty->assign('clearfilter',$qrystring);
		}

		$html .= $smarty->fetch(get_tpl('summarytable'));

        return $html;
    }
}
?>