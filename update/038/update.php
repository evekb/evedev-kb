<?php
/**
 * @package EDK
 */

function update038()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "038" )
	{
		$qry = DBFactory::getDBQuery(true);

		$sql = 'ALTER TABLE `kb3_dgmtypeattributes` CHANGE `typeID` `typeID` INT( 11 ) NOT NULL DEFAULT \'0\';';
		$qry->execute($sql);

		$qry->execute("SHOW COLUMNS FROM kb3_moons LIKE 'moo_id'");
		if(!$qry->recordCount())
		{
			$sql = 'ALTER TABLE `kb3_moons` CHANGE `itemID` `moo_id` INT( 11 ) NOT NULL ,
					CHANGE `itemName` `moo_name` VARCHAR( 127 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;';
			$qry->execute($sql);
		}
		
		config::set("DBUpdate", "038");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '038' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '038'");
		config::del("038updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 038 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

