<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

require_once('class.eveapi.php'); 
// **********************************************************************************************************************************************
// ****************                                       API StarbaseList - /corp/StarbaseList.xml.aspx                         ****************
// **********************************************************************************************************************************************
class API_StarbaseList
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

	function getStarbases()
	{
		return $this->Starbase_;
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
        		return "Something went wrong with finiding pilots external ID<br>";
    		}

			$newsql = 'SELECT userID , apiKey FROM kb3_api_user WHERE charID = "' . $API_charID . '"';
			$qry->execute($newsql);
    		$userrow = $qry->getRow();

			$API_userID = $userrow['userID'];
			$API_apiKey = $userrow['apiKey'];

			$myKeyString = "userID=" . $API_userID . "&apiKey=" . $API_apiKey . "&characterID=" . $API_charID;

			$data = $this->loaddata($myKeyString);
		} else {
			if ($this->API_userID_ != "" && $this->API_apiKey_ != "" && $this->API_charID_ != "")
			{
				$myKeyString = "userID=" . $this->API_userID_ . "&apiKey=" . $this->API_apiKey_ . "&characterID=" . $this->API_charID_;
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
            return "<i>Error getting XML data from api.eve-online.com/StarbaseList.xml.aspx  </i><br><br>";

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
						case "ITEMID":
							$tempdata['itemID'] = $v;
                            break;
						case "TYPEID":
							$tempdata['typeID'] = $v;
							$tempdata['typeName'] = gettypeIDname($v);
                            break;
						case "LOCATIONID":
							$tempdata['locationID'] = $v;
							$sql = 'select sys.sys_name, sys.sys_sec from kb3_systems sys where sys.sys_eve_id = '.$v;

                        	$qry = DBFactory::getDBQuery();;
                        	$qry->execute($sql);
                        	$row = $qry->getRow();

                        	$tempdata['locationName'] = $row['sys_name'];
                        	$mysec = $row['sys_sec'];
                        	if ($mysec <= 0)
                            	$tempdata['locationSec'] = number_format(0.0, 1);
                        	else
                            	$tempdata['locationSec'] = number_format(round($mysec, 1), 1);
                            break;
						case "MOONID":
							$tempdata['moonID'] = $v;
							$tempmoon = getMoonName($v);

							if ($tempmoon == "")
							{
								// Use API IDtoName to get Moon Name.
								$this->myIDName = new API_IDtoName();
								$this->myIDName->clear();
								$this->myIDName->setIDs($v);
								$this->Output_ .= $this->myIDName->fetchXML();
								$myNames = $this->myIDName->getIDData();
								$tempdata['moonName'] = $myNames[0]['name'];
							} else {
								$tempdata['moonName'] = $tempmoon;
   							}
                            break;
						case "STATE":
							$tempdata['state'] = $v;
                            break;
						case "STATETIMESTAMP":
							$tempdata['stateTimestamp'] = $v;
                            break;
						case "ONLINETIMESTAMP":
							$tempdata['onlineTimestamp'] = $v;
							$this->Starbase_[] = $tempdata;

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
		// Details
		if ($name == "ERROR")
			$this->html .= $this->characterDataValue;
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			ApiCache::set( $this->CharName_ . '_StarbaseList' , $this->characterDataValue);
		}
    }

    function characterData($parser, $data)
    {
		$this->characterDataValue = $data;
    }

	function loaddata($keystring)
    {
		$configvalue = $this->CharName_ . '_StarbaseList';

		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');

        $url = "http://api.eve-online.com/corp/StarbaseList.xml.aspx" . $keystring;

        $path = '/corp/StarbaseList.xml.aspx';

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
        	$fp = @fsockopen("api.eve-online.com", 80);

        	if (!$fp)
        	{
            	$this->Output_ .= "Could not connect to API URL";
        	} else {
           	 	// request the xml
            	fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
            	fputs ($fp, "Host: api.eve-online.com\r\n");
            	fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            	fputs ($fp, "User-Agent: PHPApi\r\n");
            	fputs ($fp, "Content-Length: " . strlen($keystring) . "\r\n");
            	fputs ($fp, "Connection: close\r\n\r\n");
            	fputs ($fp, $keystring."\r\n");
				stream_set_timeout($fp, 10);

           	 	// retrieve contents
            	$contents = "";
            	while (!feof($fp))
            	{
                	$contents .= fgets($fp);
            	}

            	// close connection
            	fclose($fp);

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
