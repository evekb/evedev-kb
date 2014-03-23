<?php
/**
 * @package EDK
 */

// Add alliance and corp summary tables.
function update011()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "011" )
	{
		$qry = DBFactory::getDBQuery(true);
		$sql = "ALTER TABLE `kb3_ships` CHANGE `shp_baseprice` `shp_baseprice` BIGINT( 12 ) NOT NULL DEFAULT '0'";
		$qry->execute($sql);

		config::set("DBUpdate", "011");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '011' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '011'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 011 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

