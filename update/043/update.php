<?php
/**
 * @package EDK
 */

function update043()
{

	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "043" )
	{
		if(is_null(config::get('043updatestatus'))) {
			config::set('043updatestatus',0);
		}
		$qry = DBFactory::getDBQuery(true);

		if(config::get('043updatestatus') <1)
		{
			$qry->execute("DELETE FROM kb3_sum_alliance");
			$qry->execute("DELETE FROM kb3_sum_corp");
			$qry->execute("DELETE FROM kb3_sum_pilot");
			$qry->execute("UPDATE kb3_pilots SET plt_lpoints=0, plt_kpoints=0");

			config::set('043updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. Add date summary columns to summary tables.");
			$smarty->display('update.tpl');
			die();
		}

		if(config::get('043updatestatus') <2)
		{
			$qry->execute("ALTER TABLE kb3_sum_alliance ADD COLUMN asm_monthday TINYINT(1) NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_alliance ADD COLUMN asm_year YEAR(4) NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp ADD COLUMN csm_monthday TINYINT(1) NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp ADD COLUMN csm_year YEAR(4) NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot ADD COLUMN psm_monthday TINYINT(1) NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot ADD COLUMN psm_year YEAR(4) NOT NULL");

			config::set('043updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. Add date summary columns to summary tables.");
			$smarty->display('update.tpl');
			die();
		}

		if(config::get('043updatestatus') <3)
		{
			$qry->execute("ALTER TABLE kb3_sum_alliance DROP PRIMARY KEY, ADD PRIMARY KEY(asm_all_id, asm_shp_id, asm_monthday, asm_year)");
			$qry->execute("ALTER TABLE kb3_sum_corp DROP PRIMARY KEY, ADD PRIMARY KEY(csm_crp_id, csm_shp_id, csm_monthday, csm_year)");
			$qry->execute("ALTER TABLE kb3_sum_pilot DROP PRIMARY KEY, ADD PRIMARY KEY(psm_plt_id, psm_shp_id, psm_monthday, psm_year)");

			config::set('043updatestatus',3);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. Add date summary columns to summary tables.");
			$smarty->display('update.tpl');
			die();
		}

		if(config::get('043updatestatus') <4)
		{
			config::set( 'last_summary_id', -1);
			config::set('043updatestatus',4);
			
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. Set config variable for last summary id" );
			$smarty->display('update.tpl');
			die();
		}

		if(config::get('043updatestatus') <5)
		{
			$qry->execute("ALTER TABLE kb3_sum_alliance ADD COLUMN asm_kill_loot DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot ADD COLUMN psm_kill_loot DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp ADD COLUMN csm_kill_loot DECIMAL(15,2) DEFAULT 0 NOT NULL");

			$qry->execute("ALTER TABLE kb3_sum_alliance ADD COLUMN asm_loss_loot DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot ADD COLUMN psm_loss_loot DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp ADD COLUMN csm_loss_loot DECIMAL(15,2) DEFAULT 0 NOT NULL");

			$qry->execute("ALTER TABLE kb3_sum_alliance ADD COLUMN asm_kill_points int(11) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp ADD COLUMN csm_kill_points int(11) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot ADD COLUMN psm_kill_points int(11) DEFAULT 0 NOT NULL");

			$qry->execute("ALTER TABLE kb3_sum_alliance ADD COLUMN asm_loss_points int(11) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp ADD COLUMN csm_loss_points int(11) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot ADD COLUMN psm_loss_points int(11) DEFAULT 0 NOT NULL");
			
			$qry->execute("ALTER TABLE kb3_sum_alliance CHANGE COLUMN asm_kill_isk asm_kill_isk DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot CHANGE COLUMN psm_kill_isk psm_kill_isk DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp CHANGE COLUMN csm_kill_isk csm_kill_isk DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_alliance CHANGE COLUMN asm_loss_isk asm_loss_isk DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_pilot CHANGE COLUMN psm_loss_isk psm_loss_isk DECIMAL(15,2) DEFAULT 0 NOT NULL");
			$qry->execute("ALTER TABLE kb3_sum_corp CHANGE COLUMN csm_loss_isk csm_loss_isk DECIMAL(15,2) DEFAULT 0 NOT NULL");

			config::set('043updatestatus',5);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. Add additional summary columns to summary tables.");
			$smarty->display('update.tpl');
			die();
		}
		
		if(config::get('043updatestatus') < 6)
		{
			$qry->execute("ALTER TABLE kb3_kills ADD COLUMN kll_isk_loot DECIMAL(14,2) NOT NULL DEFAULT '0'");
			config::set( 'last_kllloot_id', -1);

			config::set('043updatestatus',6);
			
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. add loot value to kills table" );
			$smarty->display('update.tpl');
			die();
		}

		if(config::get('043updatestatus') < 7)
		{
			$qry->execute("ALTER TABLE kb3_kills MODIFY kll_isk_loss DECIMAL(14,2) NOT NULL DEFAULT '0'");
			config::set('043updatestatus',7);
			
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. modify loss value type in kills table" );
			$smarty->display('update.tpl');
			die();
		}

		if(config::get('043updatestatus') < 8)
		{
			config::set('last_summary_id',0);
			config::set('043updatestatus',8);
			
			$smarty->assign('refresh',1);
			$smarty->assign('content', "43. add last_summary_id config variable" );
			$smarty->display('update.tpl');
			die();
		}

		config::set("DBUpdate", "043");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '043' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '043'");
		config::del("043updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 043 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

