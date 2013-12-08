<?php
/**
 * Store summary statistics for Alliances.
 * @package EDK
 */
class allianceSummary extends statSummary
{
	/** @var integer */
	private $all_id = null;

	/**
	 * Create an Alliance summary.
	 * 
	 * @param integer $all_id
	 */
	function allianceSummary($all_id)
	{
		$this->all_id = (int) $all_id;
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
		if (!$this->all_id) {
			$this->executed = true;
			return false;
		}

		$qry = DBFactory::getDBQuery();

		$sql = "SELECT scl_class, scl_id, asm_shp_id, sum(asm_loss_count) as asm_loss_count, 
				sum(asm_loss_isk) as asm_loss_isk, sum(asm_kill_count) as asm_kill_count,
				sum(asm_kill_isk) as asm_kill_isk, sum(asm_kill_loot) as asm_kill_loot,
				sum(asm_kill_points) as asm_kill_points, sum(asm_loss_points) as asm_loss_points,
				sum(asm_loss_loot) as asm_loss_loot
			FROM kb3_ship_classes left join kb3_sum_alliance
				ON (asm_shp_id = scl_id AND asm_all_id = ".$this->all_id.")
			WHERE scl_class not in ('Drone','Unknown')
				GROUP BY asm_shp_id ORDER BY scl_class";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = (int) $row['asm_kill_count'];
			$this->summary[$row['scl_id']]['killisk'] = (float) $row['asm_kill_isk'];
			$this->summary[$row['scl_id']]['losscount'] = (int) $row['asm_loss_count'];
			$this->summary[$row['scl_id']]['lossisk'] = (float) $row['asm_loss_isk'];
			$this->summary[$row['scl_id']]['lossloot'] = (float) $row['asm_loss_loot'];
			$this->summary[$row['scl_id']]['losspoints'] = (int) $row['asm_loss_points'];
			$this->summary[$row['scl_id']]['killloot'] = (float) $row['asm_kill_loot'];
			$this->summary[$row['scl_id']]['killpoints'] = (int) $row['asm_kill_points'];
		}
		$this->executed = true;
	}

	public function getMonthlySummary()
	{
		if (!$this->all_id) {
			return false;
		}

		$qry = DBFactory::getDBQuery();

		$sql = "SELECT asm_year, asm_monthday, sum(asm_loss_count) as asm_loss_count,
				sum(asm_loss_isk) as asm_loss_isk, sum(asm_kill_count) as asm_kill_count,
				sum(asm_kill_isk) as asm_kill_isk, sum(asm_kill_loot) as asm_kill_loot,
				sum(asm_kill_points) as asm_kill_points, sum(asm_loss_points) as asm_loss_points,
				sum(asm_loss_loot) as asm_loss_loot
			FROM kb3_sum_alliance WHERE asm_all_id = ".$this->all_id."
			GROUP BY asm_year, asm_monthday ORDER BY asm_year desc, asm_monthday desc";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$dt = new DateTime();
			$dt->setDate($row['asm_year'], $row['asm_monthday'], 1); // returns DateTime object

			$summary[$row['asm_year']][$row['asm_monthday']]['date'] = $dt;
			$summary[$row['asm_year']][$row['asm_monthday']]['killcount'] = (int) $row['asm_kill_count'];
			$summary[$row['asm_year']][$row['asm_monthday']]['killisk'] = (float) $row['asm_kill_isk'];
			$summary[$row['asm_year']][$row['asm_monthday']]['losscount'] = (int) $row['asm_loss_count'];
			$summary[$row['asm_year']][$row['asm_monthday']]['lossisk'] = (float) $row['asm_loss_isk'];
			$summary[$row['asm_year']][$row['asm_monthday']]['lossloot'] = (float) $row['asm_loss_loot'];
			$summary[$row['asm_year']][$row['asm_monthday']]['losspoints'] = (int) $row['asm_loss_points'];
			$summary[$row['asm_year']][$row['asm_monthday']]['killloot'] = (float) $row['asm_kill_loot'];
			$summary[$row['asm_year']][$row['asm_monthday']]['killpoints'] = (int) $row['asm_kill_points'];
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
		$shipid = $kill->getVictimShip()->getClass()->getID();
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$involved = $kill->getInvolved();
		$killisk = $kill->getISKLoss();
		$killloot = $kill->getISKLoot();
		$points = $kill->getKillPoints();

		$sql = "UPDATE kb3_sum_alliance SET asm_loss_count=asm_loss_count+1, asm_loss_isk=asm_loss_isk+$killisk, asm_loss_loot=asm_loss_loot+$killloot, asm_loss_points=asm_loss_points+$points WHERE " .
			   " asm_all_id = " . $kill->getVictimAllianceID() . " AND asm_shp_id = $shipid AND asm_monthday = $month AND asm_year = $year";
		$qry->execute($sql);
		if ( $qry->affectedRows() == 0 ) {
			$sql = "INSERT INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_loss_count, asm_loss_isk, asm_loss_loot, asm_loss_points, asm_monthday, asm_year) ".
					"VALUES ( ".$kill->getVictimAllianceID().", ".$shipid.", 1, $killisk, $killloot, $points, $month, $year)";
			$qry->execute($sql);
		}
		
		/* 
		 * If you are a victim, do not credit for a 'kill' - this avoids counting for people getting on same alliance titan/super "losses" 
		 * Note: Special case - if it's a solo kill/loss, we'll count it as a kill+loss - this covers 'training' kills etc
		*/
		$alls[$kill->getVictimAllianceID()] = 1;
		$solo = true;

		foreach ($involved as $inv) {
			if (isset($alls[$inv->getAllianceID()])) {
				continue;
			}
			$alls[$inv->getAllianceID()] = 1;
			$solo = false;

			$sql = "UPDATE kb3_sum_alliance SET asm_kill_count=asm_kill_count+1, asm_kill_isk=asm_kill_isk+$killisk, asm_kill_loot=asm_kill_loot+$killloot, asm_kill_points=asm_kill_points+$points WHERE " .
			   " asm_all_id = " . $inv->getAllianceID() . " AND asm_shp_id = $shipid AND asm_monthday = $month AND asm_year = $year";
			$qry->execute($sql);
			if ( $qry->affectedRows() == 0 ) {
				$sql = "INSERT INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_kill_count, asm_kill_isk, asm_kill_loot, asm_kill_points, asm_monthday, asm_year) ".
						"VALUES ( ".$inv->getAllianceID().", ".$shipid.", 1, $killisk, $killloot, $points, $month, $year)";
				$qry->execute($sql);
			}		
		}
		
		if( $solo ) {
			/* row must exist at this point - it would have been inserted when adding victim above */
			$sql = "UPDATE kb3_sum_alliance SET asm_kill_count=asm_kill_count+1, asm_kill_isk=asm_kill_isk+$killisk, asm_kill_loot=asm_kill_loot+$killloot, asm_kill_points=asm_kill_points+$points WHERE " .
				   " asm_all_id = " . $kill->getVictimAllianceID() . " AND asm_shp_id = $shipid AND asm_monthday = $month AND asm_year = $year";
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
		$sql = "UPDATE kb3_sum_alliance SET asm_loss_count = asm_loss_count - 1, ".
				" asm_loss_isk = asm_loss_isk-".$killisk.
				" ,asm_loss_loot = asm_loss_loot-".$killloot .
				" ,asm_loss_points = asm_loss_points-".$points.
				" WHERE asm_all_id = ".$kill->getVictimAllianceID().
				" AND asm_shp_id = $shipid AND asm_monthday = $month AND asm_year = $year";
		$qry->execute($sql);

		$solo = true;
		$alls[$kill->getVictimAllianceID()] = 1;
		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getAllianceID()])) {
				continue;
			}
			$alls[$inv->getAllianceID()] = 1;
			$solo = false;

			$sql = "UPDATE kb3_sum_alliance SET asm_kill_count = asm_kill_count - 1, ".
					" asm_kill_isk = asm_kill_isk - ".$killisk.
					" ,asm_kill_loot = asm_kill_loot - ".$killloot.
					" ,asm_kill_points = asm_kill_points - ".$points.
					" WHERE asm_all_id = ".$inv->getAllianceID().
					" AND asm_shp_id = $shipid AND asm_monthday = $month AND asm_year = $year";
			$qry->execute($sql);
		}
		
		if( $solo ) {
			$sql = "UPDATE kb3_sum_alliance SET asm_kill_count=asm_kill_count-1, asm_kill_isk=asm_kill_isk-$killisk, asm_kill_loot=asm_kill_loot-$killloot, asm_kill_points=asm_kill_points-$points WHERE " .
			" asm_all_id = " . $kill->getVictimAllianceID() . " AND asm_shp_id = $shipid AND asm_monthday = $month AND asm_year = $year";
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
		$year = date('Y',strtotime($kill->getTimestamp()));
		$month = date('m',strtotime($kill->getTimestamp()));
		$shipid = $kill->getVictimShip()->getClass()->getID();
		$difference = (float)$difference;
		$alls = array();
		$qry = DBFactory::getDBQuery();

		$sql = "UPDATE kb3_sum_alliance SET asm_loss_isk = asm_loss_isk + "
				.$difference." WHERE asm_all_id = "
				.$kill->getVictimAllianceID()." AND asm_shp_id = "
				.$shipid;
		$qry->execute($sql);

		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getAllianceID()])) {
				continue;
			}
			$alls[$inv->getAllianceID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = "
					.$inv->getAllianceID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "UPDATE kb3_sum_alliance SET asm_kill_isk = asm_kill_isk + "
					.$difference." WHERE asm_all_id = ".$inv->getAllianceID()
					." AND asm_shp_id = "
					.$shipid;
			$qry->execute($sql);
		}
	}
}
