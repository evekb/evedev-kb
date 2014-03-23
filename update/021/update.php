<?php
/**
 * @package EDK
 */

// Add faction column to ship table
function update021()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "021" )
	{
		$qry = DBFactory::getDBQuery(true);
		if(is_null(config::get('021updatestatus'))) config::set('021updatestatus',0);
		

		if(config::get('021updatestatus') <1)
		{
		// Add timestamp column to kb3_inv_detail
			$qry->execute("SHOW COLUMNS FROM kb3_ships LIKE 'shp_isfaction'");
			if(!$qry->recordCount())
			{
				$qry->execute("ALTER TABLE `kb3_ships` ADD `shp_isfaction` TINYINT(1) DEFAULT '0' AFTER `shp_techlevel`;");
				config::set('021updatestatus',1);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "21. kb3_ships shp_isfaction column added");
				$smarty->display('update.tpl');
				die();
			}
		}
		if(config::get('021updatestatus') <3)
		{		
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 3628;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17715;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17718;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17720;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17722;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17736;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17738;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17740;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17918;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17920;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17922;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17924;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17926;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17928;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17930;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 17932;");
			$qry->execute("UPDATE kb3_ships SET shp_isfaction = '1' WHERE shp_externalid = 32207;");

			config::set('021updatestatus',3);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "21. faction tags added");
			$smarty->display('update.tpl');
			die();
			
		}

		killCache();
		config::set("DBUpdate", "021");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '021' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '021'");
		config::del("021updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 021 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

