<?php
require_once('common/includes/class.kill.php');
require_once('common/includes/class.ship.php');

class allianceSummary
{
	function allianceSummary($all_id)
	{
		$this->all_id_ = intval($all_id);
		$this->executed_ = false;
	}
	//! Get the complete summary for this alliance.

	//! \return an array of ship id by kill/loss count/isk.
	function getSummary()
	{
		if(!$this->executed_) $this->execute();
		return $this->summary;
	}
	//! Return total ISK killed.
	function getTotalKillISK()
	{
		if(!$this->executed_) $this->execute();
		foreach($this->summary as $value)
			$totalisk .= $value['killisk'];
		return $totalisk;
	}
	//! Return total ISK lost.
	function getTotalLossISK()
	{
		if(!$this->executed_) $this->execute();
		foreach($this->summary as $value)
			$totalisk .= $value['lossisk'];
		return $totalisk;
	}
	//! Return the number of kills for the given ship class.
	function getKillCount($shp_class)
	{
		if(!$this->executed_) $this->execute();
		return intval($this->summary[$ship_class]['killcount']);
	}
	//! Return the ISK value of kills for the given ship class.
	function getKillISK($shp_class)
	{
		if(!$this->executed_) $this->execute();
		return intval($this->summary[$ship_class]['killisk']);
	}
	//! Return the number of losses for the given ship class.
	function getLossCount($shp_class)
	{
		if(!$this->executed_) $this->execute();
		return intval($this->summary[$ship_class]['losscount']);
	}
	//! Return the ISK value of losses for the given ship class.
	function getLossISK($shp_class)
	{
		if(!$this->executed_) $this->execute();
		return intval($this->summary[$ship_class]['lossisk']);
	}
	//! Fetch the summary information.
	function execute()
	{
		if($this->executed_) return;
		if(!$this->all_id_)
		{
			$this->executed_ = true;
			return false;
		}

		$qry = new DBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$this->all_id_);
		if(!$qry->recordCount())
			$this->buildSummary($this->all_id_);

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
		$this->executed_ = true;
	}
	//! Build a new summary table for an alliance.
	function buildSummary($all_id)
	{
		$all_id = intval($all_id);
		if(!$all_id) return false;
		$qry = new DBQuery();
		$qry->autocommit(false);
		$sql = "CREATE TEMPORARY TABLE `tmp_all_summary` (
		  `asm_all_id` int(11) NOT NULL DEFAULT '0',
		  `asm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `asm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `asm_kill_isk` float NOT NULL DEFAULT '0',
		  `asm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `asm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`asm_all_id`,`asm_shp_id`)
		) ENGINE = MEMORY";
		$qry->execute($sql);

		$sql = 'INSERT INTO tmp_all_summary (asm_all_id, asm_shp_id, asm_kill_count, asm_kill_isk)
			SELECT '.$all_id.', shp_class, COUNT(kll.kll_id) AS knb,
				sum(kll_isk_loss) AS kisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp
					ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_all ina ON (ina.ina_all_id ='.$all_id.'
						AND ina.ina_kll_id = kll.kll_id
						AND kll.kll_all_id <> '.$all_id.')
			GROUP BY shp_class';
		$qry->execute($sql);
		$sql = 'INSERT INTO tmp_all_summary (asm_all_id, asm_shp_id, asm_loss_count, asm_loss_isk)
			SELECT '.$all_id.', shp_class, count(distinct kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_all ina ON ( kll.kll_id = ina.ina_kll_id
							AND ina.ina_all_id <> '.$all_id.')
			WHERE  kll.kll_all_id = '.$all_id.'
			GROUP BY shp_class
			ON DUPLICATE KEY UPDATE asm_loss_count = values(asm_loss_count),
				asm_loss_isk = values(asm_loss_isk)';
		$qry->execute($sql);
		$qry->execute("INSERT IGNORE INTO kb3_sum_alliance SELECT * FROM tmp_all_summary");
		$qry->execute("DROP TEMPORARY TABLE tmp_all_summary");
		$qry->autocommit(true);
	}
	//! Add a Kill and its value to the summary.
	function addKill($kill)
	{
		$alls = array();
		$qry = new DBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_alliance WHERE asm_all_id = ".$kill->getVictimAllianceID());
// Causes big slowdowns for feeds so just return and leave summary creation until the page is viewed.
//		if(!$qry->recordCount()) allianceSummary::buildSummary($kill->getVictimAllianceID());
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
			if(intval($alls[$inv->getAllianceID()])) continue;
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
	function delKill($kill)
	{
		$alls = array();
		$qry = new DBQuery();
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
	function update($kill, $difference)
	{
		$alls = array();
		$qry = new DBQuery();
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

class corpSummary extends allianceSummary
{
	function corpSummary($crp_id)
	{
		$this->crp_id_ = intval($crp_id);
		$this->executed_ = false;
	}
	//! Fetch the summary information.
	function execute()
	{
		if($this->executed_) return;
		if(!$this->crp_id_)
		{
			$this->executed_ = true;
			return false;
		}

		$qry = new DBQuery();
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
		$this->executed_ = true;
	}
	//! Build a new summary table for an corp.
	function buildSummary($crp_id)
	{
		$crp_id = intval($crp_id);
		if(!$crp_id) return false;
		$qry = new DBQuery();
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
			SELECT '.$crp_id.', shp_class, COUNT(distinct kll.kll_id) AS knb,
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
			SELECT '.$crp_id.', shp_class, count(kll_id) AS lnb, sum(kll_isk_loss) AS lisk
			FROM kb3_kills kll
				INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
				INNER JOIN kb3_inv_crp inc ON ( kll.kll_id = inc.inc_kll_id)
			WHERE  kll.kll_crp_id = '.$crp_id.' AND inc.inc_crp_id <> '.$crp_id.'
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
		$qry = new DBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_corp WHERE csm_crp_id = ".$kill->getVictimcorpID());
		if(!$qry->recordCount())
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
			if(intval($alls[$inv->getCorpID()])) continue;
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
		$qry = new DBQuery();
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
		$qry = new DBQuery();
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

class pilotSummary extends allianceSummary
{
	function pilotSummary($plt_id)
	{
		$this->plt_id_ = intval($plt_id);
		$this->executed_ = false;
	}
	//! Fetch the summary information.
	function execute()
	{
		if($this->executed_) return;
		if(!$this->plt_id_)
		{
			$this->executed_ = true;
			return false;
		}

		$qry = new DBQuery();
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
		$this->executed_ = true;
	}
	//! Build a new summary table for an pilot.
	function buildSummary($plt_id)
	{
		$plt_id = intval($plt_id);
		if(!$plt_id) return false;
		$qry = new DBQuery();
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
			SELECT '.$plt_id.', shp_class, COUNT(distinct kll.kll_id) AS knb,
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
		$qry = new DBQuery();
		$qry->execute("SELECT 1 FROM kb3_sum_pilot WHERE psm_plt_id = ".$kill->getVictimID());
//		if(!$qry->recordCount()) pilotSummary::buildSummary($kill->getVictimpilotID());
		if(!$qry->recordCount())
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
			if(intval($alls[$inv->getPilotID()])) continue;
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
		$qry = new DBQuery();
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
		$qry = new DBQuery();
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

class summaryCache
{
	function addKill($kill)
	{
		allianceSummary::addKill($kill);
		corpSummary::addKill($kill);
		pilotSummary::addKill($kill);
	}
	function delKill($kill)
	{
		allianceSummary::delKill($kill);
		corpSummary::delKill($kill);
		pilotSummary::delKill($kill);
	}
	function update($kill, $difference)
	{
		allianceSummary::update($kill, $difference);
		corpSummary::update($kill, $difference);
		pilotSummary::update($kill, $difference);
	}
}
?>