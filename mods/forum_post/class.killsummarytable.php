<?php
/**
 * @package EDK
 */
class KillSummaryTable
{
    function KillSummaryTable($klist = null, $llist = null)
    {
        $this->klist_ = $klist;
	    $this->llist_ = $llist;
        $this->verbose_ = false;
        $this->filter_ = true;
        $this->inv_crp_ = array();
        $this->inv_all_ = array();
    }

    function setBreak($break)
    {
        $this->break_ = $break;
    }

    function setVerbose($verbose)
    {
        $this->verbose_ = $verbose;
    }

    function setFilter($filter)
    {
        $this->filter_ = $filter;
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

    function addInvolvedCorp($corp)
    {
        $this->inv_crp_[] = $corp->getID();
        if ($this->inv_plt_ || $this->inv_all_)
        {
            $this->mixedinvolved_ = true;
        }
    }

    function addInvolvedAlliance($alliance)
    {
        $this->inv_all_[] = $alliance->getID();
        if ($this->inv_plt_ || $this->inv_crp_)
        {
            $this->mixedinvolved_ = true;
        }
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

        $qry = DBFactory::getDBQuery();
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

        if ($this->inv_crp_)
        {
            $sql .= ' inner join kb3_inv_crp inc on ( inc.inc_crp_id in ( '.join(',', $this->inv_crp_).' ) and kll.kll_id = inc.inc_kll_id ) ';
        }
        elseif ($this->inv_all_)
        {
            $sql .= ' inner join kb3_inv_all ina on ( ina.ina_all_id in ( '.join(',', $this->inv_all_).' ) and kll.kll_id = ina.ina_kll_id ) ';
        }
        $sql .= 'GROUP BY scl_class order by scl_class';

        $qry = DBFactory::getDBQuery();
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

        if ($this->inv_crp_)
        {
            $sql .= ' where kll.kll_crp_id in ( '.join(',', $this->inv_crp_).' ) ';
        }
        elseif ($this->inv_all_)
        {
            $sql .= ' where kll.kll_all_id in ( '.join(',', $this->inv_all_).' ) ';
        }
        $sql .= 'GROUP BY scl_class order by scl_class';

        $qry = DBFactory::getDBQuery();
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
        if ($this->klist_)
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
            while ($kill = $this->klist_->getKill())
            {
                $classname = $kill->getVictimShipClassName();
                $entry[$classname]['kills']++;
                $entry[$classname]['kills_isk'] += $kill->getVictimShipValue();
                $this->tkcount_++;
                $this->tkisk_ += $kill->getVictimShipValue();
            }
            // losses
            while ($kill = $this->llist_->getKill())
            {
                $classname = $kill->getVictimShipClassName();
                $entry[$classname]['losses']++;
                $entry[$classname]['losses_isk'] += $kill->getVictimShipValue();
                $this->tlcount_++;
                $this->tlisk_ += $kill->getVictimShipValue();
            }
        }
        else
        {
            $this->getkills();
            $entry = &$this->entry_;
        }

        $odd = false;
        $prevdate = "";
        $width = round($this->break_/count($entry)*100);
        $width_abs = round($this->break_/count($entry)*600);

        $html .= "<table class=kb-subtable width=\"100%\" border=\"0\" cellspacing=0>";
        if ($this->break_)
            $html .= "<tr><td valign=top width=\"$width%\"><table class=kb-table cellspacing=\"1\" width=\"100%\">";
        $counter = 1;

        if ($this->verbose_)
        {
            $header = "<tr class=kb-table-header><td class=kb-table-cell width=\"$width_abs\">Ship class</td><td class=kb-table-cell width=60 align=center>Kills</td><td class=kb-table-cell width=60 align=center>ISK (M)</td><td class=kb-table-cell width=60 align=center>Losses</td><td class=kb-table-cell width=60 align=center>ISK (M)</td></tr>";
        }
        else
        {
            $header = "<tr class=kb-table-header><td class=kb-table-cell width=\"$width_abs\">Ship class</td><td class=kb-table-cell width=30 align=center>K</td><td class=kb-table-cell width=30 align=center>L</td></tr>";
        }

        $html .= $header;

        foreach ($entry as $k => $v)
        {
            if (!$v['id'] || $v['id'] == 3)
                continue;
            if ($this->break_ && $counter > $this->break_)
            {
                $html .= "</table></td>";
                $html .= "<td valign=top width=\"$width%\"><table class=kb-table cellspacing=\"1\">";
                $html .= $header;
                $counter = 1;
            }

            if (!$odd)
            {
                $odd = true;
                $class = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $class = 'kb-table-row-even';
            }

            if (isset($_GET['scl_id']) && $v['id'] == $_GET['scl_id'])
                $highlight = "-hl";
            else
                $highlight = "";

            if ($v['kills'] == 0)
                $kclass = "kl-kill-null";
            else
                $kclass = "kl-kill";

            if ($v['losses'] == 0)
                $lclass = "kl-loss-null";
            else
                $lclass = "kl-loss";

            $html .= "<tr class=".$class.">";

            $qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", $_SERVER['QUERY_STRING']);
            $qrystring = preg_replace("/&page=([0-9]?[0-9])/", "", $qrystring);
            if ($this->view_)
            {
                $qrystring .= '&view='.$this->view_;
            }
            $html .= "<td nowrap class=kb-table-cell><b>";

            if ($this->filter_) $html .= "<a class=kb-shipclass".$highlight." href=\"?".$qrystring."&scl_id=".$v['id']."\">";

            $html .= $k;

            if ($this->filter_) $html .= "</a>";

            $html .= "</b></td>";

            $html .= "<td class=".$kclass." align=center>".$v['kills']."</td>";
            if ($this->verbose_)
                $html .= "<td class=".$kclass." align=center>".round($v['kills_isk']/1000000, 2)."</td>";

            $html .= "<td class=".$lclass." align=center>".$v['losses']."</td>";
	        if ($this->verbose_)
	            $html .= "<td class=".$lclass." align=center>".round($v['losses_isk']/1000000, 2)."</td>";

            $html .= "</tr>";

            $counter++;

            $this->tkcount_ += $kcount;
            $this->tkisk_ += $kisk;
            $this->tkpoints_ += $kpoints;
            $this->tlcount_ += $lcount;
            $this->tlisk_ += $lisk;
            $this->tlpoints_ += $lpoints;
        }
        if ($this->break_)
            $html .= "</table></td>";

        $html .= "</tr></table>";

        if (config::get('summarytable_summary'))
        {
            $html .= '<table width=100% border=0 cellspacing=2>'
                     .'<tr align=center><td width=51%><span align=right class="killcount">'
                     .$this->tkcount_.' Ships killed ('.round($this->tkisk_/1000000, 2).'M ISK)</span></td><td width=49%><span class="losscount">'.$this->tlcount_.' Ships lost ('.round($this->tlisk_/1000000, 2).'M ISK)</span></td></tr></table>';
        }

        if (isset($_GET['scl_id']))
        {
            $html .= "<table align=center><tr><td align=center valign=top class=weeknav>";
            $qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", $_SERVER['QUERY_STRING']);
            $html .= "[<a href=\"?".$qrystring."\">clear filter</a>]</td></tr></table>";
        }

        return $html;
    }
	
	    function forum()
    {
	
        if ($this->klist_)
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
            while ($kill = $this->klist_->getKill())
            {
                $classname = $kill->getVictimShipClassName();
                $entry[$classname]['kills']++;
                $entry[$classname]['kills_isk'] += $kill->getVictimShipValue();
                $this->tkcount_++;
                $this->tkisk_ += $kill->getVictimShipValue();
            }
            // losses
            while ($kill = $this->llist_->getKill())
            {
                $classname = $kill->getVictimShipClassName();
                $entry[$classname]['losses']++;
                $entry[$classname]['losses_isk'] += $kill->getVictimShipValue();
                $this->tlcount_++;
                $this->tlisk_ += $kill->getVictimShipValue();
            }
        }
        else
        {
            $this->getkills();
            $entry = &$this->entry_;
        }

		// Build our Post
		$config = new config(KB_SITE);
		$set_colours = config::get('forum_post_colours'); 	//load colour settings
		if(!is_array($set_colours)) { $set_colours = array(); } 				// if the settings have been reset create an empty array so as not to brake the code later on
		$set_styles = config::get('forum_post_styles');		//load style settings
		if(!is_array($set_styles)) { $set_styles = array(); }					// if the settings have been reset create an empty array so as not to brake the code later on
		$set_isk = config::get('forum_post_isk',$_POST['isk']);			// load isk setting
		$forum_post_miss_empty_class = config::get(forum_post_miss_empty_class);
		//print_r($set_styles);
        foreach ($entry as $k => $v)
        {
        if($forum_post_miss_empty_class == 1 && $v['kills'] == 0 && $v['losses'] == 0) {
		
		}
		else {	
			$class =  $k.$kclass;
			$kills = $v['kills'];
				if($set_isk == "yes")
				{
				$kills_isk = "(".round($v['kills_isk']/1000000, 2)."M)"; 
				$loss_isk = "(".round($v['losses_isk']/1000000, 2)."M)";
				}
			$loss = $v['losses'];
			$close = "\r\n";
			$spacer = " / "; 
			if(array_key_exists(str_replace(" ","",$class),$set_colours))
			{
			$colour_open = "[".$set_colours[str_replace(" ","",$class)]."]";
			$colour_close = "[/".$set_colours[str_replace(" ","",$class)]."]";
			}
			else
			{
			$colour_open = "";
			$colour_close = "";
			}
			if(array_key_exists(str_replace(" ","",$class),$set_styles))
			{
			$style_open = "[".$set_styles[str_replace(" ","",$class)]."]";
			$style_close = "[/".$set_styles[str_replace(" ","",$class)]."]";
			}
			else
			{
			$style_open = "";
			$style_close = "";
			}		
			$order = config::get('forum_post_order');
			
			if($order == "first"){
			$kills_list .= $colour_open . $style_open . $class . $spacer . $kills . $kills_isk . $spacer . $loss . $loss_isk . $style_close . $colour_close. $close;
			}
			else
			{
			$kills_list .= $colour_open . $style_open . $kills . $kills_isk . $spacer . $loss . $loss_isk . $spacer . $class  . $style_close . $colour_close. $close;
			}
				$counter++;
				$this->tkcount_ += $kcount;
				$this->tlcount_ += $lcount;
				$this->tkisk_ += $kisk;
				$this->tlisk_ += $lisk;
				$this->tkpoints_ += $kpoints;
				$this->tlpoints_ += $lpoints;
			}
        }
		
		if($order == "first")
		{ 
		$html .= "Class / "; 
		}
		$html.= "Kills";
			if($set_isk == "yes")
			{ 
			$html .= "(kills isk)";
			}
		$html .=" / losses ";
			if($set_isk == "yes")
			{ 
			$html .= "(losses isk)";
			}
		if($order != "first")
		{ 
		$html .= " / Class"; 
		}
		$html .="\r\n";
		$html .= $kills_list;
		$html .= "Total / ".$this->tkcount_;
		$html .= " (".round($this->tkisk_/1000000, 2)."M)";
		$html .= " / ".$this->tlcount_;
		$html .= " (".round($this->tlisk_/1000000, 2)."M)";
        return $html;
    }
}