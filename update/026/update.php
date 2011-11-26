<?php
/**
 * @package EDK
 */

// move victim into the involved table
function update026()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "026" )
	{
		$qry = DBFactory::getDBQuery(true);

		$qry->execute("INSERT IGNORE INTO `kb3_item_locations` ("
			."`itl_id` , `itl_location`)"
			." VALUES ('9', 'Copy');");

		killCache();
		config::set("DBUpdate", "026");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '026' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '026'");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "26. kb3_item_locations updated.");
		$smarty->display('update.tpl');
		die();
	}
}

