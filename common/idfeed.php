<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

/*
 * Create a syndication feed of kills stored on this board.
 *
 * Flags
 * startdate = unix timestamp for start date
 * enddate = unix timestamp for end date
 * lastID = return all kills from lastID on (ordered by kll_external_id)
 * lastintID = return all kills from lastintID internal id on (ordered by kll_id)
 * range = return all kills between lastID and lastID + range
 *     (limited by $maxkillsreturned)
 * allkills = also return results without an external id set
 * pilot = pilot id to retrieve kills for
 * corp =  corp id to retrieve kills for
 * alliance = alliance id to retrieve kills for
 * pilotname = pilot id to retrieve kills for
 * corpname =  corp name to retrieve kills for
 * alliancename = alliance name to retrieve kills for
 * system = restrict kills to a specific system
 * region = restrict kills to a specific region
 *
 */

header("Content-Type: text/xml");

$maxkillsreturned = 200;

$list = new KillList();
if(!isset($_GET['allkills'])) $list->setAPIKill();
$list->setLimit($maxkillsreturned);
$list->setOrdered(true);
if(!isset($_GET['allkills'])) $list->setOrderBy(' kll.kll_external_id ASC ');
else $list->setOrderBy(' kll.kll_id ASC ');
$qry = DBFactory::getDBQuery();
if(isset($_GET['alliance']))
{
	$qry->execute("SELECT all_id FROM kb3_alliances WHERE all_external_id = ".intval($_GET['alliance']));
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addCombinedAlliance($row['all_id']);
}
else if(isset($_GET['corp']))
{
	$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id = ".intval($_GET['corp']));
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addCombinedCorp($row['crp_id']);
}
else if(isset($_GET['pilot']))
{
	$qry->execute("SELECT plt_id FROM kb3_pilots WHERE plt_externalid = ".intval($_GET['pilot'])." LIMIT 1");
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addCombinedPilot($row['plt_id']);
}
else if(isset($_GET['alliancename']))
{
	$qry->execute("SELECT all_id FROM kb3_alliances WHERE all_name = '".$qry->escape($_GET['alliancename'])."' LIMIT 1");
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addCombinedAlliance($row['all_id']);
}
else if(isset($_GET['corpname']))
{
	$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_name = '".$qry->escape($_GET['corpname'])."' LIMIT 1");
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addCombinedCorp($row['crp_id']);
}
else if(isset($_GET['pilotname']))
{
	$qry->execute("SELECT plt_id FROM kb3_pilots WHERE plt_name = '".$qry->escape($_GET['pilotname'])."' LIMIT 1");
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addCombinedPilot($row['plt_id']);
}

if(isset($_GET['system']))
{
	$qry->execute("SELECT sys_id FROM kb3_systems WHERE sys_eve_id = ".intval($_GET['system'])." LIMIT 1");
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addSystem($row['sys_id']);
}
else if(isset($_GET['region']))
{
	$qry->execute("SELECT reg_id FROM kb3_regions WHERE reg_id = ".intval($_GET['region'])." LIMIT 1");
	if(!$qry->recordCount())
	{
		$xml = "<?xml version='1.0' encoding='UTF-8'?><eveapi version='2'></eveapi>";
		die($xml);
	}
	$row = $qry->getRow();
	$list->addRegion($row['reg_id']);
}
if(isset($_GET['lastID']))
{
	$list->setMinExtID(intval($_GET['lastID']));
	if(isset($_GET['range'])) $list->setMaxExtID(intval($_GET['lastID'] + $_GET['range']));
}
else if(isset($_GET['lastintID']) && isset($_GET['allkills']) && $_GET['allkills'])
{
	$list->setMinKllID(intval($_GET['lastintID']));
	if(isset($_GET['range'])) $list->setMaxKllID(intval($_GET['lastintID'] + $_GET['range']));
}
if(isset($_GET['startdate'])) $list->setStartDate(gmdate('Y-m-d H:i:s',intval($_GET['startdate'])));
if(isset($_GET['enddate'])) $list->setEndDate(gmdate('Y-m-d H:i:s',intval($_GET['startdate'])));
$date = gmdate('Y-m-d H:i:s');

// Let's start making the xml.
$xml = "<?xml version='1.0' encoding='UTF-8'?>
<eveapi version='2'>
</eveapi>";
$sxe = new SimpleXMLElement($xml);
$sxe->addChild('currentTime', $date);
$result = $sxe->addChild('result');
$kills = $result->addChild('rowset');
$kills->addAttribute('name', 'kills');
$kills->addAttribute('key', 'killID');
$kills->addAttribute('columns', 'killID,solarSystemID,killTime,moonID,hash,trust');
//$list->getAllKills();
$count = 0;
while($kill1 = $list->getKill())
{//$kill = new Kill();
	$count++;
	if($kill1->isClassified()) continue;
	$kill = new Kill($kill1->getID());
	$kill->setDetailedInvolved();
	$row = $kills->addChild('row');
	$row->addAttribute('killID', intval($kill->getExternalID()));
	if(isset($_GET['allkills'])) $row->addAttribute('killInternalID', intval($kill->getID()));
	$row->addAttribute('solarSystemID', $kill->getSystem()->getExternalID());
	$row->addAttribute('killTime', $kill->getTimeStamp());
	$row->addAttribute('moonID', '0');
	$row->addAttribute('hash', bin2hex($kill->getHash()));
	$row->addAttribute('trust', $kill->getTrust());
	$victim = new Pilot($kill->getVictimID());
	$victimCorp = new Corporation($kill->getVictimCorpID());
	$victimAlliance = new Alliance($kill->getVictimAllianceID());
	$victimrow = $row->addChild('victim');
	$victimrow->addAttribute('characterID', $victim->getExternalID());
	$victimrow->addAttribute('characterName', $victim->getName());
	$victimrow->addAttribute('corporationID', $victimCorp->getExternalID());
	$victimrow->addAttribute('corporationName', $victimCorp->getName());
	if($victimAlliance->isFaction())
	{
		$victimrow->addAttribute('allianceID', 0);
		$victimrow->addAttribute('allianceName', '');
		$victimrow->addAttribute('factionID', $victimAlliance->getName());
		$victimrow->addAttribute('factionName', $victimAlliance->getFactionID());
	}
	else
	{
		$victimrow->addAttribute('allianceID', $victimAlliance->getExternalID());
		$victimrow->addAttribute('allianceName', $victimAlliance->getName());
		$victimrow->addAttribute('factionID', 0);
		$victimrow->addAttribute('factionName', '');
	}
	$victimrow->addAttribute('damageTaken', $kill->getDamageTaken());
	$victimrow->addAttribute('shipTypeID', $kill->getVictimShip()->getExternalID());
	$involved = $row->addChild('rowset');
	$involved->addAttribute('name', 'attackers');
	$involved->addAttribute('columns', 'characterID,characterName,corporationID,corporationName,allianceID,allianceName,factionID,factionName,securityStatus,damageDone,finalBlow,weaponTypeID,shipTypeID');
	foreach ($kill->getInvolved() as $inv)
	{
		$invrow = $involved->addChild('row');
		$invPilot = $inv->getPilot();
		$invCorp = $inv->getCorp();
		$invAlliance = $inv->getAlliance();
		$invrow->addAttribute('characterID', $invPilot->getExternalID());
		$invrow->addAttribute('characterName', $invPilot->getName());
		$invrow->addAttribute('corporationID', $invCorp->getExternalID());
		$invrow->addAttribute('corporationName', $invCorp->getName());
		if($invAlliance->isFaction())
		{
			$invrow->addAttribute('allianceID', 0);
			$invrow->addAttribute('allianceName', '');
			$invrow->addAttribute('factionID', $invAlliance->getFactionID());
			$invrow->addAttribute('factionName', $invAlliance->getName());
		}
		else
		{
			$invrow->addAttribute('allianceID', $invAlliance->getExternalID());
			$invrow->addAttribute('allianceName', $invAlliance->getName());
			$invrow->addAttribute('factionID', 0);
			$invrow->addAttribute('factionName', '');
		}
		$invrow->addAttribute('securityStatus', $inv->getSecStatus());
		$invrow->addAttribute('damageDone', $inv->getDamageDone());
		if($invPilot->getID() == $kill->getFBPilotID()) $final = 1;
		else $final = 0;
		$invrow->addAttribute('finalBlow', $final);
		$invrow->addAttribute('weaponTypeID', $inv->getWeapon()->getID());
		$invrow->addAttribute('shipTypeID', $inv->getShip()->getExternalID());
	}
	$droppedItems = $kill->getDroppedItems();
	$destroyedItems = $kill->getDestroyedItems();
	if(count($destroyedItems) || count($droppedItems))
	{
		$items = $row->addChild('rowset');
		$items->addAttribute('name', 'items');
		$items->addAttribute('columns', 'typeID,flag,qtyDropped,qtyDestroyed');

		foreach($destroyedItems as $destroyed)
		{
			$item = $destroyed->getItem();
			$itemRow = $items->addChild('row');
			$itemRow->addAttribute('typeID', $item->getID());
			if ($destroyed->getLocationID() == 4) // cargo
				$itemRow->addAttribute('flag', 5);
			else if ($destroyed->getLocationID() == 6) // drone
				$itemRow->addAttribute('flag', 87);
			else
				$itemRow->addAttribute('flag', 0);
			$itemRow->addAttribute('qtyDropped', 0);
			$itemRow->addAttribute('qtyDestroyed', $destroyed->getQuantity());
			if ($destroyed->getQuantity() > 1)
				$mail .= ", Qty: ".$destroyed->getQuantity();
		}


		foreach($droppedItems as $dropped)
		{
			$item = $dropped->getItem();
			$itemRow = $items->addChild('row');
			$itemRow->addAttribute('typeID', $item->getID());
			if ($dropped->getLocationID() == 4) // cargo
				$itemRow->addAttribute('flag', 5);
			else if ($dropped->getLocationID() == 6) // drone
				$itemRow->addAttribute('flag', 87);
			else
				$itemRow->addAttribute('flag', 0);
			$itemRow->addAttribute('qtyDropped', $dropped->getQuantity());
			$itemRow->addAttribute('qtyDestroyed', 0);
			if ($dropped->getQuantity() > 1)
				$mail .= ", Qty: ".$dropped->getQuantity();
		}
	}

}
$sxe->addChild('cachedUntil', $date);
echo $sxe->asXML();
