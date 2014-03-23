<?php
/**
 * @package EDK
 */

function update019()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "019" )
	{
		$qry = DBFactory::getDBQuery(true);
		
		$sql = "ALTER TABLE `kb3_pilots` CHANGE `plt_name` `plt_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
		$qry->execute($sql);

		config::set("DBUpdate", "019");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '019' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '019'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 019 completed.");
		$smarty->display('update.tpl');
		die();
	}
}