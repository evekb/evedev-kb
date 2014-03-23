<?php
/**
 * @package EDK
 */

function update016()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "016" )
	{
		$qry = DBFactory::getDBQuery(true);
		
		$sql = "ALTER TABLE `kb3_mails` ADD `kll_modified_time` DATETIME NOT NULL ";
		$qry->execute("SHOW COLUMNS FROM kb3_mails LIKE 'kll_modified_time'");
		if(!$qry->recordCount()) $qry->execute($sql);

		$sql = "ALTER TABLE `kb3_mails` ADD INDEX ( `kll_modified_time` ) ";
		$qry->execute("SHOW INDEXES FROM kb3_mails");
		$indexexists = false;
		while($testresult = $qry->getRow())
		{
			if($testresult['Column_name'] == 'kll_modified_time')
				$indexexists = true;
		}
		if(!$indexexists) $qry->execute($sql);
		
		config::set("DBUpdate", "016");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '016' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '016'");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 016 completed.");
		$smarty->display('update.tpl');
		die();
	}
}