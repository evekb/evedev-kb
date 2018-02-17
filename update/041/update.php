<?php


class UpdateException extends Exception {}



/**
 * Creates table for for storing SSO information.
 * 
 * @package EDK
 */
function update041()
{
       global $url, $smarty;
       $DB_UPDATE = "041";
       
    //Checking if this Update already done
    if (CURRENT_DB_UPDATE < $DB_UPDATE) {
        
                
        Config::set("DBUpdate", "$DB_UPDATE");
        // insert SSO registration into top navigation
        $qry = DBFactory::getDBQuery(true);
        $qry->execute("SELECT MAX(posnr) from kb3_navigation;");
        $result = $qry->getRow();
        $ssoRegistrationPosition = (int)$result + 1;
        $qry->execute("INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'SSO Registration','?a=ssoregistration','_self',$ssoRegistrationPosition,'ALL_PAGES',0,'".KB_SITE."');");
        
        // add kb3_esisso table
        $qry->execute("CREATE TABLE IF NOT EXISTS `kb3_esisso` (
            `id` int(11) NOT NULL auto_increment,
            `characterID` int(11) NOT NULL,
            `keyType` varchar(16) NOT NULL,
            `refreshToken` varchar(255) NOT NULL,
            `ownerHash` varchar(255) NOT NULL,
            `failCount` int(11) DEFAULT NULL,
            `isEnabled` tinyint(1) NOT NULL,
            `lastKillID` int(11) DEFAULT NULL,
            `lastKillFetchTimestamp` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) Engine=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");
        $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '$DB_UPDATE' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");
        Config::del($DB_UPDATE."updatestatus");

        // initialize configuration for maximum processing time for each sso key
        Config::set('cfg_max_proc_time_per_sso_key', 55);
        
        $smarty->assign('refresh', 1);
        $smarty->assign('content', "Update $DB_UPDATE completed.");
        $smarty->display('update.tpl');
        die();
    }
}

