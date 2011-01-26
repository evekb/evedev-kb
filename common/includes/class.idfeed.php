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
 *
 * 0.90 almost final - kills are returned in descending order which confuses
 * 'last kill returned' responses.
 * 0.91 final release version for 3.0 boards.
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
	const version = "1.03";

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
		// Remove error messages at the top.
		if(strpos($this->xml,"<?xml") !== 0) $this->xml = substr($this->xml, strpos($this->xml,"<?xml"));

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
		if(!$sxe['edkapi'] || $sxe['edkapi'] < 0.91) return false;
		$this->time = $sxe->currentTime;
		$this->cachedTime = $sxe->cachedUntil;
		if(!is_null($sxe->result)) foreach($sxe->result->rowset->row as $row) $this->processKill($row);
		return count($this->posted) + count($this->skipped);
	}
	function setID($type = '', $id = 0)
	{
		//Check id is int.
		$id = intval($id);

		//Set to board owner.
		if($type == '')
		{
			if(config::get('cfg_allianceid'))
			{
				$alls = array();
				foreach(config::get('cfg_allianceid') as $val)
				{
					$all = new Alliance($val);
					if(!$all->getExternalID()) return false;
					$alls[] = $all->getExternalID();
				}
				$this->options['alliance'] = implode(',', $alls);
			}
			if(config::get('cfg_corpid'))
			{
				$crps = array();
				foreach(config::get('cfg_corpid') as $val)
				{
					$crp = new Corporation($val);
					if(!$crp->getExternalID()) return false;
					$crps[] = $crp->getExternalID();
				}
				$this->options['corp'] = implode(',', $crps);
			}
			if(config::get('cfg_pilotid'))
			{
				$pilots = array();
				foreach(config::get('cfg_pilotid') as $val)
				{
					$pilot = new Pilot($val);
					if(!$pilot->getExternalID()) return false;
					$pilots[] = $pilot->getExternalID();
				}
				$this->options['pilot'] = implode(',', $pilots);
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
			return $this->setID();
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
			if(intval($row['trust']) >= $this->trust && intval($row['killID'])) $kill->setExternalID(intval($row['killID']));
			if($row['hash']) $kill->setHash(decbin(hexdec(strval($row['hash']))));
			if($row['trust']) $kill->setTrust(intval($row['trust']));

			$kill->setTimeStamp(strval($row['killTime']));

			$qry->execute("SELECT sys_id FROM kb3_systems WHERE sys_eve_id = '".intval($row['solarSystemID'])."'");
			if(!$qry->recordCount()) return false;
			$qrow = $qry->getRow();
			$sys = new SolarSystem($qrow['sys_id']);
			$kill->setSolarSystem($sys);

			$this->processVictim($row->victim, $kill, strval($row['killTime']));

			foreach($row->rowset[0]->row as $inv) $this->processInvolved($inv, $kill, strval($row['killTime']));
			if(isset($row->rowset[1]->row[0])) foreach($row->rowset[1]->row as $item) $this->processItem($item, $kill);
			$id = $kill->add();

			$internalID = intval($row['killInternalID']);
			if($id > 0)
			{
				$this->posted[] = $id;
				logger::logKill($id, "ID:".$this->url);
			}
			//TODO should these be reversed?
			else if($internalID) $this->skipped[$internalID] = $kill->getDupe();
			else  $this->skipped[intval($row['killID'])] = $kill->getDupe();
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
		if(intval($victim['allianceID']))
			$alliance->add(strval($victim['allianceName']), intval($victim['allianceID']));
		else if(intval($victim['factionID']))
			$alliance->add(strval($victim['factionName']), intval($victim['factionID']));
		else
			$alliance->add("None");
		$corp->add(strval($victim['corporationName']), $alliance, $time, intval($victim['corporationID']));

		$pilot = new Pilot();
		$pilot->add(strval($victim['characterName']), $corp, $time, intval($victim['characterID']));
		$ship = new Ship(0, intval($victim['shipTypeID']));

		$kill->setVictim($pilot);
		$kill->setVictimID($pilot->getID());
		$kill->setVictimCorpID($corp->getID());
		$kill->setVictimAllianceID($alliance->getID());
		$kill->setVictimShip($ship);
		$kill->set('dmgtaken', intval($victim['damageTaken']));
	}
	private function processInvolved($inv, &$kill, $time)
	{
		$ship = new Ship(0, intval($inv['shipTypeID']));
		$weapon = new Item(intval($inv['weaponTypeID']));

		$alliance = new Alliance();
		$corp = new Corporation();
		if(intval($inv['allianceID']))
			$alliance->add(strval($inv['allianceName']), intval($inv['allianceID']));
		else if(intval($inv['factionID']))
			$alliance->add(strval($inv['factionName']), intval($inv['factionID']));
		else
			$alliance->add("None");
		$corp->add(strval($inv['corporationName']), $alliance, $time, intval($inv['corporationID']));

		$pilot = new Pilot();
		// Allow for blank names for consistency with CCP API.
		if(preg_match("/^(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/", $inv['characterName']))
		{
			$inv['characterName'] = $inv['corporationName'].' - '.$inv['characterName'];
		}
		else if($inv['characterName'] == ""
			&&(preg_match("/^(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/", $weapon->getName())))
		{
			$inv['characterName'] = $inv['corporationName'].' - '.$weapon->getName();
		}
		$pilot->add(strval($inv['characterName']), $corp, $time, intval($inv['characterID']));

		$iparty = new InvolvedParty($pilot->getID(), $corp->getID(),
			$alliance->getID(), floatval($inv['securityStatus']), $ship, $weapon, intval($inv['damageDone']));

		$kill->addInvolvedParty($iparty);
		if($inv['finalBlow'] == 1) $kill->setFBPilotID($pilot->getID());
	}
	private function processItem($item, &$kill)
	{
		if(intval($item['flag']) == 5) $location = 4;
		else if(intval($item['flag']) == 87) $location = 6;
		else
		{
			$litem = new Item(intval($item['typeID']));
			$location = $litem->getSlot();
		}

		if(intval($item['qtyDropped']))
		{
			$kill->addDroppedItem(new DroppedItem(new Item(intval($item['typeID'])), intval($item['qtyDropped']), '', $location));
		}
		else
		{
			$kill->addDestroyedItem(new DestroyedItem(new Item(intval($item['typeID'])), intval($item['qtyDestroyed']), '', $location));
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
		if(intval($row['killID']) > 0)
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
			$qry->execute("SELECT kll_id FROM kb3_mails WHERE kll_hash = 0x".$qry->escape(strval($row['hash'])));
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
