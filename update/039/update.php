<?php


class UpdateException extends Exception {}



/**
 * adds location column to the kb3_kills table in order to store
 * the nearest celestial of the kill
 * 
 * @package EDK
 */
function update039()
{
       global $url, $smarty;
       $DB_UPDATE = "039";
       
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < $DB_UPDATE) {
		
                $qry = DBFactory::getDBQuery(true);
                
                $sql = "SELECT *
                        FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = '".DB_NAME."'
                            AND TABLE_NAME = 'kb3_kills'
                            AND COLUMN_NAME = 'kll_location'";
                $qry->execute($sql);
                if(!$qry->getRow())
                {
                    // add column
                    $sql = 'ALTER TABLE kb3_kills '
                            . 'ADD COLUMN `kll_location` int(11)';
                    $qry->execute($sql);

                    // add index
                    $sql = 'ALTER TABLE `kb3_kills` ADD INDEX(`kll_location`)';
                    $qry->execute($sql);
                }
                
            
                
		config::set("DBUpdate", "$DB_UPDATE");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '$DB_UPDATE' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");
		config::del($DB_UPDATE."updatestatus");

		$smarty->assign('refresh', 1);
		$smarty->assign('content', "Update $DB_UPDATE completed.");
		$smarty->display('update.tpl');
		die();
	}
}

