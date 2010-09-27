<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


//! Store summary statistics for Alliances.
class allianceSummary extends statSummary
{
	private $all_id_ = null;

	function allianceSummary($all_id)
	{
		$this->all_id_ = intval($all_id);
	}
	//! Fetch the summary information.
	protected function execute()
	{
		if($this->executed) return;
		if(!$this->all_id_)
		{
			$this->executed = true;
			return false;
		}

		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$this->all_id_);
		if(!$qry->recordCount())
			self::buildSummary($this->all_id_);

		$sql = "SELECT scl_class, scl_id, kb3_sum_alliance.*
			FROM kb3_ship_classes left join kb3_sum_alliance
				ON (asm_shp_id = scl_id AND asm_all_id = ".$this->all_id_.")
			WHERE scl_class not in ('Drone','Unknown')
				ORDER BY scl_class";
		$qry->execute($sql);
		while($row = $qry->getRow())
		{
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = intval($row['asm_kill_count']);
			$this->summary[$row['scl_id']]['killisk'] = floatval($row['asm_kill_isk']);
			$this->summary[$row['scl_id']]['losscount'] = intval($row['asm_loss_count']);
			$this->summary[$row['scl_id']]['lossisk'] = floatval($row['asm_loss_isk']);
		}
		$this->executed = true;
	}
	//! Build a new summary table for an alliance.
	private static function buildSummary($all_id)
	{
		$all_id = intval($all_id);
		if(!$all_id) return false;
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);

		$sql = "REPLACE INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_kill_count, asm_kill_isk, asm_loss_count, asm_loss_isk) select kills.asm_all_id, kills.asm_shp_id, kills.knb, kills.kisk, losses.lnb,losses.lisk 
		FROM (SELECT $all_id as asm_all_id, shp_class as asm_shp_id, 0 as knb,0 as kisk ,count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_all_id = $all_id
					AND EXISTS (SELECT 1
							FROM kb3_inv_all
							WHERE kll.kll_id = ina_kll_id
							AND ina_all_id <> $all_id limit 0,1)
			GROUP BY shp_class) losses join (SELECT $all_id as asm_all_id, shp_class as asm_shp_id, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk,0 as lnb,0 as lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_all ina ON (ina.ina_kll_id = kll.kll_id)
			WHERE ina.ina_all_id = $all_id
				AND kll.kll_all_id <> $all_id
			GROUP BY shp_class) kills
		WHERE kills.asm_all_id = losses.asm_all_id
			AND kills.asm_shp_id = losses.asm_shp_id ";
		$qry->execute($sql);
		$qry->autocommit(true);
		return;

//		$sql = "CREATE TEMPORARY TABLE `tmp_all_summary` (
//		  `asm_all_id` int(11) NOT NULL DEFAULT '0',
//		  `asm_shp_id` int(3) NOT NULL DEFAULT '0',
//		  `asm_kill_count` int(11) NOT NULL DEFAULT '0',
//		  `asm_kill_isk` float NOT NULL DEFAULT '0',
//		  `asm_loss_count` int(11) NOT NULL DEFAULT '0',
//		  `asm_loss_isk` float NOT NULL DEFAULT '0',
//		  PRIMARY KEY (`asm_all_id`,`asm_shp_id`)
//		) ENGINE = MEMORY";
//		$qry->execute($sql);
//
//		$sql = 'INSERT INTO tmp_all_summary (asm_all_id, asm_shp_id, asm_kill_count, asm_kill_isk)
//			SELECT '.$all_id.', shp_class, COUNT(kll.kll_id) AS knb,
//				sum(kll_isk_loss) AS kisk
//			FROM kb3_kills kll
//				INNER JOIN kb3_ships shp
//					ON ( shp.shp_id = kll.kll_ship_id )
//				INNER JOIN kb3_inv_all ina ON (ina.ina_kll_id = kll.kll_id)
//			WHERE ina.ina_all_id ='.$all_id.'
//				AND kll.kll_all_id <> '.$all_id.'
//			GROUP BY shp_class';
//		$qry->execute($sql);
//		$sql = 'INSERT INTO tmp_all_summary (asm_all_id, asm_shp_id, asm_loss_count, asm_loss_isk)
//			SELECT '.$all_id.', shp_class, count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
//			FROM kb3_kills kll
//				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
//			WHERE  kll.kll_all_id = '.$all_id.'
//					AND EXISTS (SELECT 1
//							FROM kb3_inv_all
//							WHERE kll.kll_id = ina_kll_id
//							AND ina_all_id <> '.$all_id.' limit 0,1)
//			GROUP BY shp_class
//			ON DUPLICATE KEY UPDATE asm_loss_count = values(asm_loss_count),
//				asm_loss_isk = values(asm_loss_isk)';
//		$qry->execute($sql);
//		$qry->execute("INSERT IGNORE INTO kb3_sum_alliance SELECT * FROM tmp_all_summary");
//		$qry->execute("DROP TEMPORARY TABLE tmp_all_summary");
//		$qry->autocommit(true);
	}
	//! Add a Kill and its value to the summary.
	public static function addKill($kill)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$kill->getVictimAllianceID());
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "INSERT INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_loss_count, asm_loss_isk) ".
				"VALUES ( ".$kill->getVictimAllianceID().", ".$class->getID().", 1, ".
				$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
				"asm_loss_count = asm_loss_count + 1, ".
				"asm_loss_isk = asm_loss_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(isset($alls[$inv->getAllianceID()])) continue;
			$alls[$inv->getAllianceID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$inv->getAllianceID());
			//if(!$qry->recordCount()) allianceSummary::buildSummary($inv->getAllianceID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "INSERT INTO kb3_sum_alliance (asm_all_id, asm_shp_id, asm_kill_count, asm_kill_isk) ".
				"VALUES ( ".$inv->getAllianceID().", ".$class->getID().", 1, ".
				$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
				"asm_kill_count = asm_kill_count + 1, ".
				"asm_kill_isk = asm_kill_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
	}
	//! Add a Kill and its value to the summary.
	public static function delKill($kill)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$kill->getVictimAllianceID());
		// No summary table to remove kill from so skip.
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_alliance SET asm_loss_count = asm_loss_count - 1, ".
				" asm_loss_isk = asm_loss_isk - ".$kill->getISKLoss().
				" WHERE asm_all_id = ".$kill->getVictimAllianceID().
					" AND asm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(intval($alls[$inv->getAllianceID()])) continue;
			$alls[$inv->getAllianceID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$inv->getAllianceID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_alliance SET asm_kill_count = asm_kill_count - 1, ".
				" asm_kill_isk = asm_kill_isk - ".$kill->getISKLoss().
				" WHERE asm_all_id = ".$inv->getAllianceID().
					" AND asm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
	}
	//! Update the summary table when a kill value changes.
	public static function update($kill, $difference)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$kill->getVictimAllianceID());
		// No summary table to remove kill from so skip.
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_alliance SET asm_loss_isk = asm_loss_isk + ".$difference.
				" WHERE asm_all_id = ".$kill->getVictimAllianceID().
					" AND asm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(intval($alls[$inv->getAllianceID()])) continue;
			$alls[$inv->getAllianceID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$inv->getAllianceID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_alliance SET asm_kill_isk = asm_kill_isk + ".$difference.
				" WHERE asm_all_id = ".$inv->getAllianceID().
					" AND asm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
	}
}
