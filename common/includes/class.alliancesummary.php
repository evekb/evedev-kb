<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

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
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$this->all_id);
		if (!$qry->recordCount()) {
			self::buildSummary($this->all_id);
		}

		$sql = "SELECT scl_class, scl_id, kb3_sum_alliance.*
			FROM kb3_ship_classes left join kb3_sum_alliance
				ON (asm_shp_id = scl_id AND asm_all_id = ".$this->all_id.")
			WHERE scl_class not in ('Drone','Unknown')
				ORDER BY scl_class";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = (int) $row['asm_kill_count'];
			$this->summary[$row['scl_id']]['killisk'] = (float) $row['asm_kill_isk'];
			$this->summary[$row['scl_id']]['losscount'] = (int) $row['asm_loss_count'];
			$this->summary[$row['scl_id']]['lossisk'] = (float) $row['asm_loss_isk'];
		}
		$this->executed = true;
	}

	/**
	 * Build a new summary table for an alliance.
	 *
	 * @param integer $all_id
	 * @return boolean
	 */
	private static function buildSummary($all_id)
	{
		$all_id = (int) $all_id;
		if (!$all_id) {
			return false;
		}
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);

		// insert into summary ((select all kills) union (select all losses))
		$sql = "REPLACE INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_kill_count, asm_kill_isk, asm_loss_count, asm_loss_isk)
		SELECT $all_id, losses.asm_shp_id, ifnull(kills.knb,0), ifnull(kills.kisk,0), ifnull(losses.lnb,0), ifnull(losses.lisk,0)
		FROM (SELECT shp_class as asm_shp_id, 0 as knb,0 as kisk ,count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_all_id = $all_id
					AND EXISTS (SELECT 1
							FROM kb3_inv_all
							WHERE kll.kll_id = ina_kll_id
							AND ina_all_id <> $all_id limit 0,1)
			GROUP BY shp_class) losses left join (SELECT $all_id as asm_all_id, shp_class as asm_shp_id, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk,0 as lnb,0 as lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_all ina ON (ina.ina_kll_id = kll.kll_id)
			WHERE ina.ina_all_id = $all_id
				AND kll.kll_all_id <> $all_id
			GROUP BY shp_class) kills ON (kills.asm_shp_id = losses.asm_shp_id)
		UNION
		SELECT $all_id, kills.asm_shp_id, ifnull(kills.knb,0), ifnull(kills.kisk,0), ifnull(losses.lnb,0), ifnull(losses.lisk,0)
		FROM (SELECT shp_class as asm_shp_id, 0 as knb,0 as kisk ,count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_all_id = $all_id
					AND EXISTS (SELECT 1
							FROM kb3_inv_all
							WHERE kll.kll_id = ina_kll_id
							AND ina_all_id <> $all_id limit 0,1)
			GROUP BY shp_class) losses right join (SELECT $all_id as asm_all_id, shp_class as asm_shp_id, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk,0 as lnb,0 as lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_all ina ON (ina.ina_kll_id = kll.kll_id)
			WHERE ina.ina_all_id = $all_id
				AND kll.kll_all_id <> $all_id
			GROUP BY shp_class) kills on (kills.asm_shp_id = losses.asm_shp_id)		";
		$qry->execute($sql);
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
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$kill->getVictimAllianceID());
		if ($qry->recordCount()) {
			$sql = "INSERT INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_loss_count, asm_loss_isk) ".
					"VALUES ( ".$kill->getVictimAllianceID().", ".$kill->getVictimShip()->getClass()->getID().", 1, ".
					$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
					"asm_loss_count = asm_loss_count + 1, ".
					"asm_loss_isk = asm_loss_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getAllianceID()])) {
				continue;
			}
			$alls[$inv->getAllianceID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = "
					.$inv->getAllianceID());
			//if(!$qry->recordCount()) allianceSummary::buildSummary($inv->getAllianceID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "INSERT INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_kill_count, asm_kill_isk) ".
					"VALUES ( ".$inv->getAllianceID().", ".$kill->getVictimShip()->getClass()->getID().", 1, ".
					$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
					"asm_kill_count = asm_kill_count + 1, ".
					"asm_kill_isk = asm_kill_isk + ".$kill->getISKLoss();
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
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = "
				.$kill->getVictimAllianceID());
		// No summary table to remove kill from so skip.
		if ($qry->recordCount()) {
			$sql = "UPDATE kb3_sum_alliance SET asm_loss_count = asm_loss_count - 1, ".
					" asm_loss_isk = asm_loss_isk - ".$kill->getISKLoss().
					" WHERE asm_all_id = ".$kill->getVictimAllianceID().
					" AND asm_shp_id = ".$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
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
			$sql = "UPDATE kb3_sum_alliance SET asm_kill_count = asm_kill_count - 1, ".
					" asm_kill_isk = asm_kill_isk - ".$kill->getISKLoss().
					" WHERE asm_all_id = ".$inv->getAllianceID().
					" AND asm_shp_id = ".$kill->getVictimShip()->getClass()->getID();
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
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = "
				.$kill->getVictimAllianceID());
		// No summary table to remove kill from so skip.
		if ($qry->recordCount()) {
			$sql = "UPDATE kb3_sum_alliance SET asm_loss_isk = asm_loss_isk + "
					.$difference." WHERE asm_all_id = "
					.$kill->getVictimAllianceID()." AND asm_shp_id = "
					.$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
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
					.$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
	}
}
