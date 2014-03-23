<?php
/**
 * @package EDK
 */

//Fix a few issues with the kb3_ships table in one fowl swoop.
//I put this here because these update packages force the structure to be fixed. - FRK
function update022()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "022" )
	{
		if(is_null(config::get('022updatestatus'))) config::set('022updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('022updatestatus') <1)
		{
                        $qry->execute("DELETE FROM `kb3_ships` WHERE `shp_externalid` = 0 AND `shp_name` NOT LIKE '%Unknown%'");
                        $qry->execute("DELETE FROM `kb3_ships` WHERE `shp_id` IN (206, 497, 348, 349,352, 354, 606)");
                        config::set('022updatestatus',1);
                        $smarty->assign('refresh',1);
                        $smarty->assign('content', "22. Delete unused and duplicated ships from ships table.");
                        $smarty->display('update.tpl');
                        die();
		}

                if(config::get('022updatestatus') <2)
		{
		// Add timestamp column to kb3_inv_detail
			$qry->execute("SHOW INDEX FROM `kb3_ships`");
			$indextexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Column_name'] == 'shp_externalid')
					$indextexists = true;
			}
			if(!$indextexists)
			{
				$qry->execute("ALTER TABLE `kb3_ships` ADD UNIQUE `shp_externalid` ( `shp_externalid` ) ");
				config::set('022updatestatus',2);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "22. Ships table added unique index for external IDs.");
				$smarty->display('update.tpl');
				die();
			}
		}

                if(config::get('022updatestatus') <3)
		{
                        //not too happy about this one but it does force the ships to use *my* IDs, as it would be if i dumped it - FRK
                        $qry->execute("INSERT IGNORE INTO `kb3_ships` (`shp_id` ,`shp_name` ,`shp_class` ,`shp_externalid` ,`shp_rce_id` ,`shp_baseprice` ,`shp_techlevel` ,`shp_isfaction`) VALUES (704 , 'Guristas Shuttle', '11', '21628', '1', '10000000', '1', '1');");
                        $qry->execute("INSERT IGNORE INTO `kb3_ships` (`shp_id` ,`shp_name` ,`shp_class` ,`shp_externalid` ,`shp_rce_id` ,`shp_baseprice` ,`shp_techlevel` ,`shp_isfaction`) VALUES (705 , 'Civilian Gallente Shuttle', '11', '27303', '8', '0', '1', '0');");
                        config::set('022updatestatus',3);
                        $smarty->assign('refresh',1);
                        $smarty->assign('content', "22. Insert missing shuttles.");
                        $smarty->display('update.tpl');
                        die();
		}

		killCache();
		config::set("DBUpdate", "022");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '022' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '022'");
		config::del("022updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 022 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

