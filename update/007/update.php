<?php
/**
 * @package EDK
 */

function update007()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "007" )
	{
		$qry = DBFactory::getDBQuery(true);
		if(is_null(config::get('007updatestatus')))
			config::set('007updatestatus',0);
		if(config::get('007updatestatus') <1)
		{
		// Add columns for external ids.
			$qry->execute("SHOW COLUMNS FROM kb3_alliances LIKE 'all_external_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_alliances` ".
					"ADD `all_external_id` INT( 11 ) UNSIGNED NULL ".
					"DEFAULT NULL , ADD UNIQUE ( all_external_id )";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_external_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ".
					"ADD `crp_external_id` INT( 11 ) UNSIGNED NULL ".
					"DEFAULT NULL , ADD UNIQUE ( crp_external_id )";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_external_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_kills` ".
					"ADD `kll_external_id` INT( 11 ) UNSIGNED NULL ".
					"DEFAULT NULL , ADD UNIQUE ( kll_external_id )";
				$qry->execute($sql);
			}
			config::set('007updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. External ID columns added");
			$smarty->display('update.tpl');
			die();
		}
		// Add isk loss column to kb3_kills
		if(config::get('007updatestatus') <8)
		{
		// Update price with items destroyed and ship value, excluding
		// blueprints since default cost is for BPO and BPC looks identical
			if(config::get('007updatestatus') <2)
			{
				$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_ship` (
				  `kll_id` int(11) NOT NULL DEFAULT '0',
				  `value` float NOT NULL DEFAULT '0',
				  PRIMARY KEY (`kll_id`)
				) ENGINE=MyISAM";
				$qry->execute($sql);
				$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_destroyed` (
				  `kll_id` int(11) NOT NULL DEFAULT '0',
				  `value` float NOT NULL DEFAULT '0',
				  PRIMARY KEY (`kll_id`)
				) ENGINE=MyISAM";
				$qry->execute($sql);
				$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_dropped` (
				  `kll_id` int(11) NOT NULL DEFAULT '0',
				  `value` float NOT NULL DEFAULT '0',
				  PRIMARY KEY (`kll_id`)
				) ENGINE=MyISAM";
				$qry->execute($sql);
				config::set('007updatestatus',2);
			}
			$qry->execute("LOCK TABLES tmp_price_ship WRITE, tmp_price_destroyed WRITE,
				tmp_price_dropped WRITE, kb3_kills WRITE, kb3_ships WRITE,
				kb3_ships_values WRITE, kb3_items_destroyed WRITE, kb3_items_dropped WRITE,
				kb3_invtypes WRITE, kb3_item_price WRITE, kb3_config WRITE");
			if(config::get('007updatestatus') <3)
			{
				$qry->execute("INSERT IGNORE INTO tmp_price_ship select
					kll_id,if(isnull(shp_value),shp_baseprice,shp_value) FROM kb3_kills
					INNER JOIN kb3_ships ON kb3_ships.shp_id = kll_ship_id
					LEFT JOIN kb3_ships_values ON kb3_ships_values.shp_id = kll_ship_id");
				$qry->execute($sql);
				config::set('007updatestatus',3);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "7. Kill values: Ship prices calculated");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('007updatestatus') <4)
			{
				$sql = "INSERT IGNORE INTO tmp_price_destroyed
					SELECT itd_kll_id,
					sum(if(typeName LIKE '%Blueprint%',0,if(isnull(itd_quantity),
					0,itd_quantity * if(price = 0 OR isnull(price),basePrice,price))))
					FROM kb3_items_destroyed
					LEFT JOIN kb3_item_price ON kb3_item_price.typeID = itd_itm_id
					LEFT JOIN kb3_invtypes ON itd_itm_id = kb3_invtypes.typeID
					GROUP BY itd_kll_id";
				$qry->execute($sql);
				config::set('007updatestatus',4);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "7. Kill values: Destroyed item prices calculated");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('007updatestatus') <5)
			{
				if(config::get('kd_droptototal'))
				{
					$action = "calculated";
					$sql = "INSERT INTO tmp_price_dropped
						SELECT itd_kll_id,
						sum(if(typeName LIKE '%Blueprint%',0,if(isnull(itd_quantity),
						0,itd_quantity * if(price = 0 OR isnull(price),basePrice,price))))
						FROM kb3_items_dropped
						LEFT JOIN kb3_item_price ON kb3_item_price.typeID = itd_itm_id
						LEFT JOIN kb3_invtypes ON itd_itm_id = kb3_invtypes.typeID
						GROUP BY itd_kll_id";
					$qry->execute($sql);
				}
				else $action = "ignored";
				config::set('007updatestatus',5);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "7. Kill values: Dropped item prices $action");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('007updatestatus') <7)
			{
				$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_isk_loss'");
				if(!$qry->recordCount())
				{
					$qry->execute("ALTER TABLE `kb3_kills` ADD `kll_isk_loss` FLOAT NOT NULL DEFAULT '0'");
					config::set('007updatestatus',7);
					$smarty->assign('refresh',1);
					$smarty->assign('content', "7. Kill values: ISK column created");
					$smarty->display('update.tpl');
					die();
				}
				config::set('007updatestatus',7);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "7. Kill values: ISK column already exists.");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('007updatestatus') <8)
			{
			// default step size
				$step = 8192;
				if(!config::get('007.8status'))
				{
					config::set('007.8status', 0);
					config::set('007.8step', $step);
				}
				// If we had to restart then halve the step size up to 4 times.
				if(config::get('007.8status') > 0 && config::get('007.8step') >= $step / 2^4)
					config::set('007.8step', config::get('007.8step') / 2);
				$qry->execute("SELECT max(kll_id) as max FROM kb3_kills");
				$row=$qry->getRow();
				$count=$row['max'];
				while(config::get('007.8status') < $count)
				{
					$sql = 'UPDATE kb3_kills
						natural join tmp_price_ship
						left join tmp_price_destroyed on kb3_kills.kll_id = tmp_price_destroyed.kll_id ';
					if(config::get('kd_droptototal')) $sql .= ' left join tmp_price_dropped on kb3_kills.kll_id = tmp_price_dropped.kll_id ';
					$sql .= 'SET kb3_kills.kll_isk_loss = tmp_price_ship.value + ifnull(tmp_price_destroyed.value,0) ';
					if(config::get('kd_droptototal')) $sql .= ' + ifnull(tmp_price_dropped.value,0) ';
					$sql .= ' WHERE kb3_kills.kll_id >= '.config::get('007.8status').' AND kb3_kills.kll_id < '.
						(intval(config::get('007.8status')) + intval(config::get('007.8step')));
					$qry->execute ($sql);
					config::set('007.8status',(intval(config::get('007.8status')) + intval(config::get('007.8step'))) );
				}
				config::del('007.8status');
				config::del('007.8step');
				$qry->execute("UNLOCK TABLES");
				$qry->execute('DROP TABLE tmp_price_ship');
				$qry->execute('DROP TABLE tmp_price_destroyed');
				$qry->execute('DROP TABLE tmp_price_dropped');
				config::set('007updatestatus',8);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "7. Kill values: Totals updated");
				$smarty->display('update.tpl');
				die();
			}
		}
		if(config::get('007updatestatus') <9)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_fb_crp_id'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE `kb3_kills` DROP `kll_fb_crp_id`");
			config::set('007updatestatus',9);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. kll_fb_crp_id column dropped");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('007updatestatus') <10)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_fb_all_id'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE `kb3_kills` DROP `kll_fb_all_id`");
			config::set('007updatestatus',10);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. kll_fb_all_id column dropped");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('007updatestatus') <11)
		{
		// Drop unused columns
			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_trial'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE kb3_corps DROP crp_trial");
			$qry->execute("SHOW COLUMNS FROM kb3_pilots LIKE 'plt_killpoints'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE kb3_pilots DROP plt_killpoints");
			$qry->execute("SHOW COLUMNS FROM kb3_pilots LIKE 'plt_losspoints'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE kb3_pilots DROP plt_losspoints");
			config::set('007updatestatus',11);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. Unused crp and plt columns dropped");
			$smarty->display('update.tpl');
			die();
		}

		// Add corp and alliance index to kb3_inv_detail
		$qry->execute("SHOW INDEX FROM kb3_inv_detail");

		$indexcexists = false;
		$indexaexists = false;
		while($testresult = $qry->getRow())
			if($testresult['Column_name'] == 'ind_crp_id')
				$indexcexists = true;
			elseif($testresult['Column_name'] == 'ind_all_id')
				$indexaexists = true;
		if(config::get('007updatestatus') <12)
		{
			if(!$indexcexists)
				$qry->execute("ALTER  TABLE `kb3_inv_detail` ADD INDEX ( `ind_crp_id` ) ");
			config::set('007updatestatus',12);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. kb3_inv_detail ind_crp_id index added");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('007updatestatus') <13)
		{
			if(!$indexaexists)
				$qry->execute("ALTER  TABLE `kb3_inv_detail` ADD INDEX ( `ind_all_id` ) ");
			config::set('007updatestatus',13);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. kb3_inv_detail ind_all_id index added");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('007updatestatus') <14)
		{
		// Add table for api cache
			$sql = "CREATE TABLE IF NOT EXISTS `kb3_apicache` (
				 `cfg_site` varchar(16) NOT NULL default '',
				 `cfg_key` varchar(32) NOT NULL default '',
				 `cfg_value` text NOT NULL,
				 PRIMARY KEY  (`cfg_site`,`cfg_key`)
				 )";
			$qry->execute($sql);
			$qry->execute("CREATE TABLE IF NOT EXISTS `kb3_apilog` (
				`log_site` VARCHAR( 20 ) NOT NULL ,
				`log_keyname` VARCHAR( 20 ) NOT NULL ,
				`log_posted` INT NOT NULL ,
				`log_errors` INT NOT NULL ,
				`log_ignored` INT NOT NULL ,
				`log_verified` INT NOT NULL ,
				`log_totalmails` INT NOT NULL ,
				`log_source` VARCHAR( 20 ) NOT NULL ,
				`log_type` VARCHAR( 20 ) NOT NULL ,
				`log_timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
				) ENGINE = MYISAM ");

			// set API update complete
			config::set('API_DBUpdate', '1');
			config::set('007updatestatus',14);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. API tables added");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('007updatestatus') <15)
		{

		// Add subsystem slot
			$qry->execute("SELECT 1 FROM kb3_item_locations WHERE itl_id = 7");
			if(!$qry->recordCount())
			{
				$qry->execute("INSERT INTO `kb3_item_locations` (`itl_id`, `itl_location`) VALUES(7, 'Subsystem Slot')");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 954 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 955 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 956 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 957 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 958 LIMIT 1");
			}
			config::set('007updatestatus',15);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. Subsystem slots added");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('007updatestatus') <16)
		{
			$qry->execute('SHOW TABLES');
			$qry2 = DBFactory::getDBQuery(true);
			while($row = $qry->getRow())
			{
				$tablename = implode($row);
				if($tablename == 'kb3_inv_all') $qry2->execute("TRUNCATE kb3_inv_all");
				if($tablename == 'kb3_inv_crp') $qry2->execute("TRUNCATE kb3_inv_crp");
				if($tablename == 'kb3_inv_plt') $qry2->execute("TRUNCATE kb3_inv_plt");
			}
			killCache();
			config::set("DBUpdate","007");
			$qry->execute("UPDATE kb3_config SET cfg_value = '007' WHERE cfg_key = 'DBUpdate'");
			config::del('007updatestatus');
			$smarty->assign('refresh',1);
			$smarty->assign('content', "7. Empty tables truncated.<br>Update 007 completed.");
			$smarty->display('update.tpl');
			die();
		}
	}
}
