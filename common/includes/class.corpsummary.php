<?php
/*
 * $Id $
 */

require_once('common/includes/class.kill.php');
require_once('common/includes/class.ship.php');
require_once('class.statsummary.php');

//! Store summary statistics for Corporations.
class corpSummary extends statSummary
{
	function corpSummary($crp_id)
	{
		$this->crp_id_ = intval($crp_id);
		$this->executed = false;
	}
	//! Fetch the summary information.
	function execute()
	{
		if($this->executed) return;
		if(!$this->crp_id_)
		{
			$this->executed = true;
			return false;
		}

		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$this->crp_id_);
		if(!$qry->recordCount())
			$this->buildSummary($this->crp_id_);

		$sql = "SELECT scl_class, scl_id, kb3_sum_corp.*
			FROM kb3_ship_classes left join kb3_sum_corp
				ON (csm_shp_id = scl_id AND csm_crp_id = ".$this->crp_id_.")
			WHERE scl_class not in ('Drone','Unknown')
				ORDER BY scl_class";
		$qry->execute($sql);
		while($row = $qry->getRow())
		{
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = intval($row['csm_kill_count']);
			$this->summary[$row['scl_id']]['killisk'] = floatval($row['csm_kill_isk']);
			$this->summary[$row['scl_id']]['losscount'] = intval($row['csm_loss_count']);
			$this->summary[$row['scl_id']]['lossisk'] = floatval($row['csm_loss_isk']);
		}
		$this->executed = true;
	}
	//! Build a new summary table for an corp.
	function buildSummary($crp_id)
	{
		$crp_id = intval($crp_id);
		if(!$crp_id) return false;
		$qry = DBFactory::getDBQuery();;
		$qry->autocommit(false);
		$sql = "CREATE TEMPORARY TABLE `tmp_sum_corp` (
		  `csm_crp_id` int(11) NOT NULL DEFAULT '0',
		  `csm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `csm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `csm_kill_isk` float NOT NULL DEFAULT '0',
		  `csm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `csm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`csm_crp_id`,`csm_shp_id`)
		) ENGINE = MEMORY";
		$qry->execute($sql);

		$sql = 'INSERT INTO tmp_sum_corp (csm_crp_id, csm_shp_id, csm_kill_count, csm_kill_isk)
			SELECT '.$crp_id.', shp_class, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_crp inc
					ON (inc.inc_kll_id = kll.kll_id)
			WHERE inc.inc_crp_id ='.$crp_id.' AND kll.kll_crp_id <> '.$crp_id.'
			GROUP BY shp_class';
		$qry->execute($sql);
		$sql = 'INSERT INTO tmp_sum_corp (csm_crp_id, csm_shp_id, csm_loss_count, csm_loss_isk)
			SELECT '.$crp_id.', shp_class, count(kll_id) AS lnb,
				sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
			WHERE  kll.kll_crp_id = '.$crp_id.'
				AND EXISTS (SELECT 1
						FROM kb3_inv_crp
						WHERE kll.kll_id = inc_kll_id
						AND inc_crp_id <> '.$crp_id.' limit 0,1)
			GROUP BY shp_class
			ON DUPLICATE KEY UPDATE csm_loss_count = values(csm_loss_count),
				csm_loss_isk = values(csm_loss_isk)';
		$qry->execute($sql);
		$qry->execute("INSERT INTO kb3_sum_corp SELECT * FROM tmp_sum_corp");
		$qry->execute("DROP TEMPORARY TABLE tmp_sum_corp");
		$qry->autocommit(true);
	}
	//! Add a Kill and its value to the summary.
	function addKill($kill)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$kill->getVictimcorpID());
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "INSERT INTO kb3_sum_corp (csm_crp_id, csm_shp_id, csm_loss_count, csm_loss_isk) ".
				"VALUES ( ".$kill->getVictimCorpID().", ".$class->getID().", 1, ".
				$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
				"csm_loss_count = csm_loss_count + 1, ".
				"csm_loss_isk = csm_loss_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(isset($alls[$inv->getCorpID()])) continue;
			$alls[$inv->getCorpID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$inv->getCorpID());
			//if(!$qry->recordCount()) corpSummary::buildSummary($inv->getcorpID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "INSERT INTO kb3_sum_corp (csm_crp_id, csm_shp_id, csm_kill_count, csm_kill_isk) ".
				"VALUES ( ".$inv->getCorpID().", ".$class->getID().", 1, ".
				$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
				"csm_kill_count = csm_kill_count + 1, ".
				"csm_kill_isk = csm_kill_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
	}
	//! Add a Kill and its value to the summary.
	function delKill($kill)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$kill->getVictimCorpID());
		// No summary table to remove kill from so skip.
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_corp SET csm_loss_count = csm_loss_count - 1, ".
				" csm_loss_isk = csm_loss_isk - ".$kill->getISKLoss().
				" WHERE csm_crp_id = ".$kill->getVictimCorpID().
					" AND csm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(intval($alls[$inv->getCorpID()])) continue;
			$alls[$inv->getCorpID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$inv->getCorpID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_corp SET csm_kill_count = csm_kill_count - 1, ".
				" csm_kill_isk = csm_kill_isk - ".$kill->getISKLoss().
				" WHERE csm_crp_id = ".$inv->getCorpID().
					" AND csm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
	}
	//! Update the summary table when a kill value changes.
	function update($kill, $difference)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$kill->getVictimCorpID());
		// No summary table to remove kill from so skip.
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_corp SET csm_loss_isk = csm_loss_isk + ".$difference.
				" WHERE csm_crp_id = ".$kill->getVictimCorpID().
					" AND csm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(intval($alls[$inv->getCorpID()])) continue;
			$alls[$inv->getCorpID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$inv->getCorpID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_corp SET csm_kill_isk = csm_kill_isk + ".$difference.
				" WHERE csm_crp_id = ".$inv->getCorpID().
					" AND csm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
	}
}
