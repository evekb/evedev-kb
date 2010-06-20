<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

//! EDK IDFeed Syndication reader class.

/*! This class is used to fetch and read the feed from another EDK board. It
 *  adds all fetched kills to the board and returns the id of the highest kill
 *  fetched.
 */
class IDFeed
{
	private $url = '';
	private $trust = 0;
	private $xml = '';
	private $options = array();
	private $lastReturned = 0;
	private $lastInternalReturned = 0;
	private $posted = array();
	private $skipped = array();
	private $time = '';
	private $cachedTime = '';
	private $errormsg = '';
	const version = "0.90";

	//! Construct the Fetcher class and initialise variables.

	//! \param $trackerKey If set record progress of a read in the config.
	function IDFeed()
	{
	}
	//! Fetch a new feed.

	/*! Use the input parameters to fetch a feed.
	 * \param $url The base URL of the feed to fetch
	 */
	private function fetch()
	{
		if(!$this->url) return false;

		$this->posted = array();
		$this->skipped = array();
		$this->lastReturned = 0;
		$this->time = '';
		$this->cachedTime = '';

		if(strpos($this->url, "?") === false) $options = "?";
		else $options = "&";
		$first = true;
		foreach($this->options as $key=>$val)
		{
			if($first) $first = false;
			else $options .= "&";
			$options .= $key."=".$val;
		}

		global $idfeedversion;

		$http = new http_request($this->url.$options);
		$http->set_useragent("EDK IDFeedfetcher ".self::version);
		$http->set_timeout(300);
		$this->xml = $http->get_content();
		unset($http);
		if($this->xml) return true;
		else return false;
	}
	//! Fetch a new feed and parse it.

	/*! Use the input parameters to fetch a feed.
	 * \param $url The base URL of the feed to fetch
	 */
	public function read($url = '')
	{
		$this->url = $url;
		if(!$this->fetch())
		{
			trigger_error("Error reading feed.", E_USER_NOTICE);
			return false;
		}

		libxml_use_internal_errors(true);
		$sxe = simplexml_load_string($this->xml);
		if(!$sxe)
		{
			$this->errormsg = "XML error:\n";
			foreach(libxml_get_errors() as $error)
			{
				$this->errormsg .= "\t".$error->message."\n";
			}
			return false;
		}
		if(!$sxe['edkapi'] || $sxe['edkapi'] < 0.90) return false;
		$this->time = $sxe->currentTime;
		$this->cachedTime = $sxe->cachedUntil;
		foreach($sxe->result->rowset->row as $row) $this->processKill($row);
		return count($this->posted) + count($this->skipped);
	}
	function setID($type = '', $id = 0)
	{
		//Check id is int.
		$id = intval($id);

		//Set to board owner.
		if($type == '')
		{
			if(ALLIANCE_ID)
			{
				$all = new Alliance(ALLIANCE_ID);
				if(!$all->getExternalID()) return false;
				$this->options['alliance'] = $all->getExternalID();
				return true;
			}
			elseif(CORP_ID)
			{
				$crp = new Corporation(CORP_ID);
				if(!$all->getExternalID()) return false;
				$this->options['corp'] = $crp->getExternalID();
				return true;
			}
			elseif(PILOT_ID)
			{
				$plt = new Pilot(PILOT_ID);
				if(!$plt->getExternalID()) return false;
				$this->options['pilot'] = $plt->getExternalID();
				return true;
			}
			return true;
		}
		else if ($id > 0)
		{
			if($type == 'alliance') $this->options['alliance'] = $id;
			elseif($type == 'corporation') $this->options['corp'] = $id;
			elseif($type == 'pilot') $this->options['pilot'] = $id;
			else return false;

			return true;
		}
		return false;
	}
	function setName($type = '', $name = '')
	{
		//Set to board owner.
		if($type == '')
		{
			if(ALLIANCE_ID)
			{
				$all = new Alliance(ALLIANCE_ID);
				if(!$all->getExternalID()) return false;
				$this->options['alliance'] = $all->getExternalID();
				return true;
			}
			elseif(CORP_ID)
			{
				$crp = new Corporation(CORP_ID);
				if(!$crp->getExternalID()) return false;
				$this->options['corp'] = $crp->getExternalID();
				return true;
			}
			elseif(PILOT_ID)
			{
				$plt = new Pilot(PILOT_ID);
				if(!$plt->getExternalID()) return false;
				$this->options['pilot'] = $plt->getExternalID();
				return true;
			}
			return true;
		}
		else
		{
			$name = urlencode($name);
			if($type == 'alliance') $this->options['alliancename'] = $name;
			elseif($type == 'corporation') $this->options['corpname'] = $name;
			elseif($type == 'pilot') $this->options['pilotname'] = $name;
			else return false;

			return true;
		}
		return false;
	}
	//! Set the lowest kill ID you want returned.

	/*!
	 * \param $id The minimum kill ID
	 * \param $internal Set true to use internal kill IDs instead of CCP IDs.
	 *
	 * \return False on error, True on success.
	 */
	function setStartKill($id = 0, $internal = false)
	{
		$id = intval($id);
		if(!$id) return false;
		if($internal) $this->options['lastintID'] = $id;
		else $this->options['lastID'] = $id;
		return true;
	}
	function setRange($range = 0)
	{
		$range = intval($range);
		if($range <= 0) return false;
		$this->options['range'] = $range;
		return true;
	}
	//! Set a starting date in unix timestamp format.
	function setStartDate($date = 0)
	{
		if(!$date = intval($date)) return false;
		$this->options['startdate'] = $date;
		return true;
	}
	//! Set an ending date in unix timestamp format.
	function setEndDate($date = 0)
	{
		if(!$date = intval($date)) return false;
		$this->options['enddate'] = $date;
		return true;
	}
	//! Set the system to filter by.

	/*!
	 * \param $systemID Eve system ID.
	 *
	 * \return False on error, True on success.
	 */
	function setSystem($systemID = 0)
	{
		if(!$systemID = intval($systemID)) return false;
		$this->options['system'] = $systemID;
		return true;
	}
	//! Set the region to filter by.

	/*!
	 * \param $regionID Eve region ID.
	 *
	 * \return False on error, True on success.
	 */
	function setRegion($regionID = 0)
	{
		if(!$regionID = intval($regionID)) return false;
		$this->options['region'] = $regionID;
		return true;
	}
	//! Set true to include kills with no external ID;
	function setAllKills($allkills = false)
	{
		if($allkills) $this->options['allkills'] = 1;
		else $this->options['allkills'] = 0;
		return $this->options['allkills'];
	}
	function setTrust($trust = 0)
	{
		$this->trust = intval($trust);
		return $this->trust;
	}
	function getLastReturned()
	{
		return $this->lastReturned;
	}
	function getLastInternalReturned()
	{
		return $this->lastInternalReturned;
	}
	function getXML()
	{
		return $this->xml;
	}
	private function processKill($row)
	{
		if(!$id = $this->killExists($row))
		{
			$qry = DBFactory::getDBQuery();

			$kill = new Kill();
			if($row['trust'] >= $this->trust && $row['killID']) $kill->setExternalID($row['killID']);
			if($row['hash']) $kill->setHash(decbin(hexdec($row['hash'])));
			if($row['trust']) $kill->setTrust($row['trust']);

			$kill->setTimeStamp($row['killTime']);

			$qry->execute("SELECT sys_id FROM kb3_systems WHERE sys_eve_id = '".intval($row['solarSystemID'])."'");
			if(!$qry->recordCount()) return false;
			$qrow = $qry->getRow();
			$sys = new SolarSystem($qrow['sys_id']);
			$kill->setSolarSystem($sys);

			$this->processVictim($row->victim, $kill, $row['killTime']);

			foreach($row->rowset[0]->row as $inv) $this->processInvolved($inv, $kill, $row['killTime']);
			if(isset($row->rowset[1]->row[0])) foreach($row->rowset[1]->row as $item) $this->processItem($item, $kill);
			$id = $kill->add();

			$internalID = intval($row['killInternalID']);
			if($id > 0) $this->posted[] = $id;
			//TODO should these be reversed?
			else if($internalID) $this->skipped[intval($internalID)] = $kill->getDupe();
			else $this->skipped[$row['killID']] = $kill->getDupe();
		}
		else
		{
			$internalID = intval($row['killInternalID']);
			//TODO should these be reversed?
			if($internalID) $this->skipped[$internalID] = $id;
			else $this->skipped[intval($row['killID'])] = $id;
		}
		
		if($this->lastReturned < $row['killID']) $this->lastReturned = $row['killID'];
		if($this->lastInternalReturned < $internalID) $this->lastInternalReturned = $internalID;
		
	}
	private function processVictim($victim, &$kill, $time)
	{
		$alliance = new Alliance();
		$corp = new Corporation();
		if($victim['allianceID'])
			$alliance->add($victim['allianceName'], $victim['allianceID']);
		else if($victim['factionID'])
			$alliance->add($victim['factionName'], $victim['factionID']);
		else
			$alliance->add("None");
		$corp->add($victim['corporationName'], $alliance, $time, $victim['corporationID']);

		$pilot = new Pilot();
		$pilot->add($victim['characterName'], $corp, $time, $victim['characterID']);
		$ship = new Ship(0, $victim['shipTypeID']);

		$kill->setVictim($pilot);
		$kill->setVictimID($pilot->getID());
		$kill->setVictimCorpID($corp->getID());
		$kill->setVictimAllianceID($alliance->getID());
		$kill->setVictimShip($ship);
		$kill->set('dmgtaken', $victim['damageTaken']);
	}
	private function processInvolved($inv, &$kill, $time)
	{
		$alliance = new Alliance();
		$corp = new Corporation();
		if($inv['allianceID'])
			$alliance->add($inv['allianceName'], $inv['allianceID']);
		else if($inv['factionID'])
			$alliance->add($inv['factionName'], $inv['factionID']);
		else
			$alliance->add("None");
		$corp->add($inv['corporationName'], $alliance, $time, $inv['corporationID']);
		$pilot = new Pilot();
		$pilot->add($inv['characterName'], $corp, $time, $inv['characterID']);
		$ship = new Ship(0, $inv['shipTypeID']);
		$weapon = new Item($inv['weaponTypeID']);

		$iparty = new InvolvedParty($pilot->getID(), $corp->getID(),
			$alliance->getID(), $inv['securityStatus'], $ship, $weapon, $inv['damageDone']);

		$kill->addInvolvedParty($iparty);
		if($inv['finalBlow']) $kill->setFBPilotID($pilot->getID());
	}
	private function processItem($item, &$kill)
	{
		if($item['flag'] == 5) $location = 4;
		else if($item['flag'] == 87) $location = 6;
		else
		{
			$litem = new Item($item['typeID']);
			$location = $litem->getSlot();
		}

		if($item['qtyDropped'])
		{
			$kill->addDroppedItem(new DroppedItem($item['typeID'], $item['qtyDropped'], '', $location));
		}
		else
		{
			$kill->addDestroyedItem(new DestroyedItem($item['typeID'], $item['qtyDestroyed'], '', $location));
		}
	}
	//! Return the array of posted kill IDs.
	function getPosted()
	{
		return $this->posted;
	}
	//! Return an array of skipped kill IDs
	function getSkipped()
	{
		return $this->skipped;
	}
	function getTime()
	{
		return $this->time;
	}
	function getCachedTime()
	{
		return $this->cachedTime;
	}
	//! Returns the id of a matching existing kill if found.
	
	/*! This does not guarantee non-existence as it only checks external id and
	 * hash
	 * 
	 * \param $row A SimpleXML object containing the kill.
	 *
	 * \return 0 if no match found, the kll_id if found.
	 */
	private function killExists(&$row)
	{
		$qry = DBFactory::getDBQuery(true);
		if($row['killID'] > 0)
		{
			$qry->execute("SELECT kll_id FROM kb3_kills WHERE kll_external_id = ".intval($row['killID']));
			if($qry->recordCount())
			{
				$qrow = $qry->getRow();
				$id = $qrow['kll_id'];
				return $id;
			}
		}
		if(strlen($row['hash']) > 1)
		{
			$qry->execute("SELECT kll_id FROM kb3_mails WHERE kll_hash = 0x".$qry->escape($row['hash']));
			if($qry->recordCount())
			{
				$qrow = $qry->getRow();
				$id = $qrow['kll_id'];
				return $id;
			}
		}
		return 0;
	}
	public function errormsg()
	{
		return $this->errormsg;
	}
}
