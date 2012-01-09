<?php

/**
 * @package EDK555555555555555
 */
function update029()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "029") {
		$qry = DBFactory::getDBQuery(true);

		$newrows = array();
		$sql = 'SELECT cfg_site, cfg_key, cfg_value FROM kb3_config WHERE cfg_key'
				.' IN ("cfg_pilotid", "cfg_corpid", "cfg_allianceid")';
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			if (is_numeric($row['cfg_value'])) {
				$newrows[] = '"'.$row['cfg_site'].'", "'.$row['cfg_key'].'", "'.
				serialize(array((int)$row['cfg_value'])).'"';
			}
		}
		if($newrows) {
			$qry->execute('REPLACE INTO kb3_config (cfg_site, cfg_key, cfg_value) VALUES'.
					'('.join('), (', $newrows).')');
		}

		config::set("DBUpdate", "029");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '029' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '029'");
		config::del("029updatestatus");

		$smarty->assign('refresh', 1);
		$smarty->assign('content', "Update 029 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

