<?php
/**
 * @package EDK
 */

function update035()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "035" )
	{
		if(is_null(config::get('035updatestatus'))) config::set('035updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		//update item types [in case user does not refresh ccpdb]
		if(config::get('035updatestatus') <1)
		{
			//check for user changes from SMA thread
			$sql = "SELECT * from kb3_items_destroyed WHERE itd_itl_id NOT IN (0,1,2,3,4,5,6,7,8,9,87,89,90,116,117,118,119,120,121,133,134,135,136,137,138,139,140,141,142,143,148,149,150,151,154,155)";
			$qry->execute("$sql LIMIT 1");
			if($qry->recordCount()) {
				$smarty->assign('content', "Destroyed items table already has items in custom locations. Update these location IDs as appropriate before continuing (use CCP flags).<br /><br />
					Use this query in your database to find the offending records:<br /><br />$sql");
				$smarty->display('update.tpl');
				die();
			}
			$sql = preg_replace('/kb3_items_destroyed/', 'kb3_items_dropped', $sql);
			$qry->execute("$sql LIMIT 1");
			if($qry->recordCount()) {
				$smarty->assign('content', "Dropped items table already has items in custom locations. Update these location IDs as appropriate before continuing (use CCP flags).<br /><br />
					Use this query in your database to find the offending records:<br /><br />$sql");
				$smarty->display('update.tpl');
				die();
			}

			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '92' WHERE `itt_slot` = 5");
			config::set('035updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (1 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <2)
		{
			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '27' WHERE `itt_slot` = 1");
			config::set('035updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (2 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <3)
		{
			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '19' WHERE `itt_slot` = 2");
			config::set('035updatestatus',3);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (3 of 35)");
			$smarty->display('update.tpl');
			die();
		}				
		if(config::get('035updatestatus') <4)
		{
			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '11' WHERE `itt_slot` = 3");
			config::set('035updatestatus',4);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (4 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <5)
		{
			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '5' WHERE `itt_slot` = 4");
			config::set('035updatestatus',5);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (5 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <6)
		{
			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '87' WHERE `itt_slot` = 6");
			config::set('035updatestatus',6);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (6 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <7)
		{
			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '89' WHERE `itt_slot` = 8");
			config::set('035updatestatus',7);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (7 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <8)
		{
			$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '125' WHERE `itt_slot` = 7");
			config::set('035updatestatus',8);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (8 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		
		// kb3_item_locations
		if(config::get('035updatestatus') <9)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '92' WHERE `itl_id` = 5");
			config::set('035updatestatus',9);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (9 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <10)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '27' WHERE `itl_id` = 1");
			config::set('035updatestatus',10);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (10 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <11)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '19' WHERE `itl_id` = 2");
			config::set('035updatestatus',11);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (11 of 35)");
			$smarty->display('update.tpl');
			die();
		}				
		if(config::get('035updatestatus') <12)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '11' WHERE `itl_id` = 3");
			config::set('035updatestatus',12);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (12 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <13)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '5' WHERE `itl_id` = 4");
			config::set('035updatestatus',13);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (13 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <14)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '87' WHERE `itl_id` = 6");
			config::set('035updatestatus',14);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (14 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <15)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '89' WHERE `itl_id` = 8");
			config::set('035updatestatus',15);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (15 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <16)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '125' WHERE `itl_id` = 7");
			config::set('035updatestatus',16);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (16 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <17)
		{
			$qry->execute("UPDATE `kb3_item_locations` SET `itl_id` = '-1' WHERE `itl_id` = 9");
			config::set('035updatestatus',17);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (17 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		
		// destroyed items
		if(config::get('035updatestatus') <18)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '92' WHERE `itd_itl_id` = 5");
			config::set('035updatestatus',18);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (18 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <19)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '27' WHERE `itd_itl_id` = 1");
			config::set('035updatestatus',19);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (19 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <20)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '19' WHERE `itd_itl_id` = 2");
			config::set('035updatestatus',20);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (20 of 35)");
			$smarty->display('update.tpl');
			die();
		}				
		if(config::get('035updatestatus') <21)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '11' WHERE `itd_itl_id` = 3");
			config::set('035updatestatus',21);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (21 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <22)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '5' WHERE `itd_itl_id` = 4");
			config::set('035updatestatus',22);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (22 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <23)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '87' WHERE `itd_itl_id` = 6");
			config::set('035updatestatus',23);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (23 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <24)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '89' WHERE `itd_itl_id` = 8");
			config::set('035updatestatus',24);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (24 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <25)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '125' WHERE `itd_itl_id` = 7");
			config::set('035updatestatus',25);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (25 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <26)
		{
			$qry->execute("UPDATE `kb3_items_destroyed` SET `itd_itl_id` = '-1' WHERE `itd_itl_id` = 9");
			config::set('035updatestatus',26);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (26 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		
		// dropped items
		if(config::get('035updatestatus') <27)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '92' WHERE `itd_itl_id` = 5");
			config::set('035updatestatus',27);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (27 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <28)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '27' WHERE `itd_itl_id` = 1");
			config::set('035updatestatus',28);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (28 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <29)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '19' WHERE `itd_itl_id` = 2");
			config::set('035updatestatus',29);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (29 of 35)");
			$smarty->display('update.tpl');
			die();
		}				
		if(config::get('035updatestatus') <30)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '11' WHERE `itd_itl_id` = 3");
			config::set('035updatestatus',30);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (30 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <31)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '5' WHERE `itd_itl_id` = 4");
			config::set('035updatestatus',31);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (31 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <32)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '87' WHERE `itd_itl_id` = 6");
			config::set('035updatestatus',32);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (32 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <33)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '89' WHERE `itd_itl_id` = 8");
			config::set('035updatestatus',33);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (33 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <34)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '125' WHERE `itd_itl_id` = 7");
			config::set('035updatestatus',34);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (34 of 35)");
			$smarty->display('update.tpl');
			die();
		}
		if(config::get('035updatestatus') <35)
		{
			$qry->execute("UPDATE `kb3_items_dropped` SET `itd_itl_id` = '-1' WHERE `itd_itl_id` = 9");
			config::set('035updatestatus',35);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Update (35 of 35)");
			$smarty->display('update.tpl');
			die();
		}

		if(config::get('035updatestatus') <36)
		{
			$qry->execute("SELECT max(kll_id) as max FROM kb3_kills");
			$ret = $qry->getRow();
			$maxid = $qry->escape($ret['max']);
			config::set('035killid',$maxid);

			config::set('035updatestatus',36);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Setting Max Kill ID before conversion");
			$smarty->display('update.tpl');
			die();
		}

		config::set("DBUpdate", "035");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '035' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '035'");
		config::del("035updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 035 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

