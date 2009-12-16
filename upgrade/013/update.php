<?php
// Add site id to kb3_comments
function update013()
{
	global $url, $header, $footer;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "013" )
	{
		if(is_null(config::get('013updatestatus'))) config::set('013updatestatus',0);
		$qry = new DBQuery(true);

		if(config::get('013updatestatus') <1)
		{
			// Add timestamp column to kb3_inv_detail
			$qry->execute("SHOW COLUMNS FROM kb3_comments LIKE 'site'");
			if(!$qry->recordCount())
			{
				$qry->execute("ALTER TABLE `kb3_comments` ADD `site` CHAR( 16 ) DEFAULT NULL AFTER `id`");
				config::set('013updatestatus',1);
				echo $header;
				echo "13. kb3_comments site column added";
				echo $footer;
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
				if($testresult['Column_name'] == 'kll_id')
					$indexkexists = true;
				if($testresult['Column_name'] == 'site_kll_id')
					$indexsexists = true;
			}
			if($indexkexists)
			{
				$qry->execute("ALTER TABLE kb3_comments DROP INDEX kll_id");
				config::set('013updatestatus',2);
				echo $header;
				echo "13. kb3_comments kll_id index dropped";
				echo $footer;
				die();
			}
			if(!$indexsexists)
			{
				$qry->execute("ALTER TABLE `kb3_comments` ADD INDEX `site_kll_id` ( `site` , `kll_id` ) ");
				config::set('013updatestatus',3);
				echo $header;
				echo "13. kb3_comments site_kll_id index added";
				echo $footer;
				die();
			}
		}

		killCache();
		config::set("DBUpdate", "013");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '013' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '013'");
		config::del("013updatestatus");
		echo $header;
		echo "Update 013 completed.";
		echo $footer;
		die();
	}
}

