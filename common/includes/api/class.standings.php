<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

/**
 * API Standings - /corp & char/Standings.xml.aspx
 * @package EDK
 */
class API_Standings
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	// boolean value - sets between char/corp
	function isUser($value)
	{
		$this->isUser_ = $value;
	}

	function setAPIKey($key)
	{
		$this->API_apiKey_ = $key;
	}

	function setUserID($uid)
	{
		$this->API_userID_ = $uid;
	}

	function setCharacterID($cid)
	{
		$this->API_characterID_ = $cid;
	}

	function getCharacters()
	{
		return $this->Characters_;
	}
	function getCorporations()
	{
		return $this->Corporations_;
	}
	function getAlliances()
	{
		return $this->Alliances_;
	}
	function getAgents()
	{
		return $this->Agents_;
	}
	function getNPCCorporations()
	{
		return $this->NPCCorporations_;
	}
	function getFactions()
	{
		return $this->Factions_;
	}
	function getAllianceCorporations()
	{
		return $this->AllianceCorporations_;
	}
	function getAllianceAlliances()
	{
		return $this->AllianceAlliances_;
	}

	function fetchXML()
	{
		$this->isAllianceStandings_ = false;
		$this->isCorporationStandings_ = false;

		if ($this->isUser_)
		{
			// is a player feed - take details from logged in user
			if (user::get('usr_pilot_id'))
    		{
				$myEveCharAPI = new API_CharacterSheet();
				$this->html .= $myEveCharAPI->fetchXML();

				$skills = $myEveCharAPI->getSkills();

				$this->connections_ = 0;
				$this->diplomacy_ = 0;

				foreach ((array)$skills as $myTempData)
				{
					if ($myTempData['typeID'] == "3359")
						$this->connections_ = $myTempData['Level'];
					if ($myTempData['typeID'] == "3357")
						$this->diplomacy_ = $myTempData['Level'];
				}

				$myKeyString = array();
				$myKeyString["userID"] = $this->API_userID_;
				$myKeyString["apiKey"] = $this->API_apiKey_;
				$myKeyString["characterID"] = $this->API_characterID_;

				$data = $this->loaddata($myKeyString, "char");
			} else {
				return "You are not logged in.";
			}

		} else {
			// is a corp feed
			$myKeyString = "userID=" . $this->API_userID_ . "&apiKey=" . $this->API_apiKey_ . "&characterID=" . $this->API_characterID_;
			$data = $this->loaddata($myKeyString, "corp");
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from ".API_SERVER."/Standings.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

		// sort the arrays (in descending order of standing)
		$this->Factions_ = $this->mysortarray($this->Factions_);
		$this->Characters_ = $this->mysortarray($this->Characters_);
		$this->Corporations_ = $this->mysortarray($this->Corporations_);
		$this->Alliances_ = $this->mysortarray($this->Alliances_);
		$this->Agents_ = $this->mysortarray($this->Agents_);
		$this->NPCCorporations_ = $this->mysortarray($this->NPCCorporations_);
		$this->AllianceCorporations_ = $this->mysortarray($this->AllianceCorporations_);
		$this->AllianceAlliances_ = $this->mysortarray($this->AllianceAlliances_);

		return $this->html;
	}

	function mysortarray($arraydata)
	{
		if (count($arraydata) != 0 )
		{
			foreach ((array)$arraydata as $key => $row) {
    			$standing[$key]  = $row['Standing'];
    			$name[$key] = $row['Name'];
				$id[$key] = $row['ID'];
			}

			array_multisort($standing, SORT_DESC, $name, SORT_ASC, $id, SORT_ASC, $arraydata);

			$standing = array();
			unset($standing);
			$name = array();
			unset($name);
			$id = array();
			unset($id);

			return $arraydata;
		}
	}

	function startElement($parser, $name, $attribs)
    {

		if ($name == "CORPORATIONSTANDINGS")
		{
			$this->isAllianceStandings_ = false;
			$this->isCorporationStandings_ = true;
		}
		if ($name == "STANDINGSTO")
		{
			$this->StandingsTo_ = true; // used to determine if/when to apply diplomacy/connections bonus
		}
		if ($name == "STANDINGSFROM")
		{
			$this->StandingsTo_ = false;
		}
		if ($name == "ALLIANCESTANDINGS")
		{
			$this->isAllianceStandings_ = true;
			$this->isCorporationStandings_ = false;
		}

		if ($name == "ROWSET") // In this If statement we set booleans to ultimately determine where the data in the next If statement is stored
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "NAME":
							switch ($v)
                    		{
								// bitwise or numeric flag would be better and more concise - but fuck it!
								case "characters": // can only be personal/corp
									$this->isCharacters_ = true;
									$this->isCorporations_ = false;
									$this->isAlliances_ = false;
									$this->isAgents_ = false;
									$this->isNPCCorporations_ = false;
									$this->isFactions_ = false;
									$this->isAllianceCorporations_ = false;
									$this->isAllianceAlliances_ = false;
									break;
								case "corporations": // can be either personal/corp or alliance
									$this->isCharacters_ = false;
									$this->isCorporations_ = false;
									$this->isAlliances_ = false;
									$this->isAgents_ = false;
									$this->isNPCCorporations_ = false;
									$this->isFactions_ = false;
									$this->isAllianceCorporations_ = false;
									$this->isAllianceAlliances_ = false;

									if (!$this->isAllianceStandings_) // then it is personal/corp
									{
										$this->isCorporations_ = true;
									} else { // then it is alliance
										$this->isAllianceCorporations_ = true;
									}
									break;
								case "alliances": // can be either personal/corp or alliance
									$this->isCharacters_ = false;
									$this->isCorporations_ = false;
									$this->isAlliances_ = false;
									$this->isAgents_ = false;
									$this->isNPCCorporations_ = false;
									$this->isFactions_ = false;
									$this->isAllianceCorporations_ = false;
									$this->isAllianceAlliances_ = false;

									if (!$this->isAllianceStandings_) // then it is personal/corp
									{
										$this->isAlliances_ = true;
									} else { // then it is alliance
										$this->isAllianceAlliances_ = true;
									}
									break;
								case "agents": // can only be personal/corp
									$this->isCharacters_ = false;
									$this->isCorporations_ = false;
									$this->isAlliances_ = false;
									$this->isAgents_ = true;
									$this->isNPCCorporations_ = false;
									$this->isFactions_ = false;
									$this->isAllianceCorporations_ = false;
									$this->isAllianceAlliances_ = false;
									break;
								case "NPCCorporations": // can only be personal/corp
									$this->isCharacters_ = false;
									$this->isCorporations_ = false;
									$this->isAlliances_ = false;
									$this->isAgents_ = false;
									$this->isNPCCorporations_ = true;
									$this->isFactions_ = false;
									$this->isAllianceCorporations_ = false;
									$this->isAllianceAlliances_ = false;
									break;
								case "factions": // can only be personal/corp
									$this->isCharacters_ = false;
									$this->isCorporations_ = false;
									$this->isAlliances_ = false;
									$this->isAgents_ = false;
									$this->isNPCCorporations_ = false;
									$this->isFactions_ = true;
									$this->isAllianceCorporations_ = false;
									$this->isAllianceAlliances_ = false;
									break;
							}
                            break;
					}
				}
			}
		}

		if ($name == "ROW")
        {
			global $tempdata;

			if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
						case "TOID":
							$tempdata['ID'] = $v;
                            break;
						case "FROMID":
							$tempdata['ID'] = $v;
                            break;
						case "TONAME":
							$tempdata['Name'] = $v;
                            break;
						case "FROMNAME":
							$tempdata['Name'] = $v;
                            break;
						case "STANDING":
							// use flags determined in previous If... to load the correct array[]
							if ($this->isUser_ && !$this->StandingsTo_)
							{
								// set standings bonus where applicable
								// calculate bonus
								if ($v >= 0) // Connections
									$tempdata['Standing'] = number_format($v + ((10 - $v) * (0.04 * $this->connections_)), 2, '.', '');
								else  // Diplomacy
									$tempdata['Standing'] = number_format($v + ((10 - $v) * (0.04 * $this->diplomacy_)), 2, '.', '');
							} else {
								$tempdata['Standing'] = $v;
							}

							// check that 'Name' isn't empty as this means the value was reset
							if ($tempdata['Name'] != "")
							{
								if ($this->isCharacters_) {
									$this->Characters_[] = $tempdata;
								} elseif ($this->isCorporations_) {
									$this->Corporations_[] = $tempdata;
								} elseif ($this->isAlliances_ ) {
									$this->Alliances_[] = $tempdata;
								} elseif ($this->isAgents_) {
									$this->Agents_[] = $tempdata;
								} elseif ($this->isNPCCorporations_) {
									$this->NPCCorporations_[] = $tempdata;
								} elseif ($this->isFactions_) {
									$this->Factions_[] = $tempdata;
								} elseif ($this->isAllianceCorporations_) {
									$this->AllianceCorporations_[] = $tempdata;
								} elseif ($this->isAllianceAlliances_) {
									$this->AllianceAlliances_[] = $tempdata;
								}
							}

							$tempdata = array();
							unset($tempdata);
                            break;
					}
				}
			}
		}
    }

    function endElement($parser, $name)
    {

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $this->characterDataValue;
		}
    }

    function characterData($parser, $data)
    {
		$this->characterDataValue = $data;
    }

	function loaddata($keystring, $typestring)
    {
		$configvalue = $this->API_characterID_ . '_Standings';

		$UseCaching = config::get('API_UseCache');

        $url = API_SERVER."/" . $typestring . "/Standings.xml.aspx" . $keystring;
        $path = "/" . $typestring . "/Standings.xml.aspx";

		if (is_file(KB_CACHEDIR.'/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;


		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )// if API_UseCache = 1 (off) then don't use cache
    	{
			$http = new http_request($url);
			$http->set_useragent("PHPApi");

			foreach($keystring as $key => $val) $http->set_postform($key, $val);
			$contents = $http->get_content();

			$start = strpos($contents, "?>");
			if ($start !== FALSE)
			{
				$contents = substr($contents, $start + strlen("\r\n\r\n"));
			}

			if ($UseCaching == 0) // Save the file if we're caching (0 = true in Thunks world)
			{
				$file = fopen(KB_CACHEDIR.'/api/'.$configvalue.'.xml', 'w+');
				fwrite($file, $contents);
				fclose($file);
				@chmod(KB_CACHEDIR.'/api/'.$configvalue.'.xml',0666);
			}
        	
		} else {
			// re-use cached XML
			if ($fp = @fopen(KB_CACHEDIR.'/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(KB_CACHEDIR.'/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";
    		}
		}
        return $contents;
    }
}
