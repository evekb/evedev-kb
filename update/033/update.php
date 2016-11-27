<?php

/**
 * @package EDK
 */
function update033()
{
    global $url, $smarty;
        $DB_UPDATE = "033";
    //Checking if this Update already done
    if (CURRENT_DB_UPDATE < $DB_UPDATE) {
        $qry = DBFactory::getDBQuery(true);

        $sql = 'CREATE TABLE IF NOT EXISTS `kb3_zkbfetch` (
  `fetchID` int(11) NOT NULL auto_increment,
  `url` varchar(127) NOT NULL,
  `lastKillTimestamp` datetime default NULL,
  PRIMARY KEY (`fetchID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $qry->execute($sql);

        config::set("DBUpdate", "$DB_UPDATE");
        $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '$DB_UPDATE' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");
        config::del($DB_UPDATE."updatestatus");

        $smarty->assign('refresh', 1);
        $smarty->assign('content', "Update $DB_UPDATE completed.");
        $smarty->display('update.tpl');
        die();
    }
}

