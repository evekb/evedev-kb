<?php
/**
 * Store summary statistics for Pilots.
 * @package EDK
 */
class pilotSummary extends statSummary
{
	/** @var integer */
	private $plt_id = null;

	/**
	 * @param integer $plt_id
	 */
	function pilotSummary($plt_id)
	{
		$this->plt_id = (int) $plt_id;
		$this->executed = false;
	}

	/**
	 * Fetch the summary information.
	 *
	 * @return boolean Returns false on error
	 */
	protected function execute()
	{
		if ($this->executed) {
			return;
		}
		if (!$this->plt_id) {
			$this->executed = true;
			return false;
		}

		$qry = DBFactory::getDBQuery();
		$sql = "SELECT scl_class, scl_id, psm_shp_id, sum(psm_loss_count) as psm_loss_count, 
				sum(psm_loss_isk) as psm_loss_isk, sum(psm_kill_count) as psm_kill_count,
				sum(psm_kill_isk) as psm_kill_isk, sum(psm_kill_loot) as psm_kill_loot,
				sum(psm_kill_points) as psm_kill_points, sum(psm_loss_points) as psm_loss_points,
				sum(psm_loss_loot) as psm_loss_loot
			FROM kb3_ship_classes left join kb3_sum_pilot
				ON (psm_shp_id = scl_id AND psm_plt_id = ".$this->plt_id.")
			WHERE scl_class not in ('Drone','Unknown')
				GROUP BY psm_shp_id ORDER BY scl_class";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = (int) $row['psm_kill_count'];
			$this->summary[$row['scl_id']]['killisk'] = (float) $row['psm_kill_isk'];
			$this->summary[$row['scl_id']]['losscount'] = (int) $row['psm_loss_count'];
			$this->summary[$row['scl_id']]['lossisk'] = (float) $row['psm_loss_isk'];
			$this->summary[$row['scl_id']]['lossloot'] = (float) $row['psm_loss_loot'];
			$this->summary[$row['scl_id']]['losspoints'] = (int) $row['psm_loss_points'];
			$this->summary[$row['scl_id']]['killloot'] = (float) $row['psm_kill_loot'];
			$this->summary[$row['scl_id']]['killpoints'] = (int) $row['psm_kill_points'];
		}

		$this->executed = true;
	}

	/**
	 * Build a new summary table for a pilot.
	 *
	 * @param integer $plt_id
	 * @return boolean Returns false on error.
	 */
	private static function buildSummary($plt_id)
	{
		$plt_id = (int) $plt_id;
		if (!$plt_id) {
			return false;
		}
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);

		$klist = new KillList();
		$klist->addInvolvedPilot($plt_id);
		$klist->getAllKills();
		$kpoints = $klist->getPoints();
		unset($klist);
		$llist = new KillList();
		$llist->addVictimPilot($plt_id);
		$llist->getAllKills();
		$lpoints = $llist->getPoints();
		unset($llist);
		$qry->execute("UPDATE kb3_pilots SET plt_kpoints = $kpoints,
			 plt_lpoints = $lpoints WHERE plt_id = $plt_id");

		$qry->autocommit(true);
		return;
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
		
		$sql = "UPDATE kb3_sum_pilot SET psm_loss_count=psm_loss_count+1, psm_loss_isk=psm_loss_isk+$killisk, psm_loss_loot=psm_loss_loot+$killloot, psm_loss_points=psm_loss_points+$points WHERE " .
			   " psm_plt_id = " . $kill->getVictimID() . " AND psm_shp_id = $shipid AND psm_monthday = $month AND psm_year = $year";
		$qry->execute($sql);
		if ( $qry->affectedRows() == 0 ) {
			$sql = "INSERT INTO kb3_sum_pilot (psm_plt_id, psm_shp_id, psm_loss_count, psm_loss_isk, psm_loss_loot, psm_loss_points, psm_monthday, psm_year) ".
					"VALUES ( ".$kill->getVictimID().", ".$shipid.", 1, $killisk, $killloot, $points, $month, $year)";
			$qry->execute($sql);
		}	

		$sql = "UPDATE kb3_pilots SET plt_lpoints=plt_lpoints+".$points. " WHERE plt_id = ".$kill->getVictimID();
		$qry->execute($sql);

		foreach ($involved as $inv) {
			if (isset($alls[$inv->getPilotID()])) {
				continue;
			}
			$alls[$inv->getPilotID()] = 1;

			$sql = "UPDATE kb3_sum_pilot SET psm_kill_count=psm_kill_count+1, psm_kill_isk=psm_kill_isk+$killisk, psm_kill_loot=psm_kill_loot+$killloot, psm_kill_points=psm_kill_points+$points WHERE " .
				   " psm_plt_id = " . $inv->getPilotID() . " AND psm_shp_id = $shipid AND psm_monthday = $month AND psm_year = $year";
			$qry->execute($sql);
			if ( $qry->affectedRows() == 0 ) {
				$sql = "INSERT INTO kb3_sum_pilot (psm_plt_id, psm_shp_id, psm_kill_count, psm_kill_isk, psm_kill_loot, psm_kill_points, psm_monthday, psm_year) ".
						"VALUES ( ".$inv->getPilotID().", ".$shipid.", 1, $killisk, $killloot, $points, $month, $year)";
				$qry->execute($sql);
			}

			$sql = "UPDATE kb3_pilots SET plt_kpoints=plt_kpoints + ".$kill->getKillPoints(). " WHERE plt_id = ".$inv->getPilotID();
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
		$sql = "UPDATE kb3_sum_pilot SET psm_loss_count = psm_loss_count - 1, ".
				" psm_loss_isk = psm_loss_isk-".$killisk.
				" ,psm_loss_loot = psm_loss_loot-".$killloot .
				" ,psm_loss_points = psm_loss_points-".$points.
				" WHERE psm_plt_id = ".$kill->getVictimID().
				" AND psm_shp_id = $shipid AND psm_monthday = $month AND psm_year = $year";
		$qry->execute($sql);

		$sql = "UPDATE kb3_pilots SET plt_lpoints = plt_lpoints - " .$kill->getKillPoints()
			  ." WHERE plt_id = ".$kill->getVictimID();
		$qry->execute($sql);

		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getPilotID()])) {
				continue;
			}
			$alls[$inv->getPilotID()] = 1;

			$sql = "UPDATE kb3_sum_pilot SET psm_kill_count = psm_kill_count - 1, ".
					" psm_kill_isk = psm_kill_isk - ".$killisk.
					" ,psm_kill_loot = psm_kill_loot - ".$killloot.
					" ,psm_kill_points = psm_kill_points - ".$points.
					" WHERE psm_plt_id = ".$inv->getPilotID().
					" AND psm_shp_id = $shipid AND psm_monthday = $month AND psm_year = $year";
			$qry->execute($sql);

			$sql = "UPDATE kb3_pilots SET plt_kpoints = plt_kpoints - ".$kill->getKillPoints()
					." WHERE plt_id = ".$inv->getPilotID();
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
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = "
				.$kill->getVictimID());
		// No summary table to remove kill from so skip.
		if ($qry->recordCount()) {
			$sql = "UPDATE kb3_sum_pilot SET psm_loss_isk = psm_loss_isk - "
					.$difference." WHERE psm_plt_id = ".$kill->getVictimID()
					." AND psm_shp_id = "
					.$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
		foreach ($kill->getInvolved() as $inv) {
			if ($alls[$inv->getPilotID()]) {
				continue;
			}
			$alls[$inv->getPilotID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$inv->getPilotID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "UPDATE kb3_sum_pilot SET psm_kill_isk = psm_kill_isk - ".$difference.
					" WHERE psm_plt_id = ".$inv->getPilotID().
					" AND psm_shp_id = ".$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
	}
}
