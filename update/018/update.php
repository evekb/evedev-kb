<?php
/**
 * @package EDK
 */

function update018()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "018" )
	{
		$qry = DBFactory::getDBQuery(true);
		
		$sql = "ALTER TABLE `kb3_apilog` ADD `log_errorcode` INT NOT NULL DEFAULT '0' AFTER `log_type` ";
		$qry->execute("SHOW COLUMNS FROM kb3_apilog LIKE 'log_errorcode'");
		if(!$qry->recordCount()) $qry->execute($sql);

		config::set("DBUpdate", "018");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '018' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '018'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 018 completed.");
		$smarty->display('update.tpl');
		die();
	}
}