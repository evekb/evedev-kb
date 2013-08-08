<?php
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
 * 		Kills are logged with source board's id.
 * 1.0.7 Better CCP API handling
 * 1.0.8 Handle NPC ships in API feeds.
 * 1.0.9 Add Implant location
 * 1.1 Fix Trust issues.
 * 1.2 Use CCP's slot numbering
 * 1.5 Support fetching from zKillboard.
 *		Depreciated Hash & trust are but they are left here for backwards board compatibility.
 *		Trust is now hard-coded to 3.
 *		Support gzip encoding.
 *		Version bumped to consolidate syndication v1.04 and fetcher v1.2.
 * 
 * @package EDK
 */
class IDFeed
{
	private $url = '';
	private $xml = '';
	private $options = array();
	private $lastReturned = 0;
	private $lastInternalReturned = 0;
	private $posted = array();
	private $skipped = array();
	private $duplicate = array();
	private $time = '';
	private $cachedTime = '';
	private $parsemsg = array();
	private $errormsg = '';
	private $errorcode = 0;
	private $npcOnly = true;
	private $lookupLocation = false;

	/**
	 * Fetch a new feed.
	 */
	private function fetch()
	{
		if (!$this->url) {
			return false;
		}

		$this->posted = array();
		$this->skipped = array();
		$this->duplicate = array();
		$this->lastReturned = 0;
		$this->time = '';
		$this->cachedTime = '';
		
		global $idfeedversion;

		$http = new http_request($this->getFullURL());
		$http->set_useragent("EDK IDFeedfetcher ".$idfeedversion);
		$http->set_timeout(300);
		
		//accept gzip encoding
		$http->set_header('Accept-Encoding: gzip');
		if (strpos($http->get_header(), 'Content-Encoding: gzip') !== false)
			$this->xml = gzdecode($http->get_content());
		else
			$this->xml = $http->get_content();
		
		if ($http->get_http_code() != 200) {
			trigger_error("HTTP error ".$http->get_http_code()." while fetching feed from ".$this->url.$options.".", E_USER_WARNING);
			return false;
		}

		if ($this->xml) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Fetch a new feed and parse it.
	 * Use the input parameters to fetch a feed.
	 *
	 * @param string $url The base URL of the feed to fetch
	 * @return boolean
	 */
	public function read($url = '')
	{
		$this->url = $url;
		if ($this->xml) {
			;
		} else if (substr($url, 0, 4) != "http") {
			if (!$this->xml = file_get_contents($url)) {
				trigger_error("Error reading file.", E_USER_WARNING);
				return false;
			}
		} else {
			if (!$this->fetch()) {
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

	/**
	 * Set the XML parsed by the idfeed.
	 * @param string $xml
	 * @return type
	 */
	function setXML($xml)
	{
		$this->xml = $xml;
	}

	/**
	 * Set the XML parsed by the idfeed.
	 * @param string $xml
	 * @return type
	 */
	function setLogName($name)
	{
		$this->name = preg_replace("/[^\w\d _-]/", "", (string)$name);
	}

	/**
	 * Set the type and ID of the feed to retrieve.
	 * @param string $type pilot/corp/alliance.
	 * @param integer $id
	 * @return boolean false on failure.
	 */
	function setID($type = '', $id = 0)
	{
		//Check id is int.
		$id = (int) $id;

		//Set to board owner.
		if ($type == '') {
			if (config::get('cfg_allianceid')) {
				$alls = array();
				foreach (config::get('cfg_allianceid') as $val) {
					$all = new Alliance($val);
					if (!$all->getExternalID()) return false;
					$alls[] = $all->getExternalID();
				}
				$this->options['alliance'] = implode(',', $alls);
			}
			if (config::get('cfg_corpid')) {
				$crps = array();
				foreach (config::get('cfg_corpid') as $val) {
					$crp = new Corporation($val);
					if (!$crp->getExternalID()) return false;
					$crps[] = $crp->getExternalID();
				}
				$this->options['corp'] = implode(',', $crps);
			}
			if (config::get('cfg_pilotid')) {
				$pilots = array();
				foreach (config::get('cfg_pilotid') as $val) {
					$pilot = new Pilot($val);
					if (!$pilot->getExternalID()) return false;
					$pilots[] = $pilot->getExternalID();
				}
				$this->options['pilot'] = implode(',', $pilots);
			}
			return true;
		}
		else if ($id > 0) {
			if ($type == 'alliance') {
				$this->options['alliance'] = $id;
			} else if ($type == 'corporation') {
				$this->options['corp'] = $id;
			} else if ($type == 'pilot') {
				$this->options['pilot'] = $id;
			} else {
				return false;
			}

			return true;
		}
		return false;
	}

	function setName($type = '', $name = '')
	{
		//Set to board owner.
		if ($type == '') {
			return $this->setID();
		} else {
			$name = urlencode($name);
			if ($type == 'alliance') {
				$this->options['alliancename'] = $name;
			} elseif ($type == 'corporation') {
				$this->options['corpname'] = $name;
			} elseif ($type == 'pilot') {
				$this->options['pilotname'] = $name;
			} else {
				return false;
			}

			return true;
		}
		return false;
	}

	/**
	 * Set the lowest kill ID you want returned.
	 *
	 * @param integer $id The minimum kill ID
	 * @param boolean $internal Set true to use internal kill IDs instead of
	 * CCP IDs.
	 *
	 * @return mixed False on error, True on success.
	 */
	function setStartKill($id = 0, $internal = false)
	{
		$id = (int)$id;
		if (!$id) {
			return false;
		}
		if ($internal) {
			$this->options['lastintID'] = $id;
		} else {
			$this->options['lastID'] = $id;
		}
		return true;
	}

	function setRange($range = 0)
	{
		$range = (int)$range;
		if ($range <= 0) {
			return false;
		}
		$this->options['range'] = $range;
		return true;
	}

	/**
	 * Set a starting date in unix timestamp format.
	 */
	function setStartDate($date = 0)
	{
		if (!$date = (int)$date) {
			return false;
		}
		$this->options['startdate'] = $date;
		return true;
	}

	/**
	 * Set an ending date in unix timestamp format.
	 *
	 * @param string $date YYYY-mm-dd hh:ss
	 * @return boolean True if set successfully. False if not.
	 */
	function setEndDate($date = 0)
	{
		if (!$date = (int)$date) {
			return false;
		}
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
		if (!$systemID = (int)$systemID) {
			return false;
		}
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
		if (!$regionID = (int)$regionID) {
			return false;
		}
		$this->options['region'] = $regionID;
		return true;
	}

	/**
	 * Set true to include kills with no external ID;
	 *
	 * @param boolean $allkills
	 * @return boolean
	 */
	function setAllKills($allkills = false)
	{
		if ($allkills) {
			$this->options['allkills'] = 1;
		} else {
			$this->options['allkills'] = 0;
		}
		return $this->options['allkills'];
	}

	/**
	 * Fetch the External ID of the last kill returned.
	 * @return int External ID of the last kill returned.
	 */
	function getLastReturned()
	{
		return $this->lastReturned;
	}

	/**
	 * Fetch the Internal ID of the last kill returned.
	 * @return int Internal ID of the last kill returned.
	 */
	function getLastInternalReturned()
	{
		return $this->lastInternalReturned;
	}

	/**
	 * Return the xml input for this idfeed
	 * @return string The xml input for this idfeed
	 */
	function getXML()
	{
		return $this->xml;
	}
	
	/**
	 * Builds a feed URL from the base url and all set options
	 *
	 * @return mixed The fully assembled url or false if url is not set
	 *
	 */	
	function getFullURL() {
		if (!$this->url) {
			return false;
		}

		$url = strtolower($this->url);
		if (strpos($url, 'zkillboard')) {
			$options = '';
			if (substr($url, -1) != '/')
				$options .= '/';
			if (strpos($url, 'api') === false)
				$options .= 'api/';
			if (strpos($url, 'xml') === false)
				$options .= 'xml/';
			if (strpos($url, 'orderdirection') === false)
				$options .= 'orderDirection/asc/';
			
			//zKill currently doesn't allow querying negative killmails (these are how zKill denotes non-api verified mails)
			//additionally, edk doesn't yet have code to handle negative killmails either
			//as a result we have to insist on api kills only
			if (strrpos($url, 'api-only') === false)
				$options .= 'api-only/';
			
			foreach ($this->options as $key => $val) {
				switch ($key) {
					case 'startdate':
						$key = 'startTime';
						$val = date('YmdHi', $val);
						break;
					case 'enddate':
						$key = 'endTime';
						$val = date('YmdHi', $val);
						break;
					case 'lastintID': //zKill doesn't have an internal kill
						$val -= 1; //cron_feed.php adds 1
					case 'lastID':
						$key = 'afterKillID';
						break;
					case 'allkills':
						//if in the future both zKill and edk support negative killmails, this can be uncommented
						//if ($val == 0)
						//	$options .= 'api-only/';
						continue 2;
						break;
					case 'pilot':
						$key = 'characterID';
						break;
					case 'corp':
						$key = 'corporationID';
						break;
					case 'alliance':
						$key = 'allianceID';
						break;
					case 'system':
						$key = 'solarSystemID';
						break;
					case 'region':
						$key = 'regionID';
						break;
					case 'limit':
						break;
					//case 'kll_ext_id': //untested
					//	$key = "limit/1/afterKillID";
					//	$val -= 1;
					//	break;
					default:
						continue 2; //this option not recognized by zKill, skip
				}
				$options .= "$key/$val/";
			}
		} else {
			if (strpos($this->url, "?") === false) {
				$options = "?";
			} else {
				$options = "&";
			}
			$first = true;
			foreach ($this->options as $key => $val) {
				if ($first) {
					$first = false;
				} else {
					$options .= "&";
				}
				$options .= $key."=".$val;
			}
		}
		
		return $this->url.$options;
	}

	/**
	 * Return the code for the last error encountered.
	 * @return int The code for the last error encountered.
	 */
	function getErrorCode()
	{
		return $this->errorcode;
	}

	/**
	 * Return the message for the last error encountered.
	 * @return string The message for the last error encountered.
	 */
	function getErrorMessage()
	{
		return $this->errormsg;
	}

	/**
	 * Return any messages generated by parsing the xml.
	 * @return string Text for any messages generated by parsing the xml.
	 */
	function getParseMessages()
	{
		return $this->parsemsg;
	}

	/**
	 * Process the set feed.
	 * @return int The count of kills processed
	 */
	function processFeed()
	{
		// Remove error messages at the top.
		if (strpos($this->xml, "<?xml") !== 0) {
			$this->xml = substr($this->xml, strpos($this->xml, "<?xml"));
		}

		libxml_use_internal_errors(true);
		$sxe = simplexml_load_string($this->xml);
		if (!$sxe) {
			$this->errormsg = "XML error:\n";
			foreach (libxml_get_errors() as $error) {
				$this->errormsg .= "\t".$error->message."\n";
			}
			return false;
		}
		if (floatval($sxe['edkapi']) && $sxe['edkapi'] < 0.91) {
			return false;
		}
		if ($sxe['edkapi'] != null && !($sxe['edkapi'] > 1.2)) {
			$this->lookupLocation = true;
		}
		$this->time = $sxe->currentTime;
		$this->cachedTime = $sxe->cachedUntil;
		if (isset($sxe->error)) {
			$this->errorcode = (int)$sxe->error['code'];
			$this->errormsg = strval($sxe->error);
			return false;
		}
		if (!empty($sxe->result->error)) {
			$this->errormsg = strval($sxe->result->error);
			return false;
		}

		if (!empty($sxe->result->rowset->row)) {
			foreach ($sxe->result->rowset->row as $row) {
				$this->processKill($row);
			}
		}
		return count($this->posted) + count($this->skipped);
	}

	/**
	 * @param SimpleXMLElement $row The row for a single kill.
	 */
	private function processKill($row)
	{
		$skip = false;
		$dup = false;
		$errorstring = "";
		$internalID = (int)$row['killInternalID'];
		$externalID = (int)$row['killID'];
		$id = 0;
		if (config::get('filter_apply') && config::get('filter_date')
				> strtotime(strval($row['killTime']))) {
			$skip = true;
		} else {
			$kill = new Kill();
			if ($externalID) {
				$kill->setExternalID($externalID);
					
				$id = $kill->getDupe(); //speedy dup check based on external id only
				if ($id > 0) { //duplicate found
					$qry = DBFactory::getDBQuery(true);
					$qry->execute("INSERT IGNORE INTO kb3_mails (`kll_id`,"
						." `kll_timestamp`, `kll_external_id`, `kll_modified_time`)"
						."VALUES($id, '".$kill->getTimeStamp()."', $externalID,  UTC_TIMESTAMP())");
					$qry->execute("UPDATE kb3_kills SET kb3_kills.kll_external_id = $externalID WHERE kb3_kills.kll_id = $id AND kb3_kills.kll_external_id IS NULL");
					$dup = true;
				}
			}
			if (!$dup) {
				$kill->setTimeStamp(strval($row['killTime']));

				$sys = SolarSystem::getByID((int)$row['solarSystemID']);
				if (!$sys->getName()) {
					$errorstring .= " Invalid solar system";
					$skip = true;
				}
				$kill->setSolarSystem($sys);

				if (!$this->processVictim($row, $kill, strval($row['killTime']))) {
					$errorstring .= " Invalid victim.";
					$skip = true;
				}

				if (!$skip) { //skipping intensive involved party processing
					$this->npcOnly = true; //there's no real check for this anymore?
					foreach ($row->rowset[0]->row as $inv)
						if (!$this->processInvolved($inv, $kill, strval($row['killTime']))) {
							$errorstring .= " Invalid involved party.";
							$skip = true;
							break;
						}
					// Don't post NPC only kills if configured.
					if ($this->npcOnly && Config::get('post_no_npc_only')) {
						$errorstring .= " NPC Only mail.";
						$skip = true;
					}
						
					if (!$skip) { //skipping intensive items processing
						if (isset($row->rowset[1]->row[0]))
							foreach ($row->rowset[1]->row as $item)
								$this->processItem($item, $kill);
						
						$authorized = false;
						if (config::get('cfg_allianceid') && in_array($kill->getVictimAllianceID(), config::get('cfg_allianceid')))
							$authorized = true;
						else if (config::get('cfg_corpid') && in_array($kill->getVictimCorpID(), config::get('cfg_corpid')))
							$authorized = true;
						else if (config::get('cfg_pilotid') && in_array($kill->getVictimID(), config::get('cfg_pilotid')))
							$authorized = true;
						foreach($kill->getInvolved() as $inv) {
							if (config::get('cfg_allianceid') && in_array($inv->getAllianceID(), config::get('cfg_allianceid')))
								$authorized = true;
							else if (config::get('cfg_corpid') && in_array($inv->getCorpID(), config::get('cfg_corpid')))
								$authorized = true;
							else if (config::get('cfg_pilotid') && in_array($inv->getPilotID(), config::get('cfg_pilotid')))
								$authorized = true;
						}
						if (!$authorized)
							$skip = true;
						else {
							$id = $kill->add();
							if ($kill->getDupe(true)) {
								$dup = true;
							} else {
								$this->posted[] = array($externalID, $internalID, $id);
								// Prepare text for the log.
								if($this->url) {
									$logaddress = "ID:".$this->url;
									if (strpos($logaddress, "?")) {
										$logaddress = substr($logaddress, 0,
											strpos($logaddress, "?"));
									}
									if ($kill->getExternalID()) {
										$logaddress .= "?a=kill_detail&kll_ext_id="
										.$kill->getExternalID();
									} else if ($internalID) {
										$logaddress .= "?a=kill_detail&kll_id=".$internalID;
									}
								} else if ($this->name) {
									$logaddress = $this->name;
									if ($kill->getExternalID()) {
										$logaddress .= ":kll_ext_id="
										.$kill->getExternalID();
									} else if ($internalID) {
										$logaddress .= ":kll_id=".$internalID;
									}
								} else {
									$logaddress = "ID: local input";
								}

								logger::logKill($id, $logaddress);
							}
						}
					}
				}
			}
		}
		if ($skip) {
			$this->skipped[] = array($externalID, $internalID, $id);
			if ($errorstring) {
				$errorstring .= " Kill not added. killID =  $externalID" . ($internalID ? ", killInternalID = $internalID." : ".");
				$this->parsemsg[] = $errorstring;
			}
		}
		if ($dup) {
			$this->duplicate[] = array($externalID, $internalID, $id);
		}
		if ($this->lastReturned < $externalID)
			$this->lastReturned = $externalID;
		if ($this->lastInternalReturned < $internalID)
			$this->lastInternalReturned = $internalID;
	}

	/**
	 * @param SimpleXMLElement $row
	 * @param Kill $kill
	 * @param string $time YYYY-mm-dd hh:ss
	 * @return boolean false on error
	 */
	private function processVictim($row, &$kill, $time)
	{
		// If we have a character ID but no name then we give up - the needed
		// info is gone.
		// If we have no character ID and no name then it's a structure or NPC
		//	- if we have a moonID (anchored at a moon) call it corpname - moonname
		//	- if we don't have a moonID call it corpname - systemname
		$victim = $row->victim;
		if (!strval($victim['characterName'])
				&& (int)$victim['characterID']) {
			return false;
		} else if (!strval($victim['corporationName'])) {
			return false;
		}

		if ((int)$victim['allianceID']) {
			$alliance = Alliance::add(strval($victim['allianceName']),
					(int)$victim['allianceID']);
		} else if ((int)$victim['factionID']) {
			$alliance = Alliance::add(strval($victim['factionName']),
					(int)$victim['factionID']);
		} else {
			$alliance = Alliance::add("None");
		}
		$corp = Corporation::add(strval($victim['corporationName']), $alliance, $time,
					(int)$victim['corporationID']);

		if (!strval($victim['characterName'])) {
			if ((int)$row['moonID']) {
				$name = API_Helpers::getMoonName((int)$row['moonID']);
				if (!$name) {
					$idtoname = new API_IDtoName();
					$idtoname->setIDs((int)$row['moonID']);

					if ($idtoname->fetchXML()) {
						return false;
					}

					$namedata = $idtoname->getIDData();

					$name = $namedata[0]['name'];
				}
				$name = strval($victim['corporationName'])." - ".$name;
			} else {
				$name = strval($victim['corporationName'])." - "
						.$kill->getSystem()->getName();
			}
		} else if (!(int)$victim['shipTypeID']) {
			return false;
		} else {
			$name = strval($victim['characterName']);
		}

		$pilot = Pilot::add($name, $corp, $time, (int)$victim['characterID']);
		$ship = Ship::getByID((int)$victim['shipTypeID']);

		$kill->setVictim($pilot);
		$kill->setVictimID($pilot->getID());
		$kill->setVictimCorpID($corp->getID());
		$kill->setVictimAllianceID($alliance->getID());
		$kill->setVictimShip($ship);
		$kill->set('dmgtaken', (int)$victim['damageTaken']);
		return true;
	}

	/**
	 * @param SimpleXMLElement $inv
	 * @param Kill $kill
	 * @param string $time YYYY-mm-dd hh:ss
	 * @return boolean false on error
	 */
	private function processInvolved($inv, &$kill, $time)
	{
		if (!(int)$inv['shipTypeID']
				&& !(int)$inv['weaponTypeID']
				&& !(int)$inv['characterID']
				&& !(string)$inv['characterName']) {
			$this->parsemsg[] = "Involved party blank.";
			return false;
		}
		$npc = false;
		$ship = Ship::getByID((int)$inv['shipTypeID']);
		$weapon = Cacheable::factory('Item', (int)$inv['weaponTypeID']);

		$alliance = new Alliance();

		if ((int)$inv['allianceID']) {
			$alliance = Alliance::add(strval($inv['allianceName']),
					(int)$inv['allianceID']);
		} else if ((int)$inv['factionID']) {
			$alliance = Alliance::add(strval($inv['factionName']),
					(int)$inv['factionID']);
		} else {
			$alliance = Alliance::add("None");
		}
		$corp = Corporation::add(strval($inv['corporationName']), $alliance, $time,
					(int)$inv['corporationID']);

		$charid = (int)$inv['characterID'];
		$charname = (string)$inv['characterName'];
		// Allow for blank names for consistency with CCP API.
		if (preg_match("/(Mobile (Large|Medium|Small) Warp Disruptor I?I?|\w+ Control Tower( \w+)?)/",
				$charname)) {
			$charname = $inv['corporationName'].' - '.$charname;
			$charid = 0;
		} else if ($charname == ""
				&& (preg_match("/(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/",
				   $weapon->getName()))) {
			$charname = $inv['corporationName'].' - '.$weapon->getName();
			$charid = 0;
		} else if ($charname == "" && !$charid) {
			// NPC ship
			$ship = Ship::lookup("Unknown");
			$weapon = Item::getByID((int) $inv['shipTypeID']);
			$charname = $weapon->getName();
			$npc = true;
			$charid = $weapon->getID();
		} else if ($charname == "" && $charid) {
			// Bugged kill
			$this->parsemsg[] = "Involved party has blank pilot name.";
			return false;
		}

		$pilot = Pilot::add((string)$charname, $corp, $time,
					 $charid);

		$iparty = new InvolvedParty($pilot->getID(), $corp->getID(),
				$alliance->getID(), (float) $inv['securityStatus'],
						$ship->getID(), $weapon->getID(),
						(int) $inv['damageDone']);

		$kill->addInvolvedParty($iparty);
		if ((int)$inv['finalBlow'] == 1) {
			$kill->setFBPilotID($pilot->getID());
		}
		$this->npcOnly = $this->npcOnly && $npc;
		return true;
	}

	/**
	 * @param SimpleXMLElement $item The element containing an Item.
	 * @param Kill $kill The Kill to add the item to.
	 * @param int $slot Set a default slot if none is specified.
	 * @return boolean false on error
	 */
	private function processItem($item, &$kill, $slot = null)
	{
		if ((int)$item['singleton'] == 2) {
			// Blueprint copy - in the cargohold
			$location = -1;
		}

		if ($slot != null) {
			$location = $slot;
		} else {
			if( $this->lookupLocation == true ) {
				if( $item['flag'] > 10 || $item['flag'] == 5 ) {
					// item locations in edk only goes up to ~9.
					// If someone is sending flags > 10 they are probably sending correct ccp flags..
					// flag 5 is old+new cargo hold so we can also pass in
					$location = $item['flag'];
				} else {
					$litem = new Item((int)$item['typeID']);
					$location = $litem->getSlot();
				}
			} else {
				$location = $item['flag'];
			}
		}

		if ((int)$item['qtyDropped']) {
			$kill->addDroppedItem(new DestroyedItem(new Item(
					(int)$item['typeID']), (int)$item['qtyDropped'], '',
					$location, $this->lookupLocation));
		}
		if ((int)$item['qtyDestroyed']) {
			$kill->addDestroyedItem(new DestroyedItem(new Item(
					(int)$item['typeID']), (int)$item['qtyDestroyed'], '',
					$location, $this->lookupLocation));
		}
		// Check for containers.
		if (isset($item->rowset)) {
			foreach ($item->rowset->row as $subitem) {
				$this->processItem($subitem, $kill, $location);
			}
		}
		return true;
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

	/**
	 * Return an array of duplicate kill IDs
	 *
	 * @return array
	 */
	function getDuplicate()
	{
		return $this->duplicate;
	}

	function getTime()
	{
		return $this->time;
	}

	function getCachedTime()
	{
		return $this->cachedTime;
	}

	public function errormsg()
	{
		return $this->errormsg;
	}
	/**
	 *
	 * @param KillList $killList
	 * @return string KillList as XML
	 */
	public static function killListToXML($killList)
	{
		global $idfeedversion;
		$qry = DBFactory::getDBQuery();
		$date = gmdate('Y-m-d H:i:s');
		$xml = "<?xml version='1.0' encoding='UTF-8'?>
		<eveapi version='2' edkapi='".$idfeedversion."'>
		</eveapi>";
		$sxe = new SimpleXMLElement($xml);
		// Let's start making the xml.
		$sxe->addChild('currentTime', $date);
		$result = $sxe->addChild('result');
		$kills = $result->addChild('rowset');
		$kills->addAttribute('name', 'kills');
		$kills->addAttribute('key', 'killID');
		$kills->addAttribute('columns', 'killID,solarSystemID,killTime,moonID,hash,trust');

		$count = 0;
		$timing = '';
		while ($kill = $killList->getKill()) {
			if (config::get('km_cache_enabled') && CacheHandler::exists($kill->getID().".xml", 'mails')) {
				$cachedRow = new SimpleXMLElement(CacheHandler::get($kill->getID().".xml", 'mails'));
				IDFeed::addXMLElement($kills, $cachedRow);
				continue;
			}

			$count++;
			if ($kill->isClassified()) continue;
			//$kill = Kill::getByID($kill->getID());
			$row = $kills->addChild('row');
			$row->addAttribute('killID', intval($kill->getExternalID()));
			$row->addAttribute('killInternalID', intval($kill->getID()));
			$row->addAttribute('solarSystemID', $kill->getSystem()->getExternalID());
			$row->addAttribute('killTime', $kill->getTimeStamp());
			$row->addAttribute('moonID', '0');
			$row->addAttribute('hash', bin2hex(IDFeed::getHash($kill, true)));
			$row->addAttribute('trust', 3);
			$victim = Pilot::getByID($kill->getVictimID());
			$victimCorp = Corporation::getByID($kill->getVictimCorpID());
			$victimAlliance = Alliance::getByID($kill->getVictimAllianceID());
			$victimrow = $row->addChild('victim');
			if ($victim->getName() == $kill->getVictimShipName()) {
				$victimrow->addAttribute('characterID', "0");
				$victimrow->addAttribute('characterName', "");
			} else {
				$victimrow->addAttribute('characterID', $victim->getExternalID());
				$victimrow->addAttribute('characterName', $victim->getName());
			}
			$victimrow->addAttribute('corporationID', $victimCorp->getExternalID());
			$victimrow->addAttribute('corporationName', $victimCorp->getName());
			if ($victimAlliance->isFaction()) {
				$victimrow->addAttribute('allianceID', 0);
				$victimrow->addAttribute('allianceName', '');
				$victimrow->addAttribute('factionID', $victimAlliance->getFactionID());
				$victimrow->addAttribute('factionName', $victimAlliance->getName());
			} else {
				$victimrow->addAttribute('allianceID', $victimAlliance->getExternalID());
				$victimrow->addAttribute('allianceName', $victimAlliance->getName());
				$victimrow->addAttribute('factionID', 0);
				$victimrow->addAttribute('factionName', '');
			}
			$victimrow->addAttribute('damageTaken', $kill->getDamageTaken());
			$victimrow->addAttribute('shipTypeID', $kill->getVictimShipExternalID());
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
				WHERE ind_kll_id = ".$kill->getID()." ORDER BY ind_order ASC";
			$qry->execute($sql);

			while ($inv = $qry->getRow()) {
				$invrow = $involved->addChild('row');
				if (strpos($inv['plt_name'], '- ') !== false) {
					$inv['plt_name'] = substr($inv['plt_name'],
							strpos($inv['plt_name'], '- ') + 2);
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
				$invAlliance = Alliance::getByID($inv['ind_all_id']);
				if ($invAlliance->isFaction()) {
					$invrow->addAttribute('allianceID', 0);
					$invrow->addAttribute('allianceName', '');
					$invrow->addAttribute('factionID', $invAlliance->getFactionID());
					$invrow->addAttribute('factionName', $invAlliance->getName());
				} else {
					if (strcasecmp($invAlliance->getName(), "None") == 0) {
						$invrow->addAttribute('allianceID', 0);
						$invrow->addAttribute('allianceName', "");
					} else {
						$invrow->addAttribute('allianceID', $invAlliance->getExternalID());
						$invrow->addAttribute('allianceName', $invAlliance->getName());
					}
					$invrow->addAttribute('factionID', 0);
					$invrow->addAttribute('factionName', '');
				}
				$invrow->addAttribute('securityStatus',
						number_format($inv['ind_sec_status'], 1));
				$invrow->addAttribute('damageDone', $inv['ind_dmgdone']);
				if ($inv['plt_id'] == $kill->getFBPilotID()) {
					$final = 1;
				} else {
					$final = 0;
				}
				$invrow->addAttribute('finalBlow', $final);
			}
			$sql = "SELECT * FROM kb3_items_destroyed WHERE itd_kll_id = ".$kill->getID();
			$qry->execute($sql);
			$qry2 = DBFactory::getDBQuery();
			$sql = "SELECT * FROM kb3_items_dropped WHERE itd_kll_id = ".$kill->getID();
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
			if (config::get('km_cache_enabled')) {
				CacheHandler::put($kill->getID().".xml", $row->asXML(), 'mails');
			}
			$timing .= $kill->getID().": ".(microtime(true) - $starttime)."<br />";
		}
		$sxe->addChild('cachedUntil', $date);
		return $sxe->asXML();
	}
	/**
	 * Recursively add a SimpleXMLElement to another.
	 *
	 * @param SimpleXMLElement $dest
	 * @param SimpleXMLElement $source
	 */
	private static function addXMLElement(&$dest, $source)
	{
		$new_dest = $dest->addChild($source->getName(), $source[0]);

		foreach ($source->attributes() as $name => $value) {
			$new_dest->addAttribute($name, $value);
		}

		foreach ($source->children() as $child) {
			IDFeed::addXMLElement($new_dest, $child);
		}
	}

	/**
	 * Depreciated. Returns a hash of the killmail for backwards
	 * compatibility with older boards using this board's idfeed.
	 * 
	 * Optionally pdates the db with the generated hash if missing.
	 *
	 * @param Kill $kill The kill to hash
	 * @param bool $update Whether to set the hash in the mails db table if it's missing
	 * @return mixed The hash string or false upon error
	 */	
	static function getHash($kill, $update = false) {
		$qry = DBFactory::getDBQuery();
		if (!is_object($kill))
			return false;
		$killID = $kill->getID();
		if($killID) {
				$qry->execute("SELECT hex(kll_hash) FROM kb3_mails WHERE kll_id = " . (int)$killID);
			if ($qry->recordCount()) {
				$row = $qry->getRow();
					if ($row['kll_hash']) //so far this field should never be null, but we could remove hash in the future...
					return $row['kll_hash'];
			}
		}
		
		//generate hash
		$restoreClassification = false;
		if ($kill->isClassified) { //temporarily disable classification for raw mail generation
			config::set('kill_classified', 0);
			$restoreClassification = true;
		}
			
			$mail = trim($kill->getRawMail());
		$mail = str_replace("\r\n", "\n", $mail);

		if(is_null($mail)) {
			if ($restoreClassification)
				config::set('kill_classified', 1);
			return false;
		}

		$involvedStart = strpos($mail, "Involved parties:");
		if ($involvedStart === false) {
			if ($restoreClassification)
				config::set('kill_classified', 1);
			return false;
		}
		$involvedStart = strpos($mail, "Name:", $involvedStart);

		$itemspos = strpos($mail, "\nDestroyed items:");
		if ($itemspos === false)
			$itemspos = strpos($mail, "\nDropped items:");
		if ($itemspos === false)
			$involved = substr($mail, $involvedStart);
		else
			$involved = substr($mail, $involvedStart, $itemspos - $involvedStart);
			
		$invList = explode("Name: ", $involved);
		$invListDamage = array();
		$invlistName = array();
		foreach($invList as $party)
			if(!empty($party))
			{
				$pos = strrpos($party, ": ");
				$damage = @intval(substr($party, $pos+2));
				$invListDamage[] = $damage;
				$pos = strpos($party, "\n");
				$name = trim(substr($party,0,$pos));
				$invListName[] = $name;
			}
				
		// Sort the involved list by damage done then alphabetically.
		array_multisort($invListDamage, SORT_DESC, SORT_NUMERIC, $invListName, SORT_ASC, SORT_STRING);

		$hashIn = substr($mail, 0, 16);
		$hashIn = str_replace('.', '-', $hashIn);

		$pos = strpos($mail, "Victim: ");
		if($pos === false)
			$pos = strpos($mail, "Moon: ");
		$pos += 8;

		$posEnd = strpos($mail, "\n", $pos);
		if ($pos === false || $posEnd === false)
			return false;
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$pos = strpos($mail, "Destroyed: ") + 11;
		$posEnd = strpos($mail, "\n", $pos);
		if ($pos === false || $posEnd === false) {
			if ($restoreClassification)
				config::set('kill_classified', 1);
			return false;
		}
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$pos = strpos($mail, "System: ") + 8;
		$posEnd = strpos($mail, "\n", $pos);
		if ($pos === false || $posEnd === false) {
			if ($restoreClassification)
				config::set('kill_classified', 1);
			return false;
		}
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$pos = strpos($mail, "Damage Taken: ") + 14;
		$posEnd = strpos($mail, "\n", $pos);
		if ($pos === false || $posEnd === false) {
			if ($restoreClassification)
				config::set('kill_classified', 1);
			return false;
		}
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$hashIn .= implode(',', $invListName);
		$hashIn .= implode(',', $invListDamage);

		$hash = md5($hashIn, true);
			
		if ($update) {
			$externalID = $kill->getExternalID();
			if ($externalID) {
				$sql = "INSERT IGNORE INTO kb3_mails (`kll_id`, `kll_timestamp`, ".
				"`kll_external_id`, `kll_hash`, `kll_modified_time`) VALUES ($killID, '".
				$kill->getTimeStamp()."', $externalID, '".$qry->escape($hash)."', UTC_TIMESTAMP())";
			} else {
				$sql = "INSERT IGNORE INTO kb3_mails (`kll_id`, `kll_timestamp`, ".
				"`kll_hash`, `kll_modified_time`) VALUES ($killID, '".
				$kill->getTimeStamp()."', '".$qry->escape($hash)."', UTC_TIMESTAMP())";
			}
			$qry->execute($sql);
		}
		if ($restoreClassification)
			config::set('kill_classified', 1);
		return $hash;
	}
}
