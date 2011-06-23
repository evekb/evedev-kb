<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_Base
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

	/**
	 * Set the maximum number of results to show in the TopList.
	 *
	 *  @param integer $limit Maximum number of kills to show.
	 *
	 *  @return integer value TopList was set to.
	 */
	function setLimit($limit = 10)
	{
		$this->limit = intval($limit);
		return $this->limit;
	}
	/**
	 * Include or exclude pods/noob ships/shuttles.
	 *
	 *  @param boolean $flag true to show P/N/S, false to remove.
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

	/**
	 * Remove structures.
	 *
	 * Note that these class types are hard coded so will need modification
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

	/**
	 * Set a victim ship class to include.
	 *
	 * If this is set then only ship classes set will be in the output.
	 *
	 * @param integer $shipclass ID of a ship class.
	 */
	function addVictimShipClass($shipclass)
	{
		if(!is_numeric($shipclass)) $scl_id = $shipclass->getID();
		else $scl_id = intval($shipclass);
		$this->inc_vic_scl[$scl_id] = $scl_id;
		unset ($this->exc_vic_scl[$scl_id]);
	}

	/**
	 * Set a victim ship class to exclude.
	 *
	 * If this is set then only ship classes not set will be in the output.
	 *
	 * @param integer $shipclass ID of a ship class
	 */
	function excludeVictimShipClass($shipclass)
	{
		if(!is_numeric($shipclass)) $scl_id = $shipclass->getID();
		else $scl_id = intval($shipclass);
		$this->exc_vic_scl[$scl_id] = $scl_id;
		unset ($this->inc_vic_scl[$scl_id]);
	}

	/**
	 * Set a victim ship type to include.
	 *
	 * If this is set then only ship types set will be in the output.
	 *
	 * @param integer $ship ID of a shiptype
	 */
	function addVictimShip($ship)
	{
		$ship = intval($ship);
		$this->inc_vic_shp[$ship] = $ship;
		unset ($this->exc_vic_shp[$ship]);
	}

	/**
	 * Set a victim ship type to exclude.
	 *
	 * If this is set then only ship types not set will be in the output.
	 *
	 * @param integer $ship ID of a shiptype
	 */
	function excludeVictimShip($ship)
	{
		$ship = intval($ship);
		$this->exc_vic_shp[$ship] = $ship;
		unset ($this->inc_vic_shp[$ship]);
	}

	/**
	 * @param integer|Region $region
	 */
	function addRegion($region)
	{
		if(is_numeric($region)) array_push($this->regions_, $region);
		else array_push($this->regions_, $region->getID());
	}

	/**
	 * @param integer|SolarSystem $system
	 */
	function addSystem($system)
	{
		if(is_numeric($system)) array_push($this->systems_, $system);
		else array_push($this->systems_, $system->getID());
	}

	function addGroupBy($groupby)
	{
		array_push($this->groupby_, $groupby);
	}

	/**
	 * @param PageSplitter $pagesplitter
	 */
	function setPageSplitter($pagesplitter)
	{
		if (isset($_GET['page'])) $page = $_GET['page'];
		else $page = 1;
		$this->plimit_ = $pagesplitter->getSplit();
		$this->poffset_ = ($page * $this->plimit_) - $this->plimit_;
	}

	/**
	 * @param integer $weekno
	 */
	function setWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->weekno_ = 1;
		if($weekno >53) $this->weekno_ = 53;
		else $this->weekno_ = $weekno;
	}

	/**
	 * @param integer $monthno
	 */
	function setMonth($monthno)
	{
		$monthno = intval($monthno);
		if($monthno < 1) $this->monthno_ = 1;
		if($monthno > 12) $this->monthno_ = 12;
		else $this->monthno_ = $monthno;
	}

	/**
	 * @param integer $yearno
	 */
	function setYear($yearno)
	{
	// 1970-2038 is the allowable range for the timestamp code used
	// Needs to be revisited in the next 30 years
		$yearno = intval($yearno);
		if($yearno < 1970) $this->yearno_ = 1970;
		if($yearno > 2038) $this->yearno_ = 2038;
		else $this->yearno_ = $yearno;
	}

	/**
	 * @param integer $weekno
	 */
	function setStartWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->startweekno_ = 1;
		if($weekno >53) $this->startweekno_ = 53;
		else $this->startweekno_ = $weekno;
	}

	/**
	 * @param string $timestamp
	 */
	function setStartDate($timestamp)
	{
	// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->startDate_ = $timestamp;
	}

	/**
	 * @param string $timestamp
	 */
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
			{
				$this->sql_ .= "\n\tINNER JOIN(SELECT inc_kll_id as kll_id
					FROM kb3_inv_crp
					WHERE ";
				if($this->getDateFilter())
					$this->sql_ .= $this->getDateFilter("inc_timestamp")." AND ";
				$this->sql_ .= "inc_crp_id IN (".implode(",", $this->inv_crp).")".
					" GROUP BY kll_id) inv ON (inv.kll_id = ind.ind_kll_id)";

			}
			else if ($this->inv_all)
			{
				$this->sql_ .= "\n\tINNER JOIN (SELECT ina_kll_id as kll_id
					FROM kb3_inv_all
					WHERE ";
				if($this->getDateFilter())
					$this->sql_ .= $this->getDateFilter("ina_timestamp")." AND ";
				$this->sql_ .= "ina_all_id IN (".implode(",", $this->inv_all).")".
					" GROUP BY kll_id) inv ON (inv.kll_id = ind.ind_kll_id)";
			}
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
