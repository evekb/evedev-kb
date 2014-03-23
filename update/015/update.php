<?php
/**
 * @package EDK
 */

function update015()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "015" )
	{
		$qry = DBFactory::getDBQuery(true);
		$sql = "ALTER TABLE `kb3_contracts` ADD `ctr_comment` VARCHAR( 255 ) NULL DEFAULT NULL ";
		$qry->execute("SHOW COLUMNS FROM kb3_contracts LIKE 'ctr_comment'");
		if(!$qry->recordCount()) $qry->execute($sql);

		config::set("DBUpdate", "015");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '015' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '015'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 015 completed.");
		$smarty->display('update.tpl');
		die();
	}
}