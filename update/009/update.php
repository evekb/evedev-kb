<?php
/**
 * @package EDK
 */

// Add alliance and corp summary tables.
function update009()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "009" )
	{
		$qry = DBFactory::getDBQuery(true);
		$sql = "CREATE TABLE IF NOT EXISTS `kb3_sum_alliance` (
		  `asm_all_id` int(11) NOT NULL DEFAULT '0',
		  `asm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `asm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `asm_kill_isk` float NOT NULL DEFAULT '0',
		  `asm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `asm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`asm_all_id`,`asm_shp_id`)
		) ENGINE=InnoDB";
		$qry->execute($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `kb3_sum_corp` (
		  `csm_crp_id` int(11) NOT NULL DEFAULT '0',
		  `csm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `csm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `csm_kill_isk` float NOT NULL DEFAULT '0',
		  `csm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `csm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`csm_crp_id`,`csm_shp_id`)
		) ENGINE=InnoDB";
		$qry->execute($sql);
		config::set("DBUpdate", "009");
		$qry->execute("UPDATE kb3_config SET cfg_value = '009' WHERE cfg_key = 'DBUpdate'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 009 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

