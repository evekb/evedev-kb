<?php
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

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $this->entry_[$row['scl_class']] = array('id' => $row['scl_id'],
                                                     'kills' => 0, 'kills_isk' => 0);
        }

        $sql = 'SELECT count(*) AS knb, scl_id, scl_class,';
        if (config::get('ship_values'))
        {
            $sql .= ' sum(ifnull(ksv.shp_value,scl.scl_value)) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
                    left join kb3_ships_values ksv on (shp.shp_id = ksv.shp_id)';
        }
        else
        {
            $sql .= ' sum(scl.scl_value) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
        }
        $sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

        if ($this->inv_crp_)
        {
            $sql .= ' inner join kb3_inv_detail ind on ( ind.ind_crp_id in ( '.join(',', $this->inv_crp_).' ) and kll.kll_id = ind.ind_kll_id ) ';
        }
        elseif ($this->inv_all_)
        {
            $sql .= ' inner join kb3_inv_detail ind on ( ind.ind_all_id in ( '.join(',', $this->inv_all_).' ) and kll.kll_id = ind.ind_kll_id ) ';
        }
        $sql .= 'GROUP BY scl_class order by scl_class';

        $qry = new DBQuery();
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

            $qry = new DBQuery();
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
		if($this->break_) $columns = ceil($num/$this->break_);
		else $columns = 2;
		if(!$columns) $columns = 2;
        $width_mod = 1/$columns;
        $width = round($width_mod*100);
		if($this->verbose_) $width_abs = round($width_mod*(760-120*$columns));
		else $width_abs = round($width_mod*(760-30*$columns));

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
		$smarty->assign('count', count($entry));
		$smarty->assign('break', $this->break_);
		$smarty->assign('width', $width);
		$smarty->assign('width_abs', $width_abs);
		$smarty->assign('columns', $columns);
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