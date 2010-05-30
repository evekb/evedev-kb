<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */
define ("APIVERSION", "V3.3");

//
// Eve-Dev API Killmail parser by Captain Thunk! (ISK donations are all gratefully received)
//

// **********************************************************************************************************************************************
// ****************                                   API Char list - /account/Characters.xml.aspx                               ****************
// **********************************************************************************************************************************************

class APIChar
{
    function fetchChars($apistring)
    {
        $data = $this->loaddata($apistring);

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/account/Characters.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

        // add any characters not already in the kb
        $numchars = count($this->chars_);
        for ( $x = 0; $x < $numchars; $x++ )
        {
            // check if chars eveid exists in kb
            $sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $this->chars_[$x]['Name'] . '"';

            $qry = DBFactory::getDBQuery();;
            $qry->execute($sql);
            if ($qry->recordCount() != 0)
            {
				// pilot is in kb db, check he has his char id
                $row = $qry->getRow();

                $pilot_id = $row['plt_id'];
                $pilot_external_id = $row['plt_externalid'];

                if ( $pilot_external_id == 0 && $pilot_id != 0 )
                {
					// update DB with ID
                    $qry->execute("update kb3_pilots set plt_externalid = " . intval($this->chars_[$x]['charID']) . "
                                     where plt_id = " . $pilot_id);
                }
            } else {
                // pilot is not in DB

                // Set Corp
				$pilotscorp = new Corporation();
				$pilotscorp->lookup($this->chars_[$x]['corpName']);
                // Check Corp was set, if not, add the Corp
                if ( !$pilotscorp->getID() )
                {
                    $ialliance = new Alliance();
                    $ialliance->add('NONE');
                    $pilotscorp->add($this->chars_[$x]['corpName'], $ialliance, gmdate("Y-m-d H:i:s"));
                }
                $ipilot = new Pilot();
                $ipilot->add($this->chars_[$x]['Name'], $pilotscorp, gmdate("Y-m-d H:i:s"));
				$ipilot->setCharacterID(intval($this->chars_[$x]['charID']));
            }
        }

        return $this->chars_;
    }

    function startElement($parser, $name, $attribs)
    {
		global $character;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "NAME":
                            $character['Name'] = $v;
                            break;
                        case "CORPORATIONNAME":
                            $character['corpName'] = $v;
                            break;
                        case "CHARACTERID":
                            $character['charID'] = $v;
                            break;
                        case "CORPORATIONID":
                            $character['corpID'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $character;

        if ($name == "ROW")
		{
			$this->chars_[] = $character;
			$character = array();
			unset($character);
		}
    }

    function characterData($parser, $data)
    {
        // nothing
    }

    function loaddata($apistring)
    {
        $path = '/account/Characters.xml.aspx';
        $fp = @fsockopen("api.eve-online.com", 80);

        if (!$fp)
        {
            echo "Error", "Could not connect to API URL<br>";
        } else {
            // request the xml
            fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
            fputs ($fp, "Host: api.eve-online.com\r\n");
            fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            fputs ($fp, "User-Agent: PHPApi\r\n");
            fputs ($fp, "Content-Length: " . strlen($apistring) . "\r\n");
            fputs ($fp, "Connection: close\r\n\r\n");
            fputs ($fp, $apistring."\r\n");
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
            if ($start != false)
            {
                $contents = substr($contents, $start + strlen("\r\n\r\n"));
            }
        }
        return $contents;
    }
}

// **********************************************************************************************************************************************
// ****************                                 API Alliance list - /eve/AllianceList.xml.aspx                               ****************
// **********************************************************************************************************************************************

class AllianceAPI
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}


	function initXML()
	{
		global $myalliancelist;

		$data = LoadGlobalData('/eve/AllianceList.xml.aspx');

    	$xml_parser = xml_parser_create();
    	xml_set_object ( $xml_parser, $this );
    	xml_set_element_handler($xml_parser, "startElement", "endElement");
    	xml_set_character_data_handler ( $xml_parser, 'characterData' );

    	if (!xml_parse($xml_parser, $data, true))
       	 	return false;

   	 	xml_parser_free($xml_parser);
		return true;
	}

    function fetchalliances($overide=false)
    {
        global $myalliancelist;

		if (!isset($this->alliances_))
			$this->initXML($overide);

        return $myalliancelist;
    }

    function startElement($parser, $name, $attribs)
    {
        global $myalliancelist, $alliancedetail, $membercorps, $membercorp, $iscorpsection;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "NAME":
                            $alliancedetail['allianceName'] = $v;
                            break;
						case "SHORTNAME":
                            $alliancedetail['shortName'] = $v;
                            break;
                        case "ALLIANCEID":
                            $alliancedetail['allianceID'] = $v;
                            break;
						case "EXECUTORCORPID":
                            $alliancedetail['executorCorpID'] = $v;
                            break;
						case "MEMBERCOUNT":
                            $alliancedetail['memberCount'] = $v;
                            break;
						case "STARTDATE":
							if (!$iscorpsection)
							{
                            	$alliancedetail['startDate'] = $v;
							} else {
								$membercorp['startDate'] = $v;
								$membercorps[] = $membercorp;
							}
                            break;
						case "CORPORATIONID":
                            $membercorp['corporationID'] = $v;
							$iscorpsection = true;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
        global $myalliancelist, $alliancedetail, $membercorps, $membercorp, $iscorpsection;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_alliancelist') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else {
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_eve_AllianceList' , $this->CachedUntil_);
		}

        switch ($name)
        {
            case "ROWSET":
                if ($alliancedetail['allianceName'] != "" && $alliancedetail['allianceID'] != "0")
                {
                    $myalliancelist['Name'][] = $alliancedetail['allianceName'];
                    $myalliancelist['allianceID'][] = $alliancedetail['allianceID'];
                }
				$alliancedetail['memberCorps'] = $membercorps;
				$this->alliances_[] = $alliancedetail;

				$alliancedetail['allianceName'] = "";
				$alliancedetail['shortName'] = "";
				$alliancedetail['allianceID'] = "";
				$alliancedetail['executorCorpID'] = "";
				$alliancedetail['memberCount'] = "";
				$alliancedetail['startDate'] = "";
				$alliancedetail['memberCorps'] = array();
				$membercorps = array();
				$membercorp = array();
				unset($alliancedetail['memberCorps']);
				unset($membercorps);
				unset($membercorp);
				$iscorpsection = false;
                break;
        }
    }

    function characterData($parser, $data)
    {
		global $tempvalue;

		$tempvalue = $data;
    }

	function updatealliancetable()
    {
        if (!isset($this->alliances_))
            $this->initXML();

        if (!isset($this->alliances_))
            return false;

        $qry = DBFactory::getDBQuery();;
        $qry->execute("DROP TABLE IF EXISTS `kb3_all_corp`;");
        $qry->execute("CREATE TABLE kb3_all_corp (
              all_id bigint(3) unsigned default '0',
              corp_id bigint(3) unsigned default '0',
              all_name varchar(200) default NULL
            ) ");

        $alliances = $this->alliances_;

        foreach ($alliances as $arraykey => $arrayvalue)
        {
            $tempally = $arrayvalue;

            foreach ($tempally as $key => $value)
            {
                switch ($key)
                {
                    case "allianceName":
                        $allyname = $value;
                        break;
                    case "allianceID":
                        $allyid = $value;
                        break;
                    case "memberCorps":
                        $allycorps = $value;
                        $q='';
                        foreach ($allycorps as $corpkey => $corpvalue)
                        {
                            $tempcorp = $corpvalue;
                            foreach ($tempcorp as $tempkey => $tempvalue)
                            {
                                switch ($tempkey)
                                {
                                    case "corporationID":
                                        $q.="(".$allyid.",".$tempvalue.",'".slashfix($allyname)."'),";
                                        break;
                                }
                            }
                        }
                        if (strlen($q)>0)
                        	$qry->execute("INSERT INTO kb3_all_corp values ".substr($q,0,strlen($q)-1));
                        break;
                }
            }
        }
        return true;
    }

	function LocateAlliance($name)
	{
		if (!isset($this->alliances_))
            $this->initXML();

        if (!isset($this->alliances_))
            return false;

		$alliances = $this->alliances_;

        foreach ($alliances as $arraykey => $arrayvalue)
        {
            $tempally = $arrayvalue;
			if($tempally['allianceName'] == $name) return $tempally;

//            foreach ($tempally as $key => $value)
//            {
//                switch ($key)
//                {
//                    case "allianceName":
//                        //return $tempally;
//						if ( $value == $name )
//						{
//							return $tempally;
//						}
//                        break;
//                }
//            }
        }
		return false;
	}

	function LocateAllianceID($id)
	{
		if (!isset($this->alliances_))
            $this->initXML();

        if (!isset($this->alliances_))
            return false;

		$alliances = $this->alliances_;

        foreach ($alliances as $arraykey => $arrayvalue)
        {
            $tempally = $arrayvalue;
			if($tempally['allianceID'] == $id) return $tempally;
//            foreach ($tempally as $key => $value)
//            {
//                switch ($key)
//                {
//                    case "allianceID":
//                        //return $tempally;
//						if ( $value == $id )
//						{
//							return $tempally;
//						}
//                        break;
//                }
//            }
        }
		return false;
	}

	function UpdateAlliances($andCorps = false)
	{
		if (!isset($this->alliances_))
            $this->initXML();

        if (!isset($this->alliances_))
            return false;

		if ($andCorps)
		{
			// Remove every single corp in the Killboard DB from their current Alliance
			$db = DBFactory::getDBQuery(true);;
			$db->execute("SELECT all_id FROM kb3_alliances WHERE all_name LIKE 'None'");
			$row = $db->getRow();
			$db->execute("UPDATE kb3_corps
							SET crp_all_id = ".$row['all_id']);
		}

		$alliances = $this->alliances_;
		$alliance = new Alliance();
		$tempMyCorp = new Corporation();
		$myCorpAPI = new API_CorporationSheet();

		$NumberOfAlliances = 0;
		$NumberOfCorps = 0;
		$NumberOfAlliancesAdded = 0; // we won't know this
		$NumberOfCorpsAdded = 0;

		foreach ($alliances as $arraykey => $arrayvalue)
        {
            $tempally = $arrayvalue;
			$NumberOfAlliances++;

            foreach ($tempally as $key => $value)
            {
                switch ($key)
                {
                    case "allianceName":
                        $alliance->add($value);
                        break;
					case "memberCorps":
						// if $andCorps = true then add each and every single corp to the evekb db - resolving each name (expect this to be slow)
						// WARNING: Processing 5000+ corps this way is extremely slow and is almost guaranteed not to complete
						if ($andCorps)
						{
							foreach ($value as $tempcorp)
							{
								$NumberOfCorps++;

								$myCorpAPI->setCorpID($tempcorp["corporationID"]);
								$result .= $myCorpAPI->fetchXML();

								//$NumberOfCorpsAdded++;
								$tempMyCorp->add($myCorpAPI->getCorporationName(), $alliance , gmdate("Y-m-d H:i:s"));

							}

						}
						break;
                }
            }
        }
		$returnarray["NumAlliances"] = $NumberOfAlliances;
		$returnarray["NumCorps"] = $NumberOfCorps;
		$returnarray["NumAlliancesAdded"] = $NumberOfAlliancesAdded;
		$returnarray["NumCorpsAdded"] = $NumberOfCorpsAdded;
		return $returnarray;

	}
}


// **********************************************************************************************************************************************
// **********************************************************************************************************************************************
// ****************                         					  GENERIC FUNCTIONS                					             ****************
// **********************************************************************************************************************************************
// **********************************************************************************************************************************************

// **********************************************************************************************************************************************
// ****************                         					   Load Generic XML               					             ****************
// **********************************************************************************************************************************************

// loads a generic XML sheet that requires no API Login as such
function LoadGlobalData($path)
{
	$temppath = substr($path, 0, strlen($path) - 9);
	$configvalue = "API" . str_replace("/", "_", $temppath);

	$CachedTime = ApiCache::get($configvalue);
	$UseCaching = config::get('API_UseCache');

	// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
	if ($CachedTime == "")
    {
        $CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    }

	if (is_file(KB_CACHEDIR.'/api/'.$configvalue.'.xml'))
		$cacheexists = true;
	else
		$cacheexists = false;

	if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )// if API_UseCache = 1 (off) then don't use cache
    {
        $fp = @fsockopen("api.eve-online.com", 80);

        if (!$fp)
        {
            echo "Error", "Could not connect to API URL<br>";
        } else {
            // request the xml
            fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
            fputs ($fp, "Host: api.eve-online.com\r\n");
            fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            fputs ($fp, "User-Agent: PHPApi\r\n");
            fputs ($fp, "Content-Length: 0\r\n");
            fputs ($fp, "Connection: close\r\n\r\n");
            fputs ($fp, "\r\n");
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
            if ($start != false)
            {
             	$contents = substr($contents, $start + strlen("\r\n\r\n"));
            }

			// Save the file if we're caching (0 = true in Thunks world)
			if ( $UseCaching == 0 )
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

// **********************************************************************************************************************************************
// ****************                         					Convert ID -> Name               					             ****************
// **********************************************************************************************************************************************
function gettypeIDname($id)
{
	$sql = 'select inv.typeName from kb3_invtypes inv where inv.typeID = ' . $id;

    $qry = DBFactory::getDBQuery();;
    $qry->execute($sql);
    $row = $qry->getRow();

    return $row['typeName'];
}

// **********************************************************************************************************************************************
// ****************                         					Get GroupID from ID               					             ****************
// **********************************************************************************************************************************************
function getgroupID($id)
{
	$sql = 'select inv.groupID from kb3_invtypes inv where inv.typeID = ' . $id;

    $qry = DBFactory::getDBQuery();;
    $qry->execute($sql);
    $row = $qry->getRow();

    return $row['groupID'];
}

// **********************************************************************************************************************************************
// ****************                         			    Convert groupID -> groupName           					             ****************
// **********************************************************************************************************************************************
function getgroupIDname($id)
{
	$sql = 'select itt.itt_name from kb3_item_types itt where itt.itt_id = ' . $id;

    $qry = DBFactory::getDBQuery();;
    $qry->execute($sql);
    $row = $qry->getRow();

	return $row['itt_name'];
}

// **********************************************************************************************************************************************
// ****************                         					Get Skill Rank from ID                				             ****************
// **********************************************************************************************************************************************
function gettypeIDrank($id)
{
	$sql = 'select att.value from kb3_dgmtypeattributes att where att.typeID = ' . $id . ' and att.attributeID = 275';

    $qry = DBFactory::getDBQuery();;
    $qry->execute($sql);
    $row = $qry->getRow();

    return $row['value'];
}

// **********************************************************************************************************************************************
// ****************                         			    Convert MoonID -> MoonName           					             ****************
// **********************************************************************************************************************************************
function getMoonName($id)
{
	if ($id != 0)
	{
        $qry = DBFactory::getDBQuery();;
		$sql = "SHOW TABLES LIKE 'kb3_moons'";
        $qry->execute($sql);
		if(!$qry->recordCount()) return "";

		$sql = 'select moon.itemID, moon.itemName from kb3_moons moon where moon.itemID = '.$id;

        $qry->execute($sql);
        $row = $qry->getRow();

        return $row['itemName'];
	} else {
		return "Unknown";
	}
}

// **********************************************************************************************************************************************
// ****************                         			    		Find Thunky          		 					             ****************
// **********************************************************************************************************************************************
function FindThunk()
{ // round about now would probably be a good time for apologising about my sense of humour :oD
    $sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "Captain Thunk"';

    $qry = DBFactory::getDBQuery();;
    $qry->execute($sql);
    $row = $qry->getRow();

    $pilot_id = $row['plt_id'];
    $pilot_charid = $row['plt_externalid'];

    if ( $pilot_id != 0 )	{
        return '<a href="?a=pilot_detail&plt_id=' . $pilot_id . '" ><font size="2">Captain Thunk</font></a>';
    } else {
        return "Captain Thunk";
    }
}

// **********************************************************************************************************************************************
// ****************                         			         Update  CCP CorpID              					             ****************
// **********************************************************************************************************************************************
function Update_CorpID($corpName, $corpID)
{
	if ( (strlen($corpName) != 0) && ($corpID != 0) )
	{
		$qry = DBFactory::getDBQuery();;
		$qry->execute( "SELECT * FROM `kb3_corps` WHERE `crp_name` = '" . slashfix($corpName) . "'");

		if ($qry->recordCount() != 0)
    	{
			$row = $qry->getRow();
			if ($row['crp_external_id'] == NULL)
			{
				$qry->execute("update kb3_corps set crp_external_id = " . $corpID . " where `crp_id` = " . $row['crp_id']);
			}
		}
	}
}

// **********************************************************************************************************************************************
// ****************                         			        Update CCP AllianceID            					             ****************
// **********************************************************************************************************************************************
function Update_AllianceID($allianceName, $allianceID)
{
	if ( ($allianceName != "NONE") && ($allianceID != 0) )
	{
		$qry = DBFactory::getDBQuery();;
		$qry->execute( "SELECT * FROM `kb3_alliances` WHERE `all_name` = '" . slashfix($allianceName) . "'");

		if ($qry->recordCount() != 0)
    	{
			$row = $qry->getRow();
			if ($row['all_external_id'] == NULL)
			{
				$qry->execute("update kb3_alliances set all_external_id = " . $allianceID . " where `all_id` = " . $row['all_id']);
			}
		}
	}
}

// **********************************************************************************************************************************************
// ****************                         		Convert GMT Timestamp to local time            					             ****************
// **********************************************************************************************************************************************
function ConvertTimestamp($timeStampGMT)
{
	if (!config::get('API_ConvertTimestamp'))
	{
		// set gmt offset
		$gmoffset = (strtotime(date("M d Y H:i:s")) - strtotime(gmdate("M d Y H:i:s")));
		//if (!config::get('API_ForceDST'))
			//$gmoffset = $gmoffset + 3600;

		$cachetime = date("Y-m-d H:i:s",  strtotime($timeStampGMT) + $gmoffset);
	} else {
		$cachetime = $timeStampGMT;
	}

	return $cachetime;
}
