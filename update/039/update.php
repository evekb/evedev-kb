<?php
/**
 * @package EDK
 */

function update039()
{

	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "039" )
	{
		if(is_null(config::get('039updatestatus'))) {
			config::set('039updatestatus',0);
		}
		$qry = DBFactory::getDBQuery(true);

		if(config::get('039updatestatus') <1)
		{
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 11194 WHERE ind_shp_id = 206"); // Kitsune
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 22428 WHERE ind_shp_id = 348"); // Redeemer
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 22430 WHERE ind_shp_id = 349"); // Sin
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 22436 WHERE ind_shp_id = 352"); // Widow
			$qry->execute("UPDATE kb3_inv_detail SET ind_shp_id = 22440 WHERE ind_shp_id = 354"); // Panther

			config::set('039updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "39. Update kb3_inv_detail.");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('039updatestatus') <2)
		{
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 11194 WHERE kll_ship_id = 206"); // Kitsune
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 22428 WHERE kll_ship_id = 348"); // Redeemer
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 22430 WHERE kll_ship_id = 349"); // Sin
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 22436 WHERE kll_ship_id = 352"); // Widow
			$qry->execute("UPDATE kb3_kills SET kll_ship_id = 22440 WHERE kll_ship_id = 354"); // Panther

			$qry->execute("delete from kb3_config where cfg_key = 'style_name'");

			config::set('039updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "39. Update kb3_kills.");
			$smarty->display('update.tpl');
			die();
		}
		killCache();
		config::set("DBUpdate", "039");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '039' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '039'");
		config::del("039updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 039 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

