<?php

/**
 * @package EDK
 */
function update030()
{
    global $url, $smarty;
    //Checking if this Update already done
    if (CURRENT_DB_UPDATE < "030") {
        $qry = DBFactory::getDBQuery(true);

        $sql = 'ALTER IGNORE TABLE kb3_mails ADD COLUMN kll_crest_hash CHAR(40) DEFAULT NULL';
        $qry->execute($sql);

        config::set("DBUpdate", "030");
        $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '030' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '030'");
        config::del("030updatestatus");

        $smarty->assign('refresh', 1);
        $smarty->assign('content', "Update 030 completed.");
        $smarty->display('update.tpl');
        die();
    }
}

