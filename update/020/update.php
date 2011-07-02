<?php
/**
 * @package EDK
 */

//! It keeps coming up as old db are updated, new owners added, and the way people keep trying to do a new install over an existing board.
function update020()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "020" )
	{
		$qry = DBFactory::getDBQuery(true);
		//Some DBs require auto-increment rol_id to be the first column in an index
		$sql = "ALTER IGNORE TABLE `kb3_roles` ADD INDEX `rol_id_tmp` (  `rol_id` )";
		$qry->execute($sql);
		$sql = "ALTER TABLE `kb3_roles` DROP PRIMARY KEY , ADD PRIMARY KEY ( `rol_site` , `rol_id` ) ";
		$qry->execute($sql);
		$sql = "ALTER IGNORE TABLE `kb3_roles` DROP INDEX `rol_id_tmp`";
		@$qry->execute($sql);

		config::set("DBUpdate", "020");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '020' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '020'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 020 completed.");
		$smarty->display('update.tpl');
		die();
	}
}