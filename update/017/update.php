<?php
/**
 * @package EDK
 */

function update017()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "017" )
	{
		$qry = DBFactory::getDBQuery(true);
		
		$sql = "ALTER TABLE `kb3_pilots` ADD `plt_kpoints` INTEGER NOT NULL DEFAULT 0,
			 ADD `plt_lpoints` INTEGER NOT NULL DEFAULT 0";
		$qry->execute("SHOW COLUMNS FROM kb3_pilots LIKE 'plt__points'");
		if(!$qry->recordCount()) $qry->execute($sql);

		config::set("DBUpdate", "017");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '017' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '017'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 017 completed.");
		$smarty->display('update.tpl');
		die();
	}
}