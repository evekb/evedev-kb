<?php
/**
 * @package EDK
 */

// Fix any broken ship IDs
function update023()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "023" )
	{
		if(is_null(config::get('023updatestatus'))) {
			config::set('023updatestatus',0);
		}
		$qry = DBFactory::getDBQuery(true);

		if(config::get('023updatestatus') <1)
		{
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 206 WHERE ind_shp_id = 432"); // Kitsune
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 348 WHERE ind_shp_id = 435"); // Redeemer
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 349 WHERE ind_shp_id = 436"); // Sin
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 352 WHERE ind_shp_id = 437"); // Widow
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 354 WHERE ind_shp_id = 438"); // Panther

			config::set('023updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "23. Updated kb3_inv_detail.");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('023updatestatus') <2)
		{
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 206 WHERE kll_ship_id = 432"); // Kitsune
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 348 WHERE kll_ship_id = 435"); // Redeemer
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 349 WHERE kll_ship_id = 436"); // Sin
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 352 WHERE kll_ship_id = 437"); // Widow
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 354 WHERE kll_ship_id = 438"); // Panther

			config::set('023updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "23. Updated kb3_kills.");
			$smarty->display('update.tpl');
			die();
		}
		killCache();
		config::set("DBUpdate", "023");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '023' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '023'");
		config::del("023updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 023 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

