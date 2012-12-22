<?php
/**
 * @package EDK
 */

function update032()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "032" )
	{
		if(is_null(config::get('032updatestatus'))) config::set('032updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('032updatestatus') <1)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_alliances LIKE 'all_short_name'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_alliances` ADD `all_short_name` CHAR(5) NULL ";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_alliances LIKE 'all_executor_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_alliances` ADD `all_executor_id` INT(11) UNSIGNED NULL ";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_alliances LIKE 'all_member_count'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_alliances` ADD `all_member_count` INT(11) UNSIGNED NULL ";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_alliances LIKE 'all_start_date'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_alliances` ADD `all_start_date` DATETIME NULL";
				$qry->execute($sql);
			}
		}

		config::set("DBUpdate", "032");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '032' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '032'");
		config::del("032updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 032 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

