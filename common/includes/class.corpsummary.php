<?php
/**
 * Store summary statistics for Corporations.
 * @package EDK
 */
class corpSummary extends statSummary
{
	/** @var integer */
	private $crp_id = null;

	/**
	 * Create a Corporation summary for the given corp ID.
	 * 
	 * @param integer $crp_id
	 */
	function corpSummary($crp_id)
	{
		$this->crp_id = (int)$crp_id;
	}

	/**
	 * Fetch the summary information.
	 *
	 * @return boolean
	 */
	protected function execute()
	{
		if ($this->executed) {
			return;
		}
		if (!$this->crp_id) {
			$this->executed = true;
			return false;
		}

		$qry = DBFactory::getDBQuery();
		$sql = "SELECT scl_class, scl_id, csm_shp_id, sum(csm_loss_count) as csm_loss_count, 
				sum(csm_loss_isk) as csm_loss_isk, sum(csm_kill_count) as csm_kill_count,
				sum(csm_kill_isk) as csm_kill_isk, sum(csm_kill_loot) as csm_kill_loot,
				sum(csm_kill_points) as csm_kill_points, sum(csm_loss_points) as csm_loss_points,
				sum(csm_loss_loot) as csm_loss_loot
			FROM kb3_ship_classes left join kb3_sum_corp
				ON (csm_shp_id = scl_id AND csm_crp_id = ".$this->crp_id.")
			WHERE scl_class not in ('Drone','Unknown')
				GROUP BY scl_id ORDER BY scl_class";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = (int) $row['csm_kill_count'];
			$this->summary[$row['scl_id']]['killisk'] = (float) $row['csm_kill_isk'];
			$this->summary[$row['scl_id']]['losscount'] = (int) $row['csm_loss_count'];
			$this->summary[$row['scl_id']]['lossisk'] = (float) $row['csm_loss_isk'];
			$this->summary[$row['scl_id']]['lossloot'] = (float) $row['csm_loss_loot'];
			$this->summary[$row['scl_id']]['losspoints'] = (int) $row['csm_loss_points'];
			$this->summary[$row['scl_id']]['killloot'] = (float) $row['csm_kill_loot'];
			$this->summary[$row['scl_id']]['killpoints'] = (int) $row['csm_kill_points'];
		}
		
		$this->executed = true;
	}

	public function getMonthlySummary()
	{
		if (!$this->crp_id) {
			return false;
		}

		$qry = DBFactory::getDBQuery();

		$sql = "SELECT csm_year, csm_monthday, sum(csm_loss_count) as csm_loss_count,
				sum(csm_loss_isk) as csm_loss_isk, sum(csm_kill_count) as csm_kill_count,
				sum(csm_kill_isk) as csm_kill_isk, sum(csm_kill_loot) as csm_kill_loot,
				sum(csm_kill_points) as csm_kill_points, sum(csm_loss_points) as csm_loss_points,
				sum(csm_loss_loot) as csm_loss_loot
			FROM kb3_sum_corp WHERE csm_crp_id = ".$this->crp_id."
			GROUP BY csm_year, csm_monthday ORDER BY csm_year desc, csm_monthday desc";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$dt = new DateTime();
			$dt->setDate($row['csm_year'], $row['csm_monthday'], 1); // returns DateTime object

			$summary[$row['csm_year']][$row['csm_monthday']]['date'] = $dt;
			$summary[$row['csm_year']][$row['csm_monthday']]['killcount'] = (int) $row['csm_kill_count'];
			$summary[$row['csm_year']][$row['csm_monthday']]['killisk'] = (float) $row['csm_kill_isk'];
			$summary[$row['csm_year']][$row['csm_monthday']]['losscount'] = (int) $row['csm_loss_count'];
			$summary[$row['csm_year']][$row['csm_monthday']]['lossisk'] = (float) $row['csm_loss_isk'];
			$summary[$row['csm_year']][$row['csm_monthday']]['lossloot'] = (float) $row['csm_loss_loot'];
			$summary[$row['csm_year']][$row['csm_monthday']]['losspoints'] = (int) $row['csm_loss_points'];
			$summary[$row['csm_year']][$row['csm_monthday']]['killloot'] = (float) $row['csm_kill_loot'];
			$summary[$row['csm_year']][$row['csm_monthday']]['killpoints'] = (int) $row['csm_kill_points'];
		}
		return $summary;
	}

	/**
	 * Add a Kill and its value to the summary.
	 *
	 * @param Kill $kill 
	 */
	public static function addKill($kill)
	{
		$year = date('Y',strtotime($kill->getTimestamp()));
		$month = date('m',strtotime($kill->getTimestamp()));
		$alls = array();
		$shipid = $kill->getVictimShip()->getClass()->getID();
		$involved = $kill->getInvolved();
		$killisk = $kill->getISKLoss();
		$killloot = $kill->getISKLoot();
		$points = $kill->getKillPoints();

		$qry = DBFactory::getDBQuery();		

		$sql = "UPDATE kb3_sum_corp SET csm_loss_count=csm_loss_count+1, csm_loss_isk=csm_loss_isk+$killisk, csm_loss_loot=csm_loss_loot+$killloot, csm_loss_points=csm_loss_points+$points WHERE " .
			   " csm_crp_id = " . $kill->getVictimCorpID() . " AND csm_shp_id = $shipid AND csm_monthday = $month AND csm_year = $year";
		$qry->execute($sql);
		if ( $qry->affectedRows() == 0 ) {
			$sql = "INSERT INTO kb3_sum_corp (csm_crp_id, csm_shp_id, csm_loss_count, csm_loss_isk, csm_loss_loot, csm_loss_points, csm_monthday, csm_year) ".
					"VALUES ( ".$kill->getVictimCorpID().", ".$shipid.", 1, $killisk, $killloot, $points, $month, $year)";
			$qry->execute($sql);
		}	

		/* 
		 * If you are a victim, do not credit for a 'kill' - this avoids counting for people getting on same alliance titan/super "losses" 
		 * Note: Special case - if it's a solo kill/loss, we'll count it as a kill+loss - this covers 'training' kills etc
		*/
		$alls[$kill->getVictimCorpID()] = 1;
		$solo = true;

		foreach ($involved as $inv) {
			if (isset($alls[$inv->getCorpID()])) {
				continue;
			}
			$alls[$inv->getCorpID()] = 1;
			$solo = false;

			$sql = "UPDATE kb3_sum_corp SET csm_kill_count=csm_kill_count+1, csm_kill_isk=csm_kill_isk+$killisk, csm_kill_loot=csm_kill_loot+$killloot, csm_kill_points=csm_kill_points+$points WHERE " .
				   " csm_crp_id = " . $inv->getCorpID() . " AND csm_shp_id = $shipid AND csm_monthday = $month AND csm_year = $year";
			$qry->execute($sql);
			if ( $qry->affectedRows() == 0 ) {
				$sql = "INSERT INTO kb3_sum_corp (csm_crp_id, csm_shp_id, csm_kill_count, csm_kill_isk, csm_kill_loot, csm_kill_points, csm_monthday, csm_year) ".
						"VALUES ( ".$inv->getCorpID().", ".$shipid.", 1, $killisk, $killloot, $points, $month, $year)";
				$qry->execute($sql);
			}	
		}
		
		if( $solo ) {
			/* row must exist at this point - it would have been inserted when adding victim above */
			$sql = "UPDATE kb3_sum_corp SET csm_kill_count=csm_kill_count+1, csm_kill_isk=csm_kill_isk+$killisk, csm_kill_loot=csm_kill_loot+$killloot, csm_kill_points=csm_kill_points+$points WHERE " .
				   " csm_crp_id = " . $kill->getVictimCorpID() . " AND csm_shp_id = $shipid AND csm_monthday = $month AND csm_year = $year";
			$qry->execute($sql);
		}

		unset($qry);
	}

	/**
	 * Delete a Kill and remove its value from the summary.
	 *
	 * @param Kill $kill
	 */
	public static function delKill($kill)
	{
		$year = date('Y',strtotime($kill->getTimestamp()));
		$month = date('m',strtotime($kill->getTimestamp()));
		$shipid = $kill->getVictimShip()->getClass()->getID();
		$killisk = $kill->getISKLoss();
		$killloot = $kill->getISKLoot();
		$points = $kill->getKillPoints();
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$sql = "UPDATE kb3_sum_corp SET csm_loss_count = csm_loss_count - 1, ".
				" csm_loss_isk = csm_loss_isk-".$killisk.
				" ,csm_loss_loot = csm_loss_loot-".$killloot .
				" ,csm_loss_points = csm_loss_points-".$points.
				" WHERE csm_crp_id = ".$kill->getVictimCorpID().
				" AND csm_shp_id = $shipid AND csm_monthday = $month AND csm_year = $year";
		$qry->execute($sql);

		$solo = true;
		$alls[$kill->getVictimCorpID()] = 1;

		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getCorpID()])) {
				continue;
			}
			$alls[$inv->getCorpID()] = 1;
			$solo = false;

			$sql = "UPDATE kb3_sum_corp SET csm_kill_count = csm_kill_count - 1, ".
					" csm_kill_isk = csm_kill_isk - ".$killisk.
					" ,csm_kill_loot = csm_kill_loot - ".$killloot.
					" ,csm_kill_points = csm_kill_points - ".$points.
					" WHERE csm_crp_id = ".$inv->getCorpID().
					" AND csm_shp_id = $shipid AND csm_monthday = $month AND csm_year = $year";
			$qry->execute($sql);
		}
		
		if( $solo ) {
			$sql = "UPDATE kb3_sum_corp SET csm_kill_count=csm_kill_count-1, csm_kill_isk=csm_kill_isk-$killisk, csm_kill_loot=csm_kill_loot-$killloot, csm_kill_points=csm_kill_points-$points WHERE " .
			" csm_crp_id = " . $kill->getVictimCorpID() . " AND csm_shp_id = $shipid AND csm_monthday = $month AND csm_year = $year";
			$qry->execute($sql);
		}
	}

	/**
	 * Update the summary table when a kill value changes.
	 *
	 * @param Kill $kill
	 * @param float $difference
	 */
	public static function update($kill, $difference)
	{
		$difference = (float)$difference;
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = "
				.$kill->getVictimCorpID());
		// No summary table to remove kill from so skip.
		if ($qry->recordCount()) {
			$sql = "UPDATE kb3_sum_corp SET csm_loss_isk = csm_loss_isk + "
					.$difference." WHERE csm_crp_id = ".$kill->getVictimCorpID()
					." AND csm_shp_id = "
					.$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getCorpID()])) {
				continue;
			}
			$alls[$inv->getCorpID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = "
					.$inv->getCorpID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "UPDATE kb3_sum_corp SET csm_kill_isk = csm_kill_isk + "
					.$difference." WHERE csm_crp_id = ".$inv->getCorpID()
					." AND csm_shp_id = "
					.$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
	}
}
