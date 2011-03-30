<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList
{
	protected $exc_vic_scl = array();
	protected $inc_vic_scl = array();
	protected $exc_vic_shp = array();
	protected $inc_vic_shp = array();

	protected $inv_all = array();
	protected $inv_crp = array();
	protected $inv_plt = array();

	protected $vic_all = array();
	protected $vic_crp = array();
	protected $vic_plt = array();

	protected $mixedvictims = false;
	protected $mixedinvolved = false;

	protected $regions_ = array();
	protected $systems_ = array();
	protected $qry = null;

	protected $weekno_ = 0;
	protected $yearno_ = 0;
	protected $monthno_ = 0;
	protected $startweekno_ = 0;
	protected $startDate_ = 0;
	protected $endDate_ = 0;
	protected $limit = 10;

	protected $finalStartDate = null;
	protected $finalEndDate = null;
	
	function TopList()
	{
	}

	//! Set the maximum number of results to show in the toplist.

	/*!
	 *  \param $limit Maximum number of kills to show.
	 *
	 *  \returns value toplist was set to.
	 */
	function setLimit($limit = 10)
	{
		$this->limit = intval($limit);
		return $this->limit;
	}
	//! Include or exclude pods/noob ships/shuttles.

	/*!
	 *  \param $flag true to show P/N/S, false to remove.
	 */
	function setPodsNoobShips($flag)
	{
		if (!$flag)
		{
			$this->excludeVictimShipClass(2);
			$this->excludeVictimShipClass(3);
			$this->excludeVictimShipClass(11);
		}
	}

	//! Remove structures.

	/*!
	 *  Note that these class types are hard coded so will need modification
	 * if the classes are changed in future.
	 */
	function setNoStructures()
	{
		$this->excludeVictimShipClass(35);
		$this->excludeVictimShipClass(36);
		$this->excludeVictimShipClass(37);
		$this->excludeVictimShipClass(38);
		$this->excludeVictimShipClass(41);
		$this->excludeVictimShipClass(42);
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
		involved::add($this->inv_plt,$pilot);
	}

	function addInvolvedCorp($corp)
	{
		involved::add($this->inv_crp,$corp);
	}

	function addInvolvedAlliance($alliance)
	{
		involved::add($this->inv_all,$alliance);
	}

	function addVictimPilot($pilot)
	{
		involved::add($this->vic_plt,$pilot);
	}

	function addVictimCorp($corp)
	{
		involved::add($this->vic_crp,$corp);
	}

	function addVictimAlliance($alliance)
	{
		involved::add($this->vic_all,$alliance);
	}

	/*!
	 * Set a victim ship class to include.
	 *
	 * If this is set then only ship classes set will be in the output.
	 *
	 * \param $shipclass ID of a ship class.
	 */
	function addVictimShipClass($shipclass)
	{
		if(!is_numeric($shipclass)) $scl_id = $shipclass->getID();
		else $scl_id = intval($shipclass);
		$this->inc_vic_scl[$scl_id] = $scl_id;
		unset ($this->exc_vic_scl[$scl_id]);
	}

	/*!
	 * Set a victim ship class to exclude.
	 *
	 * If this is set then only ship classes not set will be in the output.
	 *
	 * \param $shipclass ID of a ship class
	 */
	function excludeVictimShipClass($shipclass)
	{
		if(!is_numeric($shipclass)) $scl_id = $shipclass->getID();
		else $scl_id = intval($shipclass);
		$this->exc_vic_scl[$scl_id] = $scl_id;
		unset ($this->inc_vic_scl[$scl_id]);
	}

	/*!
	 * Set a victim ship type to include.
	 *
	 * If this is set then only ship types set will be in the output.
	 *
	 * \param $ship ID of a shiptype
	 */
	function addVictimShip($ship)
	{
		$ship = intval($ship);
		$this->inc_vic_shp[$ship] = $ship;
		unset ($this->exc_vic_shp[$ship]);
	}

	/*!
	 * Set a victim ship type to exclude.
	 *
	 * If this is set then only ship types not set will be in the output.
	 *
	 * \param $ship ID of a shiptype
	 */
	function excludeVictimShip($ship)
	{
		$ship = intval($ship);
		$this->exc_vic_shp[$ship] = $ship;
		unset ($this->inc_vic_shp[$ship]);
	}

	function addRegion($region)
	{
		if(is_numeric($region)) array_push($this->regions_, $region);
		else array_push($this->regions_, $region->getID());
	}

	function addSystem($system)
	{
		if(is_numeric($system)) array_push($this->systems_, $system);
		else array_push($this->systems_, $system->getID());
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
	function getDateFilter($field = "kll.kll_timestamp")
	{
		$sql = "";
		if(is_null($this->finalStartDate))
		{
			$this->finalStartDate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
			$this->finalEndDate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);
		}
		if($this->finalStartDate || $this->finalEndDate)
		{
			if($this->finalStartDate) $sql .= " $field >= '".gmdate('Y-m-d H:i',$this->finalStartDate)."' ";
			if($this->finalStartDate && $this->finalEndDate) $sql .= " AND ";
			if($this->finalEndDate) $sql .= " $field <= '".gmdate('Y-m-d H:i',$this->finalEndDate)."' ";
		}
		return $sql;
	}

	function setGroupBy($groupby)
	{
		$this->groupby_ = $groupby;
	}

	function execQuery()
	{
		if ($this->inv_plt && $this->inv_crp || $this->inv_plt && $this->inv_all
			|| $this->inv_crp && $this->inv_all) $this->mixedinvolved = true;
		if ($this->vic_plt && $this->vic_crp || $this->vic_plt && $this->vic_all
			|| $this->vic_crp && $this->vic_all) $this->mixedvictims = true;
		$this->sql_ .= $this->sqltop_;
		// involved
		if(!$this->mixedinvolved)
		{
			if ($this->inv_crp)
				$this->sql_ .= "\n\tINNER JOIN kb3_inv_crp inc ON (kll.kll_id = inc.inc_kll_id) ";
			if ($this->inv_all)
				$this->sql_ .= "\n\tINNER JOIN kb3_inv_all ina ON (kll.kll_id = ina.ina_kll_id) ";
		}
		else
		{
			$mixedinv = array();
			// All this to avoid plt OR crp OR all over kb3_inv_details
			// which makes mysql unhappy - sometimes
			if ($this->inv_plt)
			{
				$misql = "SELECT ind_kll_id as kll_id
					FROM kb3_inv_detail
					WHERE ";
				if($this->getDateFilter())
					$misql .= $this->getDateFilter("ind_timestamp")." AND ";
				$misql .= "ind_plt_id IN (".implode(",", $this->inv_plt).")";
				$mixedinv[] = $misql;
			}
			if ($this->inv_crp)
			{
				$misql = "SELECT inc_kll_id as kll_id
					FROM kb3_inv_crp
					WHERE ";
				if($this->getDateFilter())
					$misql .= $this->getDateFilter("inc_timestamp")." AND ";
				$misql .= "inc_crp_id IN (".implode(",", $this->inv_crp).")";
				$mixedinv[] = $misql;
			}
			if ($this->inv_all)
			{
				$misql = "SELECT ina_kll_id as kll_id
					FROM kb3_inv_all
					WHERE ";
				if($this->getDateFilter())
					$misql .= $this->getDateFilter("ina_timestamp")." AND ";
				$misql .= "ina_all_id IN (".implode(",", $this->inv_all).")";
				$mixedinv[] = $misql;
			}
			$this->sql_ .= "\n\tINNER JOIN (".implode("\nUNION\n", $mixedinv).") inv ON (inv.kll_id = ind.ind_kll_id)";
		}

		if (count($this->inc_vic_scl) || count($this->exc_vic_scl))
		{
			$this->sql_ .= "\n\tINNER JOIN kb3_ships shp
	  		         ON ( shp.shp_id = kll.kll_ship_id )";
		}

		if (count($this->regions_))
		{
			$this->sql_ .= "\n\tINNER JOIN kb3_systems sys
      	                         on ( sys.sys_id = kll.kll_system_id )
                         INNER JOIN kb3_constellations con
      	                         on ( con.con_id = sys.sys_con_id and
			         con.con_reg_id in ( ".implode($this->regions_, ",")." ) )";
		}

		$op = "\nWHERE ";
		// victim filter
		if ($this->vic_plt || $this->vic_crp || $this->vic_all)
		{
			$vicP = array();

			if ($this->vic_plt)
				$vicP[] = "kll.kll_victim_id IN ( ".implode(",", $this->vic_plt)." )";
			if ($this->vic_crp)
				$vicP[] = "kll.kll_crp_id IN ( ".implode(",", $this->vic_crp)." )";
			if ($this->vic_all)
				$vicP[] = "kll.kll_all_id IN ( ".implode(",", $this->vic_all)." )";

			$this->sql_ .= $op."( ".implode(" OR ", $vicP).")";
			$op = " AND ";
		}

		if (count($this->exc_vic_scl))
		{
			$this->sql_ .= $op." shp.shp_class not IN ( ".implode(",", $this->exc_vic_scl)." ) ";
			$op = " AND ";
		}

		if (count($this->inc_vic_scl))
		{
			$this->sql_ .= $op." shp.shp_class IN ( ".implode(",", $this->inc_vic_scl)." ) ";
			$op = " AND ";
		}

		if (count($this->exc_vic_shp))
		{
			$this->sql_ .= $op." kll.kll_ship_id not IN ( ".implode(",", $this->exc_vic_shp)." ) ";
			$op = " AND ";
		}

		if (count($this->inc_vic_shp))
		{
			$this->sql_ .= $op." kll.kll_ship_id IN ( ".implode(",", $this->inc_vic_shp)." ) ";
			$op = " AND ";
		}

		if(!$this->mixedinvolved)
		{
			if ($this->inv_crp)
			{
				$this->sql_ .= $op." inc.inc_crp_id IN ( ".implode(",", $this->inv_crp)." ) ";
				$op = " AND ";
			}
			else if ($this->inv_all)
			{
				$this->sql_ .= $op." ina.ina_all_id IN ( ".implode(",", $this->inv_all)." ) ";
				$op = " AND ";
			}
		}
		$invP = array();
		if ($this->inv_plt)
			$invP[] = "ind.ind_plt_id IN (".implode(",", $this->inv_plt).")";
		if ($this->inv_crp)
			$invP[] = "ind.ind_crp_id IN (".implode(",", $this->inv_crp).")";
		if ($this->inv_all)
			$invP[] = "ind.ind_all_id IN (".implode(",", $this->inv_all).")";
		if($invP)
		{
			$this->sql_ .= $op." ( ".implode(' OR ', $invP)." ) ";
			$op = " AND ";
		}

		if (count($this->systems_))
		{
			$this->sql_ .= $op." kll.kll_system_id IN ( ".implode($this->systems_, ",").") ";
			$op = " AND ";
		}

		// Add dates
		if ($this->vic_plt || $this->vic_crp || $this->vic_all
			|| !($this->inv_plt || $this->inv_crp || $this->inv_all))
				{
					if($this->getDateFilter())
					{
						$this->sql_ .= $op.$this->getDateFilter();
						$op = " AND ";
					}
				}

		if($this->getDateFilter())
		{
			$filter = "";
			if($this->mixedinvolved) $filter = "ind.ind_timestamp";
			else if($this->inv_all) $filter = "ina.ina_timestamp";
			else if($this->inv_crp) $filter = "inc.inc_timestamp";
			else if($this->inv_plt) $filter = "ind.ind_timestamp";
			if($filter)
			{
				$this->sql_ .= $op.$this->getDateFilter($filter)." ";
				$op = " AND ";
			}
		}

		// This is a little ugly but is needed since the bottom can start with
		// AND or GROUP BY.
		if($op == " WHERE ") $this->sql_ .= $op." 1=1 ";

		$this->sql_ .= " ".$this->sqlbottom_;
		$this->sql_ .= " /* ".get_class($this)." */";
		$this->qry = DBFactory::getDBQuery();
		$this->qry->execute($this->sql_);
	}

	function getRow()
	{
		if (is_null($this->qry))
			$this->execQuery();

		$row = $this->qry->getRow();
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
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              INNER JOIN kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id )";
		
		$this->setSQLTop($sql);

		$this->setSQLBottom($sqlB." group by ind.ind_plt_id order by 1 desc
                            limit ".$this->limit);
		if (count($this->vic_scl_id))
		{
			$this->setPodsNoobShips(true);
		}
		else
		{
			$this->setPodsNoobShips(config::get('podnoobs'));
		}
	}
}

class TopCorpKillsList extends TopList
{
	function TopCorpKillsList()
	{
		$this->TopList();
	}

	function generate()
	{
		$sql = "select count(distinct(kll.kll_id)) as cnt, ind.ind_crp_id as crp_id
                from kb3_kills kll
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )";

		$this->setSQLTop($sql);

		$this->setSQLBottom("group by ind.ind_crp_id order by 1 desc
                            limit ".$this->limit);
		if (count($this->vic_scl_id))
		{
			$this->setPodsNoobShips(true);
		}
		else
		{
			$this->setPodsNoobShips(config::get('podnoobs'));
		}
	}
}

class TopScoreList extends TopList
{
	function TopScoreList()
	{
		$this->limit = 30;
		$this->TopList();
	}

	function generate()
	{
		$sql = "select sum(kll.kll_points) as cnt, ind.ind_plt_id as plt_id, plt.plt_name
                from kb3_kills kll
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              INNER JOIN kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id )";
// Restrict results to pilots in the involved corp/all/pilot lists.
		$sqlB = "";
 		if ($this->inv_crp || $this->inv_all || $this->inv_plt)
		{
			$invP = array();
			if ($this->inv_plt)
				$invP[] = "ind.ind_plt_id IN ( ".implode(",", $this->inv_plt)." )";
			if ($this->inv_crp)
				$invP[] = "ind.ind_crp_id IN ( ".implode(",", $this->inv_crp)." )";
			if ($this->inv_all)
				$invP[] = "ind.ind_all_id IN ( ".implode(",", $this->inv_all)." )";
			$sqlB = " AND (".implode(" OR ", $invP).") ";
		}

		$this->setSQLTop($sql);

		$this->setSQLBottom($sqlB." group by ind.ind_plt_id order by 1 desc
                            limit ".$this->limit);
	}
}

class TopLossesList extends TopList
{
	function TopLossesList()
	{
		$this->TopList();
	}

	function generate()
	{
		$this->setSQLTop("select count(*) as cnt, kll.kll_victim_id as plt_id
                           from kb3_kills kll");
		$this->setSQLBottom("group by kll.kll_victim_id order by 1 desc
                            limit ".$this->limit);
		if (!count($this->inc_vic_scl))
		{
			$this->setPodsNoobShips(config::get('podnoobs'));
		}
	}
}

class TopCorpLossesList extends TopList
{
	function TopCorpLossesList()
	{
		$this->TopList();
	}

	function generate()
	{
		$this->setSQLTop("select count(*) as cnt, kll.kll_crp_id as crp_id
                           from kb3_kills kll");
		$this->setSQLBottom("group by kll.kll_crp_id order by 1 desc
                            limit ".$this->limit);
		if (count($this->vic_scl_id))
		{
			$this->setPodsNoobShips(true);
		}
		else
		{
			$this->setPodsNoobShips(config::get('podnoobs'));
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
		$sql = "select count(ind.ind_kll_id) as cnt, kll.kll_fb_plt_id as plt_id
                from kb3_inv_detail ind
                INNER JOIN kb3_kills kll on (ind.ind_kll_id = kll.kll_id)";

		$this->setSQLTop($sql);

		$this->setSQLBottom("AND ind.ind_plt_id = kll.kll_fb_plt_id group by ind.ind_plt_id order by cnt desc
                            limit 10 /* TopFinalBlowList */");
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
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id and ind.ind_order = 0)
              INNER JOIN kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";

		$sql .= ")";

		$this->setSQLTop($sql);

		$this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit ".$this->limit);
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

		$this->setSQLTop($sql);

		$this->setSQLBottom(" AND ".
			"NOT EXISTS (SELECT 1 FROM kb3_inv_detail ind2 ".
			"WHERE ind2.ind_kll_id = ind.ind_kll_id AND ".
			"ind2.ind_order = 1 ) ".
			"GROUP BY ind.ind_plt_id ".
			"ORDER BY cnt DESC ".
			"limit ".$this->limit);
	}
}

class TopPodKillerList extends TopKillsList
{
	function TopPodKillerList()
	{
		trigger_error("Using ".get_class($this)." is deprecated. Use TopKillsList and set ship classes as needed.", E_USER_NOTICE);
		$this->TopKillsList();
		$this->addVictimShipClass(2); // capsule
	}
}

class TopGrieferList extends TopKillsList
{
	function TopGrieferList()
	{
		trigger_error("Using ".get_class($this)." is deprecated. Use TopKillsList and set ship classes as needed.", E_USER_NOTICE);
		$this->TopKillsList();
		$this->addVictimShipClass(20); // freighter
		$this->addVictimShipClass(22); // exhumer
		$this->addVictimShipClass(7); // industrial
		$this->addVictimShipClass(12); // barge
		$this->addVictimShipClass(14); // transport
	}
}

class TopCapitalShipKillerList extends TopKillsList
{
	function TopCapitalShipKillerList()
	{
		trigger_error("Using ".get_class($this)." is deprecated. Use TopKillsList and set ship classes as needed.", E_USER_NOTICE);
		$this->TopKillsList();
		$this->addVictimShipClass(20); // freighter
		$this->addVictimShipClass(19); // dread
		$this->addVictimShipClass(27); // carrier
		$this->addVictimShipClass(28); // mothership
		$this->addVictimShipClass(26); // titan
		$this->addVictimShipClass(29); // cap. industrial
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
					$this->addVictimCorp($target->getID());
					break;
				case "alliance":
					$this->addVictimAlliance($target->getID());
					break;
				case "region":
					$this->addRegion($target->getID());
					break;
				case "system":
					$this->addSystem($target->getID());
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
					$this->addVictimCorp($target->getID());
					break;
				case "alliance":
					$this->addVictimAlliance($target->getID());
					break;
				case "region":
					$this->addRegion($target->getID());
					break;
				case "system":
					$this->addSystem($target->getID());
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
		global $smarty;
		$this->toplist_->generate();

		$i = 1;
		$rows = array();
		while ($row = $this->toplist_->getRow())
		{
			$pilot = new Pilot($row['plt_id']);
			if($pilot->getExternalID()) $uri = "?a=pilot_detail&amp;plt_ext_id=".$pilot->getExternalID();
			else $uri = "?a=pilot_detail&amp;plt_id=".$row['plt_id'];
			$rows[] = array(
				'rank' => $i,
				'name' => $pilot->getName(),
				'uri' => $uri,
				'portrait' => $pilot->getPortraitURL(32),
				'count' => $row['cnt']);
			$i++;
		}

		$smarty->assign('tl_name', 'Pilot');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
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
		global $smarty;
		$this->toplist_->generate();

		$i = 1;
		while ($row = $this->toplist_->getRow())
		{
			$corp = new Corporation($row['crp_id']);
			if($corp->getExternalID()) $uri = "?a=corp_detail&amp;crp_ext_id=".$corp->getExternalID();
			else $uri = "?a=corp_detail&amp;crp_id=".$row['crp_id'];
			$rows[] = array(
				'rank' => $i,
				'name' => $corp->getName(),
				'uri' => $uri,
				'portrait' => $corp->getPortraitURL(32),
				'count' => $row['cnt']);
			$i++;
		}

		$smarty->assign('tl_name', 'Corporation');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}

class TopShipList extends TopList
{
	function TopShipList()
	{
		$this->TopList();
	}

	function generate()
	{
		$sqltop = "select count( ind.ind_kll_id) as cnt, ind.ind_shp_id as shp_id
              from kb3_inv_detail ind
			  INNER JOIN kb3_kills kll on (kll.kll_id = ind.ind_kll_id)
	      INNER JOIN kb3_ships shp on ( shp_id = ind.ind_shp_id )";

		$this->setSQLTop($sqltop);

		$sqlbottom .= " group by ind.ind_shp_id order by 1 desc".
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
		global $smarty;
		$this->toplist_->generate();

		while ($row = $this->toplist_->getRow())
		{
			$ship = new Ship($row['shp_id']);
			$shipclass = $ship->getClass();
			$shipclass->getName();
			
			$rows[] = array(
				'rank' => false,
				'name' => $ship->getName(),
				'subname' => $shipclass->getName(),
				'uri' => "?a=invtype&amp;id=".$ship->getExternalID(),
				'portrait' => $ship->getImage(32),
				'count' => $row['cnt']);
		}

		$smarty->assign('tl_name', 'Ship');
		$smarty->assign('tl_type', 'Kills');
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}

class TopWeaponList extends TopList
{
	function TopWeaponList()
	{
		$this->TopList();
	}

	function generate()
	{
		// Does not need to be distinct (i.e. weapon was used by two different
		// pilots on one kill, but in this case using distinct is twice as fast.
		$sql = "select count(distinct ind.ind_kll_id) as cnt, ind.ind_wep_id as itm_id
				from kb3_inv_detail ind
				INNER JOIN kb3_kills kll on (kll.kll_id = ind.ind_kll_id)
				INNER JOIN kb3_invtypes itm on (typeID = ind.ind_wep_id)";

		$this->setSQLTop($sql);
		// since ccps database doesnt have icons for ships this will also fix the ship as weapon bug
		$sqlbottom .=" and (itm.icon != '' OR groupID = 100)".
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
		global $smarty;
		$this->toplist_->generate();

		while ($row = $this->toplist_->getRow())
		{
			$item = new Item($row['itm_id']);
			$rows[] = array(
				'rank' => false,
				'name' => $item->getName(),
				'uri' => "?a=invtype&amp;id=".$item->getID(),
				'icon' => $item->getIcon(32),
				'count' => $row['cnt']);
		}

		$smarty->assign('tl_name', 'Weapon');
		$smarty->assign('tl_type', 'Kills');
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}
