<?php
/**
 * @package EDK
 */

function update034()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "034" )
	{
		if(is_null(config::get('034updatestatus'))) config::set('034updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('034updatestatus') <1)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_invtypes LIKE 'radius'");
			if($qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_invtypes` DROP COLUMN `radius` ";
				$qry->execute($sql);
			}
		}

		config::set("DBUpdate", "034");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '034' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '034'");
		config::del("034updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 034 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

