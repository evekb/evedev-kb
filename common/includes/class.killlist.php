<?php
require_once('class.kill.php');
require_once('class.pagesplitter.php');

class KillList
{
    function KillList()
    {
        $this->qry_ = new DBQuery();
        $this->killpointer_ = 0;
        $this->kills_ = 0;
        $this->losses_ = 0;
        $this->killisk_ = 0;
        $this->lossisk_ = 0;
        $this->exclude_scl_ = array();
        $this->vic_scl_id_ = array();
        $this->regions_ = array();
        $this->systems_ = array();
        $this->groupby_ = array();
        $this->offset_ = 0;
        $this->killcounter_ = 0;
        $this->realkillcounter_ = 0;
        $this->ordered_ = false;
        $this->walked = false;
		$this->apikill_ = false;
    }

    function execQuery()
    {
        /* Killlist philosophy
         * For EDK the most common uses of a killlist require date ordering
         * based on kb3_kills. This is then restricted by joins with other
         * tables. MySQL will decide on a query interpretation based on rows
         * returned in each step. For EDK this can be very suboptimal as the
         * most important restriction is by date or number of kills - both
         * based on kb3_kills - which is usually followed by ordering by date.
         *
         * The killlist is constructed so as to give MySQL the least opportunity
         * to carry out the query in a slow manner. kb3_kills is used as the
         * main table. Where possible it is only joined with tables that do not
         * restrict the output. Comment count and involved count are in an outer
         * query to avoid the use of distinct on the whole kb3_kills table and
         * the potential ordering of a query with kb3_inv_detail first.
         *
         */
        if (!$this->qry_->executed_)
        {
			$datefilter=$this->getDateFilter();
            $this->sql_ = '';
            if (!count($this->groupby_) && ($this->comments_ || $this->involved_))
            {
                $this->sql_ .= 'SELECT list.* ';
                if($this->comments_) $this->sql_ .= ', count(distinct com.id) as comments';
                if($this->involved_) $this->sql_ .= ', max(ind.ind_order) + 1 as inv';
                $this->sql_ .= ' FROM (';
            }
            if (!count($this->groupby_))
            {
                if($this->summary_)
                {
					$this->sql_ .= 'select distinct kll.kll_id, kll.kll_ship_id,
							kll.kll_points, kll.kll_isk_loss,
						shp.shp_class, shp.shp_name,
					shp.shp_externalid, shp.shp_id,
					scl.scl_id, scl.scl_class, scl.scl_value';
                    if(count($this->systems_))
                      $this->sql_ .= ', sys.sys_name, sys.sys_sec';
                }
                else
				{
					$this->sql_ .= 'select distinct kll.kll_id, kll.kll_timestamp, kll.kll_external_id,
								plt.plt_name, crp.crp_name, crp.crp_id,
								ali.all_name, ali.all_id,
								kll.kll_system_id, kll.kll_ship_id,
								kll.kll_victim_id, plt.plt_externalid,
								kll.kll_crp_id, kll.kll_points, kll.kll_isk_loss,
								shp.shp_class, shp.shp_name,
								shp.shp_externalid, shp.shp_id,
								scl.scl_id, scl.scl_class, scl.scl_value,
								sys.sys_name, sys.sys_sec,
								fbplt.plt_name as fbplt_name,
								fbplt.plt_externalid as fbplt_externalid,
								fbcrp.crp_name as fbcrp_name,
								fbali.all_name as fball_name';
				}
            }


            if (count($this->groupby_))
            {
                $this->sql_ .= "SELECT COUNT(1) as cnt, ".implode(",", $this->groupby_);
            }
            if (config::get('ship_values'))
            {
                $this->sql_ .= ', ksv.shp_value';
            }

            $this->sql_ .= "    FROM kb3_kills kll ";
            // MySQL tends to pick an index for this query poorly so specify one.
            //if($this->ordered_ && !$this->orderby_) $this->sql_ .= "use index (".$timeindex.") ";

            $this->sql_ .= "INNER JOIN kb3_ships shp
	  		      ON ( shp.shp_id = kll.kll_ship_id )
	  		   INNER JOIN kb3_ship_classes scl
	  		      ON ( scl.scl_id = shp.shp_class )";
            if (config::get('ship_values'))
            {
                $this->sql_ .= ' left join kb3_ships_values ksv ON (kll.kll_ship_id = ksv.shp_id) ';
            }

            if (!$this->summary_) $this->sql_ .= "INNER JOIN kb3_pilots plt
								ON ( plt.plt_id = kll.kll_victim_id )
							INNER JOIN kb3_corps crp
								ON ( crp.crp_id = kll.kll_crp_id )
							INNER JOIN kb3_alliances ali
								ON ( ali.all_id = kll.kll_all_id )
							INNER JOIN kb3_pilots fbplt
								ON ( fbplt.plt_id = kll.kll_fb_plt_id )
							INNER JOIN kb3_inv_detail fb
								ON ( fb.ind_kll_id = kll.kll_id AND fb.ind_plt_id = kll.kll_fb_plt_id )
							INNER JOIN kb3_corps fbcrp
								ON ( fbcrp.crp_id = fb.ind_crp_id )
							INNER JOIN kb3_alliances fbali
								ON ( fbali.all_id = fb.ind_all_id )
                           ";
            if(!$this->summary_ || count($this->systems_))
              $this->sql_ .= " INNER JOIN kb3_systems sys
                              ON ( sys.sys_id = kll.kll_system_id )";

            // regions
            if (count($this->regions_))
            {
                $this->sql_ .= " INNER JOIN kb3_constellations con
	                      ON ( con.con_id = sys.sys_con_id and
			   con.con_reg_id in ( ".implode($this->regions_, ",")." ) )";
            }

            if( $datefilter == "" && ( $this->inv_plt_ || $this->inv_crp_ || $this->inv_all_))
            {
				$this->sql_ .= " INNER JOIN kb3_inv_detail ind ON (kll.kll_id = ind.ind_kll_id AND (";
				$invop = "";
				if($this->inv_plt_ )
				{
					$this->sql_ .= " ind.ind_plt_id IN (".
						implode(',', $this->inv_plt_)." ) ";
					$invop = "OR";
				}
				if($this->inv_crp_ )
				{
					$this->sql_ .= $invop." ind.ind_crp_id IN (".
						implode(',', $this->inv_crp_)." ) ";
					$invop = "OR";
				}
				if($this->inv_all_ )
				{
					$this->sql_ .= $invop." ind.ind_all_id IN (".
						implode(',', $this->inv_all_)." ) ";
				}
				$this->sql_ .= ") ) ";
            }
            // The first argument after WHERE affects sql optimisation so check
            // each possible argument to see whether it should use AND or WHERE
            // rather than start with WHERE 1
            $sqlwhereop = ' WHERE ';
            // Date filter
            if($datefilter != "")
            {
                $this->sql_ .= $sqlwhereop.$datefilter;
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
            // victim filter
            if($this->vic_plt_ || $this->vic_crp_ || $this->vic_all_)
            {
				$this->sql_ .= $sqlwhereop." ( ";
				$sqlwhereop = "";

				if ($this->vic_plt_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_victim_id in ( ".implode(',', $this->vic_plt_)." )"; $sqlwhereop = " OR ";}
				if ($this->vic_crp_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_crp_id in ( ".implode(',', $this->vic_crp_)." )"; $sqlwhereop = " OR ";}
				if ($this->vic_all_)
					{$this->sql_ .= " ".$sqlwhereop." kll.kll_all_id in ( ".implode(',', $this->vic_all_)." )"; $sqlwhereop = " OR ";}
					
				$sqlwhereop = ' AND ';
				$this->sql_ .= " ) ";
            }

            // related kills
            if ($this->related_)
            {
                $rqry = new DBQuery();
                $rsql = "select kll_timestamp, kll_system_id from kb3_kills where kll_id = ".$this->related_;

                $rqry->execute($rsql);
                $rrow = $rqry->getRow();

                $this->sql_ .= $sqlwhereop."kll.kll_timestamp <=
			       ".(date('Y-m-d H:i:s',strtotime($rrow['kll_timestamp']) + 60 * 60)).
	                   " and kll.kll_timestamp >=
			       ".(date('Y-m-d H:i:s',strtotime($rrow['kll_timestamp']) - 60 * 60)).
                            " and kll.kll_system_id = ".$rrow['kll_system_id'];
                $sqlwhereop = " AND ";
            }
            // Get all kills after given kill id (used for feed syndication)
            if ($this->minkllid_)
            {
                $this->sql_ .= $sqlwhereop.'kll.kll_id >= '.$this->minkllid_.' ';
                $sqlwhereop = ' AND ';
            }

            // Get all kills before given kill id (used for feed syndication)
            if ($this->maxkllid_)
            {
                $this->sql_ .= $sqlwhereop.'kll.kll_id <= '.$this->maxkllid_.' ';
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
            // involved filter
            // involved parties who aren't also the victim
            // Only use a join if no other limits are used.
            if($this->inv_plt_ || $this->inv_crp_ || $this->inv_all_)
            {
				if($this->inv_all_ && $this->inv_crp_)
				{
					$this->sql_ .= $sqlwhereop." ( ";
					$sqlwhereop = '';
				}
				// No need to check the involved pilot since they can't shoot themself and get a killmail.
                if ($this->inv_all_)
                {
                    $this->sql_ .= $sqlwhereop." kll.kll_all_id NOT IN ( ".implode(',', $this->inv_all_)." )";
                    $sqlwhereop = ' AND ';
                }
                if ($this->inv_crp_)
                {
                    $this->sql_ .= $sqlwhereop." kll.kll_crp_id NOT IN ( ".implode(',', $this->inv_crp_)." )";
                    $sqlwhereop = ' AND ';
                }
				if($this->inv_all_ && $this->inv_crp_) $this->sql_ .= ") ";
                if( $datefilter != "")
                {
                    $this->sql_ .= $sqlwhereop." EXISTS (SELECT 1 FROM kb3_inv_detail exind ".
						 "WHERE kll.kll_id = exind.ind_kll_id AND (";
					$sqlwhereop = "";
                    if ($this->inv_all_)
                        {$this->sql_ .= $sqlwhereop." exind.ind_all_id in ( ".implode(',', $this->inv_all_)." ) ";$sqlwhereop = " OR ";}
                    if ($this->inv_crp_)
                        {$this->sql_ .= $sqlwhereop." exind.ind_crp_id in ( ".implode(',', $this->inv_crp_)." ) ";$sqlwhereop = " OR ";}
                    if ($this->inv_plt_)
                        $this->sql_ .= $sqlwhereop." exind.ind_plt_id in ( ".implode(',', $this->inv_plt_)." ) ";
                    $this->sql_ .= " ) ) ";
                    $sqlwhereop = " AND ";
                }
            }
            // This may scan the entire table if LIMIT or a date filter are not used.
			// Some queries combining groups will be slow on mysql versions < 5 without index merge.
            if($this->comb_plt_ || $this->comb_crp_ || $this->comb_all_)
            {
                $this->sql_ .= $sqlwhereop." ( ";
                $sqlwhereop = " OR ";
				$sqlexistop = "EXISTS ( SELECT 1 FROM kb3_inv_detail cind WHERE kll.kll_id = cind.ind_kll_id AND (";
                if($this->comb_plt_)
                {
                        $this->sql_ .= $sqlexistop." cind.ind_plt_id in ( ".implode(',', $this->comb_plt_)." )";
                        $sqlexistop = " OR ";
                }
                if($this->comb_crp_)
                {
                        $this->sql_ .= $sqlexistop." cind.ind_crp_id in ( ".implode(',', $this->comb_crp_)." )";
                        $sqlexistop = " OR ";
                }
                if($this->comb_all_)
                {
                        $this->sql_ .= $sqlexistop." cind.ind_all_id in ( ".implode(',', $this->comb_all_)." )";
                }
				$this->sql_ .= " ) )";
                if($this->comb_plt_)
                {
                        $this->sql_ .= $sqlwhereop."kll.kll_victim_id in ( ".implode(',', $this->comb_plt_)." ) ";
                        $sqlwhereop = " OR ";
                }
                if($this->comb_crp_)
                {
                        $this->sql_ .= $sqlwhereop."kll.kll_crp_id in ( ".implode(',', $this->comb_crp_)." ) ";
                        $sqlwhereop = " OR ";
                }
                if($this->comb_all_)
                {
                        $this->sql_ .= $sqlwhereop."kll.kll_all_id in ( ".implode(',', $this->comb_all_)." ) ";
                }
                $this->sql_ .= " )";
                $sqlwhereop = " AND ";
            }

            // GROUP BY
            if ($this->groupby_) $this->sql_ .= " GROUP BY ".implode(",", $this->groupby_);
            // order/limit
            if ($this->ordered_)
            {
                if (!$this->orderby_) $this->sql_ .= " order by kll_timestamp desc";
                else $this->sql_ .= " order by ".$this->orderby_;
            }
            if ($this->limit_) $this->sql_ .= " limit ".$this->limit_." OFFSET ".$this->offset_;
            // Enclose query in another to fetch comments and involved parties
            if(!count($this->groupby_) && ($this->comments_ || $this->involved_))
            {
                $this->sql_ .= ") list";
                if($this->involved_) $this->sql_ .= ' join kb3_inv_detail ind ON (ind.ind_kll_id = list.kll_id)';
                if($this->comments_) $this->sql_ .= ' left join kb3_comments com ON (list.kll_id = com.kll_id)';
                $this->sql_ .= " group by list.kll_id";
                // Outer query also needs to be ordered, if there's an order
                if ($this->ordered_)
                {
                    if (!$this->orderby_) $this->sql_ .= " order by kll_timestamp desc";
                    else $this->sql_ .= " order by ".$this->orderby_;
                }
            }
            $this->sql_ .= " -- kill list";
            $this->qry_->execute($this->sql_);
        }
    }

    function getRow()
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

    function getKill()
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
        if ($row)
        {
            $this->killcounter_++;
            if ($row['scl_class'] != 2 && $row['scl_class'] != 3 && $row['scl_class'] != 11)
                $this->realkillcounter_++;

           if (config::get('ship_values'))
            {
                if ($row['shp_value'])
                {
                    $row['scl_value'] = $row['shp_value'];
                }
            }

            if ($this->walked == false)
            {
                $this->killisk_ += $row['kll_isk_loss'];
                $this->killpoints_ += $row['kll_points'];
            }

            $kill = new Kill($row['kll_id']);
            $kill->setTimeStamp($row['kll_timestamp']);
            $kill->setSolarSystemName($row['sys_name']);
            $kill->setSolarSystemSecurity($row['sys_sec']);
            $kill->setVictimName($row['plt_name']);
            $kill->setVictimCorpName($row['crp_name']);
            $kill->setVictimCorpID($row['crp_id']);
            $kill->setVictimAllianceName($row['all_name']);
            $kill->setVictimAllianceID($row['all_id']);
            $kill->setVictimShipName($row['shp_name']);
            $kill->setVictimShipExternalID($row['shp_externalid']);
            $kill->setVictimShipClassName($row['scl_class']);
            $kill->setVictimShipValue($row['scl_value']);
            $kill->setVictimID($row['kll_victim_id']);
            $kill->setFBPilotName($row['fbplt_name']);
            $kill->setFBCorpName($row['fbcrp_name']);
            $kill->setFBAllianceName($row['fbcrp_name']);
            $kill->setKillPoints($row['kll_points']);
			$kill->setExternalID($row['kll_external_id']);
			$kill->setISKLoss($row['kll_isk_loss']);
			$kill->plt_ext_ = $row['plt_externalid'];
            $kill->fbplt_ext_ = $row['fbplt_externalid'];
            $kill->_sclid = $row['scl_id'];
            $kill->_shpid = $row['shp_id'];
            //Set the involved party count if it is known
            if($this->involved_) $kill->setInvolvedPartyCount($row['inv']);
            //Set the comment count if it is known
            if($this->comments_) $kill->setCommentCount($row['comments']);
            if ($this->_tag)
            {
                $kill->_tag = $this->_tag;
            }
            if (config::get('kill_classified'))
            {
                if ($kill->isClassified())
                {
                    $kill->setSolarSystemName('Classified');
                    $kill->setSolarSystemSecurity('0.0');
                }
            }

            return $kill;
        }
        else
        {
            $this->walked = true;
            return null;
        }
    }

    function getAllKills()
    {
        while ($this->getKill())
        {
        }
        $this->rewind();
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

    function addVictimPilot($pilot)
    {
        if(is_numeric($pilot)) $this->vic_plt_[] = $pilot;
            else $this->vic_plt_[] = $pilot->getID();
    }

    function addVictimCorp($corp)
    {
        if(is_numeric($corp)) $this->vic_crp_[] = $corp;
            else $this->vic_crp_[] = $corp->getID();
    }

    function addVictimAlliance($alliance)
    {
        if(is_numeric($alliance)) $this->vic_all_[] = $alliance;
        else $this->vic_all_[] = $alliance->getID();
    }

    function addCombinedPilot($pilot)
    {
            if(is_numeric($pilot)) $this->comb_plt_[] = $pilot;
            else $this->comb_plt_[] = $pilot->getID();
    }

    function addCombinedCorp($corp)
    {
            if(is_numeric($corp)) $this->comb_crp_[] = $corp;
            else $this->comb_crp_[] = $corp->getID();
    }

    function addCombinedAlliance($alliance)
    {
            if(is_numeric($alliance)) $this->comb_all_[] = $alliance;
            else $this->comb_all_[] = $alliance->getID();
    }

    function addVictimShipClass($shipclass)
    {
            if(is_numeric($shipclass)) $this->vic_scl_id_[] = $shipclass;
            else $this->vic_scl_id_[] = $shipclass->getID();
    }

    function addVictimShip($ship)
    {
    }

    function addItemDestroyed($item)
    {
    }

    function addRegion($region)
    {
        if(is_numeric($region)) $this->regions_[] = $region;
        else $this->regions_[] = $region->getID();
    }

    function addSystem($system)
    {
        if(is_numeric($system)) $this->systems_[] = $system;
        else $this->systems_[] = $system->getID();
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

    //! Filter results by week. Requires the year to also be set.
    function setWeek($weekno)
    {
        $weekno=intval($weekno);
        if($weekno <1)  $this->weekno_ = 1;
        if($weekno >53) $this->weekno_ = 53;
        else $this->weekno_ = $weekno;
    }

    //! Filter results by year.
    function setYear($yearno)
    {
        // 1970-2038 is the allowable range for the timestamp code used
        // Needs to be revisited in the next 30 years
        $yearno = intval($yearno);
        if($yearno < 1970) $this->yearno_ = 1970;
        if($yearno > 2038) $this->yearno_ = 2038;
        else $this->yearno_ = $yearno;
    }

    //! Filter results by starting week. Requires the year to also be set.
    function setStartWeek($weekno)
    {
        $weekno=intval($weekno);
        if($weekno <1)  $this->startweekno_ = 1;
        if($weekno >53) $this->startweekno_ = 53;
        else $this->startweekno_ = $weekno;
    }

    //! Filter results by starting date/time.
    function setStartDate($timestamp)
    {
        // Check timestamp is valid before adding
        if(strtotime($timestamp)) $this->startDate_ = $timestamp;
    }

    //! Filter results by ending date/time.
    function setEndDate($timestamp)
    {
        // Check timestamp is valid before adding
        if(strtotime($timestamp)) $this->endDate_ = $timestamp;
    }

    //! Convert given date ranges to SQL date range.

	//! \return string containing SQL date filter.
    function getDateFilter()
    {
		$qstartdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
		$qenddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);
		if($qstartdate) $sql .= " kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' ";
		if($qstartdate && $qenddate) $sql .= " AND ";
		if($qenddate) $sql .= " kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' ";
		return $sql;
    }

    function setRelated($killid)
    {
        $this->related_ = $killid;
    }

    function setLimit($limit)
    {
        $this->limit_ = $limit;
    }
	//! Only return kills with an external id set.
	function setAPIKill($hasid = true)
	{
		$this->apikill_ = $hasid;
	}

    function setOrderBy($orderby)
    {
        $this->orderby_ = $orderby;
    }

    function setMinKllID($id)
    {
        $this->minkllid_ = $id;
    }

    function setMaxKllID($id)
    {
        $this->maxkllid_ = $id;
    }

    function setSummary($summary = false)
    {
        $this->summary_ = $summary;
    }

    function getCount()
    {
        $this->execQuery();
        return $this->qry_->recordCount();
    }

    function getRealCount()
    {
        $this->execQuery();
        return $this->qry_->recordCount();
    }

    function getISK()
    {
        $this->execQuery();
        return $this->killisk_;
    }

    function getPoints()
    {
        return $this->killpoints_;
    }

    function rewind()
    {
        $this->qry_->rewind();
        $this->killcounter_ = 0;
    }

    function setPodsNoobShips($flag)
    {
        if (!$flag)
        {
            array_push($this->exclude_scl_, 2);
            array_push($this->exclude_scl_, 3);
            array_push($this->exclude_scl_, 11);
        }
    }

    function setOrdered($flag)
    {
        $this->ordered_ = $flag;
    }

    function tag($string)
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
    function setCountComments($comments = true)
    {
        $this->comments_ = $comments;
    }

    // Add an involved party count to the killlist SQL
    function setCountInvolved($setinv = true)
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

    function buildKillArray()
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

    function getKill()
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

    function rewind()
    {
        // intentionally left empty to overload the standard handle
    }

    function getCount()
    {
        $count = 0;
        foreach ($this->lists as $killlist)
        {
            $count += $killlist->getCount();
        }
        return $count;
    }

    function getRealCount()
    {
        $count = 0;
        foreach ($this->lists as $killlist)
        {
            $count += $killlist->getRealCount();
        }
        return $count;
    }

    function getISK()
    {
        $sum = 0;
        foreach ($this->lists as $killlist)
        {
            $sum += $killlist->getISK();
        }
       return $sum;
    }

    function getPoints()
    {
        $sum = 0;
        foreach ($this->lists as $killlist)
        {
            $sum += $killlist->getPoints();
        }
        return $sum;
    }

}
?>