<?php


class UpdateException extends Exception {}



/**
 * adds x, y and z columns to the kb3_kills table in order to store
 * the coordinates of a kill
 * 
 * @package EDK
 */
function update038()
{
       global $url, $smarty;
       $DB_UPDATE = "038";
       
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < $DB_UPDATE) {
		$qry = DBFactory::getDBQuery(true);

		$sql = 'ALTER TABLE kb3_kills '
                        . 'ADD COLUMN `kll_x` double NOT NULL DEFAULT 0,'
                        . 'ADD COLUMN `kll_y` double NOT NULL DEFAULT 0,'
                        . 'ADD COLUMN `kll_z` double NOT NULL DEFAULT 0';
		$qry->execute($sql);
		
                
                $sql = 'CREATE TABLE IF NOT EXISTS `kb3_mapdenormalize` (
                        `itemID` int(11) NOT NULL,
                        `typeID` int(11) DEFAULT NULL,
                        `groupID` int(11) DEFAULT NULL,
                        `solarSystemID` int(11) DEFAULT NULL,
                        `constellationID` int(11) DEFAULT NULL,
                        `regionID` int(11) DEFAULT NULL,
                        `orbitID` int(11) DEFAULT NULL,
                        `x` double DEFAULT NULL,
                        `y` double DEFAULT NULL,
                        `z` double DEFAULT NULL,
                        `radius` double DEFAULT NULL,
                        `itemName` varchar(100) DEFAULT NULL,
                        `security` double DEFAULT NULL,
                        `celestialIndex` int(11) DEFAULT NULL,
                        `orbitIndex` int(11) DEFAULT NULL,
                        PRIMARY KEY (`itemID`),
                        KEY `solarSystemID` (`solarSystemID`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
                $qry->execute($sql);
                
		config::set("DBUpdate", "$DB_UPDATE");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '029' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '$DB_UPDATE'");
		config::del($DB_UPDATE."updatestatus");

		$smarty->assign('refresh', 1);
		$smarty->assign('content', "Update $DB_UPDATE completed.");
		$smarty->display('update.tpl');
		die();
	}
}

