<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                       API StarbaseDetail - /corp/StarbaseDetail.xml.aspx                          ****************
// **********************************************************************************************************************************************
class API_StarbaseDetail
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

	function setitemID($itemID)
	{
		$this->API_itemID_ = $itemID;
	}
	function getFuel()
	{
		return $this->Fuel_;
	}
	function getState()
	{
		return $this->State_;
	}
	function getstateTimestamp()
	{
		return $this->stateTimestamp_;
	}
	function getonlineTimestamp()
	{
		return $this->onlineTimestamp_;
	}
	function getusageFlags()
	{
		return $this->usageFlags_;
	}
	function getdeployFlags()
	{
		return $this->deployFlags_;
	}
	function getallowCorporationMembers()
	{
		return $this->allowCorporationMembers_;
	}
	function getallowAllianceMembers()
	{
		return $this->allowAllianceMembers_;
	}
	function getclaimSovereignty()
	{
		return $this->claimSovereignty_;
	}
	function getonStandingDrop()
	{
		return $this->onStandingDrop_;
	}
	function getonStatusDrop()
	{
		return $this->onStatusDrop_;
	}
	function getonAggression()
	{
		return $this->onAggression_;
	}
	function getonCorporationWar()
	{
		return $this->onCorporationWar_;
	}


	function fetchXML()
	{
		// is a player feed - take details from logged in user
		if (user::get('usr_pilot_id'))
    	{
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
			$myKeyString["itemID"] = $this->API_itemID_;

			$data = $this->loaddata($myKeyString);
		} else {
			if (($this->API_userID_ != "") && ($this->API_apiKey_ != "") && ($this->API_charID_ != "") && ($this->API_itemID_ != ""))
			{
				$myKeyString = array();
				$myKeyString["userID"] = $this->API_userID_;
				$myKeyString["apiKey"] = $this->API_apiKey_;
				$myKeyString["characterID"] = $this->API_charID_;
				$myKeyString["itemID"] = $this->API_itemID_;
				
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
            return "<i>Error getting XML data from ".API_SERVER."/StarbaseDetail.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
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
							$fueldata['typeID'] = $v;
							$fueldata['typeName'] = API_Helpers::gettypeIDname($v);
                            break;
						case "QUANTITY":
							$fueldata['quantity'] = $v;
							$this->Fuel_[] = $fueldata;

							$fueldata = array();
							unset($fueldata);
                            break;
					}
				}
			}
		}

		if ($name == "ONSTANDINGDROP")
        {
			if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
						case "ENABLED":
							$this->onStandingDrop_['enabled'] = $v;
                            break;
						case "STANDING":
							$this->onStandingDrop_['standing'] = $v;
                            break;
					}
				}
			}
		}

		if ($name == "ONSTATUSDROP")
        {
			if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
						case "ENABLED":
							$this->onStatusDrop_['enabled'] = $v;
                            break;
						case "STANDING":
							$this->onStatusDrop_['standing'] = $v;
                            break;
					}
				}
			}
		}

		if ($name == "ONAGGRESSION")
        {
			if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
						case "ENABLED":
							$this->onAggression_['enabled'] = $v;
                            break;
					}
				}
			}
		}

		if ($name == "ONCORPORATIONWAR")
        {
			if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
						case "ENABLED":
							$this->onCorporationWar_['enabled'] = $v;
                            break;
					}
				}
			}
		}
    }

    function endElement($parser, $name)
    {
		// Details
		if ($name == "ERROR")
			$this->html .= $this->characterDataValue;

		if ($name == "STATE")
			$this->State_ .= $this->characterDataValue;
		if ($name == "STATETIMESTAMP")
			$this->stateTimestamp_ .= $this->characterDataValue;
		if ($name == "ONLINETIMESTAMP")
			$this->onlineTimestamp_ .= $this->characterDataValue;

		// General Settings
		if ($name == "USAGEFLAGS")
			$this->usageFlags_ .= $this->characterDataValue;
		if ($name == "DEPLOYFLAGS")
			$this->deployFlags_ .= $this->characterDataValue;
		if ($name == "ALLOWCORPORATIONMEMBERS")
			$this->allowCorporationMembers_ .= $this->characterDataValue;
		if ($name == "ALLOWALLIANCEMEMBERS")
			$this->allowAllianceMembers_ .= $this->characterDataValue;
		if ($name == "CLAIMSOVEREIGNTY")
			$this->claimSovereignty_ .= $this->characterDataValue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			ApiCache::set( $this->API_itemID_ . '_StarbaseDetail' , $this->characterDataValue);
		}
    }

    function characterData($parser, $data)
    {
		$this->characterDataValue = $data;
    }

	function loaddata($keystring)
    {
		$configvalue = $this->API_itemID_ . '_StarbaseDetail';

		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');

        $url = "https://".API_SERVER."/corp/StarbaseDetail.xml.aspx";

        $path = '/corp/StarbaseDetail.xml.aspx';

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
