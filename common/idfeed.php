<?php
/*
 * Create a syndication feed of kills stored on this board.
 *
 * Flags
 * week = week
 * year = year
 * lastkllid = return all kills lastkllid on (ordered by kll_id)
 * range = return all kills with lastkllid <= id <= lastkllid + range
 * APIkills = restrict results to kills with an external id set
 * pilot = pilot to retrieve kills for
 * corp = corp_name = corp to retrieve kills for
 * alli = alli_name = alliance to retrieve kills for
 * master = retrieve all kills
 * friend = set pilot/corp/alli as involved killer (default is victim)
 * combined = return both kills and losses
 *
 */
require_once('common/includes/class.killlist.php');
$list = new KillList();
$list->setAPIKill();
$list->setLimit(10);
$list->setOrdered(true);
$list->setOrderBy(' kll.kll_external_id DESC ');
$qry = new DBQuery();
if(isset($_REQUEST['alliance']))
{
	$qry->execute("SELECT all_id FROM kb3_alliances WHERE all_external_id = ".intval($_REQUEST['alliance']));
	$row = $qry->getRow();
	$list->addCombinedAlliance($row['all_id']);
}
if(isset($_REQUEST['corp'])) 
{
	$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id = ".intval($_REQUEST['corp']));
	$row = $qry->getRow();
	$list->addCombinedCorp($row['crp_id']);
}
if(isset($_REQUEST['pilot']))
{
	$qry->execute("SELECT plt_id FROM kb3_pilots WHERE plt_externalid = ".intval($_REQUEST['pilot'])." LIMIT 1");
	$row = $qry->getRow();
	$list->addCombinedPilot($row['plt_id']);
}
if(isset($_REQUEST['lastID'])) $list->setMinExtID(intval($_REQUEST['lastID']));

//$list->addCombinedPilot(2916);
$date = gmdate('Y-m-d H:i:s');
$text = "<?xml version='1.0' encoding='UTF-8'?>
<eveapi version='2'>
  <currentTime>".$date."</currentTime>
  <result>
    <rowset name='kills' key='killID' columns='killID,solarSystemID,killTime,moonID'>
";
$xml = "<?xml version='1.0' encoding='UTF-8'?>
<eveapi version='2'>
</eveapi>";
$sxe = new SimpleXMLElement($xml);
$sxe->addChild('currentTime', $date);
$result = $sxe->addChild('result');
$kills = $result->addChild('rowset');
$kills->addAttribute('name', 'kills');
$kills->addAttribute('key', 'killID');
$kills->addAttribute('columns', 'killID,solarSystemID,killTime,moonID');
//$list->getAllKills();
$count = 0;
while($kill1 = $list->getKill())
{//$kill = new Kill();
	$count++;
	if($kill1->isClassified()) break;
	$kill = new Kill($kill1->getID());
	$kill->setDetailedInvolved();
	$row = $kills->addChild('row');
	$row->addAttribute('killID', $kill->getExternalID());
	$row->addAttribute('solarSystemID', $kill->getSystem()->getExternalID());
	$row->addAttribute('killTime', $kill->getTimeStamp());
	$row->addAttribute('moonID', '0');
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
	$victimrow->addAttribute('damageTaken', $kill->VictimDamageTaken);
	$victimrow->addAttribute('shipTypeID', $kill->getVictimShip()->getExternalID());
	$involved = $row->addChild('rowset');
	$involved->addAttribute('name', 'attackers');
	$involved->addAttribute('columns', 'characterID,characterName,corporationID,corporationName,allianceID,allianceName,factionID,factionName,securityStatus,damageDone,finalBlow,weaponTypeID,shipTypeID');
	foreach ($kill->involvedparties_ as $inv)
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
			$invrow->addAttribute('factionID', $invAlliance->getName());
			$invrow->addAttribute('factionName', $invAlliance->getFactionID());
		}
		else
		{
			$invrow->addAttribute('allianceID', $invAlliance->getExternalID());
			$invrow->addAttribute('allianceName', $invAlliance->getName());
			$invrow->addAttribute('factionID', 0);
			$invrow->addAttribute('factionName', '');
		}
		$invrow->addAttribute('securityStatus', $inv->getSecStatus());
		$invrow->addAttribute('damageDone', $inv->dmgdone_);
		if($invPilot == $kill->getFBPilotID()) $final = 1;
		else $final = 0;
		$invrow->addAttribute('finalBlow', $final);
		$invrow->addAttribute('weaponTypeID', $inv->getWeapon()->getID());
		$invrow->addAttribute('shipTypeID', $inv->getShip()->externalid_);
	}
}
$sxe->addChild('cachedUntil', $date);

echo $sxe->asXML();
die;





?>