<?php

/**
 * This database updates adds singleton columns to the tables for
 * dropped and destroyed items, so the singleton value can be stored
 * separately from the location and the location can be preserved.
 * @package EDK
 */
function update034()
{
	global $url, $smarty;
        $DB_UPDATE = "034";
        
        // change directory to make the class-loader functional
        chdir("..");
        
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < $DB_UPDATE) {
		$qry = DBFactory::getDBQuery(true);
                $qry->autocommit(FALSE);
               
                // add singleton column to destroyed items table
		$sql = 'ALTER TABLE `kb3_items_destroyed`
                    ADD COLUMN itd_singleton tinyint(1) NOT NULL DEFAULT 0';
                $qry->execute($sql);
                
                // add singleton column to dropped items table
                $sql = 'ALTER TABLE `kb3_items_dropped`
                    ADD COLUMN itd_singleton tinyint(1) NOT NULL DEFAULT 0';
		$qry->execute($sql);
                
                // populate singleton column and convert location id in destroyed items table
                $sql = 'UPDATE `kb3_items_destroyed`
                        SET
                            itd_itl_id = '.InventoryFlag::$CARGO.',
                            itd_singleton = '.InventoryFlag::$SINGLETON_COPY.'
                        WHERE itd_itl_id = '.InventoryFlag::$COPY.';';
                $qry->execute($sql);
                
                // populate singleton column and convert location id in dropped items table
                $sql = 'UPDATE `kb3_items_dropped`
                        SET
                            itd_itl_id = '.InventoryFlag::$CARGO.',
                            itd_singleton = '.InventoryFlag::$SINGLETON_COPY.'
                        WHERE itd_itl_id = '.InventoryFlag::$COPY.';';
                $qry->execute($sql);
                            
                $qry->autocommit(TRUE);

		config::set("DBUpdate", "$DB_UPDATE");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '$DB_UPDATE' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");
		config::del($DB_UPDATE."updatestatus");

		$smarty->assign('refresh', 1);
		$smarty->assign('content', "Update $DB_UPDATE completed.");
		$smarty->display('update.tpl');
		die();
	}
}

