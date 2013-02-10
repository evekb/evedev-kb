<?php
/**
 * @package EDK
 */

function update041()
{

	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "041" )
	{
		if(is_null(config::get('041updatestatus'))) {
			config::set('041updatestatus',0);
		}
		$qry = DBFactory::getDBQuery(true);

		if(config::get('041updatestatus') <1)
		{
			$qry->execute("ALTER TABLE kb3_kills ADD COLUMN kll_td_plt_id INT(3) NULL DEFAULT NULL				AFTER kll_fb_plt_id");
			
			$qry->execute("delete from kb3_config where cfg_key in ('style_name','kd_showbox')");

			config::set('041updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "41. Update kb3_kills.");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('041updatestatus') <2)
		{
			//set top damage party, don't rely on ind_order = 0
			$qry->execute("update kb3_kills
				join (select ind_kll_id, ind_plt_id from kb3_inv_detail
					group by ind_kll_id
					order by ind_dmgdone desc
				) as inv
				on kb3_kills.kll_id = inv.ind_kll_id
				set kll_td_plt_id = inv.ind_plt_id");

			config::set('041updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "41. Record Final Blow party in kb3_kills.");
			$smarty->display('update.tpl');
			die();
		}
		//killCache();
		config::set("DBUpdate", "041");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '041' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '041'");
		config::del("041updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 041 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

