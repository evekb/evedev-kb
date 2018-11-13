<?php


class UpdateException extends Exception {}



/**
 * Introduces the maximum number of failed of SSO attempts before ignoring
 * the key to the configuration.
 * 
 * @package EDK
 */
function update043()
{
       global $url, $smarty;
       $DB_UPDATE = "043";
       
    //Checking if this Update already done
    if (CURRENT_DB_UPDATE < $DB_UPDATE) {
        
                
        Config::set("DBUpdate", "$DB_UPDATE");
        // insert SSO registration into top navigation
        $qry = DBFactory::getDBQuery(true);

        // initialize the value with 5
        config::set('cfg_sso_max_fail_count', 5);
        
        $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '$DB_UPDATE' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");

        $smarty->assign('refresh', 1);
        $smarty->assign('content', "Update $DB_UPDATE completed.");
        $smarty->display('update.tpl');
        die();
    }
}

