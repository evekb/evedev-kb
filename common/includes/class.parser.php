<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class Parser
{
	private static $pilots = array();
	private static $corps = array();
	private static $alliances = array();
	private $error_ = array();
	private $killmail_ = '';
	private $externalID = 0;
	private $needs_final_blow_ = true;
	private $dupeid_ = 0;
	private $hash = null;
	private $trust = 0;
	private static $loadExternals = true;

	function uchr ($codes)
	{ //converts characterset code-pages to ascii-compatible types
		if (is_scalar($codes)) $codes= func_get_args();
		$str= '';
		foreach ($codes as $code) $str.= html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'UTF-8');
		return $str;
	}

	function Parser($killmail, $externalID = null, $loadExternals = true)
	{
		self::$loadExternals = $loadExternals;
		// -------------------------------------
		// fix for new localization in killmails
		// -------------------------------------

		// remove possible escaping of double quotation marks
		$killmail = preg_replace("/\\\\\"/", "\"", $killmail);
		// remove <localized> shit and extract the hint if any
		$killmail = preg_replace_callback("/\<localized hint=\"(.*)\"\>(.*)(\*)?(\<\/localized\>)?\\r/", create_function('$match', 'return $match[1];'), $killmail);
		// remove trailing * if any
		$killmail = preg_replace("/(\*)?\\r/", "", $killmail);

		$this->killmail_ = trim(str_replace("\r", '', $killmail));

		// Check the supplied external id is valid.
		if(!is_null($externalID))$this->externalID = intval($externalID);
		else $this->externalID = 0;

		//Fraktion added for mails that originate from griefwatch / battle-clinic that do nothing with this info
		if (strpos($this->killmail_, 'Beteiligte Parteien:') || (strpos($this->killmail_, 'Fraktion:')))
		{
			$this->preparse('german');
		}
		elseif (strpos($this->killmail_, "Корпорация") )
		{
			$this->preparse('russian');
		}
		elseif (strpos($this->killmail_, 'System Security Level:'))
		{
			// this converts the killmail internally from pre-rmr to kali format
			$this->preparse('prermr');
		}
		// Swap , for . in numbers to fix CCP localisation problems.
		$this->killmail_ = preg_replace('/: (\d+),00/', ': $1', $this->killmail_);
		$this->killmail_ = preg_replace('/(\d),(\d)/', '$1.$2', $this->killmail_);

		//get the mail's timestamp - if older than QR, then preparse for scrambler translation
		$timestamp = substr($this->killmail_, 0, 16);
		$timestamp = str_replace('.', '-', $timestamp);

		/* If we are processing an old killmail and updating it to the new format, we should process
		 * the oldest fixes first to update the killmail to the newest format. However, we'll look
		 * to see if any fixes need to be done first to reduce the number of calls as most kills will
		 * be current.
		 * In addition, we always want to process strtotime in eve's time i.e. UTC/GMT.
		 */
		$timestamp_int = strtotime($timestamp . ' UTC');
		$cru11_release = strtotime("2012-01-24 12:00:00 UTC");
		if($timestamp_int < $cru11_release) {
				$cru10_release = strtotime("2011-11-29 12:00:00 UTC");
				if($timestamp_int < $cru10_release) {
						$dom11_release = strtotime('2010-01-21 12:00:00 UTC');
						if($timestamp_int < $dom11_release) {
								$dom_release = strtotime('2009-12-03 12:00:00 UTC');
								if($timestamp_int < $dom_release) {
										$apoc_release = strtotime('2009-03-10 03:00:00 UTC');
										if($timestamp_int < $apoc_release) {
												$apoc15_release = strtotime('2009-08-20 12:00:00 UTC');
												if($timestamp_int < $apoc15_release) {
														$qr_release = strtotime('2008-11-11 00:00:00 UTC');
														if($timestamp_int < $qr_release) {
																$this->preparse('preqr');
														}
														$this->preparse('apoc15');
												}
												$this->preparse('apoc');
										}
										$this->preparse('dominion');
								}
								$this->preparse('dom11');
						}
						$this->preparse('cru10');
				}
				$this->preparse('cru11');
		}

		if (strpos($this->killmail_, '**** Truncated - mail is too large ****') > 0)
			$this->killmail_ = str_replace('**** Truncated - mail is too large ****', '', $this->killmail_);

		// Parser fix, since some killmails don't have a final blow, they would break the KB.
		//On mails without final blow info, the first name on the list becomes the final blow holder
		if (strpos($this->killmail_, 'laid the final blow') === false)
			$this->needs_final_blow_ = 1;
	}

	function parse($checkauth = true)
	{
		$qry = DBFactory::getDBQuery();

		// Check hashes with a prepared query.
		// Make it static so we can reuse the same query for feed fetches.
		static $timestamp;
		static $checkHash;
		static $hash;
		static $trust;
		static $kill_id;
		$timestamp = substr($this->killmail_, 0, 16);
		$timestamp = str_replace('.', '-', $timestamp);

		// Check hashes.
		$hash = self::hashMail($this->killmail_);
		if(!isset($checkHash))
		{
			$checkHash = new DBPreparedQuery();
			$checkHash->prepare('SELECT kll_id, kll_trust FROM kb3_mails WHERE kll_timestamp = ? AND kll_hash = ?');
			$arr = array(&$kill_id, &$trust);
			$checkHash->bind_results($arr);
			$types = 'ss';
			$arr2 = array(&$types, &$timestamp, &$hash);
			$checkHash->bind_params($arr2);
		}
		$checkHash->execute();

		if($checkHash->recordCount())
		{
			$checkHash->fetch();
			$this->dupeid_ = $kill_id;
			// We still want to update the external ID if we were given one.
			if($this->externalID)
			{
				$qry->execute("UPDATE kb3_kills"
						." JOIN kb3_mails ON kb3_mails.kll_id = kb3_kills.kll_id"
						." SET kb3_kills.kll_external_id = ".$this->externalID
						.", kb3_mails.kll_external_id = ".$this->externalID
						.", kll_modified_time = UTC_TIMESTAMP()"
						." WHERE kb3_kills.kll_id = ".$this->dupeid_
						." AND kb3_kills.kll_external_id IS NULL");

				if($trust >= 0 && $this->trust && $trust > $this->trust) {
					$qry->execute("UPDATE kb3_mails SET kll_trust = "
							.$this->trust." WHERE kll_id = ".$this->dupeid_);
				}
			}

			if($trust < 0) return -4;
			return -1;
		}
		// Check external IDs
		else if($this->externalID)
		{
			$qry->execute('SELECT kll_id FROM kb3_kills WHERE kll_external_id = '.$this->externalID);
			if($qry->recordCount())
			{
				$row = $qry->getRow();
				$this->dupeid_ = $row['kll_id'];
				//TODO if trust == 1 add to kb3_mails.
				return -1;
			}
		}
		$this->hash = $hash;

		//trim out any multiple spaces that may exist -
		//$this->killmail_ = preg_replace('/ +/', ' ', $this->killmail_);

		// header section
		$involvedpos = strpos($this->killmail_, "Involved parties:");
		if($involvedpos == 0)
		{
			$this->error("Mail lacks Involved parties header.");
			return 0;
		}

		$header = substr($this->killmail_, 0, $involvedpos);
		$timestamp = substr($header, 0, 16);

		$victim = explode("\n", trim(substr($this->killmail_, 0, $involvedpos)));
		$upper_limit =  count($victim);

		$victimname = "Unknown"; //lovely default values
		$factionname = "None";
		$alliancename = "None";
		$corpname = "Unknown";
		$shipname = "Unknown";
		$systemname = "Unknown";
		$systemsec = "0.0";
		$dmgtaken = '0';
		$this->dmgtaken = '0';
		$pos = 0;
		$moon = "";

		for($counter = 0; $counter <= $upper_limit; $counter++)
		{
			if(preg_match("/Victim: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1])
					$victimname = $matches[1];
			}
			elseif (preg_match("/Corp: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1])
					$corpname = $matches[1];
			}
			elseif (preg_match("/Alliance: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1]) $alliancename = $matches[1];
				if($alliancename == "Unknown") $alliancename = "None";
			}
			elseif (preg_match("/Faction: (.*)/", $victim[$counter], $matches))
			{
				if(strlen($matches[1]) > 5 && $matches[1] != "Unknown") //catches faction mails from -A-
					$factionname = $matches[1];
				else $factionname = "None";
			}
			elseif (preg_match("/Destroyed: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1])
					$shipname = $matches[1];
			}
			elseif (preg_match("/System: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1])
				{   //bad assumption here - moon has to come before security.
					$systemname = $matches[1];
					if ((strcmp($moon, 'Unknown') == 0) && ($pos == 1))
					{
						$moon = $matches[1];
						$victimname = $matches[1];
					}
				}
			}
			elseif (preg_match("/Security: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1])
					$systemsec = (float) $matches[1];
			}
			elseif (preg_match("/Damage Taken: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1])
				{
					$dmgtaken = (int) $matches[1];
					$this->dmgtaken = $dmgtaken;
				}
			}
			elseif (preg_match("/Moon: (.*)/", $victim[$counter], $matches))
			{
				if($matches[1])
				{
					$moon = $matches[1];
					$victimname = $matches[1];
					$pos = 1;
				}
				else
				{ //if the system is valid, it will pick this up, provided it features after
					//the moon is listed - which is unlikely unless the mail format
					//drastically changes... again :)
					$moon = 'Unknown';
					$victimname = 'Unknown';
					$pos = 1;
				}
			}
		}

		//faction warfare stuff
		if (strcasecmp($alliancename, 'None') == 0)
		{
			$alliancename = $factionname;
		}

		//report the errors for the things that make sense.
		//we need pilot names, corp names, ship types, and the system to be sure
		//the rest aren't required but for completeness, you'd want them in :)
		
		// Customs Offices don't have names. Hack a fix in by accepting mails with
		// no victim name but that do have a system.
		if (strcmp($victimname, 'Unknown') == 0)
		{
			if (strcmp($systemname, 'Unknown') == 0){
				$this->error('Victim has no name.');
				unset($victimname); //we unset the variable so that it fails the next check
				$this->error('Killmail lacks solar system information.');
				unset($systemname);
			} else {
				$victimname = $systemname;
			}
		}

		if (strcmp($corpname, 'Unknown') == 0)
		{
			$this->error('Victim has no corp.');
			unset($corpname);
		}

		if (strcmp($shipname, 'Unknown') == 0)
		{
			$this->error('Victim has no ship type.');
			unset($shipname);
		}

		if (strcmp($systemname, 'Unknown') == 0)
		{
			$this->error('Killmail lacks solar system information.');
			unset($systemname);
		}

		if ($pos == 1)
		{
			$victimname = $moon;
		}

		if (!isset($timestamp) ||
			!isset($factionname) ||
			!isset($alliancename) ||
			!isset($corpname) ||
			!isset($victimname) ||
			!isset($shipname) ||
			!isset($systemname) ||
			!isset($systemsec))
		return 0;

		if ($checkauth)
			$authorized = false;
		else $authorized = true;

		// populate/update database
		$alliance = $this->fetchAlliance($alliancename);
		$corp = $this->fetchCorp($corpname, $alliance, $timestamp);
		$victim = $this->fetchPilot($victimname, $corp, $timestamp);
		$system = SolarSystem::lookup($systemname);

		if (!$system->getID())
		{
			$this->error('System not found.', $systemname);
		}

		$ship = Ship::lookup($shipname);

		if (!$ship || !$ship->getID())
		{
			$this->error('Ship not found.', $shipname);
			$ship = new Ship();
		}

		$kill = new Kill();
		if($this->externalID)
			$kill->setExternalID($this->externalID);

		$kill->setTimeStamp($timestamp);
		$kill->setVictim($victim);
		$kill->setVictimID($victim->getID());
		$kill->setVictimCorpID($corp->getID());
		$kill->setVictimAllianceID($alliance->getID());
		$kill->setVictimShip($ship);
		$kill->setSolarSystem($system);

		if ($dmgtaken)
		{
			$kill->set('dmgtaken', $dmgtaken);
		}

		if (config::get('cfg_allianceid') && in_array($alliance->getID(), config::get('cfg_allianceid')))
		{
			$authorized = true;
		}
		elseif (config::get('cfg_corpid') && in_array($corp->getID(), config::get('cfg_corpid')))
		{
			$authorized = true;
		}
		elseif (config::get('cfg_pilotid') && in_array($victim->getID(), config::get('cfg_pilotid')))
		{
			$authorized = true;
		}

		// involved parties section
		$end = strpos($this->killmail_, "Destroyed items:");
		if ($end == 0)
		{
			$end = strpos($this->killmail_, "Dropped items:");
			if ($end == 0)
			{ //try to parse to the end of the mail in the event sections are missing
				$end = strlen($this->killmail_);
			}
		}

		$involved = explode("\n", trim(substr($this->killmail_, strpos($this->killmail_, "Involved parties:") + 17, $end - (strpos($this->killmail_, "Involved parties:") + 17))));

		$ipilot_count = 0; //allows us to be a bit more specific when errors strike
		$i = 0;

		$order = 0;
		while ($i < count($involved))
		{
			$iparts = count($involved);
			$finalblow = 0;

            while($i < $iparts) {
				$ipilot_count++;

				$ipname = "Unknown";
				$ianame = "None";
				$ifname = "None";
				$icname = "None";
				$isname = "Unknown";
				$iwname = "Unknown";
				$idmgdone = '0';
				$secstatus = "0.0";


				while($involved[$i] == '')
				{ //compensates for multiple blank lines between involved parties
					$i++;
					if($i > count($involved))
					{
						$this->error("Involved parties section prematurely ends.");
						return 0;

					}
				}

				for($counter = $i; $counter <= $iparts; $counter++)
				{
					if(preg_match("/Name: (.*)/", $involved[$counter], $matches))
					{
						if($matches[1])
						{
							if(stristr($involved[$counter], '/'))
							{
								$slash = strpos($involved[$counter], '/');
								$name = trim(substr($involved[$counter], 5, $slash-5));
								$corporation = trim(substr($involved[$counter], $slash+1, strlen($involved[$counter])- $slash+1));

								//now if the corp bit has final blow info, note it
								preg_match("/(.*) \\(laid the final blow\\)/", $corporation, $matched);
								if($matched[1])
								{
									$finalblow = 1;
									$iwname = $name;
									$end = strpos($corporation, '(') -1;
									$corporation = substr($corporation, 0, $end);
								}
								else
								{
									$finalblow = 0;
									$iwname = $name;
								}
								//alliance lookup for warp disruptors - normal NPCs aren't to be bundled in
								$crp = $this->fetchCorp($corporation);
								if($crp && $crp->getExternalID(true) > 0)
								{
									if($crp->fetchCorp())
									{
										$al = $crp->getAlliance();
										$alName = $al->getName();
										if(trim($alName) != "")
										{
											$ianame = $al->getName();
										}
									}
									// else check db for kills with that corp at the same time?
								}

								$ipname = $name;
								$icname = $corporation;
							}
							else
							{
								$ipname = $matches[1];
								preg_match("/(.*)\s*\\(laid the final blow\\)/", $ipname, $matches);
								if (isset($matches[1]))
								{
									$ipname = trim($matches[1]);
									$finalblow = 1;
								}
								else $finalblow = 0;
							}
						}
					}
					else if(preg_match("/Alliance: (.*)/", $involved[$counter], $matches))
					{
                        if($matches[1]) $ianame = $matches[1];
						if($ianame == "Unknown") $ianame = "None";
					}
					else if(preg_match("/Faction: (.*)/", $involved[$counter], $matches))
					{
						if(strlen($matches[1]) > 5 && $matches[1] != "Unknown") //catches faction mails from -A-
							$ifname = $matches[1];
						else $ifname = "NONE";
					}
					else if(preg_match("/Corp: (.*)/", $involved[$counter], $matches))
					{
						if($matches[1])
							$icname = $matches[1];
					}
					else if(preg_match("/Ship: (.*)/", $involved[$counter], $matches))
					{
						if($matches[1])
							$isname = $matches[1];
					}
					else if(preg_match("/Weapon: (.*)/", $involved[$counter], $matches))
					{
						if($matches[1])
							$iwname = $matches[1];
					}
					else if(preg_match("/Security: (.*)/", $involved[$counter], $matches))
					{
						if($matches[1])
							$secstatus = (float) $matches[1];
					}
					else if(preg_match("/Damage Done: (.*)/", $involved[$counter], $matches))
					{
						if($matches[1])
							$idmgdone = (int) $matches[1];
					}
					else if($involved[$counter] == '')
					{ //allows us to process the involved party. This is the empty line after the
						//involved party section
						$counter++;
						$i = $counter;
						break;
					}
					else { //skip over this entry, it could read anything, we don't care. Handy if/when
						//new mail fields get added and we aren't handling them yet.
						$counter++;
						$i = $counter;
					}

					if ($this->needs_final_blow_)
					{
						$finalblow = 1;
						$this->needs_final_blow_ = 0;
					}
				}

				// Faction Warfare stuff
				if (strcasecmp($ianame, "None") == 0)
				{
					$ianame = $ifname;
				}
				// end faction warfare stuff

				$ialliance = $this->fetchAlliance($ianame);

				if (strcmp($icname, 'None') == 0)
				{	//don't add corp, because pilots have to be in corps.
					$this->error('Involved party has no corp. (Party No. '.$ipilot_count.')');
					$icorp = new Corporation();
				}
				else
				{
					$icorp = $this->fetchCorp($icname, $ialliance, $kill->getTimeStamp());
				}

				if (preg_match("/^(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/",$iwname))
				{ //for involved parties parsed that lack a pilot, but are actually POS or mobile warp disruptors
					$ipname = $icname. ' - '. $iwname;
					$ipilot = $this->fetchPilot($ipname, $icorp, $timestamp);
				}

				elseif (strcmp($ipname, 'Unknown') == 0 || empty($ipname))
				{
					$ipilot = new Pilot();
					$this->error('Involved party has no name. (Party No. '.$ipilot_count.')');
				}
				else
				{ //don't add pilot if the pilot's unknown or dud
					$ipilot = $this->fetchPilot($ipname, $icorp, $timestamp);
				}

				$iship = Ship::lookup($isname);
				if (!$iship || !$iship->getName())
				{
					$this->error('Ship not found.', $isname);
				}

				if (strcmp($iwname, 'Unknown') == 0 && $iship && $iship->getID())
				{
					$iwname = $iship->getName();
				}

				$iweapon = Item::lookup($iwname);
				if (strcmp($iwname, 'Unknown') == 0)
				{
					$this->error('No weapon found for pilot "'.$ipname .'"');
					$iweapon = new Item();
				} else if (!$iweapon || !$iweapon->getID()) {
					$this->error('Weapon not found.', $iwname);
					$iweapon = new Item();
				}

				if (config::get('cfg_allianceid') && in_array($ialliance->getID(), config::get('cfg_allianceid')))
				{
					$authorized = true;
				}
				elseif (config::get('cfg_corpid') && in_array($icorp->getID(), config::get('cfg_corpid')))
				{
					$authorized = true;
				}
				elseif (config::get('cfg_pilotid') && in_array($ipilot->getID(), config::get('cfg_pilotid')))
				{
					$authorized = true;
				}

				if (!$authorized)
				{
					if ($string = config::get('post_permission'))
					{
						if ($string == 'all')
						{
							$authorized = true;
						}
						else
						{
							$tmp = explode(',', $string);
							foreach ($tmp as $item)
							{
								if (!$item)
								{
									continue;
								}
								$typ = substr($item, 0, 1);
								$id = substr($item, 1);
								if ($typ == 'a')
								{
									if ($ialliance->getID() == $id || $kill->getVictimAllianceID() == $id)
									{
										$authorized = true;
										break;
									}
								}
								elseif ($typ == 'c')
								{
									if ($icorp->getID() == $id || $kill->getVictimCorpID() == $id)
									{
										$authorized = true;
										break;
									}
								}
								elseif ($typ == 'p')
								{
									if ($ipilot->getID() == $id || $kill->getVictimID() == $id)
									{
										$authorized = true;
										break;
									}
								}
							}
						}
					}
				}

				$iparty = new InvolvedParty($ipilot->getID(), $icorp->getID(),
					$ialliance->getID(), $secstatus, $iship->getID(),
						$iweapon->getID(), $idmgdone);

				$kill->addInvolvedParty($iparty);

				if ($finalblow == 1)
				{
					$kill->setFBPilotID($ipilot->getID());
					$kill->setFBCorpID($icorp->getID());
					$kill->setFBAllianceID($ialliance->getID());
				}
			}
		}
		// Duplicate check does not use items so it's safe to check now
		if($id = $kill->getDupe(true))
		{
			$this->dupeid_ = $id;
			// If this is a duplicate and we have an external id then update the
			// existing kill.
			if($this->externalID)
			{
				$qry->execute("UPDATE kb3_kills SET kll_external_id = ".
					$this->externalID." WHERE kll_id = ".$this->dupeid_);
				$qry->execute("UPDATE kb3_mails SET kll_external_id = ".
					$this->externalID.", kll_modified_time = UTC_TIMESTAMP() ".
					"WHERE kll_id = ".$this->dupeid_." AND kll_external_id IS NULL");
			}
			return -1;
		}

		// destroyed items section
		$destroyedpos = strpos($this->killmail_, "Destroyed items:");

		if ($destroyedpos)
		{
			$endpos = strlen($this->killmail_) - $destroyedpos + 16;
			$pos = strpos($this->killmail_, "Dropped items:");
			if ($pos === false)
			{
				$pos = strlen($this->killmail_);
			}
			$endpos = $pos - $destroyedpos - 16;

			$destroyed = explode("\n", trim(substr($this->killmail_, $destroyedpos + 16, $endpos)));
			$destroyed_items = $this->scanForItems($destroyed);
			foreach ($destroyed_items as $item)
			{
				$ditem = new DestroyedItem($item['item'], $item['quantity'], '', $item['location']);
				$kill->addDestroyedItem($ditem);
			}
		}

		$startpos = strpos($this->killmail_, "Dropped items:");
		if ($startpos)
		{
			$endpos = strlen($this->killmail_) - $startpos + 14;

			$dropped = explode("\n", trim(substr($this->killmail_, $startpos + 14, $endpos)));

			$dropped_items = $this->scanForItems($dropped);
			foreach ($dropped_items as $item)
			{
				$ditem = new DestroyedItem($item['item'], $item['quantity'], '',
						$item['location']);
				$kill->addDroppedItem($ditem);
			}
		}

		if (!$authorized)
		{
			return -2;
		}
		if ($this->getError())
		{
			return 0;
		}

		if ($this->returnmail)
		{
			return $kill;
		}
		$kill->setHash($this->hash);
		$kill->setTrust($this->trust);
		$id = $kill->add();
		//unset hash and trust to be sure they aren't reused.
		$this->hash = '';
		$this->trust = 0;

		if ($id == -1)
		{
			$this->dupeid_ = $kill->getDupe(true);

			if($this->externalID)
			{
				$qry->execute("UPDATE kb3_kills SET kll_external_id = ".
					$this->externalID." WHERE kll_id = ".$this->dupeid_);
				$qry->execute("UPDATE kb3_mails SET kll_external_id = ".
					$this->externalID.", kll_modified_time = UTC_TIMESTAMP() WHERE kll_id = ".$this->dupeid_.
					" AND kll_external_id IS NULL");
			}
		}
        elseif ($id == -2) {
			$this->error("An error has occurred. Please try again later.");
			$id = 0;
		}
		return $id;
	}

	function scanForItems($destroyed)
	{
		static $locations;
		$i = 0;
		$num = count($destroyed);

		if (is_null($locations)) {
			$qry = DBFactory::getDBQuery();
			$qry->execute("SELECT itl_id, itl_location FROM kb3_item_locations");
			while ($row = $qry->getRow()) {
				$locations[$row['itl_location']] = $row['itl_id'];
			}
		}

		while ($i < $num) {
			$container = false;
			$destroyed[$i] = trim($destroyed[$i]);
			// TODO: Find a nicer way to do this. Then rewrite the rest of the parser.
			$destroyed[$i] = preg_replace("/ \(Copy\)(.*)\([\w ]*\)/", "$1(Copy)", $destroyed[$i]);
			$itemname = substr($destroyed[$i], 0, strlen($destroyed[$i]));

			if ($destroyed[$i] == "") {
				$i++;
				continue;
			}

			if ($destroyed[$i] == "Empty.") {
				$container = false;
				$i++;
				continue;
			}

			$qtypos = 0;
			$locpos = 0;
			$itemname = "";
			$quantity = "";
			$location = "";

			$qtypos = strpos($destroyed[$i], ", Qty: ");
			$locpos = strrpos($destroyed[$i], "(");

			if ($container && $locpos != false) {
				$container = false;
			}
			if (strpos($destroyed[$i], "Container")) {
				$container = true;
			}
			if ($qtypos <= 0 && !$locpos) {
				$itemlen = strlen($destroyed[$i]);
				if ($container) $location = "Cargo";
			}
			if ($qtypos > 0 && !$locpos) {
				$itemlen = $qtypos;
				$qtylen = strlen($destroyed[$i]) - $qtypos;
				if ($container) {
					$location = "Cargo";
				}
			}
			if ($locpos > 0 && $qtypos <= 0) {
				$itemlen = $locpos - 1;
				$qtylen = 0;
				$loclen = strlen($destroyed[$i]) - $locpos - 2;
				if (!$locpos) {
					$container = false;
				}
			}
			if ($locpos > 0 && $qtypos > 0)
			{
				$itemlen = $qtypos;
				$qtylen = $locpos - $qtypos - 7;
				$loclen = strlen($destroyed[$i]) - $locpos - 2;
				if (!$locpos) $container = false;
			}

			$itemname = substr($destroyed[$i], 0, $itemlen);
			if ($qtypos) {
				$quantity = substr($destroyed[$i], $qtypos + 6, $qtylen);
			}
			if ($locpos) {
				$location = substr($destroyed[$i], $locpos + 1, $loclen);
			}

			if ($quantity == "") {
				$quantity = 1;
			}

			$item = Item::lookup(trim($itemname));
			if (!$item || !$item->getID()) {
				$this->error('Item not found.', trim($itemname));
			}
			if ($location == 'In Container') {
				$location = 'Cargo';
			}

			if ($location) {
				$locid = $locations[$location];
			} else {
				$locid = 0;
			}
			$items[] = array('item' => $item, 'quantity' => $quantity,
				'location' => $locid);
			$i++;
		}

		return $items;
	}

	function error($message, $debugtext = null)
	{
		$this->error_[] = array($message, $debugtext);
	}

	function getError()
	{
		if (count($this->error_))
		{
			return $this->error_;
		}
		return false;
	}

	function preparse($set)
	{
		$translate = new Translate($set);
		$this->killmail_ = $translate->getTranslation($this->killmail_);
	}
	/**
	 * Return alliance from cached list or look up a new name.
	 *
	 * @param string $alliancename Alliance name to look up.
	 * @return Alliance Alliance object matching input name.
	 */
	private static function fetchAlliance($alliancename)
	{
		if(isset(self::$alliances[$alliancename]))
			$alliance = self::$alliances[$alliancename];
		else
		{
			$alliance = Alliance::add($alliancename);
			self::$alliances[$alliancename] = $alliance;
		}
		return $alliance;
	}
	/**
	 * Return corporation from cached list or look up a new name.
	 *
	 * @param string $corpname Alliance name to look up.
	 * @return Corporation Corporation object matching input name.
	 */
	private static function fetchCorp($corpname, $alliance = null, $timestamp = null)
	{
		if (isset(self::$corps[$corpname])) {
			if (!is_null($timestamp)
							&& self::$corps[$corpname]->isUpdatable($timestamp)) {
				self::$corps[$corpname]->add($corpname, $alliance, $timestamp, 0,
								self::$loadExternals);
			}
			$corp = self::$corps[$corpname];
		} else {
			if ($alliance == null) {
				$corp = Corporation::lookup($corpname);
				// If the corporation is new and the alliance unknown (structure)
				// fetch the alliance from the API.
				if (!$corp) {
					$corp = Corporation::add($corpname, Alliance::add("None"), $timestamp);
					if (!$corp->getExternalID()) {
						$corp = false;
					}
					else {
						$corp->execQuery();
					}
				}
			} else {
				$corp = Corporation::add($corpname, $alliance, $timestamp, 0, self::$loadExternals);
				self::$corps[$corpname] = $corp;
			}
		}
		return $corp;
	}
	/**
	 * Return pilot from cached list or look up a new name.
	 *
	 * @param string $pilotname Pilot name to look up.
	 * @return Pilot Pilot object matching input name.
	 */
	private static function fetchPilot($pilotname, $corp, $timestamp)
	{
		if (isset(self::$pilots[$pilotname])) {
			if (self::$pilots[$pilotname]->isUpdatable($timestamp)) {
				self::$pilots[$pilotname] = Pilot::add($pilotname, $corp, $timestamp,
								0, self::$loadExternals);
			}
			$pilot = self::$pilots[$pilotname];
		} else {
			$pilot = Pilot::add($pilotname, $corp, $timestamp,
							0, self::$loadExternals);
			self::$pilots[$pilotname] = $pilot;
		}
		return $pilot;
	}
	/**
	 *
	 * @param string $mail
	 * @return string
	 */
	public static function hashMail($mail = null)
	{
		if(is_null($mail)) return false;

		$mail = trim($mail);
		$mail = str_replace("\r\n", "\n", $mail);

		$involvedStart = strpos($mail, "Involved parties:");
		if($involvedStart === false) return false;
		$involvedStart = strpos($mail, "Name:", $involvedStart);

		$itemspos = strpos($mail, "\nDestroyed items:");
		if ($itemspos === false) $itemspos = strpos($mail, "\nDropped items:");

		if ($itemspos === false) $involved = substr($mail, $involvedStart);
		else $involved = substr($mail, $involvedStart, $itemspos - $involvedStart);
		$invList = explode("Name: ", $involved);
		$invListDamage = array();
		$invlistName = array();
		foreach($invList as $party)
		{
			if(!empty($party))
			{
				$pos = strrpos($party, ": ");
				$damage = @intval(substr($party, $pos+2));
				$invListDamage[] = $damage;
				$pos = strpos($party, "\n");
				$name = trim(substr($party,0,$pos));
				$invListName[] = $name;
			}
		}
		// Sort the involved list by damage done then alphabetically.
		array_multisort($invListDamage, SORT_DESC, SORT_NUMERIC, $invListName, SORT_ASC, SORT_STRING);

		$hashIn = substr($mail, 0, 16);
		$hashIn = str_replace('.', '-', $hashIn);

		$pos = strpos($mail, "Victim: ");
		if($pos ===false) $pos = strpos($mail, "Moon: ");
		$pos += 8;

		$posEnd = strpos($mail, "\n", $pos);
		if($pos === false || $posEnd === false) return false;
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$pos = strpos($mail, "Destroyed: ") + 11;
		$posEnd = strpos($mail, "\n", $pos);
		if($pos === false || $posEnd === false) return false;
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$pos = strpos($mail, "System: ") + 8;
		$posEnd = strpos($mail, "\n", $pos);
		if($pos === false || $posEnd === false) return false;
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$pos = strpos($mail, "Damage Taken: ") + 14;
		$posEnd = strpos($mail, "\n", $pos);
		if($pos === false || $posEnd === false) return false;
		$hashIn .= substr($mail, $pos, $posEnd - $pos);

		$hashIn .= implode(',', $invListName);
		$hashIn .= implode(',', $invListDamage);

		return md5($hashIn, true);
	}
	/**
	 * @return integer
	 */
	public function getDupeID()
	{
		return $this->dupeid_;
	}
	/**
	 * @return integer
	 */
	public function setTrust($trust)
	{
		$this->trust = intval($trust);
	}
}
//Currently maintained by FriedRoadKill
