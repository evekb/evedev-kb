<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

/**
 * API Character Sheet - char/CharacterSheet
 * Incomplete - Does not read Certificates or Roles
 * @package EDK
 */
class API_CharacterSheet
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getSkills()
	{
		return $this->Skills_;
	}

	// array 1-5 based on implant slot position. 6-10 don't seem to appear, presumably because Hardwirings do not affect skill training.
	function getImplants()
	{
		return $this->Implant_;
	}

	function getIntelligence()
	{
		return $this->Intelligence_;
	}

	function getMemory()
	{
		return $this->Memory_;
	}

	function getCharisma()
	{
		return $this->Charisma_;
	}

	function getPerception()
	{
		return $this->Perception_;
	}

	function getWillpower()
	{
		return $this->Willpower_;
	}
	function getCharacterID()
	{
		return $this->CharacterID_;
	}
	function getName()
	{
		return $this->Name_;
	}
	function getRace()
	{
		return $this->Race_;
	}
	function getBloodLine()
	{
		return $this->BloodLine_;
	}
	function getGender()
	{
		return $this->Gender_;
	}
	function getCorporationName()
	{
		return $this->CorporationName_;
	}
	function getCorporationID()
	{
		return $this->CorporationID_;
	}
	function getCloneName()
	{
		return $this->CloneName_;
	}
	function getCloneSkillPoints()
	{
		return $this->CloneSkillPoints_;
	}
	function getBalance()
	{
		return $this->Balance_;
	}
	function fetchXML()
	{
		// is a player feed - take details from logged in user
		if (user::get('usr_pilot_id'))
    	{
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();

			$this->CharName_ = $usersname;  // $this->CharName_ is used later for config key value for caching

			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = DBFactory::getDBQuery();;
			$qry->execute($sql);
   		 	$row = $qry->getRow();

    		$pilot_id = $row['plt_id'];
    		$API_charID = $row['plt_externalid'];

    		if ( $pilot_id == 0 )
			{
        		return "Something went wrong with finiding pilots external ID<br>";
    		}

			$newsql = 'SELECT userID , apiKey FROM kb3_api_user WHERE charID = "' . $API_charID . '"';
			$qry->execute($newsql);
    		$userrow = $qry->getRow();

			$API_userID = $userrow['userID'];
			$API_apiKey = $userrow['apiKey'];

			$myKeyString = array();
			$myKeyString["userID"] = $API_userID;
			$myKeyString["apiKey"] = $API_apiKey;
			$myKeyString["characterID"] = $API_charID;

			$data = $this->loaddata($myKeyString);
		} else {
			return "You are not logged in.";
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from ".API_SERVER."/CharacterSheet.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

		return $this->html;
	}

	function startElement($parser, $name, $attribs)
    {
		if ($name == "ROW")
        {
			global $tempdata;

			if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
						case "TYPEID":
							$tempdata['typeID'] = $v;
							$tempdata['SkillName'] = API_Helpers::getTypeIDname($v);
							$tempdata['GroupID'] = API_Helpers::getgroupID($v);
							$tempdata['GroupName'] = API_Helpers::getgroupIDname($tempdata['GroupID']);
							$tempdata['Rank'] = API_Helpers::gettypeIDrank($v);
                            break;
						case "SKILLPOINTS":
							$tempdata['SkillPoints'] = $v;
                            break;
						case "LEVEL":
							$tempdata['Level'] = $v;

							$this->Skills_[] = $tempdata;

							$tempdata = array();
							unset($tempdata);
                            break;
						case "UNPUBLISHED": // unused skill eg. Black Market Trading
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
		// Player Details
		if ($name == "CHARACTERID")
			$this->CharacterID_ = $this->characterDataValue;
		if ($name == "NAME")
			$this->Name_ = $this->characterDataValue;
		if ($name == "RACE")
			$this->Race_ = $this->characterDataValue;
		if ($name == "BLOODLINE")
			$this->BloodLine_ = $this->characterDataValue;
		if ($name == "GENDER")
			$this->Gender_ = $this->characterDataValue;
		if ($name == "CORPORATIONNAME")
			$this->CorporationName_ = $this->characterDataValue;
		if ($name == "CORPORATIONID")
			$this->CorporationID_ = $this->characterDataValue;
		if ($name == "CLONENAME")
			$this->CloneName_ = $this->characterDataValue;
		if ($name == "CLONESKILLPOINTS")
			$this->CloneSkillPoints_ = $this->characterDataValue;
		if ($name == "BALANCE")
			$this->Balance_ = $this->characterDataValue;

		// Augmentations
		if ($name == "AUGMENTATORNAME")
			$tempaug['Name'] = $this->characterDataValue;
		if ($name == "AUGMENTATORVALUE")
			$tempaug['Value'] = $this->characterDataValue;

		if ($name == "PERCEPTIONBONUS")
		{
			$this->Implant_[1] = $tempaug;

			$tempaug = array();
			unset($tempaug);
		}
		if ($name == "MEMORYBONUS")
		{
			$this->Implant_[2] = $tempaug;

			$tempaug = array();
			unset($tempaug);
		}
		if ($name == "WILLPOWERBONUS")
		{
			$this->Implant_[3] = $tempaug;

			$tempaug = array();
			unset($tempaug);
		}
		if ($name == "INTELLIGENCEBONUS")
		{
			$this->Implant_[4] = $tempaug;

			$tempaug = array();
			unset($tempaug);
		}
		if ($name == "CHARISMABONUS")
		{
			$this->Implant_[5] = $tempaug;

			$tempaug = array();
			unset($tempaug);
		}

		// Attributes
		if ($name == "INTELLIGENCE")
			$this->Intelligence_ = $this->characterDataValue;
		if ($name == "MEMORY")
			$this->Memory_ = $this->characterDataValue;
		if ($name == "CHARISMA")
			$this->Charisma_ = $this->characterDataValue;
		if ($name == "PERCEPTION")
			$this->Perception_ = $this->characterDataValue;
		if ($name == "WILLPOWER")
			$this->Willpower_ = $this->characterDataValue;

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

	function loaddata($keystring)
    {
		$configvalue = $this->CharName_ . '_CharacterSheet';

		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');

        $url = API_SERVER."/char/CharacterSheet.xml.aspx" . $keystring;

        $path = '/char/CharacterSheet.xml.aspx';

		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($CachedTime == "")
    	{
        	$CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}

		if (is_file(KB_CACHEDIR.'/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;

		// if API_UseCache = 1 (off) then don't use cache
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )
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

			// Save the file if we're caching (0 = true in Thunks world)
			if ($UseCaching == 0)
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
