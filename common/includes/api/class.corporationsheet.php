<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                   API Corporation Sheet - /corp/CorporationSheet.xml.aspx                    ****************
// **********************************************************************************************************************************************
// INCOMPLETE - MISSING CORP DIVISIONS AND WALLET DIVISIONS
class API_CorporationSheet
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
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
		$this->API_charID_ = $cid;
	}

	function setCorpID($corpid)
	{
		$this->API_corpID_ = $corpid;
	}

	function getAllianceID()
	{
		return $this->allianceID_;
	}

	function getAllianceName()
	{
		return $this->allianceName_;
	}

	function getCorporationID()
	{
		return $this->corporationID_;
	}

	function getCorporationName()
	{
		return $this->corporationName_;
	}

	function getTicker()
	{
		return $this->ticker_;
	}

	function getCeoID()
	{
		return $this->ceoID_;
	}

	function getCeoName()
	{
		return $this->ceoName_;
	}

	function getStationID()
	{
		return $this->stationID_;
	}

	function getStationName()
	{
		return $this->stationName_;
	}

	function getDescription()
	{
		return $this->description_;
	}

	function getUrl()
	{
		return $this->url_;
	}

	function getLogo()
	{
		return $this->logo_;
	}

	function getTaxRate()
	{
		return $this->taxRate_;
	}

	function getMemberCount()
	{
		return $this->memberCount_;
	}

	function getMemberLimit()
	{
		return $this->memberLimit_;
	}

	function getShares()
	{
		return $this->shares_;
	}

	function fetchXML()
	{
		// is a player feed - take details from logged in user
		if ($this->API_corpID_ != "")
		{
			$myKeyString = array();
			$myKeyString["corporationID"] = $this->API_corpID_;
			$this->CharName_ = $this->API_corpID_;
			$data = $this->loaddata($myKeyString);

		} elseif (user::get('usr_pilot_id')) {
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();

			$this->CharName_ = $usersname;

			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = DBFactory::getDBQuery();;
			$qry->execute($sql);
   		 	$row = $qry->getRow();

    		$pilot_id = $row['plt_id'];
    		$API_charID = $row['plt_externalid'];

    		if ( $pilot_id == 0 )
			{
        		return "Something went wrong with finding pilots external ID<br>";
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
			if (($this->API_userID_ != "") && ($this->API_apiKey_ != "") && ($this->API_charID_ != ""))
			{
				$myKeyString = array();
				$myKeyString["userID"] = $this->API_userID_;
				$myKeyString["apiKey"] = $this->API_apiKey_;
				$myKeyString["characterID"] = $this->API_charID_;

				$this->CharName_ = $this->API_charID_;
				$data = $this->loaddata($myKeyString);
			} else {
				return "You are not logged in and have not set API details.";
			}
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from ".API_SERVER."/CorporationSheet.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
	}

	function startElement($parser, $name, $attribs)
    {
		$this->characterDataValue = "";
    }

    function endElement($parser, $name)
    {
		// Details
		switch ($name)
        {
			case "ERROR":
				$this->html .= $this->characterDataValue;
                break;
			case "CORPORATIONID":
				$this->corporationID_ = $this->characterDataValue;
                break;
			case "CORPORATIONNAME":
				$this->corporationName_ = $this->characterDataValue;
                break;
			case "TICKER":
				$this->ticker_ = $this->characterDataValue;
                break;
			case "CEOID":
				$this->ceoID_ = $this->characterDataValue;
                break;
			case "CEONAME":
				$this->ceoName_ = $this->characterDataValue;
                break;
			case "STATIONID":
				$this->stationID_ = $this->characterDataValue;
                break;
			case "STATIONNAME":
				$this->stationName_ = $this->characterDataValue;
                break;
			case "DESCRIPTION":
				$this->description_ = $this->characterDataValue;
                break;
			case "URL":
				$this->url_ = $this->characterDataValue;
                break;
			case "ALLIANCEID":
				$this->allianceID_ = $this->characterDataValue;
                break;
			case "ALLIANCENAME":
				$this->allianceName_ = $this->characterDataValue;
                break;
			case "TAXRATE":
				$this->taxRate_ = $this->characterDataValue;
                break;
			case "MEMBERCOUNT":
				$this->memberCount_ = $this->characterDataValue;
                break;
			case "MEMBERLIMIT":
				$this->memberLimit_ = $this->characterDataValue;
                break;
			case "SHARES":
				$this->shares_ = $this->characterDataValue;
                break;

			case "GRAPHICID":
				$this->logo_["graphicID"] = $this->characterDataValue;
                break;
			case "SHAPE1":
				$this->logo_["shape1"] = $this->characterDataValue;
                break;
			case "SHAPE2":
				$this->logo_["shape2"] = $this->characterDataValue;
                break;
			case "SHAPE3":
				$this->logo_["shape3"] = $this->characterDataValue;
                break;
			case "COLOR1":
				$this->logo_["colour1"] = $this->characterDataValue;
                break;
			case "COLOR2":
				$this->logo_["colour2"] = $this->characterDataValue;
                break;
			case "COLOR3":
				$this->logo_["colour3"] = $this->characterDataValue;
                break;
		}

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			ApiCache::set( $this->CharName_ . '_CorporationSheet' , $this->characterDataValue);
		}
    }

    function characterData($parser, $data)
    {
		$this->characterDataValue .= $data;
    }

	function loaddata($keystring)
    {
		$configvalue = $this->CharName_ . '_CorporationSheet';

		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');

		$url = "https://".API_SERVER."/corp/CorporationSheet.xml.aspx";// . $keystring;

        $path = '/corp/CorporationSheet.xml.aspx';

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
