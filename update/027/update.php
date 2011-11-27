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
		$qry = new DBQuery(true);

		if(config::get('027updatestatus') <1)
		{
			// create table
			$sql = 'CREATE TABLE IF NOT EXISTS kb3_engagement_all (
						starttime datetime NOT NULL,
						endtime datetime NOT NULL,
						all_id int(11) NOT NULL,
						eng_id int(11) NOT NULL,
						losses int(11) NOT NULL DEFAULT 0,
						kills int(11) NOT NULL DEFAULT 0,
						lossisk bigint(20) NOT NULL DEFAULT 0,
						killisk bigint(20) NOT NULL DEFAULT 0,
						UNIQUE KEY btl_id (eng_id,all_id),
						KEY endtime (endtime))';
			$qry->execute($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS kb3_engagement_crp (
						starttime datetime NOT NULL,
						endtime datetime NOT NULL,
						crp_id int(11) NOT NULL,
						eng_id int(11) NOT NULL,
						losses int(11) NOT NULL DEFAULT 0,
						kills int(11) NOT NULL DEFAULT 0,
						lossisk bigint(20) NOT NULL DEFAULT 0,
						killisk bigint(20) NOT NULL DEFAULT 0,
						UNIQUE KEY btl_id (eng_id,crp_id),
						KEY endtime (endtime))';
			$qry->execute($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS kb3_engagement_eng (
						eng_id int(11) NOT NULL AUTO_INCREMENT,
						endtime datetime NOT NULL,
						starttime datetime NOT NULL,
						sys_id int(11) NOT NULL,
						btl_id int(11) NOT NULL,
						PRIMARY KEY (eng_id),
						KEY date (endtime))';
			$qry->execute($sql);

			$sql = 'CREATE TABLE IF NOT EXISTS kb3_battle_btl (
						btl_id int(11) NOT NULL AUTO_INCREMENT,
						involved int(11) NOT NULL DEFAULT 0,
						name char(36) DEFAULT NULL,
						description int(11) DEFAULT NULL,
						endtime datetime NOT NULL,
						starttime datetime NOT NULL,
						PRIMARY KEY (btl_id),
						KEY date (endtime))';
			$qry->execute($sql);
			
			$sql = 'ALTER TABLE kb3_kills ADD COLUMN kll_eng_id int(11) NOT NULL DEFAULT 0';
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

