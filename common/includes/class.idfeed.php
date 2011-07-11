<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * EDK IDFeed Syndication reader class.
 * This class is used to fetch and read the feed from another EDK board. It
 *  adds all fetched kills to the board and returns the id of the highest kill
 *  fetched.
 *
 * 0.90 almost final - kills are returned in descending order which confuses
 * 'last kill returned' responses.
 * 0.91 final release version for 3.0 boards.
 *
 * 1.0.4 Involved party structures keep their name
 *		Kills are logged with source board's id.
 * 1.0.7 Better CCP API handling
 * @package EDK
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
	private $errorcode = 0;
	const version = "1.07";

	/**
	 * Construct the Fetcher class and initialise variables.
	 */
	function IDFeed()
	{
	}
	/**
	 * Fetch a new feed.
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
		if($http->get_http_code() != 200)
		{
			trigger_error("HTTP error ".$http->get_http_code(). " while fetching file.", E_USER_WARNING);
			return false;
		}
		unset($http);
		if($this->xml) return true;
		else return false;
	}
	/**
	 * Fetch a new feed and parse it.
	 * Use the input parameters to fetch a feed.
	 * @param string $url The base URL of the feed to fetch
	 */
	public function read($url = '')
	{
		$this->url = $url;
		if($this->xml) ;
		else if(substr($url,0,4) != "http")
		{
			if(!$this->xml = file_get_contents($url))
			{
				trigger_error("Error reading file.", E_USER_WARNING);
				return false;
			}
		}
		else
		{
			if(!$this->fetch())
			{
				trigger_error("Error reading feed.", E_USER_WARNING);
				return false;
			}
		}
		if (strpos($this->xml, '<eveapi') === false) {
				trigger_error("Not a valid EVEAPI feed.", E_USER_WARNING);
				return false;
		}
		return $this->processFeed();
	}
	function setXML($xml)
	{
		$this->xml = $xml;
		return;
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
	/**
	 * Set the lowest kill ID you want returned.
	 *
	 * @param integer $id The minimum kill ID
	 * @param boolean $internal Set true to use internal kill IDs instead of CCP IDs.
	 *
	 * @return mixed False on error, True on success.
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
	/**
	 * Set a starting date in unix timestamp format.
	 */
	function setStartDate($date = 0)
	{
		if(!$date = intval($date)) return false;
		$this->options['startdate'] = $date;
		return true;
	}
	/**
	 * Set an ending date in unix timestamp format.
	 */
	function setEndDate($date = 0)
	{
		if(!$date = intval($date)) return false;
		$this->options['enddate'] = $date;
		return true;
	}
	/**
	 * Set the system to filter by.
	 *
	 * @param integer $systemID Eve system ID.
	 *
	 * @return mixed False on error, True on success.
	 */
	function setSystem($systemID = 0)
	{
		if(!$systemID = intval($systemID)) return false;
		$this->options['system'] = $systemID;
		return true;
	}
	/**
	 * Set the region to filter by.
	 *
	 * @param integer $regionID Eve region ID.
	 *
	 * @return mixed False on error, True on success.
	 */
	function setRegion($regionID = 0)
	{
		if(!$regionID = intval($regionID)) return false;
		$this->options['region'] = $regionID;
		return true;
	}
	/**
	 * Set true to include kills with no external ID;
	 */
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
	function getErrorCode()
	{
		return $this->errorcode;
	}
	function getErrorMessage()
	{
		return $this->errormsg;
	}
	function processFeed()
	{
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
		if(floatval($sxe['edkapi']) && $sxe['edkapi'] < 0.91) return false;
		$this->time = $sxe->currentTime;
		$this->cachedTime = $sxe->cachedUntil;
		if(isset($sxe->error))
		{
			$this->errorcode = intval($sxe->error['code']);
			$this->errormsg = strval($sxe->error);
			return 0;
		}
		if(!is_null($sxe->result->row)) foreach($sxe->result->rowset->row as $row) $this->processKill($row);
		return count($this->posted) + count($this->skipped);
	}
	private function processKill($row)
	{
		$internalID = intval($row['killInternalID']);
		$externalID = intval($row['killID']);
		if(!$id = $this->killExists($row))
		{
			$qry = DBFactory::getDBQuery();

			$kill = new Kill();
			if(intval($row['trust']) >= $this->trust && $externalID) $kill->setExternalID($externalID);
			//Don't trust foreign hashes
			//if(strval($row['hash'])) $kill->setHash((strval($row['hash'])));
			if(intval($row['trust'])) $kill->setTrust(intval($row['trust']));

			$kill->setTimeStamp(strval($row['killTime']));

			$qry->execute("SELECT sys_id FROM kb3_systems WHERE sys_eve_id = '".intval($row['solarSystemID'])."'");
			if(!$qry->recordCount()) return false;
			$qrow = $qry->getRow();
			$sys = new SolarSystem($qrow['sys_id']);
			$kill->setSolarSystem($sys);

			if(!$this->processVictim($row, $kill, strval($row['killTime'])))
			{
				$this->skipped[] = array($externalID, $internalID, 0);
				if($this->lastReturned < $externalID) $this->lastReturned = $externalID;
				if($this->lastInternalReturned < $internalID) $this->lastInternalReturned = $internalID;

				return;
			}

			foreach($row->rowset[0]->row as $inv) $this->processInvolved($inv, $kill, strval($row['killTime']));
			if(isset($row->rowset[1]->row[0])) foreach($row->rowset[1]->row as $item) $this->processItem($item, $kill);
			$id = $kill->add();

			if($id > 0)
			{
				$this->posted[] = array($kill->getExternalID(), $internalID, $id);
				$logaddress = "ID:".$this->url;
				if(strpos($logaddress, "?")) $logaddress = substr($logaddress, 0, strpos($logaddress, "?"));
				if($kill->getExternalID()) $logaddress .= "?a=kill_detail&kll_ext_id=".$kill->getExternalID();
				else if($internalID) $logaddress .= "?a=kill_detail&kll_id=".$internalID;
				logger::logKill($id, $logaddress);
			}
			else $this->skipped[] = array(intval($row['killID']), $internalID, $kill->getDupe());
		}
		else $this->skipped[] = array($externalID, $internalID, $id);

		if($this->lastReturned < $externalID) $this->lastReturned = $externalID;
		if($this->lastInternalReturned < $internalID) $this->lastInternalReturned = $internalID;

	}
	private function processVictim($row, &$kill, $time)
	{
		// If we have a character ID but no name then we give up - the needed info is gone.
		// If we have no character ID and no name then it's a structure or NPC
		//	- if we have a moonID (anchored at a moon) call it corpname - moonname
		//	- if we don't have a moonID call it corpname - systemname
		if(!strval($victim['characterName'])
			&& intval($victim['characterID']))
				return false;

		$victim = $row->victim;
		$alliance = new Alliance();
		$corp = new Corporation();
		if(intval($victim['allianceID']))
			$alliance->add(strval($victim['allianceName']), intval($victim['allianceID']));
		else if(intval($victim['factionID']))
			$alliance->add(strval($victim['factionName']), intval($victim['factionID']));
		else
			$alliance->add("None");
		$corp->add(strval($victim['corporationName']), $alliance, $time, intval($victim['corporationID']));

		if(!strval($victim['characterName']))
		{
			if(intval($row['moonID']))
			{
				$name = API_Helpers::getMoonName(intval($row['moonID']));
				if(!$name)
				{
					$idtoname = new API_IDtoName();
					$idtoname->setIDs(intval($row['moonID']));

					if($idtoname->fetchXML()) return false;

					$namedata = $idtoname->getIDData();

					$name = $namedata[0]['name'];
				}
				$name = strval($victim['corporationName'])." - ".$name;
			}
			else $name = strval($victim['corporationName'])." - ".$kill->getSystem()->getName();
		}
		else if(!intval($victim['shipTypeID'])) return false;
		else $name = strval($victim['characterName']);

		$pilot = new Pilot();
		$pilot->add($name, $corp, $time, intval($victim['characterID']));
		$ship = new Ship(0, intval($victim['shipTypeID']));

		$kill->setVictim($pilot);
		$kill->setVictimID($pilot->getID());
		$kill->setVictimCorpID($corp->getID());
		$kill->setVictimAllianceID($alliance->getID());
		$kill->setVictimShip($ship);
		$kill->set('dmgtaken', intval($victim['damageTaken']));
		return true;
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
		$charname = strval($inv['characterName']);
		// Allow for blank names for consistency with CCP API.
		if(preg_match("/^(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/", $charname))
		{
			$charname = $inv['corporationName'].' - '.$charname;
		}
		else if($charname == ""
			&&(preg_match("/^(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/", $weapon->getName())))
		{
			$charname = $inv['corporationName'].' - '.$weapon->getName();
		}
		else if($charname == "") $charname = $ship->getName();

		$pilot->add(strval($inv['characterName']), $corp, $time, intval($inv['characterID']));

		$iparty = new InvolvedParty($pilot->getID(), $corp->getID(),
			$alliance->getID(), floatval($inv['securityStatus']), $ship, $weapon, intval($inv['damageDone']));

		$kill->addInvolvedParty($iparty);
		if(intval($inv['finalBlow']) == 1) $kill->setFBPilotID($pilot->getID());
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
		// Check for containers.
		if(isset($item->rowset))
		{
			foreach($item->rowset->row as $subitem) $this->processItem($subitem, $kill);
		}
	}
	/**
	 * Return the array of posted kill IDs.
	 */
	function getPosted()
	{
		return $this->posted;
	}
	/**
	 * Return an array of skipped kill IDs
	 *
	 * @return array
	 */
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
	/**
	 * Returns the id of a matching existing kill if found.
	 * This does not guarantee non-existence as it only checks external id and
	 * hash
	 *
	 * @param SimpleXML $row A SimpleXML object containing the kill.
	 *
	 * @return integer 0 if no match found, the kll_id if found.
	 */
	private function killExists(&$row)
	{
		$qry = DBFactory::getDBQuery(true);
		if(strlen($row['hash']) > 1)
		{
			$qry->execute("SELECT kll_id FROM kb3_mails WHERE kll_hash = 0x".$qry->escape(strval($row['hash'])));
			if($qry->recordCount())
			{
				$qrow = $qry->getRow();
				$id = $qrow['kll_id'];
				if(intval($row['trust']) >= $this->trust && intval($row['killID']))
				{
					$qry->execute("UPDATE kb3_kills JOIN kb3_mails ON kb3_kills.kll_id = ".
						"kb3_mails.kll_id SET kb3_mails.kll_external_id = ".
						intval($row['killID']).", kb3_kills.kll_external_id = ".
						intval($row['killID'])." WHERE kb3_mails.kll_id = $id AND ".
						"kb3_mails.kll_external_id IS NULL");
				}
				return $id;
			}
		}
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
		return 0;
	}
	public function errormsg()
	{
		return $this->errormsg;
	}
}
