<?php
/**
 * @package EDK
 */

function update040()
{

	global $url, $smarty;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "040" )
	{
		if(is_null(config::get('040updatestatus'))) config::set('040updatestatus',0);
		$qry = DBFactory::getDBQuery(true);

		if( config::get('040updatestatus') <1 )
		{
			$qry->execute("SHOW COLUMNS FROM kb3_mails LIKE 'kll_json'");
			if(!$qry->recordCount()) {
				$sql = 'ALTER TABLE `kb3_mails` ADD COLUMN `kll_json` BLOB';
				$qry->execute($sql);
			}
			config::set('040updatestatus',1);
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Adding JSON field");
			$smarty->display('update.tpl');
			die();
		}
		
		if(config::get('040updatestatus') <2) {
			if( config::get('040updatelastkill') > 0 ) {
				$sql = "select kll_id from kb3_mails where kll_trust <> -1 AND kll_id > " . config::get('040updatelastkill') .
				" AND kll_json is null limit 500";
			} else {
				$sql = "select kll_id from kb3_mails where kll_trust <> -1 AND kll_json is null limit 500";
			}
			$qry->execute($sql);
			$out = '';	
			while ($row = $qry->getRow()) {
				$killid = (int)$row['kll_id'];

				$xml = killIDToXML($killid);
				if( $xml === false ) {
					config::set('040updatelastkill',$killid);
					$out .= "Corrupt Kill: $killid<br/>";
					die;
					continue;
				}
				$xml2 = (string)str_replace(array("\r", "\r\n", "\n", " ", '<?xmlversion="1.0"?>'), '', $xml->result->rowset->row->asXML());
				$killarray = xmlToArray($xml->result->rowset->row);
				$killarray['v'] = 1;

				$json = json_encode( $killarray );
				$xmlj = jsonToXML(json_decode($json));
				$xmlj = (string)str_replace(array("\r", "\r\n", "\n", " ", "<?xmlversion=\"1.0\"?>"), '', $xmlj);

				if( $xmlj == $xml2 ) {
					$kll_json = gzdeflate($json, 9);
					
					$qry2 = DBFactory::getDBQuery(true);
					$sql2 = "UPDATE kb3_mails SET kll_json='". $qry->escape($kll_json) ."' where kll_id = " . $killid;
					$qry2->execute($sql2);
				} else {
					echo 'Failed to convert Kill to JSON format';
					echo($xmlj); //--> encoded
					echo "--------------------";
					echo $xml2;// --> correct
					die; 
					continue;
				}
			}
			
			$sql = "select count(kll_id) as cnt from kb3_mails where kll_trust <> -1 AND kll_json is null";
			$qry->execute($sql);
			$row = $qry->getRow();
			if( $row['cnt'] == 0 ) {
				config::set('040updatestatus',2);
			}
			$smarty->assign('refresh',1);
			$smarty->assign('content', "Generating JSON For Kills ($killid):<br/>" . $out);
			$smarty->display('update.tpl');
			die();
		}
		
		config::set("DBUpdate", "040");
		$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '040' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '040'");
		config::del("040updatestatus");

		$smarty->assign('refresh',1);
		$smarty->assign('content', "Update 040 completed.");
		$smarty->display('update.tpl');
		die();
	}
}


/**
 *
 * @param int $id
 * @return string Kill as XML
 */
function killIDToXML($id)
{
	$qry = DBFactory::getDBQuery();
	$date = gmdate('Y-m-d H:i:s');
	$xml = "<?xml version='1.0' encoding='UTF-8'?>
	<eveapi version='2'></eveapi>";
	$sxe = new SimpleXMLElement($xml);
	// Let's start making the xml.
	$sxe->addChild('currentTime', $date);
	$result = $sxe->addChild('result');
	$kills = $result->addChild('rowset');
	$kills->addAttribute('name', 'kills');
	$kills->addAttribute('key', 'killID');
	$kills->addAttribute('columns', 'killID,solarSystemID,killTime,moonID');

	$sql = "select kll.kll_id, kll.kll_external_id, kll.kll_timestamp,
						kll.kll_victim_id, kll.kll_crp_id, kll.kll_all_id,
						kll.kll_ship_id, kll.kll_system_id,
						kll.kll_points, kll.kll_isk_loss, kll_dmgtaken,
						fb.ind_plt_id as fbplt_id,
						fb.ind_crp_id as fbcrp_id,
						fb.ind_all_id as fbali_id
					from kb3_kills kll, kb3_inv_detail fb
					where kll.kll_id = '".$id."'
						and fb.ind_kll_id = kll.kll_id
						and fb.ind_plt_id = kll.kll_fb_plt_id";
	$qry->execute($sql);
	$ret = $qry->getRow();	
	if (!$ret) {
		echo "$id : Corrupt ID: $id" . "<br/>\n";
		return false; // bad kill
	}
	$row = $kills->addChild('row');
	$row->addAttribute('killID', intval($ret['kll_external_id']));
	$row->addAttribute('solarSystemID', $ret['kll_system_id']);
	$row->addAttribute('killTime', $ret['kll_timestamp']);
	$row->addAttribute('moonID', '0');
	
	$sql = 'SELECT * FROM kb3_pilots plt'
		.' LEFT JOIN kb3_corps crp ON plt_crp_id = crp_id'
		.' LEFT JOIN kb3_alliances ali ON crp_all_id = all_id'
		." WHERE plt.plt_id = " .(int)$ret['kll_victim_id'];
	$qry->execute($sql);
	$ret2 = $qry->getRow();	
	if (!$ret2) {
		echo "$id : Bad Victim: $id" . "<br/>\n";
		return false; // bad kill
	}
	
	$sql = "SELECT typeName, shp_id FROM kb3_ships INNER JOIN kb3_invtypes ON typeID=shp_id
			WHERE shp_id = " . (int)$ret['kll_ship_id'];
	$qry->execute($sql);
	$ret4 = $qry->getRow();	
	if (!$ret4) {
		echo "$id : Missing Ship: " . $ret['kll_ship_id'] . "<br/>\n";
		return false; // bad kill
	}	
	$victimrow = $row->addChild('victim');
	if ($ret2['plt_name'] == $ret4['typeName']) {
		$victimrow->addAttribute('characterID', "0");
		$victimrow->addAttribute('characterName', "");
	} else {
		$victimrow->addAttribute('characterID', (int)$ret2['plt_externalid']);
		$victimrow->addAttribute('characterName', $ret2['plt_name']);
	}

	$sql = "select * from kb3_corps where crp_id = " . (int)$ret['kll_crp_id'];
	$qry->execute($sql);
	$ret3 = $qry->getRow();
	if (!$ret3) {
		echo "$id :Missing Corp<br/>\n";
		return false; // bad kill
	}
	$victimrow->addAttribute('corporationID', intval($ret3['crp_external_id']));
	$victimrow->addAttribute('corporationName', $ret3['crp_name']);

	$sql = "select all_id, all_name, all_external_id from kb3_alliances 
			where all_id = ".(int)$ret['kll_all_id'];
	$qry->execute($sql);
	$ret5 = $qry->getRow();
	if (!$ret5) {
		echo "$id :Missing Alliance<br/>\n";
		return false; // bad kill
	}

	$factions = array("Amarr Empire",
					  "Minmatar Republic",
					  "Caldari State",
					  "Gallente Federation");
	if (in_array($ret5['all_name'], $factions)) {
		$victimrow->addAttribute('allianceID', 0);
		$victimrow->addAttribute('allianceName', '');
		$victimrow->addAttribute('factionID', $ret5['all_external_id']);
		$victimrow->addAttribute('factionName', $ret5['all_name']);
	} else {
		$victimrow->addAttribute('allianceID', $ret5['all_external_id']);
		$victimrow->addAttribute('allianceName', $ret5['all_name']);
		$victimrow->addAttribute('factionID', 0);
		$victimrow->addAttribute('factionName', '');
	}
	$victimrow->addAttribute('damageTaken', (int)$ret['kll_dmgtaken']);
	$victimrow->addAttribute('shipTypeID', (int)$ret['kll_ship_id']);
	$involved = $row->addChild('rowset');
	$involved->addAttribute('name', 'attackers');
	$involved->addAttribute('columns',
			'characterID,characterName,corporationID,corporationName,allianceID,allianceName,factionID,factionName,securityStatus,damageDone,finalBlow,weaponTypeID,shipTypeID');

	$sql = "SELECT ind_sec_status, ind_all_id, ind_crp_id,
		ind_shp_id, ind_wep_id, ind_order, ind_dmgdone, plt_id, plt_name,
		plt_externalid, crp_name, crp_external_id,
		wtype.typeName AS wep_name FROM kb3_inv_detail
		JOIN kb3_pilots ON (plt_id = ind_plt_id)
		JOIN kb3_corps ON (crp_id = ind_crp_id)
		JOIN kb3_invtypes wtype ON (ind_wep_id = wtype.typeID)
		WHERE ind_kll_id = ".$id." ORDER BY ind_order ASC";
	$qry->execute($sql);

	while ($inv = $qry->getRow()) {
		$invrow = $involved->addChild('row');
		if (strpos($inv['plt_name'], '- ') !== false) {
			$inv['plt_name'] = substr($inv['plt_name'], strpos($inv['plt_name'], '- ') + 2);
		} else if (strpos($inv['plt_name'], '#') !== false) {
			$name = explode("#", $inv['plt_name']);
			$inv['plt_name'] = $name[3];
		}
		if ($inv['plt_name'] == $inv['wep_name']) {
			$invrow->addAttribute('characterID', 0);
			$invrow->addAttribute('characterName', "");
			$invrow->addAttribute('weaponTypeID', 0);
			$invrow->addAttribute('shipTypeID', $inv['ind_wep_id']);
		} else {
			$invrow->addAttribute('characterID', $inv['plt_externalid']);
			$invrow->addAttribute('characterName', $inv['plt_name']);
			$invrow->addAttribute('weaponTypeID', $inv['ind_wep_id']);
			$invrow->addAttribute('shipTypeID', $inv['ind_shp_id']);
		}
		$invrow->addAttribute('corporationID', $inv['crp_external_id']);
		$invrow->addAttribute('corporationName', $inv['crp_name']);

		$sql = "select all_id, all_name, all_external_id from kb3_alliances where all_id = ".(int)$inv['ind_all_id'];
		$qry->execute($sql);
		$ret6 = $qry->getRow();
		if (!$ret6) {
			echo "$id :Missing Alliance<br/>\n";
			return false; // bad kill
		}

		$factions = array("Amarr Empire",
						  "Minmatar Republic",
						  "Caldari State",
						  "Gallente Federation");
		if (in_array($ret6['all_name'], $factions)) {
			$invrow->addAttribute('allianceID', 0);
			$invrow->addAttribute('allianceName', '');
			$invrow->addAttribute('factionID', $ret6['all_external_id']);
			$invrow->addAttribute('factionName', $ret6['all_name']);
		} else {
			if (strcasecmp($ret6['all_name'], "None") == 0) {
				$invrow->addAttribute('allianceID', 0);
				$invrow->addAttribute('allianceName', "");
			} else {
				$invrow->addAttribute('allianceID', $ret6['all_external_id']);
				$invrow->addAttribute('allianceName', $ret6['all_name']);
			}
			$invrow->addAttribute('factionID', 0);
			$invrow->addAttribute('factionName', '');
		}
		$invrow->addAttribute('securityStatus',
				number_format($inv['ind_sec_status'], 1));
		$invrow->addAttribute('damageDone', $inv['ind_dmgdone']);
		if ($inv['plt_id'] == (int)$ret['fbplt_id']) {
			$final = 1;
		} else {
			$final = 0;
		}
		$invrow->addAttribute('finalBlow', $final);
	}
	$sql = "SELECT * FROM kb3_items_destroyed WHERE itd_kll_id = ".$id;
	$qry->execute($sql);
	$qry2 = DBFactory::getDBQuery();
	$sql = "SELECT * FROM kb3_items_dropped WHERE itd_kll_id = ".$id;
	$qry2->execute($sql);

	if ($qry->recordCount() || $qry2->recordCount()) {
		$items = $row->addChild('rowset');
		$items->addAttribute('name', 'items');
		$items->addAttribute('columns', 'typeID,flag,qtyDropped,qtyDestroyed, singleton');

		while ($iRow = $qry->getRow()) {
			$itemRow = $items->addChild('row');
			$itemRow->addAttribute('typeID', $iRow['itd_itm_id']);
			$itemRow->addAttribute('flag', $iRow['itd_itl_id'] );

			if ($iRow['itd_itl_id'] == -1) {
				$itemRow->addAttribute('singleton', 2);
			} else {
				$itemRow->addAttribute('singleton', 0);
			}

			$itemRow->addAttribute('qtyDropped', 0);
			$itemRow->addAttribute('qtyDestroyed', $iRow['itd_quantity']);
		}


		while ($iRow = $qry2->getRow()) {
			$itemRow = $items->addChild('row');
			$itemRow->addAttribute('typeID', $iRow['itd_itm_id']);
			$itemRow->addAttribute('flag', $iRow['itd_itl_id'] );

			if ($iRow['itd_itl_id'] == -1) {
				$itemRow->addAttribute('singleton', 2);
			} else {
				$itemRow->addAttribute('singleton', 0);
			}

			$itemRow->addAttribute('qtyDropped', $iRow['itd_quantity']);
			$itemRow->addAttribute('qtyDestroyed', 0);
		}
	}

	$sxe->addChild('cachedUntil', $date);
	return $sxe;
}




 function processItems( $row, $attributes ) {
	foreach( $attributes as $n=>$v ) {
		if( is_array($v) || is_object($v) ) {
			if( $n == 'items' && (is_array( $v ) ) ) {
				$row2 = $row->addChild( 'rowset' );
				$row2->addAttribute( 'name', 'items' );
				$row2->addAttribute( 'columns', 'typeID,flag,qtyDropped,qtyDestroyed,singleton' );
				processItems( $row2, $v );
			} else {
				$row3 =$row->addChild( 'row' );
				processItems( $row3, $v );
			}
		} else {
			$row->addAttribute( $n, $v );
		}
	}
}
 
 function jsonToXML( $json ) {
	$sxe = new SimpleXMLElement('<row></row>');

	foreach ( $json as $name => $value ) {
		switch( $name ) {
			case 'attackers':
				$row =$sxe->addChild( 'rowset' );
				$row->addAttribute( 'name', 'attackers' );
				$row->addAttribute( 'columns', 'characterID,characterName,corporationID,corporationName,allianceID,allianceName,factionID,factionName,securityStatus,damageDone,finalBlow,weaponTypeID,shipTypeID' );
				foreach( $value as $n=>$v ) {
					$row2 =$row->addChild( 'row' );
					foreach( $v as $n=>$v ) {
						$row2->addAttribute( $n, $v );
					}
				}
				break;
			case 'items':
				$row =$sxe->addChild( 'rowset' );
				$row->addAttribute( 'name', 'items' );
				$row->addAttribute( 'columns', 'typeID,flag,qtyDropped,qtyDestroyed,singleton' );
				foreach( $value as $n=>$v ) {
					$row2 =$row->addChild( 'row' );
					processItems( $row2, $v);
				}
				break;
			case 'victim':
				$row =$sxe->addChild( 'victim' );
				foreach( $value as $n=>$v ) {
					
					$row->addAttribute( $n, $v );
				}
				break;
			case 'v':
				break;
			default:
				$sxe->addAttribute( $name, $value );
				break;
		}
	}
	return html_entity_decode($sxe->asXml(), ENT_NOQUOTES, 'UTF-8');;
 }
 
 
 

 function xmlToArray(SimpleXMLElement $xml, $skipattributes = false){ 
    $return = array(); 
    $_value = trim((string)$xml); 
    if(!strlen($_value)){$_value = null;}

    if($_value!==null){ 
        $return = $_value;
    } 

	if( !$skipattributes ) {
		$attributes = array();
		foreach($xml->attributes() as $name=>$value){ 
			$attributes[$name] = trim($value); 
		} 
		if($attributes){ 
			$return = array_merge($return, $attributes);
		}
	}
	
    $children = array(); 
    $first = true;
	$skipattributes = false;
    foreach($xml->children() as $elementName => $child){ 
		if( $elementName == 'rowset') {
			$elementName = (string)$child->attributes()->name;
			$skipattributes = true;
		}
		if( $elementName == 'row') {
			$value = xmlToArray($child, $skipattributes);
			$children[] = $value;
			continue;
		}
 
        $value = xmlToArray($child, $skipattributes); 
        if(isset($children[$elementName])){ 
            if(is_array($children[$elementName])){ 
				if($first){ 
                    $temp = $children[$elementName]; 
                    unset($children[$elementName]); 
                    $children[$elementName][] = $temp; 
                    $first=false; 
                } 
                $children[$elementName][] = $value; 
            }else{ 
                $children[$elementName] = array($children[$elementName],$value); 
            } 
        } 
        else{ 
            $children[$elementName] = $value; 
        } 
    } 
    if($children){ 
        $return = array_merge($return,$children);
    } 

    return $return; 
} 

 