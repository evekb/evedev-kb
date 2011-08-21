<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

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
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$this->crp_id);
		if (!$qry->recordCount()) self::buildSummary($this->crp_id);

		$sql = "SELECT scl_class, scl_id, kb3_sum_corp.*
			FROM kb3_ship_classes left join kb3_sum_corp
				ON (csm_shp_id = scl_id AND csm_crp_id = ".$this->crp_id.")
			WHERE scl_class not in ('Drone','Unknown')
				ORDER BY scl_class";
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = (int)$row['csm_kill_count'];
			$this->summary[$row['scl_id']]['killisk'] = (float)$row['csm_kill_isk'];
			$this->summary[$row['scl_id']]['losscount'] = (int)$row['csm_loss_count'];
			$this->summary[$row['scl_id']]['lossisk'] = (float)$row['csm_loss_isk'];
		}
		$this->executed = true;
	}

	/**
	 * Build a new summary table for an corp.
	 *
	 * @param integer $crp_id
	 * @return type
	 */
	private static function buildSummary($crp_id)
	{
		$crp_id = (int)$crp_id;
		if (!$crp_id) {
			return false;
		}
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);

		// insert into summary ((select all kills) union (select all losses))
		$sql = "REPLACE INTO kb3_sum_corp (csm_crp_id, csm_shp_id, csm_kill_count, csm_kill_isk, csm_loss_count, csm_loss_isk)
		SELECT losses.csm_crp_id, losses.csm_shp_id, ifnull(kills.knb,0), ifnull(kills.kisk,0), ifnull(losses.lnb,0), ifnull(losses.lisk,0)
		FROM (SELECT $crp_id as csm_crp_id, shp_class as csm_shp_id, 0 as knb,0 as kisk ,count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_crp_id = $crp_id
					AND EXISTS (SELECT 1
							FROM kb3_inv_crp
							WHERE kll.kll_id = inc_kll_id
							AND inc_crp_id <> $crp_id limit 0,1)
			GROUP BY shp_class) losses left join (SELECT $crp_id as csm_crp_id, shp_class as csm_shp_id, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk,0 as lnb,0 as lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_crp ina ON (ina.inc_kll_id = kll.kll_id)
			WHERE ina.inc_crp_id = $crp_id
				AND kll.kll_crp_id <> $crp_id
			GROUP BY shp_class) kills ON (kills.csm_shp_id = losses.csm_shp_id)
		UNION
		SELECT kills.csm_crp_id, kills.csm_shp_id, ifnull(kills.knb,0), ifnull(kills.kisk,0), ifnull(losses.lnb,0), ifnull(losses.lisk,0)
		FROM (SELECT $crp_id as csm_crp_id, shp_class as csm_shp_id, 0 as knb,0 as kisk ,count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_crp_id = $crp_id
					AND EXISTS (SELECT 1
							FROM kb3_inv_crp
							WHERE kll.kll_id = inc_kll_id
							AND inc_crp_id <> $crp_id limit 0,1)
			GROUP BY shp_class) losses right join (SELECT $crp_id as csm_crp_id, shp_class as csm_shp_id, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk,0 as lnb,0 as lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_crp ina ON (ina.inc_kll_id = kll.kll_id)
			WHERE ina.inc_crp_id = $crp_id
				AND kll.kll_crp_id <> $crp_id
			GROUP BY shp_class) kills ON (kills.csm_shp_id = losses.csm_shp_id) ";
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
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$kill->getVictimcorpID());
		if ($qry->recordCount()) {
			$sql = "INSERT INTO kb3_sum_corp (csm_crp_id, csm_shp_id, csm_loss_count, csm_loss_isk) ".
					"VALUES ( ".$kill->getVictimCorpID().", ".$kill->getVictimShip()->getClass()->getID().", 1, ".
					$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
					"csm_loss_count = csm_loss_count + 1, ".
					"csm_loss_isk = csm_loss_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getCorpID()])) {
				continue;
			}
			$alls[$inv->getCorpID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$inv->getCorpID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "INSERT INTO kb3_sum_corp (csm_crp_id, csm_shp_id, csm_kill_count, csm_kill_isk) ".
					"VALUES ( ".$inv->getCorpID().", ".$kill->getVictimShip()->getClass()->getID().", 1, ".
					$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
					"csm_kill_count = csm_kill_count + 1, ".
					"csm_kill_isk = csm_kill_isk + ".$kill->getISKLoss();
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
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$kill->getVictimCorpID());
		// No summary table to remove kill from so skip.
		if ($qry->recordCount()) {
			$sql = "UPDATE kb3_sum_corp SET csm_loss_count = csm_loss_count - 1, ".
					" csm_loss_isk = csm_loss_isk - ".$kill->getISKLoss().
					" WHERE csm_crp_id = ".$kill->getVictimCorpID().
					" AND csm_shp_id = ".$kill->getVictimShip()->getClass()->getID();
			$qry->execute($sql);
		}
		foreach ($kill->getInvolved() as $inv) {
			if (isset($alls[$inv->getCorpID()])) {
				continue;
			}
			$alls[$inv->getCorpID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$inv->getCorpID());
			if (!$qry->recordCount()) {
				continue;
			}
			$sql = "UPDATE kb3_sum_corp SET csm_kill_count = csm_kill_count - 1, ".
					" csm_kill_isk = csm_kill_isk - ".$kill->getISKLoss().
					" WHERE csm_crp_id = ".$inv->getCorpID().
					" AND csm_shp_id = ".$kill->getVictimShip()->getClass()->getID();
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
