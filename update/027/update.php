<?php
/**
 * @package EDK
 */

function update027()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "027" )
	{
		if(is_null(config::get('027updatestatus'))) config::set('027updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('027updatestatus') <1)
		{
			// create table
			$sql = 'CREATE TABLE IF NOT EXISTS kb3_api_keys (
						key_name VARCHAR( 50 ) NOT NULL,
						key_id INT(11) NOT NULL,
						key_key VARCHAR( 64 ) NOT NULL,
						key_kbsite VARCHAR( 16 ) NOT NULL,
						key_flags TINYINT NOT NULL,
						PRIMARY KEY (key_name, key_id, key_key, key_kbsite))';
			$qry->execute($sql);
			
			// get a list of keys for each site to migrate
			$sql = "SELECT * FROM kb3_config where cfg_key = 'API_Key_count'";
			$qry->execute($sql);

			$qry2 = DBFactory::getDBQuery(true);

			while($ret = $qry->getRow()) {
				$key_kbsite = $ret['cfg_site'];
				$keycnt = $ret['cfg_value'];
								
				for( $i = 1; $i <= $keycnt; $i++ ) {
					$sql = "SELECT cfg_value FROM kb3_config where cfg_site = '$key_kbsite' AND cfg_key = 'API_UserID_$i' ";
					$qry2->execute($sql);
					$ret = $qry2->getRow();
					$key_id = $qry->escape($ret['cfg_value']);
					
					$sql = "SELECT cfg_value FROM kb3_config where cfg_site = '$key_kbsite' AND cfg_key = 'API_Key_$i' ";
					$qry2->execute($sql);
					$ret = $qry2->getRow();
					$key_key = $qry->escape($ret['cfg_value']);

					$sql = "SELECT cfg_value FROM kb3_config where cfg_site = '$key_kbsite' AND cfg_key = 'API_Name_$i' ";
					$qry2->execute($sql);
					$ret = $qry2->getRow();
					$key_name = $qry->escape($ret['cfg_value']);

					$sql = "SELECT cfg_value FROM kb3_config where cfg_site = '$key_kbsite' AND cfg_key = 'API_Type_$i' ";
					$qry2->execute($sql);
					$ret = $qry2->getRow();
					$type = $qry->escape($ret['cfg_value']);

					// need to set appropriate flags
					// a) legacy key
					// b) corp/char
					$key_flags = KB_APIKEY_LEGACY;
					if ( $type == 'corp' ) {
						$key_flags |= KB_APIKEY_CORP;
					}				
					if ( $type == 'char' ) {
						$key_flags |= KB_APIKEY_CHAR;
					}

					if ( $key_id != '' ) {
						$sql = "INSERT INTO kb3_api_keys( key_name, key_id, key_key, key_kbsite, key_flags ) VALUES ( '$key_name', '$key_id', '$key_key', '$key_kbsite', '$key_flags' )";
						$qry2->execute($sql);
					}
					
					// remove legacy config items
					$sql = "DELETE FROM kb3_config where cfg_site = '$key_kbsite' AND ( cfg_key = 'API_UserID_$i' OR cfg_key = 'API_CharID_$i' OR cfg_key = 'API_Key_$i' OR cfg_key = 'API_Name_$i' OR cfg_key = 'API_Type_$i' ) ";
					$qry2->execute($sql);
				}
			}
			
			// remove legacy api config items
			$sql = "DELETE FROM kb3_config where cfg_key = 'API_Key_count'";
			$qry->execute($sql);			
		}

		config::set("DBUpdate", "027");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '027' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '027'");
		config::del("027updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 027 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

