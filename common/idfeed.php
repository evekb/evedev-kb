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
 * lastID = return all kills from lastID on (ordered by external id)
 * lastintID = return all kills from lastintID internal id on (ordered by internal id)
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
 * kll_id = show one kill only.
 * kll_ext_id = show one kill only.
 *
 */

$starttime = microtime(true);
$idfeedversion = "1.00";

$maxkillsreturned = 200;

$xml = "<?xml version='1.0' encoding='UTF-8'?>
<eveapi version='2' edkapi='".$idfeedversion."'>
</eveapi>";

$list = new KillList();
if(isset($_GET['kll_id']))
{
	$_GET['lastintID'] = $_GET['kll_id'];
	$_GET['allkills'] = 1;
	$_GET['range'] = 0;
}
if(isset($_GET['kll_ext_id']))
{
	$_GET['lastID'] = $_GET['kll_ext_id'];
	$_GET['allkills'] = 1;
	$_GET['range'] = 0;
}
if(!isset($_GET['allkills'])) $list->setAPIKill();
$list->setLimit($maxkillsreturned);
$list->setOrdered(true);
if(!isset($_GET['allkills'])) $list->setOrderBy(' kll.kll_external_id ASC ');
else $list->setOrderBy(' kll.kll_id ASC ');

$qry = DBFactory::getDBQuery();

if(isset($_GET['alliance']))
{
	$qry->execute("SELECT all_id FROM kb3_alliances WHERE all_external_id IN (".$qry->escape($_GET['alliance']).")");
	if(!$qry->recordCount()) die($xml);
	while($row = $qry->getRow()) $list->addCombinedAlliance($row['all_id']);
}
if(isset($_GET['corp']))
{
	$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id IN (".$qry->escape($_GET['corp']).")");
	if(!$qry->recordCount()) die($xml);
	while($row = $qry->getRow()) $list->addCombinedCorp($row['crp_id']);
}
if(isset($_GET['pilot']))
{
	$qry->execute("SELECT plt_id FROM kb3_pilots WHERE plt_externalid IN (".$qry->escape($_GET['pilot']).")");
	if(!$qry->recordCount()) die($xml);
	while($row = $qry->getRow()) $list->addCombinedPilot($row['plt_id']);
}
if(isset($_GET['alliancename']))
{
	$_GET['alliancename'] = '"'.str_replace(',', '","', $_GET['alliancename']).'"';
	$qry->execute("SELECT all_id FROM kb3_alliances WHERE all_name IN (".$qry->escape(urldecode($_GET['alliancename'])).")");
	if(!$qry->recordCount()) die($xml);
	while($row = $qry->getRow()) $list->addCombinedAlliance($row['all_id']);
}
if(isset($_GET['corpname']))
{
	$_GET['corpname'] = '"'.str_replace(',', '","', $_GET['corpname']).'"';
	$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_name IN (".$qry->escape(urldecode($_GET['corpname'])).")");
	if(!$qry->recordCount()) die($xml);
	while($row = $qry->getRow()) $list->addCombinedCorp($row['crp_id']);
}
if(isset($_GET['pilotname']))
{
	$_GET['corpname'] = '"'.str_replace(',', '","', $_GET['corpname']).'"';
	$qry->execute("SELECT plt_id FROM kb3_pilots WHERE plt_name IN (".$qry->escape(urldecode($_GET['pilotname'])).")");
	if(!$qry->recordCount()) die($xml);
	while($row = $qry->getRow()) $list->addCombinedPilot($row['plt_id']);
}

if(isset($_GET['system']))
{
	$qry->execute("SELECT sys_id FROM kb3_systems WHERE sys_eve_id = ".intval($_GET['system'])." LIMIT 1");
	if(!$qry->recordCount()) die($xml);
	$row = $qry->getRow();
	$list->addSystem($row['sys_id']);
}
else if(isset($_GET['region']))
{
	$qry->execute("SELECT reg_id FROM kb3_regions WHERE reg_id = ".intval($_GET['region'])." LIMIT 1");
	if(!$qry->recordCount()) die($xml);
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
$sxe = new SimpleXMLElement($xml);
$sxe->addChild('currentTime', $date);
$result = $sxe->addChild('result');
$kills = $result->addChild('rowset');
$kills->addAttribute('name', 'kills');
$kills->addAttribute('key', 'killID');
$kills->addAttribute('columns', 'killID,solarSystemID,killTime,moonID,hash,trust');

$count = 0;
$timing = '';
while($kill = $list->getKill())
{
	if(config::get('km_cache_enabled') && CacheHandler::exists($kill->getID().".xml", 'mails'))
	{
		$cachedRow = new SimpleXMLElement(CacheHandler::get($kill->getID().".xml", 'mails'));
		AddXMLElement($kills, $cachedRow);
		continue;
	}

	$count++;
	if($kill->isClassified()) continue;
	$row = $kills->addChild('row');
	$row->addAttribute('killID', intval($kill->getExternalID()));
	$row->addAttribute('killInternalID', intval($kill->getID()));
	$row->addAttribute('solarSystemID', $kill->getSystem()->getExternalID());
	$row->addAttribute('killTime', $kill->getTimeStamp());
	$row->addAttribute('moonID', '0');
	$row->addAttribute('hash', bin2hex($kill->getHash()));
	$row->addAttribute('trust', $kill->getTrust());
	$victim = objectCache::fetchPilot($kill->getVictimID());
	$victimCorp = objectCache::fetchCorp($kill->getVictimCorpID());
	$victimAlliance = objectCache::fetchAlliance($kill->getVictimAllianceID());
	$victimrow = $row->addChild('victim');
	$victimrow->addAttribute('characterID', $victim->getExternalID());
	$victimrow->addAttribute('characterName', $victim->getName());
	$victimrow->addAttribute('corporationID', $victimCorp->getExternalID());
	$victimrow->addAttribute('corporationName', $victimCorp->getName());
	if($victimAlliance->isFaction())
	{
		$victimrow->addAttribute('allianceID', 0);
		$victimrow->addAttribute('allianceName', '');
		$victimrow->addAttribute('factionID', $victimAlliance->getFactionID());
		$victimrow->addAttribute('factionName', $victimAlliance->getName());
	}
	else
	{
		$victimrow->addAttribute('allianceID', $victimAlliance->getExternalID());
		$victimrow->addAttribute('allianceName', $victimAlliance->getName());
		$victimrow->addAttribute('factionID', 0);
		$victimrow->addAttribute('factionName', '');
	}
	$victimrow->addAttribute('damageTaken', $kill->getDamageTaken());
	$victimrow->addAttribute('shipTypeID', $kill->getVictimShipExternalID());
	$involved = $row->addChild('rowset');
	$involved->addAttribute('name', 'attackers');
	$involved->addAttribute('columns', 'characterID,characterName,corporationID,corporationName,allianceID,allianceName,factionID,factionName,securityStatus,damageDone,finalBlow,weaponTypeID,shipTypeID');

	$sql = "SELECT ind_sec_status, ind_all_id, ind_crp_id,
		ind_shp_id, ind_wep_id, ind_order, ind_dmgdone, plt_id, plt_name,
		plt_externalid, crp_name, crp_external_id,
		shp_externalid FROM kb3_inv_detail
		JOIN kb3_pilots ON (plt_id = ind_plt_id) 
		JOIN kb3_corps ON (crp_id = ind_crp_id) 
		JOIN kb3_ships ON (shp_id = ind_shp_id)
		WHERE ind_kll_id = ".$kill->getID();
	$qry->execute($sql);

	while ($inv = $qry->getRow())
	{
		$invrow = $involved->addChild('row');
		$invrow->addAttribute('characterID', $inv['plt_externalid']);
		$invrow->addAttribute('characterName', $inv['plt_name']);
		$invrow->addAttribute('corporationID', $inv['crp_external_id']);
		$invrow->addAttribute('corporationName', $inv['crp_name']);
		$invAlliance = objectCache::fetchAlliance($inv['ind_all_id']);
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
		$invrow->addAttribute('securityStatus', number_format($inv['ind_sec_status'],1));
		$invrow->addAttribute('damageDone', $inv['ind_dmgdone']);
		if($inv['plt_id'] == $kill->getFBPilotID()) $final = 1;
		else $final = 0;
		$invrow->addAttribute('finalBlow', $final);
		$invrow->addAttribute('weaponTypeID', $inv['ind_wep_id']);
		$invrow->addAttribute('shipTypeID', $inv['shp_externalid']);
	}
	$sql = "SELECT * FROM kb3_items_destroyed WHERE itd_kll_id = ".$kill->getID();
	$qry->execute($sql);
	$qry2 = DBFactory::getDBQuery();
	$sql = "SELECT * FROM kb3_items_dropped WHERE itd_kll_id = ".$kill->getID();
	$qry2->execute($sql);


	$droppedItems = $kill->getDroppedItems();
	$destroyedItems = $kill->getDestroyedItems();
	if($qry->recordCount()||$qry2->recordCount() )
	{
		$items = $row->addChild('rowset');
		$items->addAttribute('name', 'items');
		$items->addAttribute('columns', 'typeID,flag,qtyDropped,qtyDestroyed');

		while($iRow = $qry->getRow())
		{
			$itemRow = $items->addChild('row');
			$itemRow->addAttribute('typeID', $iRow['itd_itm_id']);
			if ($iRow['itd_itl_id'] == 4) // cargo
				$itemRow->addAttribute('flag', 5);
			else if ($iRow['itd_itl_id'] == 6) // drone
				$itemRow->addAttribute('flag', 87);
			else
				$itemRow->addAttribute('flag', 0);
			$itemRow->addAttribute('qtyDropped', 0);
			$itemRow->addAttribute('qtyDestroyed', $iRow['itd_quantity']);
		}


		while($iRow = $qry2->getRow())
		{
			$itemRow = $items->addChild('row');
			$itemRow->addAttribute('typeID', $iRow['itd_itm_id']);
			if ($iRow['itd_itl_id'] == 4) // cargo
				$itemRow->addAttribute('flag', 5);
			else if ($iRow['itd_itl_id'] == 6) // drone
				$itemRow->addAttribute('flag', 87);
			else
				$itemRow->addAttribute('flag', 0);
			$itemRow->addAttribute('qtyDropped', $iRow['itd_quantity']);
			$itemRow->addAttribute('qtyDestroyed', 0);
		}
	}
	if(config::get('km_cache_enabled')) CacheHandler::put($kill->getID().".xml", $row->asXML(), 'mails');
	$timing .= $kill->getID().": ".(microtime(true)-$starttime)."<br />";
}
$sxe->addChild('cachedUntil', $date);

header("Content-Type: text/xml");
echo $sxe->asXML();
//echo "<!-- ".$timing."\n -->";
//echo "<!-- Finished: ".(microtime(true)-$starttime)." -->";

class objectCache
{
	private static $pilots = array();
	private static $corps = array();
	private static $alliances = array();
	private static $ships = array();
	private static $items = array();
	//! Return Alliance from cached list or look up a new id.

	//! \param $id Alliance ID to look up.
	//! \return Alliance object matching input id.
	public static function fetchAlliance($id)
	{
		if(isset(self::$alliances[$id]))
			$alliance = &self::$alliances[$id];
		else
		{
			$alliance = new Alliance($id);
			self::$alliances[$id] = &$alliance;
		}
		return $alliance;
	}
	//! Return Corporation from cached list or look up a new id.

	//! \param $id Corporation ID.
	//! \return Corporation object matching input id.
	public static function fetchCorp($id)
	{
		if(isset(self::$corps[$id]))
		{
			$corp = self::$corps[$id];
		}
		else
		{
			$corp = new Corporation($id);
			self::$corps[$id] = $corp;
		}
		return $corp;
	}
	//! Return Pilot from cached list or look up a new id.

	//! \param $id Pilot ID to look up.
	//! \return Pilot object matching input id.
	public static function fetchPilot($id)
	{
		if(isset(self::$pilots[$id]))
		{
			$pilot = self::$pilots[$id];
		}
		else
		{
			$pilot = new Pilot($id);
			self::$pilots[$id] = $pilot;
		}
		return $pilot;
	}
	//! Return ship from cached list or look up a new id.

	//! \param $id Ship id to look up.
	//! \return Ship object matching input id.
	public static function fetchShip($id)
	{
		if(isset(self::$ships[$id]))
			$ship = self::$ships[$id];
		else
		{
			$ship = new Ship($id);
			self::$ships[$id] = $ship;
		}
		return $ship;
	}
	//! Return item from cached list or look up a new name.

	//! \param $itemname Item name to look up.
	//! \return Item object matching input name.
	public static function fetchItem($id)
	{
		if(isset(self::$items[$id]))
			$item = self::$items[$id];
		else
		{
			$item = new Item($id);
			self::$items[$id] = $item;
		}
		return $item;
	}

}

function AddXMLElement($dest, $source)
{
	$new_dest = $dest->addChild($source->getName(), $source[0]);

	foreach ($source->attributes() as $name => $value)
	{
		$new_dest->addAttribute($name, $value);
	}

	foreach ($source->children() as $child)
	{
		AddXMLElement($new_dest, $child);
	}
}
