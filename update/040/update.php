<?php


class UpdateException extends Exception {}



/**
 * adds location column to the kb3_kills table in order to store
 * the nearest celestial of the kill
 * 
 * @package EDK
 */
function update040()
{
       global $url, $smarty;
       $DB_UPDATE = "040";
       
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < $DB_UPDATE) {
		
                // initialize the setting for skipping kills that cannot be verified by CREST
                if(is_null(config::get('skipNonVerifyableKills')))
                {
                    config::set('skipNonVerifyableKills', 1);
                }
                
		config::set("DBUpdate", "$DB_UPDATE");
                 $qry = DBFactory::getDBQuery(true);
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '$DB_UPDATE' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");
		config::del($DB_UPDATE."updatestatus");

		$smarty->assign('refresh', 1);
		$smarty->assign('content', "Update $DB_UPDATE completed.");
		$smarty->display('update.tpl');
		die();
	}
}

