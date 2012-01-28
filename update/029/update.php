<?php

/**
 * @package EDK
 */
function update029()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "029") {
		$qry = DBFactory::getDBQuery(true);
		$qry2 = DBFactory::getDBQuery(true);

		$newrows = array();
		$sql = 'SELECT cfg_site FROM kb3_config GROUP BY cfg_site';
		$qry->execute($sql);
		while ($row = $qry->getRow()) {
			$qry2->execute("SELECT cfg_value FROM kb3_config WHERE cfg_site = '"
					.$row['cfg_site']."' AND cfg_key = 'cfg_pilotid'");
			if($row2 = $qry2->getRow()) {
				if (is_null($row2['cfg_value']) 
								|| is_numeric($row2['cfg_value'])) {
					$newrows[] = '"'.$row['cfg_site'].'", "cfg_pilotid", "'.
							serialize(array((int)$row['cfg_value'])).'"';
				}
			} else {
					$newrows[] = '"'.$row['cfg_site'].'", "cfg_pilotid", "'.
							serialize(array()).'"';
			}
			$qry2->execute("SELECT cfg_value FROM kb3_config WHERE cfg_site = '"
					.$row['cfg_site']."' AND cfg_key = 'cfg_corpid'");
			if($row2 = $qry2->getRow()) {
				if (is_null($row2['cfg_value']) 
								|| is_numeric($row2['cfg_value'])) {
					$newrows[] = '"'.$row['cfg_site'].'", "cfg_corpid", "'.
							serialize(array((int)$row['cfg_value'])).'"';
				}
			} else {
					$newrows[] = '"'.$row['cfg_site'].'", "cfg_corpid", "'.
							serialize(array()).'"';
			}
			$qry2->execute("SELECT cfg_value FROM kb3_config WHERE cfg_site = '"
					.$row['cfg_site']."' AND cfg_key = 'cfg_allianceid'");
			if($row2 = $qry2->getRow()) {
				if (is_null($row2['cfg_value']) 
								|| is_numeric($row2['cfg_value'])) {
					$newrows[] = '"'.$row['cfg_site'].'", "cfg_allianceid", "'.
							serialize(array((int)$row['cfg_value'])).'"';
				}
			} else {
					$newrows[] = '"'.$row['cfg_site'].'", "cfg_allianceid", "'.
							serialize(array()).'"';
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

