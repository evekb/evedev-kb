<?php
// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.
require_once("class.killlist.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.system.php");
require_once("class.ship.php");

class TopList
{
    function TopList()
    {
        $this->qry_ = new DBQuery();
        $this->exclude_scl_ = array();
        $this->vic_scl_id_ = array();
        $this->regions_ = array();
        $this->systems_ = array();
    }

    function setPodsNoobShips($flag)
    {
        if (!$flag)
        {
            array_push($this->exclude_scl_, 2);
            array_push($this->exclude_scl_, 3);
            array_push($this->exclude_scl_, 11);
        }
        else
        {
            $this->exclude_scl_ = array();
        }
    }

    function setSQLTop($sql)
    {
        $this->sqltop_ = $sql;
    }

    function setSQLBottom($sql)
    {
        $this->sqlbottom_ = $sql;
    }

    function addInvolvedPilot($pilot)
    {
        if(is_numeric($pilot)) $this->inv_plt_ = $pilot;
            else $this->inv_plt_ = $pilot->getID();
        if ($this->inv_crp_ || $this->inv_all_)
            $this->mixedinvolved_ = true;
    }

    function addInvolvedCorp($corp)
    {
        if(is_numeric($corp)) $this->inv_crp_ = $corp;
            else $this->inv_crp_ = $corp->getID();
        if ($this->inv_plt_ || $this->inv_all_)
            $this->mixedinvolved_ = true;
    }

    function addInvolvedAlliance($alliance)
    {
        if(is_numeric($alliance)) $this->inv_all_ = $alliance;
        else $this->inv_all_ = $alliance->getID();
        if ($this->inv_plt_ || $this->inv_crp_)
            $this->mixedinvolved_ = true;
    }

    function addVictimPilot($pilot)
    {
        if(is_numeric($pilot)) $this->vic_plt_ = $pilot;
            else $this->vic_plt_ = $pilot->getID();
        if ($this->vic_crp_ || $this->vic_all_)
            $this->mixedvictims_ = true;
    }

    function addVictimCorp($corp)
    {
        if(is_numeric($corp)) $this->vic_crp_ = $corp;
            else $this->vic_crp_ = $corp->getID();
        if ($this->vic_plt_ || $this->vic_all_)
            $this->mixedvictims_ = true;
    }

    function addVictimAlliance($alliance)
    {
        if(is_numeric($alliance)) $this->vic_all_ = $alliance;
        else $this->vic_all_ = $alliance->getID();
        if ($this->vic_plt_ || $this->vic_crp_)
            $this->mixedvictims_ = true;
    }

    function addVictimShipClass($shipclass)
    {
        array_push($this->vic_scl_id_, $shipclass->getID());
    }

    function addVictimShip($ship)
    {
    }

    function addItemDestroyed($item)
    {
    }

    function addRegion($region)
    {
        array_push($this->regions_, $region->getID());
    }

    function addSystem($system)
    {
        array_push($this->systems_, $system->getID());
    }

    function addGroupBy($groupby)
    {
        array_push($this->groupby_, $groupby);
    }

    function setPageSplitter($pagesplitter)
    {
        if (isset($_GET['page'])) $page = $_GET['page'];
        else $page = 1;
        $this->plimit_ = $pagesplitter->getSplit();
        $this->poffset_ = ($page * $this->plimit_) - $this->plimit_;
        // echo $this->offset_;
        // echo $this->limit_;
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

    // Convert given date ranges to SQL date range.
    function getDateFilter()
    {
		$qstartdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
		$qenddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);
		if($qstartdate || $qenddate) $sql .= " AND ";
		{
			if($qstartdate) $sql .= " kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' ";
			if($qstartdate && $qenddate) $sql .= " AND ";
			if($qenddate) $sql .= " kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' ";
		}
        return $sql;
    }

    function setGroupBy($groupby)
    {
        $this->groupby_ = $groupby;
    }

    function execQuery()
    {
        $this->sql_ .= $this->sqltop_;
        // involved
/*		if ($this->inv_plt_)
            $this->sql_ .= " inner join kb3_inv_detail inp
                                 on ( inp.ind_plt_id in ( ".$this->inv_plt_." ) and kll.kll_id = inp.ind_kll_id ) ";
        if ($this->inv_crp_)
            $this->sql_ .= " inner join kb3_inv_detail inc
	                         on ( inc.ind_crp_id in ( ".$this->inv_crp_." ) and kll.kll_id = inc.ind_kll_id ) ";

        if ($this->inv_all_)
            $this->sql_ .= " inner join kb3_inv_detail ina
                                 on ( ina.ind_all_id in ( ".$this->inv_all_." ) and kll.kll_id = ina.ind_kll_id ) ";
*/
        if (count($this->exclude_scl_))
        {
            $this->sql_ .= " inner join kb3_ships shp
	  		         on ( shp.shp_id = kll.kll_ship_id and
                                     shp.shp_class not in ( ".implode(",", $this->exclude_scl_)." ) )";
        }

        if (count($this->vic_scl_id_))
        {
            $this->sql_ .= " inner join kb3_ships shp
	  		         on ( shp.shp_id = kll.kll_ship_id and
	  		 shp.shp_class in ( ".implode(",", $this->vic_scl_id_)." ) )";
        }

        if (count($this->regions_))
        {
            $this->sql_ .= " inner join kb3_systems sys
      	                         on ( sys.sys_id = kll.kll_system_id )
                         inner join kb3_constellations con
      	                         on ( con.con_id = sys.sys_con_id and
			         con.con_reg_id in ( ".implode($this->regions_, ",")." ) )";
        }
        if (count($this->systems_))
        {
            $this->sql_ .= "   and kll.kll_system_id in ( ".implode($this->systems_, ",").")";
        }
        // victim filter
        if ($this->mixedvictims_)
        {
            $this->sql_ .= " where ( 1 = 0 ";
            $op = "or";
        }
        else
        {
            $this->sql_ .= ' where 1=1 ';
            $op = "and";
        }

        if ($this->vic_plt_)
            $this->sql_ .= " ".$op." kll.kll_victim_id in ( ".$this->vic_plt_." )";
        if ($this->vic_crp_)
            $this->sql_ .= " ".$op." kll.kll_crp_id in ( ".$this->vic_crp_." )";
        if ($this->vic_all_)
            $this->sql_ .= " ".$op." kll.kll_all_id in ( ".$this->vic_all_." )";

        if ($this->mixedvictims_)
            $this->sql_ .= " ) ";

		if ($this->inv_plt_)
            $this->sql_ .= " AND ind.ind_plt_id in ( ".$this->inv_plt_." ) ";
        if ($this->inv_crp_)
            $this->sql_ .= " AND ind.ind_crp_id in ( ".$this->inv_crp_." ) ";
        if ($this->inv_all_)
            $this->sql_ .= " AND ind.ind_all_id in ( ".$this->inv_all_." ) ";

        // timestamp filter
        $this->sql_ .= $this->getDateFilter();

        $this->sql_ .= " ".$this->sqlbottom_;
        // echo $this->sql_."<br/><br/>";
        $this->qry_->execute($this->sql_);
    }

    function getRow()
    {
        if (!$this->qry_->executed())
            $this->execQuery();

        $row = $this->qry_->getRow();
        return $row;
    }

    function getTimeFrameSQL()
    {
        return $this->getDateFilter();
    }
}

class TopKillsList extends TopList
{
    function TopKillsList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(ind.ind_kll_id) as cnt, ind.ind_plt_id as plt_id, plt.plt_name
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".$this->inv_crp_." )";
        $sql .= ")";

        if ($this->inv_all_)
        {
            $sql .= ' inner join kb3_corps crp on ( crp.crp_id = ind.ind_crp_id ';
            $sql .= " and crp.crp_all_id in ( ".$this->inv_all_." )";
	        $sql .= ")";
        }

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        if (count($this->vic_scl_id))
        {
            $this->setPodsNoobShips(false);
        }
        else
        {
            $this->setPodsNoobShips(true);
        }
    }
}

class TopCorpKillsList extends TopList
{
    function TopKillsList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(distinct(kll.kll_id)) as cnt, ind.ind_crp_id as crp_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_corps crp
	 	      on ( crp.crp_id = ind.ind_crp_id ";
        if ($this->inv_all_)
            $sql .= " and crp.crp_all_id in ( ".$this->inv_all_." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_crp_id order by 1 desc
                            limit 30");
        if (count($this->vic_scl_id))
        {
            $this->setPodsNoobShips(false);
        }
        else
        {
            $this->setPodsNoobShips(true);
        }
    }
}

class TopScoreList extends TopList
{
    function TopScoreList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select sum(kll.kll_points) as cnt, ind.ind_plt_id as plt_id, plt.plt_name
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".$this->inv_crp_." )";

        $sql .= ")";

        if ($this->inv_all_)
        {
            $sql .= ' inner join kb3_corps crp on ( crp.crp_id = ind.ind_crp_id ';
            $sql .= " and crp.crp_all_id in ( ".$this->inv_all_." )";
            $sql .= ')';
        }

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        // $this->setPodsNoobShips(false);
    }
}

class TopLossesList extends TopList
{
    function TopScoreList()
    {
        $this->TopList();
    }

    function generate()
    {
        $this->setSQLTop("select count(*) as cnt, kll.kll_victim_id as plt_id
                           from kb3_kills kll");
        $this->setSQLBottom("group by kll.kll_victim_id order by 1 desc
                            limit 30");
        if (!count($this->vic_scl_id_))
        {
            $this->setPodsNoobShips(false);
        }
    }
}

class TopCorpLossesList extends TopList
{
    function TopScoreList()
    {
        $this->TopList();
    }

    function generate()
    {
        $this->setSQLTop("select count(*) as cnt, kll.kll_crp_id as crp_id
                           from kb3_kills kll");
        $this->setSQLBottom("group by kll.kll_crp_id order by 1 desc
                            limit 30");
        if (count($this->vic_scl_id))
        {
            $this->setPodsNoobShips(false);
        }
        else
        {
            $this->setPodsNoobShips(true);
        }
    }
}

class TopFinalBlowList extends TopList
{
    function TopFinalBlowList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, kll.kll_fb_plt_id as plt_id
                from kb3_kills kll
				inner join kb3_inv_detail ind on (ind.ind_kll_id = kll.kll_id)
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = kll.kll_fb_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".$this->inv_crp_." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("AND ind.ind_plt_id = kll.kll_fb_plt_id group by kll.kll_fb_plt_id order by 1 desc
                            limit 30 /* TopFinalBlowList */");
        $this->setPodsNoobShips(false);
    }
}

class TopDamageDealerList extends TopList
{
    function TopDamageDealerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id and ind.ind_order = 0)
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".$this->inv_crp_." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopSoloKillerList extends TopList
{
    function TopSoloKillerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "SELECT ind.ind_plt_id AS plt_id, count(ind_kll_id) AS cnt".
          " FROM kb3_inv_detail ind".
          " JOIN kb3_kills kll ON kll.kll_id = ind.ind_kll_id AND ind.ind_order = 0 ";

        if ($this->inv_crp_)
            $sql .= " AND ind.ind_crp_id IN ( ".$this->inv_crp_." ) ";

        $this->setSQLTop($sql);

        $this->setSQLBottom(" AND ".
          "NOT EXISTS (SELECT 1 FROM kb3_inv_detail ind2 ".
            "WHERE ind2.ind_kll_id = ind.ind_kll_id AND ".
            "ind2.ind_order = 1 ) ".
          "GROUP BY ind.ind_plt_id ".
          "ORDER BY cnt DESC ".
          "LIMIT 30");
        $this->setPodsNoobShips(false);
    }
}

class TopPodKillerList extends TopList
{
    function TopPodKillerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".$this->inv_crp_." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->addVictimShipClass(new ShipClass(2)); // capsule
    }
}

class TopGrieferList extends TopList
{
    function TopGrieferList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".$this->inv_crp_." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->addVictimShipClass(new ShipClass(20)); // freighter
        $this->addVictimShipClass(new ShipClass(22)); // exhumer
        $this->addVictimShipClass(new ShipClass(7)); // industrial
        $this->addVictimShipClass(new ShipClass(12)); // barge
        $this->addVictimShipClass(new ShipClass(14)); // transport
    }
}

class TopCapitalShipKillerList extends TopList
{
    function TopCapitalShipKillerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".$this->inv_crp_." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->addVictimShipClass(new ShipClass(20)); // freighter
        $this->addVictimShipClass(new ShipClass(19)); // dread
        $this->addVictimShipClass(new ShipClass(27)); // carrier
        $this->addVictimShipClass(new ShipClass(28)); // mothership
        $this->addVictimShipClass(new ShipClass(26)); // titan
        $this->addVictimShipClass(new ShipClass(29)); // cap. industrial
    }
}

class TopContractKillsList extends TopKillsList
{
    function TopContractKillsList()
    {
        $this->TopKillsList();
    }

    function generate()
    {
        parent::generate();
    }

    function setContract($contract)
    {
        $this->setStartDate($contract->getStartDate());
        if ($contract->getEndDate() != "")
            $this->setEndDate($contract->getEndDate());

        while ($target = $contract->getContractTarget())
        {
            switch ($target->getType())
            {
                case "corp":
                    $this->addVictimCorp(new Corporation($target->getID()));
                    break;
                case "alliance":
                    $this->addVictimAlliance(new Alliance($target->getID()));
                    break;
                case "region":
                    $this->addRegion(new Region($target->getID()));
                    break;
                case "system":
                    $this->addSystem(new SolarSystem($target->getID()));
                    break;
            }
        }
    }
}

class TopContractScoreList extends TopScoreList
{
    function TopContractScoreList()
    {
        $this->TopScoreList();
    }

    function generate()
    {
        parent::generate();
    }

    function setContract($contract)
    {
        $this->setStartDate($contract->getStartDate());
        if ($contract->getEndDate() != "")
            $this->setEndDate($contract->getEndDate());

        while ($target = $contract->getContractTarget())
        {
            switch ($target->getType())
            {
                case "corp":
                    $this->addVictimCorp(new Corporation($target->getID()));
                    break;
                case "alliance":
                    $this->addVictimAlliance(new Alliance($target->getID()));
                    break;
                case "region":
                    $this->addRegion(new Region($target->getID()));
                    break;
                case "system":
                    $this->addSystem(new SolarSystem($target->getID()));
                    break;
            }
        }
    }
}

class TopPilotTable
{
    function TopPilotTable($toplist, $entity)
    {
        $this->toplist_ = $toplist;
        $this->entity_ = $entity;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center colspan=2>Pilot</td>";
        $html .= "<td class=kb-table-cell align=center width=60>".$this->entity_."</td>";
        $html .= "</tr>";

        $odd = true;
        $i = 1;
        while ($row = $this->toplist_->getRow())
        {
            $pilot = new Pilot($row['plt_id']);
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr class=".$class.">";
            $html .= "<td><img src=\"".$pilot->getPortraitURL(32)."\"></td>";
            $html .= "<td class=kb-table-cell width=200><b>".$i.".</b>&nbsp;<a class=kb-shipclass href=\"?a=pilot_detail&amp;plt_id=".$row['plt_id']."\">".$pilot->getName()."</a></td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
            $i++;
        }

        $html .= "</table>";

        return $html;
    }
}

class TopCorpTable
{
    function TopCorpTable($toplist, $entity)
    {
        $this->toplist_ = $toplist;
        $this->entity_ = $entity;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center>#</td>";
        $html .= "<td class=kb-table-cell align=center>Corporation</td>";
        $html .= "<td class=kb-table-cell align=center width=60>".$this->entity_."</td>";
        $html .= "</tr>";

        $odd = true;
        $i = 1;
        while ($row = $this->toplist_->getRow())
        {
            $corp = new Corporation($row['crp_id']);
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr class=".$class.">";
            $html .= "<td class=kb-table-cell align=center><b>".$i.".</b></td>";
            $html .= "<td class=kb-table-cell width=200><a href=\"?a=corp_detail&amp;crp_id=".$row['crp_id']."\">".$corp->getName()."</a></td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
            $i++;
        }

        $html .= "</table>";

        return $html;
    }
}

class TopShipList extends TopList
{
    function TopShipList()
    {
        $this->TopList();
    }

    function addInvolvedPilot($pilot)
    {
        $this->invplt_ = $pilot;
    }

    function addInvolvedCorp($corp)
    {
        $this->invcrp_ = $corp;
    }

    function addInvolvedAlliance($alliance)
    {
        $this->invall_ = $alliance;
    }

    function generate()
    {
        $sqltop = "select count(distinct ind.ind_kll_id) as cnt, ind.ind_shp_id as shp_id
              from kb3_inv_detail ind
	      inner join kb3_ships shp on ( shp_id = ind.ind_shp_id )";

		$this->setSQLTop($sqltop);

        if ($this->invplt_)
            $sqlbottom .= " and ind.ind_plt_id = ".$this->invplt_->getID();

        if ($this->invcrp_)
            $sqlbottom .= " and ind.ind_crp_id = ".$this->invcrp_->getID();

        if ($this->invall_)
            $sqlbottom .= " and ind.ind_all_id = ".$this->invall_->getID();

		$sqlbottom .= " and shp.shp_class not in (2, 17, 18)".
			" group by ind.ind_shp_id order by 1 desc".
			" limit 20";

        $this->setSQLBottom($sqlbottom);
    }
}

class TopShipListTable
{
    function TopShipListTable($toplist)
    {
        $this->toplist_ = $toplist;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center colspan=2>Ship</td>";
        $html .= "<td class=kb-table-cell align=center width=60>Kills</td>";
        $html .= "</tr>";

        $odd = true;
        while ($row = $this->toplist_->getRow())
        {
            $ship = new Ship($row['shp_id']);
            $shipclass = $ship->getClass();
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr class=".$class.">";
            $html .= "<td><img src=\"".$ship->getImage(32)."\"></td>";
            $html .= "<td class=kb-table-cell width=200><b>".$ship->getName()."</b><br>".$shipclass->getName()."</td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
        }

        $html .= "</table>";

        return $html;
    }
}

class TopWeaponList extends TopList
{
    function TopWeaponList()
    {
        $this->TopList();
    }

    function addInvolvedPilot($pilot)
    {
        $this->invplt_ = $pilot;
    }

    function addInvolvedCorp($corp)
    {
        $this->invcrp_ = $corp;
    }

    function addInvolvedAlliance($alliance)
    {
        $this->invall_ = $alliance;
    }

    function generate()
    {
		$sql = "select count(distinct ind.ind_kll_id) as cnt, ind.ind_wep_id as itm_id
				  from kb3_inv_detail ind
				  inner join kb3_invtypes itm on (typeID = ind.ind_wep_id)";

        if ($this->invplt_)
            $sqlbottom .= " and ind.ind_plt_id = ".$this->invplt_->getID();

        if ($this->invcrp_)
            $sqlbottom .= " and ind.ind_crp_id = ".$this->invcrp_->getID();

        if ($this->invall_)
            $sqlbottom .= " and ind.ind_all_id = ".$this->invall_->getID();

        $this->setSQLTop($sql);
        // since ccps database doesnt have icons for ships this will also fix the ship as weapon bug
        $sqlbottom .=" and itm.icon != ''".
				" and itm.typeName != 'Unknown'".
				" group by ind.ind_wep_id order by 1 desc limit 20";
		$this->setSQLBottom($sqlbottom);
    }
}

class TopWeaponListTable
{
    function TopWeaponListTable($toplist)
    {
        $this->toplist_ = $toplist;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center colspan=2>Weapon</td>";
        $html .= "<td class=kb-table-cell align=center width=60>Kills</td>";
        $html .= "</tr>";

        $odd = true;
        while ($row = $this->toplist_->getRow())
        {
            $item = new Item($row['itm_id']);
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr height=32 class=".$class.">";
            $html .= "<td width=32 valign=top align=left>".$item->getIcon(32)."</td>";
            $html .= "<td class=kb-table-cell width=200><b>".$item->getName()."</b></td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
        }

        $html .= "</table>";

        return $html;
    }
}
?>