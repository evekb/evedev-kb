<?php
/**
 * @package EDK
 */

function update033()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "033" )
	{
		if(is_null(config::get('033updatestatus'))) config::set('033updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('033updatestatus') <1)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_short_name'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_short_name` CHAR(5) NULL ";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_ceo_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_ceo_id` INT(11) UNSIGNED NULL ";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_station_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_station_id` INT(11) UNSIGNED NULL ";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_description'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_description` TEXT";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_url'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_url` VARCHAR(255)";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_taxrate'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_taxrate` SMALLINT";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_membercount'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_membercount` INT(11) UNSIGNED NULL";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_shares'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_shares` INT(11) UNSIGNED NULL";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_startdate'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ADD `crp_startdate` DATETIME NULL";
				$qry->execute($sql);
			}
		}

		config::set("DBUpdate", "033");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '033' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '033'");
		config::del("033updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 033 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

