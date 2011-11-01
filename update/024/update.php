<?php
/**
 * @package EDK
 */

// Replace internal IDs with CCP IDs
function update024()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "024" )
	{
		if(is_null(config::get('024updatestatus'))) {config::set('024updatestatus',0);}
		$qry = DBFactory::getDBQuery(true);

		if(config::get('024updatestatus') <1)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_systems LIKE 'sys_eve_id'");
			if($qry->recordCount()) {
				$qry->execute("SELECT min(sys_eve_id) as min FROM kb3_systems");
				$row = $qry->getRow();
				$min = $row['min'];
				$qry->execute("UPDATE kb3_kills
					JOIN kb3_systems on sys_id = kll_system_id
					SET kll_system_id = sys_eve_id
					WHERE kll_system_id < $min");
			}
			config::set('024updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. Updated kb3_kills systems.");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('024updatestatus') <2)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_systems LIKE 'sys_eve_id'");
			if($qry->recordCount()) {
				$qry->execute("SELECT min(sys_eve_id) as min FROM kb3_systems");
				$row = $qry->getRow();
				$min = $row['min'];
				$qry->execute("UPDATE kb3_contract_details
					JOIN kb3_systems on sys_id = ctd_sys_id
					SET ctd_sys_id = sys_eve_id
					WHERE ctd_sys_id < $min");
			}
			config::set('024updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. Updated kb3_contract_details.");
			$smarty->display('update.tpl');
			die();
		}
        if(config::get('024updatestatus') <3)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_systems LIKE 'sys_eve_id'");
			if($qry->recordCount()) {
				$qry->execute("UPDATE kb3_systems SET sys_id = sys_eve_id");
			}
			config::set('024updatestatus',3);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. kb3_systems is updated.");
			$smarty->display('update.tpl');
			die();
		}
        if(config::get('024updatestatus') <4)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_ships LIKE 'shp_externalid'");
			if($qry->recordCount()) {
				$qry->execute("UPDATE kb3_kills JOIN kb3_ships ON kll_ship_id ="
						." shp_id SET kll_ship_id = shp_externalid");
			}
			config::set('024updatestatus',4);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. kb3_kills ships are updated.");
			$smarty->display('update.tpl');
			die();
		}
        if(config::get('024updatestatus') <5)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_ships LIKE 'shp_externalid'");
			if($qry->recordCount()) {
				$qry->execute("UPDATE kb3_inv_detail JOIN kb3_ships ON ind_shp_id ="
						." shp_id SET ind_shp_id = shp_externalid");
			}
			config::set('024updatestatus',5);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. kb3_inv_detail ships are updated.");
			$smarty->display('update.tpl');
			die();
		}
        if(config::get('024updatestatus') <6)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_ships LIKE 'shp_externalid'");
			if($qry->recordCount()) {
				$qry->execute("UPDATE kb3_ships SET shp_id = shp_id+100000");
				$qry->execute("UPDATE kb3_ships SET shp_id = shp_externalid");
			}
			config::set('024updatestatus',6);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. kb3_ships is updated.");
			$smarty->display('update.tpl');
			die();
		}
        if(config::get('024updatestatus') <7)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_ships LIKE 'shp_externalid'");
			if($qry->recordCount()) {
				$qry->execute("ALTER TABLE `kb3_ships`"
						." DROP `shp_name`,"
						." DROP `shp_externalid`,"
						." DROP `shp_rce_id`,"
						." DROP `shp_baseprice`,"
						." DROP `shp_techlevel`,"
						." DROP `shp_isfaction`");
			}
			config::set('024updatestatus',7);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. kb3_ships shrunk.");
			$smarty->display('update.tpl');
			die();
		}
        if(config::get('024updatestatus') <8)
		{
			$qry->execute("SHOW TABLES LIKE 'kb3_ships_values'");
			if($qry->recordCount())	{
				$qry->execute("DROP TABLE kb3_ships_values");
			}
			config::set('024updatestatus',8);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. kb3_ships_values removed.");
			$smarty->display('update.tpl');
			die();
		}
        if(config::get('024updatestatus') <9)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_systems LIKE 'sys_eve_id'");
			if($qry->recordCount()) {
				$qry->execute("ALTER TABLE `kb3_systems` DROP `sys_eve_id`");
			}
			config::set('024updatestatus',9);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "24. kb3_systems shrunk.");
			$smarty->display('update.tpl');
			die();
		}
		killCache();
		config::set("DBUpdate", "024");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '024' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '024'");
		config::del("024updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 024 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

