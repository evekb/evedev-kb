<?php
/**
 * @package EDK
 */

// Add site id to kb3_comments
function update013()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "013" )
	{
		if(is_null(config::get('013updatestatus'))) config::set('013updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('013updatestatus') <1)
		{
		// Add timestamp column to kb3_inv_detail
			$qry->execute("SHOW COLUMNS FROM kb3_comments LIKE 'site'");
			if(!$qry->recordCount())
			{
				$qry->execute("ALTER TABLE `kb3_comments` ADD `site` CHAR( 16 ) DEFAULT NULL AFTER `id`");
				config::set('013updatestatus',1);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "13. kb3_comments site column added");
				$smarty->display('update.tpl');
				die();
			}
		}
		if(config::get('013updatestatus') <3)
		{
		// Add site column to kb3_comments
			$qry->execute("SHOW INDEX FROM kb3_comments");
			$indexkexists = false;
			$indexsexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Key_name'] == 'kll_id')
					$indexkexists = true;
				if($testresult['Key_name'] == 'kll_site_id')
					$indexsexists = true;
			}
			if($indexkexists)
			{
				$qry->execute("ALTER TABLE kb3_comments DROP INDEX kll_id");
				config::set('013updatestatus',2);

				$smarty->assign('refresh',1);
				$smarty->assign('content', "13. kb3_comments kll_id index dropped");
				$smarty->display('update.tpl');
				die();
			}
			if(!$indexsexists)
			{
				$qry->execute("ALTER TABLE `kb3_comments` ADD INDEX `kll_site_id` ( `kll_id`, `site` ) ");
				config::set('013updatestatus',3);

				$smarty->assign('refresh',1);
				$smarty->assign('content', "13. kb3_comments kll_site_id index added");
				$smarty->display('update.tpl');
				die();
			}
		}

		killCache();
		config::set("DBUpdate", "013");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '013' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '013'");
		config::del("013updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 013 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

