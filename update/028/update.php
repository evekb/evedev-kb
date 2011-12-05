<?php

/**
 * @package EDK
 */
function update028()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "028") {
		$qry = DBFactory::getDBQuery(true);

	// create table
	$sql = 'CREATE TABLE IF NOT EXISTS `kb3_moons` (
  `itemID` int(11) NOT NULL,
  `itemName` varchar(127) NOT NULL,
  PRIMARY KEY (`itemID`),
  KEY `itemName` (`itemName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		$qry->execute($sql);

		config::set("DBUpdate", "028");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '028' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '028'");
		config::del("028updatestatus");

		$smarty->assign('url', '?package=CCPDB');
		$smarty->assign('refresh', 1);
		$smarty->assign('content', "Update 028 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

