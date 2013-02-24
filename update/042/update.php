<?php
/**
 * @package EDK
 */

function update042()
{

	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "042" )
	{
		if(is_null(config::get('042updatestatus'))) {
			config::set('042updatestatus',0);
		}
		$qry = DBFactory::getDBQuery(true);

		if(config::get('042updatestatus') <1)
		{
			$qry->execute("ALTER TABLE kb3_alliances ADD COLUMN all_active TINYINT(1) NOT NULL DEFAULT 1 AFTER all_start_date");
			$qry->execute("ALTER TABLE kb3_alliances DROP INDEX all_name");
			$qry->execute("ALTER TABLE kb3_alliances ADD UNIQUE all_name_active ( all_name, all_active )");

			config::set('042updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "42. Add Active column to alliances.");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('042updatestatus') <2)
		{
			$qry->execute("ALTER TABLE kb3_corps ADD COLUMN crp_active TINYINT(1) NOT NULL DEFAULT 1 AFTER crp_startdate");
			$qry->execute("ALTER TABLE kb3_corps DROP INDEX crp_name");
			$qry->execute("ALTER TABLE kb3_corps ADD UNIQUE crp_name_active ( crp_name, crp_active )");

			config::set('042updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "42. Add Active column to corps.");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('042updatestatus') <3)
		{
			$qry->execute("ALTER TABLE kb3_pilots ADD COLUMN plt_active TINYINT(1) NOT NULL DEFAULT 1 AFTER plt_lpoints");
			$qry->execute("ALTER TABLE kb3_pilots DROP INDEX plt_name");
			$qry->execute("ALTER TABLE kb3_pilots ADD UNIQUE plt_name_active ( plt_name, plt_active )");

			config::set('042updatestatus',3);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "42. Add Active column to pilots.");
			$smarty->display('update.tpl');
			die();
		}
		config::set("DBUpdate", "042");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '042' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '042'");
		config::del("042updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 042 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

