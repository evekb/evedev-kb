<?php
/**
 * @package EDK
 */

function update036()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "036" )
	{
		$qry = DBFactory::getDBQuery(true);

		$sql = 'CREATE TABLE IF NOT EXISTS `kb3_stations` (
				`sta_id` int(10) NOT NULL,
				`sta_sys_id` int(10) NOT NULL,
				`sta_con_id` int(10) NOT NULL,
				`sta_reg_id` int(10) NOT NULL,
				`sta_name` varchar(100) NOT NULL
				) Engine=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;';
		$qry->execute($sql);

		config::set("DBUpdate", "036");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '036' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '036'");
		config::del("036updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 036 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

