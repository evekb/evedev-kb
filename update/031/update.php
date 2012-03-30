<?php
/**
 * @package EDK
 */

function update031()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "031" )
	{
		if(is_null(config::get('031updatestatus'))) config::set('031updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('031updatestatus') <1)
		{
			// create table
			$sql = 'CREATE TABLE IF NOT EXISTS kb3_feeds (
						feed_id INT(11) AUTO_INCREMENT NOT NULL,
						feed_url text NOT NULL,
						feed_lastkill INT(11) NOT NULL,
						feed_updated datetime DEFAULT "0000-00-00 00:00:00",
						feed_kbsite VARCHAR( 16 ) NOT NULL,
						feed_flags TINYINT NOT NULL,
						PRIMARY KEY (feed_id))';
			$qry->execute($sql);
			
			// get a list of keys for each site to migrate
			$sql = "SELECT * FROM kb3_config where cfg_key = 'fetch_idfeeds'";
			$qry->execute($sql);

			$qry2 = DBFactory::getDBQuery(true);
			
			while($ret = $qry->getRow()) {
				$feed_kbsite = $ret['cfg_site'];
				$feeddata = unserialize($ret['cfg_value']);

				foreach( $feeddata as $feed) {
					$feedurl = $qry2->escape($feed['url']);
					$lastkill = (int)$feed['lastkill'];
					$trusted = (int)$feed['trusted'];

					if( trim($feedurl) == '' ) {
						// empty feed - skip
						continue;
					}
					
					$feed_flags = FEED_ACTIVE;			
					if ( $trusted ) {
						$feed_flags |= FEED_TRUSTED;
					}
					
					$qry2->execute("SELECT feed_id, feed_lastkill, feed_flags FROM kb3_feeds WHERE feed_url = '$feedurl' AND feed_kbsite = '$feed_kbsite'");
					if($qry2->recordCount()) {
						// OK url already in DB - probably best thing to do is force a full fetch i.e. lastkill = 0:
						// We don't know at this point what the end-users intention
						$sql = "UPDATE kb3_feeds SET feed_lastkill=0 WHERE feed_url = '$feedurl' AND feed_kbsite='$feed_kbsite' )";
						$qry2->execute($sql);
					} else {			
						$sql = "INSERT INTO kb3_feeds( feed_url, feed_lastkill, feed_kbsite, feed_flags ) VALUES ( '$feedurl', $lastkill, '$feed_kbsite', '$feed_flags' )";
						$qry2->execute($sql);
					}
				}

				// remove legacy config items
				$sql = "DELETE FROM kb3_config where cfg_site = '$feed_kbsite' AND ( cfg_key = 'fetch_idfeeds' )";
				$qry2->execute($sql);
			}
		}

		config::set("DBUpdate", "031");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '031' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '031'");
		config::del("031updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 031 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

