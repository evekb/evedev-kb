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
class KillList
{
	private $qry_ = null;
	private $killpointer_ = 0;
	private $killisk_ = 0;
	private $exclude_scl_ = array();
	private $vic_scl_id_ = array();
	private $regions_ = array();
	private $systems_ = array();
	private $groupby_ = array();
	private $offset_ = 0;
	private $killcounter_ = 0;
	private $realkillcounter_ = 0;
	private $ordered_ = false;
	private $walked = false;
	private $apikill_ = false;
	private $sql_ = '';
	private $sqlinner_ = '';
	private $sqltop_ = '';
	private $sqloutertop_ = '';
	private $sqlouterbottom_ = '';
	private $weekno_ = 0;
	private $monthno_ = 0;
	private $yearno_ = 0;
	private $startweekno_ = 0;
	private $startDate_ = 0;
	private $endDate_ = 0;
	private $executed = false;
	private $plimit_ = 0;
	private $poffset_ = 0;
	private $comb_plt_ = array();
	private $comb_crp_ = array();
	private $comb_all_ = array();
	private $inv_plt_ = array();
	private $inv_crp_ = array();
	private $inv_all_ = array();
	private $vic_plt_ = array();
	private $vic_crp_ = array();
	private $vic_all_ = array();
	private $minkllid_ = 0;
	private $maxkllid_ = 0;
	private $minextkllid_ = 0;
	private $maxextkllid_ = 0;
	private $killpoints_ = 0;
	private $involved_ = 0;
	private $orderby_ = '';
	private $limit_ = 0;
	private $expr = array();

	function KillList()
	{
		$this->qry_ = DBFactory::getDBQuery();
		$this->expr = array("kll.kll_id",
			"kll.kll_timestamp",
			"kll.kll_external_id",
			"plt.plt_name",
			"crp.crp_name",
			"crp.crp_id",
			"ali.all_name",
			"ali.all_id",
			"kll.kll_system_id",
			"kll.kll_ship_id",
			"kll.kll_dmgtaken",
			"kll.kll_victim_id",
			"plt.plt_externalid",
			"kll.kll_crp_id",
			"kll.kll_points",
			"kll.kll_isk_loss",
			"shp.shp_class",
			"shp.shp_id",
			"scl.scl_id",
			"scl.scl_class",
			"scl.scl_value",
			"sys.sys_id",
			"sys.sys_name",
			"sys.sys_sec",
			"fbplt.plt_name as fbplt_name",
			"fbplt.plt_id as fbplt_id",
			"fbplt.plt_externalid as fbplt_externalid",
			"fbcrp.crp_name as fbcrp_name",
			"fbali.all_name as fball_name",
			"fbcrp.crp_id as fbcrp_id",
			"fbali.all_id as fball_id");
	}

	private function makeKllQuery($startdate, $enddate)
	{
		// Construct inner query with kb3_inv_detail, kb3_kills and kb3_ships
		// combined kills and losses are constructed with a union.
		// combined limits both parts of the union then limits the result.
		// This avoids including the whole db before a limit is applied.
		// other tables that add information are then added in the outer query.
		if($this->comb_plt_ || $this->comb_crp_ || $this->comb_all_)
		{
			//TODO: FASTER! FASTER! And without letting mysql fall over.
			$sql = "((SELECT kll.* FROM kb3_kills kll ";
			// ship filter
			if (count($this->exclude_scl_) || count($this->vic_scl_id_))
				$sql .= " INNER JOIN kb3_ships shp on kll.kll_ship_id = shp.shp_id ";
			if($this->comb_plt_ || ($this->comb_crp_ && $this->comb_all_))
			{
				$sql .= " INNER JOIN kb3_inv_detail ind ON ind.ind_kll_id = kll.kll_id ";
				$sql .= " WHERE ";
				if($this->comb_all_) $combP[] = "ind.ind_all_id IN (".implode(',', $this->comb_all_)." ) ";
				if($this->comb_crp_) $combP[] = "ind.ind_crp_id IN (".implode(',', $this->comb_crp_)." ) ";
				if($this->comb_plt_) $combP[] = "ind.ind_plt_id IN (".implode(',', $this->comb_plt_)." ) ";
				$sql .= "( ".implode(" OR ", $combP)." )";
				if($startdate) $sql .=" AND ind.ind_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
				if($enddate) $sql .=" AND ind.ind_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			}
			elseif($this->comb_crp_ )
			{
				$sql .= "INNER JOIN kb3_inv_crp inc ON inc.inc_kll_id = kll.kll_id ";
				$sql .= " WHERE inc.inc_crp_id IN (".
					implode(',', $this->comb_crp_)." ) ";
				if($startdate) $sql .=" AND inc.inc_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
				if($enddate) $sql .=" AND inc.inc_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			}
			else
			{
				$sql .= "INNER JOIN kb3_inv_all ina ON ina.ina_kll_id = kll.kll_id ";
				$sql .= " WHERE ina.ina_all_id IN (".
					implode(',', $this->comb_all_)." ) ";
				if($startdate) $sql .=" AND ina.ina_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
				if($enddate) $sql .=" AND ina.ina_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
			}

			if($this->apikill_)
				$sql .= " AND kll.kll_external_id IS NOT NULL ";

			// System filter
			if (count($this->systems_))
				$sql .= " AND kll.kll_system_id in ( ".implode($this->systems_, ",").")";

			// Get all kills after given kill id (used for feed syndication)
			if ($this->minkllid_)
				$sql .= ' AND kll.kll_id >= '.$this->minkllid_.' ';

			// Get all kills before given kill id (used for feed syndication)
			if ($this->maxkllid_)
				$sql .= ' AND kll.kll_id <= '.$this->maxkllid_.' ';

			// Get all kills after given kill id (used for feed syndication)
			if ($this->minextkllid_)
				$sql .= ' AND kll.kll_external_id >= '.$this->minextkllid_.' ';

			// Get all kills before given kill id (used for feed syndication)
			if ($this->maxextkllid_)
				$sql .= ' AND kll.kll_external_id <= '.$this->maxextkllid_.' ';

			// excluded ship filter
			if (count($this->exclude_scl_))
				$sql .= " AND shp.shp_class not in ( ".implode(",", $this->exclude_scl_)." )";
			// included ship filter
			if (count($this->vic_scl_id_))
				$sql .= " AND shp.shp_class in ( ".implode(",", $this->vic_scl_id_)." ) ";
			event::call('killlist_where_combined_kills', $sql);

			if(($this->comb_all_ || $this->comb_crp_)
					&& count($this->comb_all_) + count($this->comb_crp_) + count($this->comb_plt_) > 1)
							$sql .= " GROUP BY kll.kll_id";
			if ($this->ordered_)
			{
				if (!$this->orderby_)
				{
					if($this->comb_plt_ || ($this->comb_crp_ && $this->comb_all_)) $sql .= " order by ind.ind_timestamp desc";
					else if($this->comb_crp_ ) $sql .= " order by inc.inc_timestamp desc";
					else $sql .= " order by ina.ina_timestamp desc";
				}
				else $sql .= " order by ".$this->orderby_;
			}
			if ($this->limit_) $sql .= " limit ".($this->limit_ + $this->offset_);
			$sql .= " )";
			$sql .= " UNION ";
			$sql .= "(SELECT kll.* FROM kb3_kills kll ";
			// ship filter
			if (count($this->exclude_scl_) || count($this->vic_scl_id_))
				$sql .= " STRAIGHT_JOIN kb3_ships shp on kll.kll_ship_id = shp.shp_id ";
			$sqlwhereop = " WHERE ";

			$sql .= $sqlwhereop." ( ";
			$sqlwhereop = '';

			if ($this->comb_plt_)
			{$sql .= " ".$sqlwhereop." kll.kll_victim_id in ( ".implode(',', $this->comb_plt_)." )"; $sqlwhereop = " OR ";}
			if ($this->comb_crp_)
			{$sql .= " ".$sqlwhereop." kll.kll_crp_id in ( ".implode(',', $this->comb_crp_)." )"; $sqlwhereop = " OR ";}
			if ($this->comb_all_)
				$sql .= " ".$sqlwhereop." kll.kll_all_id in ( ".implode(',', $this->comb_all_)." )";

			$sql .= " ) ";

			if($startdate)
				$sql .= " AND kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
			if($enddate)
				$sql .= " AND kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";

			if($this->apikill_)
				$sql .= " AND kll.kll_external_id IS NOT NULL ";

			// System filter
			if (count($this->systems_))
				$sql .= " AND kll.kll_system_id in ( ".implode($this->systems_, ",").")";

			// Get all kills after given kill id (used for feed syndication)
			if ($this->minkllid_)
				$sql .= ' AND kll.kll_id >= '.$this->minkllid_.' ';

			// Get all kills before given kill id (used for feed syndication)
			if ($this->maxkllid_)
				$sql .= ' AND kll.kll_id <= '.$this->maxkllid_.' ';

			// Get all kills after given kill id (used for feed syndication)
			if ($this->minextkllid_)
				$sql .= ' AND kll.kll_external_id >= '.$this->minextkllid_.' ';

			// Get all kills before given kill id (used for feed syndication)
			if ($this->maxextkllid_)
				$sql .= ' AND kll.kll_external_id <= '.$this->maxextkllid_.' ';

			// excluded ship filter
			if (count($this->exclude_scl_))
				$sql .= " AND shp.shp_class not in ( ".implode(",", $this->exclude_scl_)." )";
			// included ship filter
			if (count($this->vic_scl_id_))
				$sql .= " AND shp.shp_class in ( ".implode(",", $this->vic_scl_id_)." ) ";
			event::call('killlist_where_combined_losses', $sql);
			if ($this->ordered_)
			{
				if (!$this->orderby_)
					$sql .= " order by kll.kll_timestamp desc";
				else $sql .= " order by ".$this->orderby_;
			}
			if ($this->limit_) $sql .= " limit ".($this->limit_ + $this->offset_);
			$sql .= " ) ";
			if ($this->limit_ && $this->offset) $sql .= " limit ".$this->limit_." OFFSET ".$this->offset_;
			$sql .= ") kll ";
		}
		elseif ( $this->inv_plt_ || $this->inv_crp_ || $this->inv_all_)
		{
			$sql = " kb3_kills kll ";
		}
		else
		{
			$sql = " kb3_kills kll ";
		}
		return $sql;
	}

	public function execQuery()
	{
        /* Killlist philosophy
		 *
		 * Killlists are constructed based on whether they set involved parties,
		 * victims or combined. Combined lists look for a party as either
		 * involved or victim. The combined list uses the union of involved
		 * and victim, both limited if a limit is set. Other parts of a killlist
		 * are then added on to this core.
		 *
		 * MySQL will sometimes try to construct the query with alliance, corp,
		 * system or ship class first. Now that timestamp order is removed it
		 * will add every kill to the result, sort and return the top few. To
		 * avoid this the secondary tables use a left or straight join which forces
		 * a particular evaluation order. Since the result is never null the
		 * result is the same.
		 *
		 * Comments and involved count are added in an outer query. This returns
		 * the counts in a single query
         *
         */
		if (!$this->executed)
		{
			$datefilter=$this->getDateFilter();
			$startdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
			$enddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);
			$this->sql_ = '';

			$this->sqlinner_ = $this->makeKllQuery($startdate, $enddate);

			if (!count($this->groupby_) && ($this->comments_ || $this->involved_))
			{
				$this->sqloutertop_ = 'SELECT list.* ';
				if($this->comments_) $this->sqloutertop_ .= ', count(distinct com.id) as comments';
				if($this->involved_) $this->sqloutertop_ .= ', count(distinct ind.ind_order) as inv';
				$this->sqloutertop_ .= ' FROM (';
			}
			if (!count($this->groupby_))
			{
				$this->sqltop_ = 'SELECT ';
				if(count($this->inv_all_) + count($this->inv_crp_) + count($this->inv_plt_) > 1)
					$this->sqltop_ .= ' DISTINCT ';
				$this->sqltop_ .= implode(', ', $this->expr);
				event::call('killlist_select_expr', $this->sqltop_);
			}
			else
			{
				$this->sqltop_ = "SELECT COUNT(1) as cnt, ".implode(",", $this->groupby_);
			}

			$this->sqltop_ .= "    FROM ".$this->sqlinner_." ";

			// LEFT JOIN/STRAIGHT_JOIN is used to force processing after the main tables.
			$this->sqllong_ = "STRAIGHT_JOIN kb3_pilots plt
								ON ( plt.plt_id = kll.kll_victim_id )
							STRAIGHT_JOIN kb3_corps crp
								ON ( crp.crp_id = kll.kll_crp_id )
							STRAIGHT_JOIN kb3_alliances ali
								ON ( ali.all_id = kll.kll_all_id )
							STRAIGHT_JOIN kb3_pilots fbplt
								ON ( fbplt.plt_id = kll.kll_fb_plt_id )
							STRAIGHT_JOIN kb3_inv_detail fb
								ON ( fb.ind_kll_id = kll.kll_id AND fb.ind_plt_id = kll.kll_fb_plt_id )
							STRAIGHT_JOIN kb3_corps fbcrp
								ON ( fbcrp.crp_id = fb.ind_crp_id )
							STRAIGHT_JOIN kb3_alliances fbali
								ON ( fbali.all_id = fb.ind_all_id )
						   ";
			// System
			if(count($this->systems_) || count($this->regions_))
				$this->sql_ .= " INNER JOIN kb3_systems sys
					ON ( sys.sys_id = kll.kll_system_id )";
			else
				$this->sqllong_ .= " STRAIGHT_JOIN kb3_systems sys
					ON ( sys.sys_id = kll.kll_system_id )";

			// regions
			if (count($this->regions_))
			{
				$this->sql_ .= " INNER JOIN kb3_constellations con
	                      ON ( con.con_id = sys.sys_con_id and
			   con.con_reg_id in ( ".implode($this->regions_, ",")." ) )";
			}
			if(count($this->exclude_scl_) || count($this->vic_scl_id_))
				$this->sql_ .= " STRAIGHT_JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
					STRAIGHT_JOIN kb3_ship_classes scl
					ON ( scl.scl_id = shp.shp_class )";
			else
				$this->sqllong_ .= " STRAIGHT_JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
					STRAIGHT_JOIN kb3_ship_classes scl
					ON ( scl.scl_id = shp.shp_class )";

			if($this->comb_plt_ || $this->comb_crp_ || $this->comb_all_)
			{
				// GROUP BY
				if ($this->groupby_) $this->sql_ .= " GROUP BY ".implode(",", $this->groupby_);
				// order/limit
				if ($this->ordered_)
				{
					if (!$this->orderby_)
						$this->sql_ .= " order by kll.kll_timestamp desc";
					else $this->sql_ .= " order by ".$this->orderby_;
				}
			}
			elseif ( $this->inv_plt_ || $this->inv_crp_ || $this->inv_all_)
			{
				if($this->inv_plt_ || ($this->inv_crp_ && $this->inv_all_))
				{
					$this->sql_ .= " INNER JOIN kb3_inv_detail ind ON (ind.ind_kll_id = kll.kll_id)
						WHERE ";
					$invP = array();
					if($this->inv_all_) $invP[] = " ind.ind_all_id in (".implode(',', $this->inv_all_)." ) ";
					if($this->inv_crp_) $invP[] = " ind.ind_crp_id in (".implode(',', $this->inv_crp_)." ) ";
					if($this->inv_plt_) $invP[] = " ind.ind_plt_id in (".implode(',', $this->inv_plt_)." ) ";
					$this->sql_ .= "( ".implode(' OR ', $invP)." )";

					if($startdate) $this->sql_ .=" AND ind.ind_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
					if($enddate) $this->sql_ .=" AND ind.ind_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
				}
				else if($this->inv_all_ )
				{
					$this->sql_ .= " INNER JOIN kb3_inv_all ina ON (ina.ina_kll_id = kll.kll_id)
						WHERE ina.ina_all_id in (".implode(',', $this->inv_all_)." ) ";
					if($startdate) $this->sql_ .=" AND ina.ina_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
					if($enddate) $this->sql_ .=" AND ina.ina_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";

				}
				else if($this->inv_crp_ )
				{
					$this->sql_ .= " INNER JOIN kb3_inv_crp inc ON (inc.inc_kll_id = kll.kll_id)
						WHERE inc.inc_crp_id in (".implode(',', $this->inv_crp_)." ) ";
					if($startdate) $this->sql_ .=" AND inc.inc_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
					if($enddate) $this->sql_ .=" AND inc.inc_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
				}

				// victim filter
				if($this->vic_plt_ || $this->vic_crp_ || $this->vic_all_)
				{
					$this->sql_ .= " AND ( ";

					if ($this->vic_plt_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_victim_id in ( ".implode(',', $this->vic_plt_)." )"; $sqlwhereop = " OR ";}
					if ($this->vic_crp_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_crp_id in ( ".implode(',', $this->vic_crp_)." )"; $sqlwhereop = " OR ";}
					if ($this->vic_all_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_all_id in ( ".implode(',', $this->vic_all_)." )"; $sqlwhereop = " OR ";}

					$this->sql_ .= " ) ";
				}
				if($this->apikill_)
					$this->sql_ .= " AND kll.kll_external_id IS NOT NULL ";

				// System filter
				if (count($this->systems_))
					$this->sql_ .= " AND kll.kll_system_id in ( ".implode($this->systems_, ",").")";

				// Get all kills after given kill id (used for feed syndication)
				if ($this->minkllid_)
					$this->sql_ .= ' AND kll.kll_id >= '.$this->minkllid_.' ';

				// Get all kills before given kill id (used for feed syndication)
				if ($this->maxkllid_)
					$this->sql_ .= ' AND kll.kll_id <= '.$this->maxkllid_.' ';

				// Get all kills after given kill id (used for feed syndication)
				if ($this->minextkllid_)
					$this->sqlinner_ .= ' AND kll.kll_external_id >= '.$this->minextkllid_.' ';

				// Get all kills before given kill id (used for feed syndication)
				if ($this->maxextkllid_)
					$this->sqlinner_ .= ' AND kll.kll_external_id <= '.$this->maxextkllid_.' ';

				// excluded ship filter
				if (count($this->exclude_scl_))
					$this->sql_ .= " AND shp.shp_class not in ( ".implode(",", $this->exclude_scl_)." )";
				// included ship filter
				if (count($this->vic_scl_id_))
					$this->sql_ .= " AND shp.shp_class in ( ".implode(",", $this->vic_scl_id_)." ) ";
				event::call('killlist_where_kill', $this->sql_);

				if ($this->ordered_)
				{
					if (!$this->orderby_)
					{
						if($this->inv_plt_ || ($this->inv_crp_ && $this->inv_all_)) $this->sql_ .= " order by ind.ind";
						elseif($this->inv_all_ ) $this->sql_ .= " order by ina.ina";
						elseif($this->inv_crp_ ) $this->sql_ .=" order by inc.inc";
						else $this->sql_ .= " order by ind.ind";
						$this->sql_ .= "_timestamp desc";
					}
					else $this->sql_ .= " order by ".$this->orderby_;
				}
			}
			else
			{
				$sqlwhereop = " WHERE ";

				if($startdate)
				{
					$this->sql_ .= $sqlwhereop." kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$startdate)."' ";
					$sqlwhereop = " AND ";
				}
				if($enddate)
				{
					$this->sql_ .=" AND kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$enddate)."' ";
					$sqlwhereop = " AND ";
				}

				// victim filter
				if($this->vic_plt_ || $this->vic_crp_ || $this->vic_all_)
				{
					$this->sql_ .= $sqlwhereop." ( ";
					$sqlwhereop = '';

					if ($this->vic_plt_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_victim_id in ( ".implode(',', $this->vic_plt_)." )"; $sqlwhereop = " OR ";}
					if ($this->vic_crp_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_crp_id in ( ".implode(',', $this->vic_crp_)." )"; $sqlwhereop = " OR ";}
					if ($this->vic_all_)
						$this->sql_ .= " ".$sqlwhereop." kll.kll_all_id in ( ".implode(',', $this->vic_all_)." )";

					$this->sql_ .= " ) ";
					$sqlwhereop = ' AND ';
				}
				if($this->apikill_)
				{
					$this->sql_ .= $sqlwhereop." kll.kll_external_id IS NOT NULL ";
					$sqlwhereop = ' AND ';
				}

				// System filter
				if (count($this->systems_))
				{
					$this->sql_ .= $sqlwhereop." kll.kll_system_id in ( ".implode($this->systems_, ",").")";
					$sqlwhereop = ' AND ';
				}

				// Get all kills after given kill id (used for feed syndication)
				if ($this->minkllid_)
				{
					$this->sql_ .= $sqlwhereop.' kll.kll_id >= '.$this->minkllid_.' ';
					$sqlwhereop = ' AND ';
				}

				// Get all kills before given kill id (used for feed syndication)
				if ($this->maxkllid_)
				{
					$this->sql_ .= $sqlwhereop.' kll.kll_id <= '.$this->maxkllid_.' ';
					$sqlwhereop = ' AND ';
				}

				// Get all kills after given kill id (used for feed syndication)
				if ($this->minextkllid_)
				{
					$this->sql_ .= $sqlwhereop.' kll.kll_external_id >= '.$this->minextkllid_.' ';
					$sqlwhereop = ' AND ';
				}

				// Get all kills before given kill id (used for feed syndication)
				if ($this->maxextkllid_)
				{
					$this->sql_ .= $sqlwhereop.' kll.kll_external_id <= '.$this->maxextkllid_.' ';
					$sqlwhereop = ' AND ';
				}

				// excluded ship filter
				if (count($this->exclude_scl_))
				{
					$this->sql_ .= $sqlwhereop." shp.shp_class not in ( ".implode(",", $this->exclude_scl_)." )";
					$sqlwhereop = ' AND ';
				}
				// included ship filter
				if (count($this->vic_scl_id_))
				{
					$this->sql_ .= $sqlwhereop." shp.shp_class in ( ".implode(",", $this->vic_scl_id_)." ) ";
					$sqlwhereop = ' AND ';
				}
				event::call('killlist_where_loss', $this->sql_);
				if ($this->ordered_)
				{
					if (!$this->orderby_)
						$this->sql_ .= " order by kll.kll_timestamp desc";
					else $this->sql_ .= " order by ".$this->orderby_;
				}
			}
			// Enclose query in another to fetch comments and involved parties
			if(!count($this->groupby_) && ($this->comments_ || $this->involved_))
			{
				$this->sqlouterbottom_ .= ") list";
				if($this->involved_) $this->sqlouterbottom_ .= ' join kb3_inv_detail ind ON (ind.ind_kll_id = list.kll_id)';
				if($this->comments_) $this->sqlouterbottom_ .= ' left join kb3_comments com ON (list.kll_id = com.kll_id AND (com.site = "'.KB_SITE.'" OR com.site IS NULL))';
				$this->sqlouterbottom_ .= " group by list.kll_id";
				// Outer query also needs to be ordered, if there's an order
				if ($this->ordered_)
				{
					if (!$this->orderby_) $this->sqlouterbottom_ .= " order by kll_timestamp desc";
					else $this->sqlouterbottom_ .= " order by ".$this->orderby_;
				}
			}
			// If the killlist will be split then only return kills in the range needed.
			if ($this->limit_) $this->sql_ .= " limit ".$this->limit_." OFFSET ".$this->offset_;
			elseif ($this->plimit_)
			{
				$splitq = DBFactory::getDBQuery();;
				$ssql = 'SELECT COUNT(1) as cnt FROM '.$this->sqlinner_.$this->sql_;

				$splitq->execute($ssql);
				$splitr = $splitq->getRow();
				$this->count_ = $splitr['cnt'];
				$this->sql_ .= " limit ".$this->plimit_." OFFSET ".$this->poffset_;
			}
			$this->sql_ = $this->sqloutertop_.$this->sqltop_.$this->sqllong_.$this->sql_.$this->sqlouterbottom_;
			$this->sql_ .= " /* kill list */";
			//			die($this->sql_);
			$this->qry_->execute($this->sql_);
			if(!$this->plimit_ || $this->limit_) $this->count_ = $this->qry_->recordcount();
			$this->executed = true;
		}
	}

	public function getRow()
	{
		$this->execQuery();
		if ($this->plimit_ && $this->killcounter_ >= $this->plimit_)
		{
		// echo $this->plimit_." ".$this->killcounter_;
			return null;
		}

		$skip = $this->poffset_ - $this->killpointer_;
		if ($skip > 0)
		{
			for ($i = 0; $i < $skip; $i++)
			{
				$this->killpointer_++;
				$row = $this->qry_->getRow();
			}
		}

		$row = $this->qry_->getRow();

		return $row;
	}

	public function getKill()
	{
		$this->execQuery();
		if ($this->plimit_ && $this->killcounter_ >= $this->plimit_)
		{
		// echo $this->plimit_." ".$this->killcounter_;
			return null;
		}

		if($this->count_ == $this->qry_->recordCount() ) $skip = $this->poffset_ - $this->killpointer_;
		else $skip = 0;
		if ($skip > 0)
		{
			for ($i = 0; $i < $skip; $i++)
			{
				$this->killpointer_++;
				$row = $this->qry_->getRow();
			}
		}

		$row = $this->qry_->getRow();
		if ($row)
		{
			$this->killcounter_++;
			if ($row['scl_id'] != 2 && $row['scl_id'] != 3 && $row['scl_id'] != 11)
				$this->realkillcounter_++;
			if ($this->walked == false)
			{
				$this->killisk_ += $row['kll_isk_loss'];
				$this->killpoints_ += $row['kll_points'];
			}

			$kill = new KillWrapper($row['kll_id']);
			$arr = array('victimexternalid' => (int)$row['plt_externalid'],
				'victimname' => $row['plt_name'],
				'victimid' => (int)$row['kll_victim_id'],
				'victimcorpid' => (int)$row['crp_id'],
				'victimcorpname' => $row['crp_name'],
				'victimallianceid' => (int)$row['all_id'],
				'victimalliancename' => $row['all_name'],
				'victimshipvalue' => $row['scl_value'],
				'fbpilotid' => (int)$row['fbplt_id'],
				'fbpilotexternalid' => (int)$row['fbplt_externalid'],
				'fbcorpid' => (int)$row['fbcrp_id'],
				'fballianceid' => (int)$row['fball_id'],
				'fbpilotname' => $row['fbplt_name'],
				'fbcorpname' => $row['fbcrp_name'],
				'fballiancename' => $row['fball_name'],
				'victimshipid' => (int)$row['shp_id'],
				'dmgtaken' => $row['kll_dmgtaken'],
				'timestamp' => $row['kll_timestamp'],
				'solarsystemid' => (int)$row['sys_id'],
				'solarsystemname' => $row['sys_name'],
				'solarsystemsecurity' => $row['sys_sec'],
				'externalid' => (int)$row['kll_external_id'],
				'killpoints' => (int)$row['kll_points'],
				'iskloss' => (float)$row['kll_isk_loss']
				);
			$kill->setArray($arr);
			//Set the involved party count if it is known
			if($this->involved_) $kill->setInvolvedPartyCount((int)$row['inv']);
			//Set the comment count if it is known
			if($this->comments_) $kill->setCommentCount((int)$row['comments']);
			if (isset($this->_tag))
			{
				$kill->_tag = $this->_tag;
			}
			return $kill;
		}
		else
		{
			$this->walked = true;
			return null;
		}
	}

	public function getAllKills()
	{
		while ($this->getKill())
		{
		}
		$this->rewind();
	}

	/**
	 * Add an expression to the SQL query.
	 *
	 * This function can be used if an expression needs to be added to the
	 * query, e.g. ship name
	 *
	 * @param string $expr The expression to add
	 */
	public function addExpression($expr)
	{
		$this->expr[] = strval($expr);
		return count($this->expr);
	}

	/**
	 * Add an expression to the SQL query.
	 *
	 * This function can be used to remove an expression. If $expr is true, or
	 * omitted, all expressions will be removed.
	 *
	 * @param string $expr The expression to remove
	 *
	 * @param string $expr
	 * @return integer The number of expressions remaining.
	 */
	public function delExpression($expr = true)
	{
		if($expr === true) $expr = array();
		else unset($this->expr[array_search($expr, $this->expr)]);
		return count($this->expr);
	}

	public function addInvolved($obj)
	{
		if(is_array($obj)) $test = reset($obj);
		else $test = &$obj;
		if($test instanceof Pilot) involved::add($this->inv_plt_, $obj);
		elseif($test instanceof Corporation) involved::add($this->inv_crp_, $obj);
		elseif($test instanceof Alliance) involved::add($this->inv_all_, $obj);
		else trigger_error ("Parameter passed was not a valid object.", E_USER_WARNING);
	}

	public function addInvolvedPilot($pilot)
	{
		involved::add($this->inv_plt_, $pilot);
	}

	public function addInvolvedCorp($corp)
	{
		involved::add($this->inv_crp_, $corp);
	}

	public function addInvolvedAlliance($alliance)
	{
		involved::add($this->inv_all_, $alliance);
	}

	public function addVictim($obj)
	{
		if(is_array($obj)) $test = reset($obj);
		else $test = &$obj;
		if($test instanceof Pilot) involved::add($this->vic_plt_, $obj);
		elseif($test instanceof Corporation) involved::add($this->vic_crp_, $obj);
		elseif($test instanceof Alliance) involved::add($this->vic_all_, $obj);
		else trigger_error ("Parameter passed was not a valid object.", E_USER_WARNING);
	}

	public function addVictimPilot($pilot)
	{
		involved::add($this->vic_plt_, $pilot);
	}

	public function addVictimCorp($corp)
	{
		involved::add($this->vic_crp_, $corp);
	}

	public function addVictimAlliance($alliance)
	{
		involved::add($this->vic_all_, $alliance);
	}

	public function addCombined($obj)
	{
		if(is_array($obj)) $test = reset($obj);
		else $test = &$obj;
		if($test instanceof Pilot) involved::add($this->comb_plt_, $obj);
		elseif($test instanceof Corporation) involved::add($this->comb_crp_, $obj);
		elseif($test instanceof Alliance) involved::add($this->comb_all_, $obj);
		else trigger_error ("Parameter passed was not a valid object.", E_USER_WARNING);
	}

	public function addCombinedPilot($pilot)
	{
		involved::add($this->comb_plt_, $pilot);
	}

	public function addCombinedCorp($corp)
	{
		involved::add($this->comb_crp_, $corp);
	}

	public function addCombinedAlliance($alliance)
	{
		involved::add($this->comb_all_, $alliance);
	}

	public function addVictimShipClass($shipclass)
	{
		if(is_numeric($shipclass)) $this->vic_scl_id_[] = $shipclass;
		else $this->vic_scl_id_[] = $shipclass->getID();
	}

	public function addRegion($region)
	{
		if(is_numeric($region)) $this->regions_[] = $region;
		else $this->regions_[] = $region->getID();
	}

	public function addSystem($system)
	{
		if(is_numeric($system)) $this->systems_[] = $system;
		else $this->systems_[] = $system->getID();
	}

	public function addGroupBy($groupby)
	{
		array_push($this->groupby_, $groupby);
	}

	public function setPageSplitter($pagesplitter)
	{
		if (isset($_GET['page'])) $page = $_GET['page'];
		else $page = 1;
		$this->plimit_ = $pagesplitter->getSplit();
		$this->poffset_ = ($page * $this->plimit_) - $this->plimit_;
	}

	public function setPageSplit($split)
	{
		if (isset($_GET['page'])) $page = $_GET['page'];
		else $page = 1;
		$this->plimit_ = $split;
		$this->poffset_ = ($page * $this->plimit_) - $this->plimit_;
	}

	/**
	 * Filter results by week. Requires the year to also be set.
	 */
	public function setWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->weekno_ = 1;
		if($weekno >53) $this->weekno_ = 53;
		else $this->weekno_ = $weekno;
	}

	/**
	 * Filter results by year.
	 */
	public function setYear($yearno)
	{
	// 1970-2038 is the allowable range for the timestamp code used
	// Needs to be revisited in the next 30 years
		$yearno = intval($yearno);
		if($yearno < 1970) $this->yearno_ = 1970;
		if($yearno > 2038) $this->yearno_ = 2038;
		else $this->yearno_ = $yearno;
	}

	/**
	 * Filter results by starting week. Requires the year to also be set.
	 */
	public function setStartWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->startweekno_ = 1;
		if($weekno >53) $this->startweekno_ = 53;
		else $this->startweekno_ = $weekno;
	}

	/**
	 * Filter results by starting date/time.
	 */
	public function setStartDate($timestamp)
	{
	// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->startDate_ = $timestamp;
	}

	/**
	 * Filter results by ending date/time.
	 */
	public function setEndDate($timestamp)
	{
	// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->endDate_ = $timestamp;
	}

	/**
	 * Convert given date ranges to SQL date range.
	 * @return mixed string containing SQL date filter.
	 */
	public function getDateFilter()
	{
		$sql = '';
		$qstartdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
		$qenddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);
		if($this->inv_all_ || $this->inv_crp_ || $this->inv_plt_)
		{
			if($qstartdate) $sql .= " kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' AND ";
			if($qenddate) $sql .= " kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' AND ";
			if($qstartdate) $sql .= " ind.ind_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' ";
			if($qstartdate && $qenddate) $sql .= " AND ";
			if($qenddate) $sql .= " ind.ind_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' ";
		}
		else
		{
			if($qstartdate) $sql .= " kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' ";
			if($qstartdate && $qenddate) $sql .= " AND ";
			if($qenddate) $sql .= " kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' ";
		}
		return $sql;
	}

	public function setLimit($limit)
	{
		$this->limit_ = $limit;
	}
	/**
	 * Only return kills with an external id set.
	 */
	public function setAPIKill($hasid = true)
	{
		$this->apikill_ = $hasid;
	}

	public function setOrderBy($orderby)
	{
		$this->orderby_ = $orderby;
	}

	public function setMinKllID($id)
	{
		$this->minkllid_ = $id;
	}

	public function setMaxKllID($id)
	{
		$this->maxkllid_ = $id;
	}

	public function setMinExtID($id)
	{
		$this->minextkllid_ = $id;
	}

	public function setMaxExtID($id)
	{
		$this->maxextkllid_ = $id;
	}

	public function getCount()
	{
		$this->execQuery();
		return $this->count_;
	}

	public function getRealCount()
	{
		$this->getCount();
		return $this->realkillcounter_;
	}

	public function getISK()
	{
		$this->execQuery();
		return $this->killisk_;
	}

	public function getPoints()
	{
		return intval($this->killpoints_);
	}

	public function rewind()
	{
		$this->qry_->rewind();
		$this->killcounter_ = 0;
	}

	public function setPodsNoobShips($flag)
	{
		if (!$flag)
		{
			array_push($this->exclude_scl_, 2);
			array_push($this->exclude_scl_, 3);
			array_push($this->exclude_scl_, 11);
		}

		if ($flag)
		{
			if (($idx = array_search(2, $this->exclude_scl_)) !== FALSE)
				unset($this->exclude_scl_[$idx]);
			if (($idx = array_search(3, $this->exclude_scl_)) !== FALSE)
				unset($this->exclude_scl_[$idx]);
			if (($idx = array_search(11, $this->exclude_scl_)) !== FALSE)
				unset($this->exclude_scl_[$idx]);
		}
	}

	public function setOrdered($flag)
	{
		$this->ordered_ = $flag;
	}

	public function tag($string)
	{
		if ($string == '')
		{
			$this->_tag = null;
		}
		else
		{
			$this->_tag = $string;
		}
	}

	// Add a comment count to the killlist SQL
	public function setCountComments($comments = true)
	{
		$this->comments_ = $comments;
	}

	// Add an involved party count to the killlist SQL
	public function setCountInvolved($setinv = true)
	{
		$this->involved_ = $setinv;
	}
}

class CombinedKillList extends KillList
{
	function CombinedKillList()
	{
	// please only load killlists here
		$this->lists = func_get_args();
		if (!is_array($this->lists))
		{
			trigger_error('No killlists given to CombinedKillList', E_USER_ERROR);
		}
		$this->kills = false;
	}

	public function buildKillArray()
	{
		$this->kills = array();
		foreach ($this->lists as $killlist)
		{
		// reset the list
			$killlist->rewind();

			// load all kills and store them in an array
			while ($kill = $killlist->getKill())
			{
			// take sure that if there are multiple kills all are stored
				if (isset($this->kills[$kill->timestamp_]))
				{
					$this->kills[$kill->timestamp_.rand()] = $kill;
				}
				else
				{
					$this->kills[$kill->timestamp_] = $kill;
				}
			}
		}

		// sort the kills by time
		krsort($this->kills);
	}

	public function getKill()
	{
	// on the first request we load up our kills
		if ($this->kills === false)
		{
			$this->buildKillArray();
			if (is_numeric($this->poffset_) && is_numeric($this->plimit_))
				$this->kills = array_slice($this->kills, $this->poffset_, $this->plimit_);
		}

		// once all kills are out this will return null so we're fine
		return array_shift($this->kills);
	}

	public function rewind()
	{
	// intentionally left empty to overload the standard handle
	}

	public function getCount()
	{
		$count = 0;
		foreach ($this->lists as $killlist)
		{
			$count += $killlist->getCount();
		}
		return $count;
	}

	public function getRealCount()
	{
		$count = 0;
		foreach ($this->lists as $killlist)
		{
			$count += $killlist->getRealCount();
		}
		return $count;
	}

	public function getISK()
	{
		$sum = 0;
		foreach ($this->lists as $killlist)
		{
			$sum += $killlist->getISK();
		}
		return $sum;
	}

	public function getPoints()
	{
		$sum = 0;
		foreach ($this->lists as $killlist)
		{
			$sum += $killlist->getPoints();
		}
		return $sum;
	}
}