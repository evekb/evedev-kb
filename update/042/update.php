<?php


class UpdateException extends Exception {}



/**
 * Creates table for for storing SSO information.
 * 
 * @package EDK
 */
function update042()
{
       global $url, $smarty;
       $DB_UPDATE = "042";
       
    //Checking if this Update already done
    if (CURRENT_DB_UPDATE < $DB_UPDATE) {
        
                
        Config::set("DBUpdate", "$DB_UPDATE");
        // insert SSO registration into top navigation
        $qry = DBFactory::getDBQuery(true);

        // increase with of column refreshToken
        $qry->execute("ALTER TABLE `kb3_esisso` 
            MODIFY `refreshToken` varchar(1024);");
        
        $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '$DB_UPDATE' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");

        $smarty->assign('refresh', 1);
        $smarty->assign('content', "Update $DB_UPDATE completed.");
        $smarty->display('update.tpl');
        die();
    }
}

