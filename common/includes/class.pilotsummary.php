<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

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
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$this->plt_id);
		if (!$qry->recordCount()) {
			self::buildSummary($this->plt_id);
		}

		$sql = "SELECT scl_class, scl_id, kb3_sum_pilot.*
			FROM kb3_ship_classes left join kb3_sum_pilot
				ON (psm_shp_id = scl_id AND psm_plt_id = ".$this->plt_id.")
			WHERE scl_class not in ('Drone','Unknown')
				ORDER BY scl_class";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = (int) $row['psm_kill_count'];
			$this->summary[$row['scl_id']]['killisk'] = (float) $row['psm_kill_isk'];
			$this->summary[$row['scl_id']]['losscount'] = (int) $row['psm_loss_count'];
			$this->summary[$row['scl_id']]['lossisk'] = (float) $row['psm_loss_isk'];
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

		// insert into summary ((select all kills) union (select all losses))
		$sql = "REPLACE INTO kb3_sum_pilot (psm_plt_id, psm_shp_id, psm_kill_count, psm_kill_isk, psm_loss_count, psm_loss_isk)
select $plt_id as psm_plt_id, losses.psm_shp_id, ifnull(kills.knb,0), ifnull(kills.kisk,0), ifnull(losses.lnb,0), ifnull(losses.lisk,0)
		FROM (SELECT shp_class as psm_shp_id, 0 as knb,0 as kisk ,count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_victim_id = $plt_id
			GROUP BY shp_class) losses left join (SELECT shp_class as psm_shp_id, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk,0 as lnb,0 as lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_detail ind ON (ind.ind_kll_id = kll.kll_id)
			WHERE ind.ind_plt_id = $plt_id
			GROUP BY shp_class) kills on (kills.psm_shp_id = losses.psm_shp_id)
		UNION
			select $plt_id as psm_plt_id, kills.psm_shp_id, ifnull(kills.knb,0), ifnull(kills.kisk,0), ifnull(losses.lnb,0), ifnull(losses.lisk,0)
		FROM (SELECT shp_class as psm_shp_id, 0 as knb,0 as kisk ,count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_victim_id = $plt_id
			GROUP BY shp_class) losses right join (SELECT $plt_id as psm_plt_id, shp_class as psm_shp_id, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk,0 as lnb,0 as lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_detail ind ON (ind.ind_kll_id = kll.kll_id)
			WHERE ind.ind_plt_id = $plt_id
			GROUP BY shp_class) kills on (kills.psm_shp_id = losses.psm_shp_id)";
		$qry->execute($sql);

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
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$kill->getVictimID());
		if ($qry->recordCount()) {
			$sql = "INSERT INTO kb3_sum_pilot (psm_plt_id, psm_shp_id, psm_loss_count, psm_loss_isk) ".
					"VALUES ( ".$kill->getVictimID().", ".$kill->getVictimShip()->getClass()->getID().", 1, ".
					$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
					"psm_loss_count = psm_loss_count + 1, ".
					"psm_loss_isk = psm_loss_isk + ".$kill->getISKLoss();
			$qry->execute($sql);

			$sql = "UPDATE kb3_pilots SET plt_lpoints = plt_lpoints + ".$kill->getKillPoints().
					" WHERE plt_id = ".$kill->getVictimID();
			$qry->execute($sql);
		}
		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getPilotID()])) {
				continue;
			}
			$alls[$inv->getPilotID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$inv->getPilotID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "INSERT INTO kb3_sum_pilot (psm_plt_id, psm_shp_id, psm_kill_count, psm_kill_isk) ".
					"VALUES ( ".$inv->getPilotID().", ".$kill->getVictimShip()->getClass()->getID().", 1, ".
					$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
					"psm_kill_count = psm_kill_count + 1, ".
					"psm_kill_isk = psm_kill_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
			$sql = "UPDATE kb3_pilots SET plt_kpoints = plt_kpoints + ".$kill->getKillPoints().
					" WHERE plt_id = ".$inv->getPilotID();
			$qry->execute($sql);
		}
	}

	/**
	 * Delete a Kill and remove its value from the summary.
	 *
	 * @param Kill $kill
	 */
	public static function delKill($kill)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = "
				.$kill->getVictimID());
		// No summary table to remove kill from so skip.
		if ($qry->recordCount()) {
			$sql = "UPDATE kb3_sum_pilot"
					." SET psm_loss_count = psm_loss_count - 1,"
					." psm_loss_isk = psm_loss_isk - ".$kill->getISKLoss()
					." WHERE psm_plt_id = ".$kill->getVictimID()
					." AND psm_shp_id = ".$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);

			$sql = "UPDATE kb3_pilots SET plt_lpoints = plt_lpoints - "
					.$kill->getKillPoints()
					." WHERE plt_id = ".$kill->getVictimID();
			$qry->execute($sql);
		}
		foreach ($kill->getInvolved() as $inv) {
			if ($alls[$inv->getPilotID()]) {
				continue;
			}
			$alls[$inv->getPilotID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = "
					.$inv->getPilotID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "UPDATE kb3_sum_pilot"
					." SET psm_kill_count = psm_kill_count - 1, "
					." psm_kill_isk = psm_kill_isk - ".$kill->getISKLoss()
					." WHERE psm_plt_id = ".$inv->getPilotID()
					." AND psm_shp_id = ".$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);

			$sql = "UPDATE kb3_pilots SET plt_kpoints = plt_kpoints - "
					.$kill->getKillPoints()
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
