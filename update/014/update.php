<?php
/**
 * @package EDK
 */

// Add site id to kb3_comments
function update014()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "014" )
	{
		if(is_null(config::get('014updatestatus'))) config::set('014updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('014updatestatus') <1)
		{
		// Add killmail summary. time, hash, trust.
			$sql = 'CREATE TABLE IF NOT EXISTS `kb3_mails` (
  `kll_id` int(11) NOT NULL auto_increment,
  `kll_timestamp` datetime NOT NULL default "0000-00-00 00:00:00",
  `kll_external_id` int(8) default NULL,
  `kll_hash` BINARY(16) NOT NULL,
  `kll_trust` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY  (`kll_id`),
  UNIQUE KEY `external_id` (`kll_external_id`),
  UNIQUE KEY `time_hash` (`kll_timestamp`,`kll_hash`)
) Engine=InnoDB';
			$qry->execute($sql);
		}

		killCache();
		config::set("DBUpdate", "014");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '014' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '014'");
		config::del("014updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 014 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

