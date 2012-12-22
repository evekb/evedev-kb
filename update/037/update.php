<?php
/**
 * @package EDK
 */

function update037()
{
	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "037" )
	{
		if(is_null(config::get('037updatestatus'))) config::set('037updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		// Fix correct slot for legacy kills post slot conversion
		if(config::get('037updatestatus') <1)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {		
				$sql = "select distinct( itd_itm_id) from kb3_items_destroyed where itd_itl_id = 0 and itd_kll_id <= " . $maxid;
				$qry->execute($sql);

				while ($row = $qry->getRow()) {
					$itemid = (int)$row['itd_itm_id'];
	
					$qry2 = DBFactory::getDBQuery();
					$sql = "select inv.*, kb3_item_types.*, dga.value as techlevel,
						   itp.price, dc.value as usedcharge, dl.value as usedlauncher
						   from kb3_invtypes inv
						   left join kb3_dgmtypeattributes dga on dga.typeID=inv.typeID and dga.attributeID=633
						   left join kb3_item_price itp on itp.typeID=inv.typeID
						   left join kb3_item_types on groupID=itt_id
						   left join kb3_dgmtypeattributes dc on dc.typeID = inv.typeID AND dc.attributeID IN (128)
						   left join kb3_dgmtypeattributes dl on dl.typeID = inv.typeID AND dl.attributeID IN (137,602)
						   where inv.typeID = '".$itemid."'";
					if ($qry2->execute($sql)) {
						$row_ = $qry2->getRow();
					} else {
						echo "Error updating item slots"; die;
					}
					
					if( $row_ == NULL ) {
						echo "Error updating item slots"; die;
					}
					$location = $row_['itt_slot'];

					// if item has no slot get the slot from parent item
					if ($location == 0) {
						$query = "select itt_slot from kb3_item_types
									inner join kb3_dgmtypeattributes d
									where itt_id = d.value
									and d.typeID = ".$row_['typeID']."
									and d.attributeID in (137,602);";
						$qry2->execute($query);
						$row = $qry2->getRow();
						if (!$row['itt_slot']) {
							$location = 0;
						} else {
							$location = $row['itt_slot'];
						}
					}
					
					if( $location > 0 ) {
						$sql = "UPDATE kb3_items_destroyed SET itd_itl_id = $location WHERE itd_itm_id = $itemid AND itd_itl_id = 0 and itd_kll_id <= " . $maxid;
						$qry2->execute($sql);
					}
				}
			}
			config::set('037updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}

		// Fix correct slot for legacy kills post slot conversion
		if(config::get('037updatestatus') <2)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {		
				$sql = "select distinct( itd_itm_id) from kb3_items_dropped where itd_itl_id = 0 and itd_kll_id <= " . $maxid;
				$qry->execute($sql);

				while ($row = $qry->getRow()) {
					$itemid = (int)$row['itd_itm_id'];
	
					$qry2 = DBFactory::getDBQuery();
					$sql = "select inv.*, kb3_item_types.*, dga.value as techlevel,
						   itp.price, dc.value as usedcharge, dl.value as usedlauncher
						   from kb3_invtypes inv
						   left join kb3_dgmtypeattributes dga on dga.typeID=inv.typeID and dga.attributeID=633
						   left join kb3_item_price itp on itp.typeID=inv.typeID
						   left join kb3_item_types on groupID=itt_id
						   left join kb3_dgmtypeattributes dc on dc.typeID = inv.typeID AND dc.attributeID IN (128)
						   left join kb3_dgmtypeattributes dl on dl.typeID = inv.typeID AND dl.attributeID IN (137,602)
						   where inv.typeID = '".$itemid."'";
					if ($qry2->execute($sql)) {
						$row_ = $qry2->getRow();
					} else {
						echo "Error updating item slots"; die;
					}
					
					if( $row_ == NULL ) {
						echo "Error updating item slots"; die;
					}
					$location = $row_['itt_slot'];

					// if item has no slot get the slot from parent item
					if ($location == 0) {
						$query = "select itt_slot from kb3_item_types
									inner join kb3_dgmtypeattributes d
									where itt_id = d.value
									and d.typeID = ".$row_['typeID']."
									and d.attributeID in (137,602);";
						$qry2->execute($query);
						$row = $qry2->getRow();
						if (!$row['itt_slot']) {
							$location = 0;
						} else {
							$location = $row['itt_slot'];
						}
					}
					
					if( $location > 0 ) {
						$sql = "UPDATE kb3_items_dropped SET itd_itl_id = $location WHERE itd_itm_id = $itemid AND itd_itl_id = 0 and itd_kll_id <= " . $maxid;
						$qry2->execute($sql);
					}
				}
			}
			config::set('037updatestatus',2);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}

		// Set implants as slot on pod's
		if(config::get('037updatestatus') <3)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {
				$sql = "select kb3_kills.kll_id from kb3_kills JOIN kb3_ships ON kb3_ships.shp_id = kb3_kills.kll_ship_id  
					    where kll_id IN ( select distinct itd_kll_id from kb3_items_destroyed where itd_itl_id = 0 and itd_kll_id <= $maxid ) and shp_class = 2";
				$qry->execute($sql);

				while ($row = $qry->getRow()) {
					$killid = (int)$row['kll_id'];
					
					if ($killid > $maxid ) { echo 'error - impossible to hit code hit' ; die; }
					
					$qry2 = DBFactory::getDBQuery();
					// 89 = implant
					$sql = "UPDATE kb3_items_destroyed SET itd_itl_id = 89 WHERE itd_kll_id = $killid AND itd_itl_id = 0 ";
					$qry2->execute($sql);
				}
			}
			config::set('037updatestatus',3);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}		

		// Ensure JF's do not have anything fitted
		if(config::get('037updatestatus') <4)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {
				$sql = "update kb3_items_destroyed set itd_itl_id = 5 where itd_kll_id <= " . $maxid . " AND itd_kll_id IN (Select kb3_kills.kll_id from kb3_kills JOIN kb3_ships ON kb3_ships.shp_id = kb3_kills.kll_ship_id  and shp_class = 34)";
				$qry->execute($sql);
			}
			config::set('037updatestatus',4);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}			

		// Ensure JF's do not have anything fitted
		if(config::get('037updatestatus') <5)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {
				$sql = "update kb3_items_dropped set itd_itl_id = 5 where itd_kll_id <= " . $maxid . " AND itd_kll_id IN (Select kb3_kills.kll_id from kb3_kills JOIN kb3_ships ON kb3_ships.shp_id = kb3_kills.kll_ship_id  and shp_class = 34)";
				$qry->execute($sql);
			}
			config::set('037updatestatus',5);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}	

		// Set Cargo Bay for anything legacy in non-capital ships
		// We can't set ore hold etc, as old kills only had cargo hold etc.
		if(config::get('037updatestatus') <6)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {
				$sql = "update kb3_items_dropped set itd_itl_id = 5 where itd_kll_id <= " . $maxid . " AND itd_itl_id = 0 AND itd_kll_id IN (Select kb3_kills.kll_id from kb3_kills JOIN kb3_ships ON kb3_ships.shp_id = kb3_kills.kll_ship_id  and shp_class NOT IN (26,27,28,29,39))";
				$qry->execute($sql);
			}
			config::set('037updatestatus',6);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}
		// Set Cargo Bay for anything legacy in non-capital ships
		if(config::get('037updatestatus') <7)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {
				$sql = "update kb3_items_destroyed set itd_itl_id = 5 where itd_kll_id <= " . $maxid . " AND itd_itl_id = 0 AND itd_kll_id IN (Select kb3_kills.kll_id from kb3_kills JOIN kb3_ships ON kb3_ships.shp_id = kb3_kills.kll_ship_id  and shp_class NOT IN (26,27,28,29,39))";
				$qry->execute($sql);
			}
			config::set('037updatestatus',7);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}
		
		// fix fuel in dread's -> fuel bay
		if(config::get('037updatestatus') <7)
		{
			$maxid = (int)config::get('035killid' );

			if( $maxid > 0 ) {
				$sql = "update kb3_items_destroyed set itd_itl_id = 133 where itd_kll_id <= " . $maxid . " AND itd_itl_id = 0 AND
						itd_itm_id IN ( 16272, 16273, 16274, 16275, 17887, 17888, 17889 ) AND
						itd_kll_id IN (Select kb3_kills.kll_id from kb3_kills JOIN kb3_ships ON kb3_ships.shp_id = kb3_kills.kll_ship_id  and shp_class IN (26,27,28,29,19))";
				$qry->execute($sql);
			}
			config::set('037updatestatus',7);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Slot Data Fixes");
			$smarty->display('update.tpl');
			die();
		}

		// Other stuff in capitals - leave in slot = 0 i.e. corp hanger
		
		config::set("DBUpdate", "037");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '037' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '037'");
		config::del("037updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 037 completed.");
		$smarty->display('update.tpl');
		die();
	}
}

