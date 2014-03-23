<?php
/**
 * @package EDK
 */

// Add unique name indices to alliance, corp and pilot
// Check kb3_inv_detail has correct indices
function update008()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "008" )
	{

		if(is_null(config::get('008updatestatus'))) config::set('008updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if(config::get('008updatestatus') <1)
		{
		// Add pilot, corp and alliance index to kb3_inv_detail
		// Incomplete in update007
			$qry->execute("SHOW INDEXES FROM kb3_inv_detail");
			$indexcexists = false;
			$indexaexists = false;
			$indexpexists = false;
			$indexkexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Column_name'] == 'ind_kll_id' && $testresult['Seq_in_index'] == 1)
					$indexkexists = true;
				if($testresult['Column_name'] == 'ind_crp_id' && $testresult['Seq_in_index'] == 1)
					$indexcexists = true;
				if($testresult['Column_name'] == 'ind_all_id' && $testresult['Seq_in_index'] == 1)
					$indexaexists = true;
				if($testresult['Column_name'] == 'ind_plt_id' && $testresult['Seq_in_index'] == 1)
					$indexpexists = true;
			}

			if(!$indexkexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_kll_id`, `ind_order` ) ");
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. kb3_inv_detail index id_order added");
				$smarty->display('update.tpl');
				die();
			}
			if(!$indexcexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_crp_id` ) ");
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. kb3_inv_detail index id_order added");
				$smarty->display('update.tpl');
				die();
			}
			if(!$indexaexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_all_id` ) ");
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. kb3_inv_detail index id_order added");
				$smarty->display('update.tpl');
				die();
			}
			if(!$indexpexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_plt_id` ) ");
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. kb3_inv_detail index id_order added");
				$smarty->display('update.tpl');
				die();
			}
			config::set('008updatestatus', 1);
		}

		$qry->execute("SHOW INDEXES FROM kb3_corps");
		$indexaexists = false;
		while($testresult = $qry->getRow())
		{
			if($testresult['Column_name'] == 'crp_name' && $testresult['Non_unique'] == 0)
				$indexcexists = true;
		}
		if(!$indexcexists)
		{
			$qry->rewind();
			$indexexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Key_name'] == 'UPGRADE8_crp_name')
					$indexexists = true;
			}
			if(!$indexexists) $qry->execute("ALTER TABLE `kb3_corps` ADD INDEX `UPGRADE8_crp_name` ( `crp_name` ) ");
			$sqlcrp = 'select a.crp_id as newid, b.crp_id as oldid from kb3_corps a, kb3_corps b where a.crp_name = b.crp_name and a.crp_id < b.crp_id';

			if(config::get('008updatestatus') <2)
			{
				$qry->execute('update kb3_inv_detail join ('.$sqlcrp.') c on c.oldid = ind_crp_id set ind_crp_id = c.newid');
				config::set('008updatestatus', 2);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique corp names: updated kb3_inv_detail");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <3)
			{
				$qry->execute('update kb3_pilots join ('.$sqlcrp.') c on (c.oldid = plt_crp_id) set plt_crp_id = c.newid');
				config::set('008updatestatus', 3);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique corp names: updated kb3_pilots");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <4)
			{
				$qry->execute('update kb3_kills join ('.$sqlcrp.') c on (c.oldid = kll_crp_id) set kll_crp_id = c.newid');
				config::set('008updatestatus', 4);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique corp names: updated kb3_kills");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <6)
			{
				$qry->execute('delete b from kb3_corps a, kb3_corps b where a.crp_name = b.crp_name and a.crp_id < b.crp_id');
				config::set('008updatestatus', 6);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique corp names: updated kb3_corps");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <7)
			{
				$qry->execute("ALTER TABLE `kb3_corps` DROP INDEX `UPGRADE8_crp_name`");
				$qry->execute("SHOW INDEXES FROM kb3_corps");
				$indexcexists = false;
				while($testresult = $qry->getRow())
				{
					if($testresult['Column_name'] == 'crp_name' && $testresult['Seq_in_index'] == 1)
					{
						$indexcname = $testresult['Key_name'];
						$indexcexists = true;
					}
					// Don't replace a custom multi-column index.
					elseif($testresult['Key_name'] == $indexcname && $testresult['Seq_in_index'] == 2)
						$indexcexists = false;
				}
				if($indexcexists) $qry->execute("ALTER TABLE `kb3_corps` DROP INDEX `".$indexcname."`");
				$qry->execute("ALTER TABLE `kb3_corps` ADD UNIQUE INDEX ( `crp_name` ) ");


				config::set('008updatestatus', 7);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique corp names: unique index added to kb3_corps");
				$smarty->display('update.tpl');
				die();
			}
		}
		// Make kb3_alliances.all_name unique without losing kills
		$qry->execute("SHOW INDEXES FROM kb3_alliances");
		$indexaexists = false;
		while($testresult = $qry->getRow())
		{
			if($testresult['Column_name'] == 'all_name' && $testresult['Non_unique'] == 0)
				$indexaexists = true;
		}
		if(!$indexaexists)
		{
			$qry->rewind();
			$indexexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Key_name'] == 'UPGRADE8_all_name')
					$indexexists = true;
			}
			if(!$indexexists) $qry->execute("ALTER TABLE `kb3_alliances` ADD INDEX `UPGRADE8_all_name` ( `all_name` ) ");
			$sqlall = 'select a.all_id as newid, b.all_id as oldid from kb3_alliances a, kb3_alliances b where a.all_name = b.all_name and a.all_id < b.all_id';
			if(config::get('008updatestatus') <8)
			{
				$qry->execute('update kb3_inv_detail join ('.$sqlall.') c on c.oldid = ind_all_id set ind_all_id = c.newid');
				config::set('008updatestatus', 8);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique all names: updated kb3_inv_detail");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <9)
			{
				$qry->execute('update kb3_corps join ('.$sqlall.') c on (c.oldid = crp_all_id) set crp_all_id = c.newid');
				config::set('008updatestatus', 9);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique all names: updated kb3_corps");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <10)
			{
				$qry->execute('update kb3_kills join ('.$sqlall.') c on (c.oldid = kll_all_id) set kll_all_id = c.newid');
				config::set('008updatestatus', 10);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique all names: updated kb3_kills");
				$smarty->display('update.tpl');
				die();
			}

			if(config::get('008updatestatus') <12)
			{
				$qry->execute('delete b from kb3_alliances a, kb3_alliances b where a.all_name = b.all_name and a.all_id < b.all_id');
				config::set('008updatestatus', 12);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique all names: updated kb3_alliances");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <13)
			{
				$qry->execute("ALTER TABLE `kb3_alliances` DROP INDEX `UPGRADE8_all_name`");
				$qry->execute("SHOW INDEXES FROM kb3_alliances");
				$indexaexists = false;
				while($testresult = $qry->getRow())
				{
					if($testresult['Column_name'] == 'all_name' && $testresult['Seq_in_index'] == 1)
					{
						$indexaname = $testresult['Key_name'];
						$indexaexists = true;
					}
					// Don't replace a custom multi-column index.
					elseif($testresult['Key_name'] == $indexaname && $testresult['Seq_in_index'] == 2)
						$indexaexists = false;
				}
				if($indexaexists) $qry->execute("ALTER TABLE `kb3_alliances` DROP INDEX `".$indexaname."`");
				$qry->execute("ALTER TABLE `kb3_alliances` ADD UNIQUE INDEX ( `all_name` ) ");
				config::set('008updatestatus', 13);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique all names: unique index applied to kb3_alliances");
				$smarty->display('update.tpl');
				die();
			}
		}

		// Make kb3_pilots.plt_name unique without losing kills
		$qry->execute("SHOW INDEXES FROM kb3_pilots");
		$indexaexists = false;
		while($testresult = $qry->getRow())
		{
			if($testresult['Column_name'] == 'plt_name' && $testresult['Non_unique'] == 0)
				$indexaexists = true;
		}
		if(!$indexaexists)
		{
			$qry->rewind();
			$indexexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Key_name'] == 'UPGRADE8_plt_name')
					$indexexists = true;
			}
			if(!$indexexists) $qry->execute("ALTER TABLE `kb3_pilots` ADD INDEX `UPGRADE8_plt_name` ( `plt_name` ) ");
			$sqlplt = 'select a.plt_id as newid, b.plt_id as oldid from kb3_pilots a, kb3_pilots b where a.plt_name = b.plt_name and a.plt_id < b.plt_id';
			if(config::get('008updatestatus') <14)
			{
				$qry->execute('update kb3_inv_detail join ('.$sqlplt.') c on c.oldid = ind_plt_id set ind_plt_id = c.newid');
				config::set('008updatestatus', 14);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique plt names: updated kb3_inv_detail");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <15)
			{
				$qry->execute('update kb3_kills join ('.$sqlplt.') c on (c.oldid = kll_victim_id) set kll_victim_id = c.newid');
				config::set('008updatestatus', 15);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique plt names: updated kb3_kills victim");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <16)
			{
				$qry->execute('update kb3_kills join ('.$sqlplt.') c on (c.oldid = kll_fb_plt_id) set kll_fb_plt_id = c.newid');
				config::set('008updatestatus', 16);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique plt names: updated kb3_kills killer");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <17)
			{
				$qry->execute('delete b from kb3_pilots a, kb3_pilots b where a.plt_name = b.plt_name and a.plt_id < b.plt_id');
				config::set('008updatestatus', 17);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique plt names: updated kb3_pilots");
				$smarty->display('update.tpl');
				die();
			}
			if(config::get('008updatestatus') <18)
			{
				$qry->execute("ALTER TABLE `kb3_pilots` DROP INDEX `UPGRADE8_plt_name`");
				$qry->execute("SHOW INDEXES FROM kb3_pilots");
				$indexpexists = false;
				while($testresult = $qry->getRow())
				{
					if($testresult['Column_name'] == 'plt_name' && $testresult['Seq_in_index'] == 1)
					{
						$indexpname = $testresult['Key_name'];
						$indexpexists = true;
					}
					// Don't replace a custom multi-column index.
					elseif($testresult['Key_name'] == $indexpname && $testresult['Seq_in_index'] == 2)
						$indexpexists = false;
				}
				if($indexpexists)  $qry->execute("ALTER TABLE `kb3_pilots` DROP INDEX `".$indexpname."`");
				$qry->execute("ALTER TABLE `kb3_pilots` ADD UNIQUE INDEX ( `plt_name` ) ");
				config::set('008updatestatus', 18);
				$smarty->assign('refresh',1);
				$smarty->assign('content', "8. Unique plt names: unique index applied to kb3_pilots.");
				$smarty->display('update.tpl');
				die();
			}
		}
		config::set('cache_update', '*');
		config::set('cache_time', '10');

		killCache();
		config::set("DBUpdate", "008");
		$qry->execute("UPDATE kb3_config SET cfg_value = '008' WHERE cfg_key = 'DBUpdate'");
		config::del("008updatestatus");
		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 008 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

