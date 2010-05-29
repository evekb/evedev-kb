<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

require_once('common/includes/class.kill.php');
require_once('common/includes/class.ship.php');
require_once('class.statsummary.php');

//! Store summary statistics for Pilots.
class pilotSummary extends statSummary
{
	function pilotSummary($plt_id)
	{
		$this->plt_id_ = intval($plt_id);
		$this->executed = false;
	}
	//! Fetch the summary information.
	function execute()
	{
		if($this->executed) return;
		if(!$this->plt_id_)
		{
			$this->executed = true;
			return false;
		}

		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$this->plt_id_);
		if(!$qry->recordCount())
			$this->buildSummary($this->plt_id_);

		$sql = "SELECT scl_class, scl_id, kb3_sum_pilot.*
			FROM kb3_ship_classes left join kb3_sum_pilot
				ON (psm_shp_id = scl_id AND psm_plt_id = ".$this->plt_id_.")
			WHERE scl_class not in ('Drone','Unknown')
				ORDER BY scl_class";
		$qry->execute($sql);
		while($row = $qry->getRow())
		{
			$this->summary[$row['scl_id']]['class_name'] = $row['scl_class'];
			$this->summary[$row['scl_id']]['killcount'] = intval($row['psm_kill_count']);
			$this->summary[$row['scl_id']]['killisk'] = floatval($row['psm_kill_isk']);
			$this->summary[$row['scl_id']]['losscount'] = intval($row['psm_loss_count']);
			$this->summary[$row['scl_id']]['lossisk'] = floatval($row['psm_loss_isk']);
		}
		$this->executed = true;
	}
	//! Build a new summary table for an pilot.
	function buildSummary($plt_id)
	{
		$plt_id = intval($plt_id);
		if(!$plt_id) return false;
		$qry = DBFactory::getDBQuery();;
		$qry->autocommit(false);
		$sql = "CREATE TEMPORARY TABLE `tmp_sum_pilot` (
		  `psm_plt_id` int(11) NOT NULL DEFAULT '0',
		  `psm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `psm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `psm_kill_isk` float NOT NULL DEFAULT '0',
		  `psm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `psm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`psm_plt_id`,`psm_shp_id`)
		) ENGINE = MEMORY";
		$qry->execute($sql);

		$sql = 'INSERT INTO tmp_sum_pilot (psm_plt_id, psm_shp_id, psm_kill_count, psm_kill_isk)
			SELECT '.$plt_id.', shp_class, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_plt inc
					ON (inp.inp_kll_id = kll.kll_id)
			WHERE inp.inp_plt_id ='.$plt_id.' AND kll.kll_plt_id <> '.$plt_id.'
			GROUP BY shp_class';
		$qry->execute($sql);
		$sql = 'INSERT INTO tmp_sum_pilot (psm_plt_id, psm_shp_id, psm_loss_count, psm_loss_isk)
			SELECT '.$plt_id.', shp_class, count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_plt inc ON ( kll.kll_id = inp.inp_kll_id)
			WHERE  kll.kll_plt_id = '.$plt_id.' AND inp.inp_plt_id <> '.$plt_id.'
			GROUP BY shp_class
			ON DUPLICATE KEY UPDATE psm_loss_count = values(psm_loss_count),
				psm_loss_isk = values(psm_loss_isk)';
		$qry->execute($sql);
		$qry->execute("INSERT INTO kb3_sum_pilot SELECT * FROM tmp_sum_pilot");
		$qry->execute("DROP TEMPORARY TABLE tmp_sum_pilot");
		$qry->autocommit(true);
	}
	//! Add a Kill and its value to the summary.
	function addKill($kill)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$kill->getVictimID());
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "INSERT INTO kb3_sum_pilot (psm_plt_id, psm_shp_id, psm_loss_count, psm_loss_isk) ".
				"VALUES ( ".$kill->getVictimID().", ".$class->getID().", 1, ".
				$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
				"psm_loss_count = psm_loss_count + 1, ".
				"psm_loss_isk = psm_loss_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(isset($alls[$inv->getPilotID()])) continue;
			$alls[$inv->getPilotID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$inv->getPilotID());
			if(!$qry->recordCount()) continue;
			//if(!$qry->recordCount())pilotSummary::buildSummary($inv->getpilotID());
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "INSERT INTO kb3_sum_pilot (psm_plt_id, psm_shp_id, psm_kill_count, psm_kill_isk) ".
				"VALUES ( ".$inv->getPilotID().", ".$class->getID().", 1, ".
				$kill->getISKLoss().") ON DUPLICATE KEY UPDATE ".
				"psm_kill_count = psm_kill_count + 1, ".
				"psm_kill_isk = psm_kill_isk + ".$kill->getISKLoss();
			$qry->execute($sql);
		}
	}
	//! Add a Kill and its value to the summary.
	function delKill($kill)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$kill->getVictimID());
		// No summary table to remove kill from so skip.
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_pilot SET psm_loss_count = psm_loss_count - 1, ".
				" psm_loss_isk = psm_loss_isk - ".$kill->getISKLoss().
				" WHERE psm_plt_id = ".$kill->getVictimID().
					" AND psm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(intval($alls[$inv->getPilotID()])) continue;
			$alls[$inv->getPilotID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$inv->getPilotID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_pilot SET psm_kill_count = psm_kill_count - 1, ".
				" psm_kill_isk = psm_kill_isk - ".$kill->getISKLoss().
				" WHERE psm_plt_id = ".$inv->getPilotID().
					" AND psm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
	}
	//! Update the summary table when a kill value changes.
	function update($kill, $difference)
	{
		$alls = array();
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$kill->getVictimID());
		// No summary table to remove kill from so skip.
		if($qry->recordCount())
		{
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_pilot SET psm_loss_isk = psm_loss_isk - ".$difference.
				" WHERE psm_plt_id = ".$kill->getVictimID().
					" AND psm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
		foreach($kill->involvedparties_ as $inv)
		{
			if(intval($alls[$inv->getPilotID()])) continue;
			$alls[$inv->getPilotID()] = 1;
			$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$inv->getPilotID());
			if(!$qry->recordCount()) continue;
			// php4 doesn't handle indirect references so specify each one.
			$ship = $kill->getVictimShip();
			$class = $ship->getClass();
			$sql = "UPDATE kb3_sum_pilot SET psm_kill_isk = psm_kill_isk - ".$difference.
				" WHERE psm_plt_id = ".$inv->getPilotID().
					" AND psm_shp_id = ".$class->getID();
			$qry->execute($sql);
		}
	}
}
