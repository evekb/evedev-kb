<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

// **********************************************************************************************************************************************
// ****************                                    API KillLog - /corp/Killlog.xml.aspx                                      ****************
// **********************************************************************************************************************************************

class API_KillLog
{
    function Import($keystring, $typestring, $keyindex)
	{
		$this->mailcount_ = 0;
		$this->ignoredmails_ = 0;
		$this->malformedmails_ = 0;
		$this->verified_ = 0;
		$this->totalmails_ = 0;
		$this->errorcode_ = 0;
		$this->Output_ = "";
		$this->isContainer = false;
		$this->hasdownloaded_ = false;
		$this->errortext_ = "";
		$this->CachedUntil_ = "";

        // reduces strain on DB
		if(function_exists("set_time_limit"))
      		set_time_limit(0);

        $this->API_IgnoreFriendPos_ = config::get('API_IgnoreFriendPos');
        $this->API_IgnoreEnemyPos_ = config::get('API_IgnoreEnemyPos');
        $this->API_IgnoreNPC_ = config::get('API_IgnoreNPC');
        $this->API_IgnoreCorpFF_ = config::get('API_IgnoreCorpFF');
        $this->API_IgnoreAllianceFF_ = config::get('API_IgnoreAllianceFF');
        $this->API_NoSpam_ = config::get('API_NoSpam');
		$this->API_CacheTime_ = ApiCache::get('API_CachedUntil_' . $keyindex);
		$this->API_UseCaching_ = config::get('API_UseCache');
        $this->keyindex_ = $keyindex;


		// Initialise for error correcting and missing itemID resolution
		$this->myIDName = new API_IDtoName();
		$this->myNameID = new API_NametoID();

        $lastdatakillid = 1;
        $currentdatakillid = 0;

		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($this->API_CacheTime_ == "")
    	{
        	$this->API_CacheTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}

		if (is_file(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
		if(true)
		{
			if($errorcode = $this->fetchErrors($keyindex))
				return "<div class=block-header2><i>"
					.config::get('API_Name_'.$keyindex)." failed with error code "
					.$errorcode."</i></div><br><br>";

			// if API_UseCache = 1 (off) then don't use cache
			if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($this->API_CacheTime_) > 0) || ($this->API_UseCaching_ == 1)  || !$cacheexists )
			{
				// Load new XML
				$logsource = "New XML";
				$this->Output_ .= "<i>Downloading latest XML file for " . config::get('API_Name_'.$keyindex) . "</i><br><br>";
				$data = '';
				$date2 = '';
				do {
					$tmp = $this->loaddata($currentdatakillid, $keystring, $typestring);
					if(!$data || strpos($tmp, 'result') !== false)
							$data .= $tmp;
					$data = preg_replace('/\s*<\/rowset>\s*<\/result.*<result>\s*<rowset[^>]*>/s', '', $data);
					$data2 .= $tmp;
					$lastdatakillid = $currentdatakillid;
					$currentdatakillid = $this->getlastkillid($data);
				} while ( $lastdatakillid != $currentdatakillid );
				if ( $this->API_UseCaching_ == 0 )
				{
					// save the file if no errors have occurred
					if ($this->errorcode_ == 0)
					{
						file_put_contents(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml', $data);
						//chmod the file so it can be altered by cronjobs in future
						@chmod(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml',0666);
					}
				}
			} else {
				// re-use cached XML
				$this->Output_ .= "<i>Using cached XML file for " . config::get('API_Name_'.$keyindex) . "</i><br><br>";
				$logsource = "Cache";

				$data = file_get_contents(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml');
				if(!$data) return "<i>error loading cached file ".config::get('API_Name_'.$keyindex)."_KillLog.xml</i><br><br>";
			}

			$feedfetch = new IDFeed();
			if($data)
			{
				$feedfetch->setXML($data);
				$feedfetch->setTrust(-1);
				$feedfetch->read();
				if($feedfetch->getCachedTime())
				{
					$this->CachedUntil_ = $feedfetch->getCachedTime();
					ApiCache::set('API_CachedUntil_' . $this->keyindex_, $feedfetch->getCachedTime());
				}
			}

			$posted = count($feedfetch->getPosted());
			$skipped = count($feedfetch->getSkipped());

			$this->Output_ .= "<div class=block-header2>" . $posted
				." kills, ". $skipped . " skipped  from feed: " . config::get('API_Name_'.$keyindex) . ".<br></div>";

			// Write to kb3_apilog
			$qry = DBFactory::getDBQuery();;
			if ($this->iscronjob_)
				$logtype = "Cron Job";
			else
				$logtype = "Manual";

			$qry->execute( "insert into kb3_apilog	values( '" . KB_SITE . "', '"
															. addslashes(config::get('API_Name_'.$keyindex)) . "',"
															. $posted . ","
															. "0 ,"
															. $skipped . ","
															. "0 ,"
															. ($posted + $skipped) . ",'"
															. $logsource . "','"
															. $logtype . "','"
															. $feedfetch->getErrorCode() . "', "
															. "UTC_TIMESTAMP() )" );

		}
		else
		{
			$qry = DBFactory::getDBQuery();
			$qry->execute("SELECT log_errorcode FROM kb3_apilog WHERE log_site = '"
				.KB_SITE."' AND log_keyname = '".
				addslashes(config::get('API_Name_'.$keyindex)).
				"' ORDER BY log_timestamp DESC");
			if($qry->recordCount())
			{
				$row = $qry->getRow();
				$errorcode = $row['log_errorcode'];
			}
			else $errorcode = 0;
			// Don't let the cron keep checking jobs that have returned an
			if(($errorcode >= 200 && $errorcode < 300|| $errorcode == 105) && $this->iscronjob_)
			{
				return "<div class=block-header2><i>".config::get('API_Name_'.$keyindex)." failed with error code ".$errorcode."</i></div><br><br>";
			}

			// if API_UseCache = 1 (off) then don't use cache
			if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($this->API_CacheTime_) > 0) || ($this->API_UseCaching_ == 1)  || !$cacheexists )
			{
				// Load new XML
				$logsource = "New XML";
				$this->Output_ .= "<i>Downloading latest XML file for " . config::get('API_Name_'.$keyindex) . "</i><br><br>";
				$data = '<myxml thunkage="1">';
				do {
					$data .= $this->loaddata($currentdatakillid, $keystring, $typestring);
					$lastdatakillid = $currentdatakillid;
					$currentdatakillid = $this->getlastkillid($data);
				} while ( $lastdatakillid != $currentdatakillid );
				$data .= '</myxml>';

				if ( ( $this->API_UseCaching_ ) == 0 )//&& ( $this->iscronjob_ == false ) )
				{
					// save the file if no errors have occurred
					if ($this->errorcode_ == 0)
					{
						$file = fopen(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml', 'w+');
						fwrite($file, $data);
						fclose($file);
						//chmod the file so it can be altered by cronjobs in future
						@chmod(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml',0666);
					}
				}
			} else {
				// re-use cached XML
				$this->Output_ .= "<i>Using cached XML file for " . config::get('API_Name_'.$keyindex) . "</i><br><br>";
				$logsource = "Cache";

				if ($fp = @fopen(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml', 'r')) {
					$data = fread($fp, filesize(KB_CACHEDIR.'/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml'));
					fclose($fp);
				} else {
					return "<i>error loading cached file ".config::get('API_Name_'.$keyindex)."_KillLog.xml</i><br><br>";
				}
			}

			$xml_parser = xml_parser_create();
			xml_set_object ( $xml_parser, $this );
			xml_set_element_handler($xml_parser, "startElement", "endElement");
			xml_set_character_data_handler ( $xml_parser, 'characterData' );

			if (!xml_parse($xml_parser, $data, true))
				return $this->Output_ .= "<i>Error getting XML data from ".API_SERVER."</i><br><br>";

			if ( strlen($data) == 28 )
				return $this->Output_ .= "<i>Error contacting ".API_SERVER."</i><br><br>";

			xml_parser_free($xml_parser);

			if ( ($this->hasdownloaded_ == false) && ($this->errortext_ != "") )
			{
				$this->Output_ .= "<font color = \"#FF0000\">".$this->errortext_ . "</font><br>";
				$logsource = "Error";
			}

			if ($this->mailcount_)
				$this->Output_ .= "<div class=block-header2>".$this->mailcount_." kills, " . $this->malformedmails_ . " malformed, " . $this->ignoredmails_ . " ignored and " . $this->verified_ . " verified from feed: " . config::get('API_Name_'.$keyindex) . " which contained ".$this->totalmails_." mails.<br></div>";
			else
				$this->Output_ .= "<div class=block-header2>No kills added, ". $this->malformedmails_ . " malformed, " . $this->ignoredmails_." ignored and " . $this->verified_ . " verified from feed: " . config::get('API_Name_'.$keyindex) . " which contained ".$this->totalmails_." mails.<br></div>";

			// Write to kb3_apilog
			$qry = DBFactory::getDBQuery();;
			if ($this->iscronjob_)
				$logtype = "Cron Job";
			else
				$logtype = "Manual";

			$qry->execute( "insert into kb3_apilog	values( '" . KB_SITE . "', '"
															. addslashes(config::get('API_Name_'.$keyindex)) . "',"
															. $this->mailcount_ . ","
															. $this->malformedmails_ . ","
															. $this->ignoredmails_ . ","
															. $this->verified_ . ","
															. $this->totalmails_ . ",'"
															. $logsource . "','"
															. $logtype . "','"
															. $this->errorcode_ . "', "
															. "UTC_TIMESTAMP() )" );
		}
		
        return $this->Output_;

    }

    function startElement($parser, $name, $attribs)
    {
        if ($name == "ROWSET")
        {
			//echo $this->rowsetCounter_ . " ";
            if (($this->pname_ == "") && ($this->typeid_ != "0"))
            {
				$this->isContainer = true;
				// this is to catch containers that spawn a new rowset so are missed off loot
                if ($this->qtydropped_ !=0)
				{
                    // dropped items
                    $this->droppeditems_['typeid'][] = $this->typeid_;
                    $this->droppeditems_['qty'][] = $this->qtydropped_;
                    //if ($this->isContainer)
					//{
					//	$this->droppeditems_['flag'][] = -1;
					//} else {
						$this->droppeditems_['flag'][] = $this->itemFlag_;
					//}
                }
                if ($this->qtydestroyed_ != 0)
				{
                    // destroyed items
                    $this->destroyeditems_['typeid'][] =$this->typeid_;
                    $this->destroyeditems_['qty'][] = $this->qtydestroyed_;
                   // if ($this->isContainer)
					//{
					//	$this->destroyeditems_['flag'][] = -1;
					//} else {
						$this->destroyeditems_['flag'][] = $this->itemFlag_;
					//}
                }
                $this->typeid_ = 0;
                $this->itemFlag_ = 0;
                $this->qtydropped_ = 0;
                $this->qtydestroyed_ = 0;
            }
			// goes after so container itself doesn't count as "(in countainer)

        }

        if (count($attribs))
        {
            foreach ($attribs as $k => $v)
			{
                switch ($k)
				{
                    case "CHARACTERID":
                        $this->charid_ = $v;
                        break;
                    case "CHARACTERNAME":
						$this->pname_ = $v;

                        break;
					case "CORPORATIONID":
                        $this->corporationID_ = $v;
						break;
                    case "CORPORATIONNAME":
						$this->corporation_ = $v;

                        break;
                    case "ALLIANCEID":
                        $this->allianceID_ = $v;
                        break;
                    case "ALLIANCENAME":
						$this->alliance_ = $v;

						if (strlen($this->alliance_) == 0)
							$this->alliance_ = "NONE";
                    	break;
                    case "DAMAGETAKEN":
                        $this->damagetaken_ = $v;
                        break;
                    case "DAMAGEDONE":
                        $this->damagedone_ = $v;
                        break;
                    case "SHIPTYPEID":
                        if ($v == 0)
						{
                            $this->shipname_ = "Unknown";
						} else {
                            $this->shipname_ = API_Helpers::gettypeIDname($v, true);
                        }
                        break;
                    case "FINALBLOW":
                        $this->finalblow_ = $v;
                        break;
                    case "SECURITYSTATUS":
						// Player security status on valid killmails always has
						// one (and only one) digit after decimal place.
						$this->security_ = number_format($v, 1);
                        break;
                    case "WEAPONTYPEID":
                        $this->weapon_ = API_Helpers::gettypeIDname($v, true);
                        break;
                    // for items
                    case "TYPEID":
                        $this->typeid_ = API_Helpers::gettypeIDname($v, true);
                        break;
                    case "FLAG":
                        $this->itemFlag_ = $v;
                        break;
                    case "QTYDROPPED":
                        $this->qtydropped_ = $v;
                        break;
                    case "QTYDESTROYED":
                        $this->qtydestroyed_ = $v;
                        break;

                    // for system/kill mail details (start of mail)
                    case "KILLID":
                        // print mail here - this will miss the last mail but it can be caught on exit. This weird way of doing things prevents falling foul
                        // of the CCP API cargo bug - using function, avoids the repetition
                        if ($this->beforekillid_ != 0)
                        {
                            $this->parseendofmail();
                        }
                        $this->beforekillid_ = $v;
						$this->killid_ = $v; // added v2.6 for help tracing erroneous mails
						$this->totalmails_++; // Count total number of mails in XML feed
						if ($this->isKillIDVerified($v) != null)
						{
							$this->killmailExists_ = true;
							return;
						} else {
							$this->killmailExists_ = false;
						}
                        break;
                    case "SOLARSYSTEMID": // convert to system name and fetch system security - DONE
                        $sql = 'select sys.sys_name, sys.sys_sec from kb3_systems sys where sys.sys_id = '.$v;

                        $qry = DBFactory::getDBQuery();;
                        $qry->execute($sql);
                        $row = $qry->getRow();

                        $this->systemname_ = $row['sys_name'];
                        $mysec = $row['sys_sec'];
                        if ($mysec <= 0)
                            $this->systemsecurity_ = number_format(0.0, 1);
                        else
                            $this->systemsecurity_ = number_format(round($mysec, 1), 1);
                        break;
                    case "MOONID": // only given with POS related stuff - unanchored mods however, do not have a moonid.
						$this->moonid_ = $v;

						$this->moonname_ = API_Helpers::getMoonName($v);
						// Missing Moon DB correction
						if (($this->moonname_ == "") && ($this->moonid_ != 0))
						{
							$this->myIDName->clear();
							$this->myIDName->setIDs($v);
							$output = $this->myIDName->fetchXML();
							if($output)	$this->Output_ .= $this->killid_.":".$output;
							$myNames = $this->myIDName->getIDData();
							//$this->typeid_ = "Item missing from DB: " . $myNames[0]['name'];
							$this->moonname_ = $myNames[0]['name'];
						}
                        break;
                    case "KILLTIME": // Time Kill took place
                        $this->killtime_ = $v;
                        break;
					case "FACTIONID": // Faction ID
                        $this->factionid_ = $v;
                        break;
					case "FACTIONNAME": // Faction Name
						if ( $v == "" ) {
							$this->factionname_ = "NONE";
                        } else {
							$this->factionname_ = $v;
						}
                        break;
					case "CODE": // error code
						$this->errorcode_ .= $v;
						break;
                }
            }
        }
    }

    function endElement($parser, $name)
    {
        switch ($name)
        {
			case "ROWSET":
				$this->isContainer = false;
				break;
            case "VICTIM":
                $this->hasdownloaded_ = true;
                // if no name is given and moonid != 0 then replace name with corp name for parsing - would lookup the moonid but we don't have that in database - replace shipname as "Unknown" this allows parsing to complete
                if ($this->moonid_ != 0 && $this->pname_ == "")
                {
                    $this->pname_ = $this->moonname_;
                    //$this->shipname_ = "Unknown"; // this is done else mail will not parse
                    $this->isposkill_ = true;
                } elseif (($this->moonid_ == 0) && ($this->pname_ == "") && ($this->charid_ == 0)) {
					// catches unanchored POS modules - as moon is unknown, we will use system name instead
					$this->pname_ = $this->systemname_;
                    $this->isposkill_ = true;
				} else {
                    $this->isposkill_ = false;
                }
                // print victim header
                $this->killmail_ = substr(str_replace('-', '.' , $this->killtime_), 0, 16) . "\r\n\r\n";
				if ($this->isposkill_ == false )
                	$this->killmail_ .= "Victim: ".$this->pname_ . "\r\n"; // This line would not appear on a POS mail
				$this->killmail_ .= "Corp: ".$this->corporation_ . "\r\n";
                $this->killmail_ .= "Alliance: ".$this->alliance_ . "\r\n";
				$this->killmail_ .= "Faction: ".$this->factionname_ . "\r\n";
                $this->killmail_ .= "Destroyed: ".$this->shipname_ . "\r\n";
				if ($this->isposkill_ == true )
					$this->killmail_ .= "Moon: ".$this->moonname_ . "\r\n"; // This line does appear on a POS mail
                $this->killmail_ .= "System: ".$this->systemname_ . "\r\n";
                $this->killmail_ .= "Security: ".$this->systemsecurity_ . "\r\n";
                $this->killmail_ .= "Damage Taken: ".$this->damagetaken_ . "\r\n\r\n";
                $this->killmail_ .= "Involved parties:\r\n\r\n";

                if ( config::get('API_Update') == 0 )
                {
					$alliance = new Alliance();
					if ($this->allianceID_ != 0)
						$alliance = Alliance::add($this->alliance_, $this->allianceID_);
					else $alliance = Alliance::add("None");

//					$corporation = new Corporation();
//					$corporation->add($this->corporation_, $alliance, $this->killtime_, $this->corporationID_);
//
//					$pilot = new Pilot();
//					$pilot->add($this->pname_, $corporation, $this->killtime_, $this->charid_);
                }

                // set victim corp and alliance for FF check
                $this->valliance_ = $this->alliance_;
                $this->vcorp_ = $this->corporation_;

                // now clear these
                //$this->killtime_ = "";
                $this->pname_ = "";
                $this->alliance_ = "";
				$this->factionname_ = "";
                $this->corporation_ = "";
                $this->destroyed_ = 0;
                $this->systemname_ = "";
                $this->systemsecurity_ = 0;
                $this->damagetaken_ = 0;
                $this->charid_ = 0;
				$this->moonid_ = 0;
				$this->mooname_ = 0;
				$this->corporationID_ = 0;
				$this->allianceID_ = 0;
                break;
            case "ROW":
                if ( $this->typeid_ != "0" )
                {
					// it's cargo
                    if ($this->qtydropped_ !=0)
                    {
                        // dropped items
                        $this->droppeditems_['typeid'][] = $this->typeid_;
                        $this->droppeditems_['qty'][] = $this->qtydropped_;
						if ($this->isContainer)
					   	{
							$this->droppeditems_['flag'][] = -1;
						} else {
							$this->droppeditems_['flag'][] = $this->itemFlag_;
						}
					}
                    if ($this->qtydestroyed_ != 0)
                    {
                    // destroyed items
                        $this->destroyeditems_['typeid'][] = $this->typeid_;
                        $this->destroyeditems_['qty'][] = $this->qtydestroyed_;
                       	if ($this->isContainer)
					   	{
							$this->destroyeditems_['flag'][] = -1;
						} else {
							$this->destroyeditems_['flag'][] = $this->itemFlag_;
						}
                    }
                    $this->typeid_ = 0;
                    $this->itemFlag_ = 0;
                    $this->qtydropped_ = 0;
                    $this->qtydestroyed_ = 0;
                }
				// using corporation_ not pname_ as NPCs don't have a name *** CHANGED to corporationID 16/03/2009 to catch 'sleeper' NPCs
                if ($this->corporationID_ != 0)
                {
					// it's an attacker
                    $this->attackerslist_['name'][] = $this->pname_;
                    $this->attackerslist_['finalblow'][] = $this->finalblow_;
                    $this->attackerslist_['security'][] = $this->security_;
					$this->attackerslist_['corporation'][] = $this->corporation_;
                    $this->attackerslist_['alliance'][] = $this->alliance_;
                    $this->attackerslist_['faction'][] = $this->factionname_;
                    $this->attackerslist_['shiptypeid'][] = $this->shipname_;
                    $this->attackerslist_['weapon'][] = $this->weapon_;
                    $this->attackerslist_['damagedone'][] = $this->damagedone_;

					if ( config::get('API_Update') == 0 )
					{
						$alliance = new Alliance();
						if ($this->allianceID_ != 0) {
							$alliance = Alliance::add($this->alliance_, $this->allianceID_);
						} else {
							$alliance = Alliance::add("None");
						}
// We don't know the time yet? Weird, yet somehow true.
//						$corporation = new Corporation();
//						$corporation->add($this->corporation_, $alliance, $this->killtime_, $this->corporationID_);
//
//						$pilot = new Pilot();
//						$pilot->add($this->pname_, $corporation, $this->killtime_, $this->charid_);
                    }

                    $this->pname_ = "";
                    $this->finalblow_ = 0;
                    $this->security_ = 0;
                    $this->alliance_ = "";
					$this->factionname_ = "";
                    $this->corporation_ = "";
                    $this->shipname_ = 0;
                    $this->weapon_ = 0;
                    $this->damagedone_ = 0;
                    $this->charid_ = 0;
					$this->corporationID_ = 0;
					$this->allianceID_ = 0;
                }
                break;
            case "RESULT":
                // reset beforekillid to allow processing of more chunks of data I've placed into $data
                $this->beforekillid_ = 0;

                // does last killmail
                if ($this->hasdownloaded_)
				{
					// catch to prevent processing without any mails
                    $this->parseendofmail();
                }
                break;
            case "MYXML":
                // end of data xml, process cachedtime here
                //$ApiCache->set('API_CachedUntil' . $this->keyindex_, $this->cachetext_);
                break;
			case "ERROR": //  Error Message
				if ($this->errortext_ == "")
				{
					$this->errortext_ .= $this->characterDataValue;
				}
				break;
			case "CURRENTTIME":
				$this->CurrentTime_ = $this->characterDataValue;
				break;
			case "CACHEDUNTIL":
				// kill log can be several xml sheets stuck together, we only want the first CachedUntil_
				if ($this->CachedUntil_ == "")
				{
					// Do not save cache key if this is an error sheet
					$this->CachedUntil_ = $this->characterDataValue;
					ApiCache::set('API_CachedUntil_' . $this->keyindex_, $this->CachedUntil_);
				}
				break;
        }
    }

    function characterData($parser, $data)
    {
		$this->characterDataValue = $data;
    }

    function parseendofmail()
    {
	    // print attacks
		$attackercounter = count($this->attackerslist_['name']);
       	// sort array into descending damage
       	if ($attackercounter != 0 )
        {
        	array_multisort($this->attackerslist_['damagedone'], SORT_NUMERIC, SORT_DESC,
                $this->attackerslist_['name'], SORT_ASC, SORT_STRING,
                $this->attackerslist_['finalblow'], SORT_NUMERIC, SORT_DESC,
                $this->attackerslist_['security'], SORT_NUMERIC, SORT_DESC,
				$this->attackerslist_['corporation'], SORT_ASC, SORT_STRING,
                $this->attackerslist_['alliance'], SORT_ASC, SORT_STRING,
                $this->attackerslist_['faction'], SORT_ASC, SORT_STRING,
                $this->attackerslist_['shiptypeid'], SORT_ASC, SORT_STRING,
                $this->attackerslist_['weapon'], SORT_ASC, SORT_STRING );
        }

        // Initialise some flags to use
        $hasplayersonmail = false;
        $this->corpFF_ = true;
        $this->allianceFF_ = true;
        $poswasfriendly = false;

        // catch for victim being in no alliance
        if ($this->valliance_ == "NONE")
       		$this->allianceFF_ = false;

        for ($attackerx = 0; $attackerx < $attackercounter; $attackerx++)
        {
       		// if NPC (name will be "") then set pname_ as corporation_ for mail parsing
        	if  ($this->attackerslist_['name'][$attackerx] == "")
        	{
				// fix for Sleepers ("Unknown")
				if ($this->attackerslist_['corporation'][$attackerx] == "")
				{
					$npccorpname = "Unknown";
				} else {
					$npccorpname = $this->attackerslist_['corporation'][$attackerx];
				}
                $this->killmail_ .= "Name: ".$this->attackerslist_['shiptypeid'][$attackerx] ." / ".$npccorpname."\r\n";
                $this->killmail_ .= "Damage Done: ".$this->attackerslist_['damagedone'][$attackerx]."\r\n";
                $this->corpFF_ = false;
                $this->allianceFF_ = false;
            } else {
                $hasplayersonmail = true;
                $this->killmail_ .= "Name: ".$this->attackerslist_['name'][$attackerx];
                if ($this->attackerslist_['finalblow'][$attackerx] == 1)
                {
                    $this->killmail_ .= " (laid the final blow)";
                }
                $this->killmail_ .= "\r\n";

                $this->killmail_ .= "Security: ".$this->attackerslist_['security'][$attackerx]."\r\n";
				$this->killmail_ .= "Corp: ".$this->attackerslist_['corporation'][$attackerx]."\r\n";
                $this->killmail_ .= "Alliance: ".$this->attackerslist_['alliance'][$attackerx]."\r\n";
                $this->killmail_ .= "Faction: ".$this->attackerslist_['faction'][$attackerx]."\r\n";
                $this->killmail_ .= "Ship: ".$this->attackerslist_['shiptypeid'][$attackerx]."\r\n";
                $this->killmail_ .= "Weapon: ".$this->attackerslist_['weapon'][$attackerx]."\r\n";
                $this->killmail_ .= "Damage Done: ".$this->attackerslist_['damagedone'][$attackerx]."\r\n";

                // set Friendly Fire matches
                if ($this->attackerslist_['alliance'][$attackerx] != $this->valliance_)
               		$this->allianceFF_ = false;
                if ($this->attackerslist_['corporation'][$attackerx] != $this->vcorp_)
                	$this->corpFF_ = false;
            }
            $this->killmail_ .= "\r\n";
        } //end for next loop

        // clear attackerslist
        $this->attackerslist_ = array();

        if (count($this->destroyeditems_['qty']) != 0)
        {
            $this->killmail_ .= "\r\nDestroyed items:\r\n\r\n";

            $counter = count($this->destroyeditems_['qty']);
            for ($x = 0; $x < $counter; $x++)
            {
                if ($this->destroyeditems_['qty'][$x] > 1)
                {
					// show quantity
                	$this->killmail_ .= $this->destroyeditems_['typeid'][$x].", Qty: ".$this->destroyeditems_['qty'][$x];
                } else {
					// just the one
                	$this->killmail_ .= $this->destroyeditems_['typeid'][$x];
                }

    	        if ($this->destroyeditems_['flag'][$x] == 5) {
    	        	$this->killmail_ .= " (Cargo)";
    	        } elseif ($this->destroyeditems_['flag'][$x] == 87) {
        	        $this->killmail_ .= " (Drone Bay)";
            	}  elseif ($this->destroyeditems_['flag'][$x] == -1)
				{
					$this->killmail_ .= " (In Container)";
				}
                $this->killmail_ .= "\r\n";
            }
        }

        if (count($this->droppeditems_['qty']) != 0)
        {
            $this->killmail_ .= "\r\nDropped items:\r\n\r\n";

            $counter = count($this->droppeditems_['qty']);
            for ($x = 0; $x < $counter; $x++)
            {
            	if ($this->droppeditems_['qty'][$x] > 1)
            	{
					// show quantity
            	    $this->killmail_ .= $this->droppeditems_['typeid'][$x].", Qty: ".$this->droppeditems_['qty'][$x];
            	} else {
					// just the one
            	    $this->killmail_ .= $this->droppeditems_['typeid'][$x];
            	}

            	if ($this->droppeditems_['flag'][$x] == 5)
               	{
                	$this->killmail_ .= " (Cargo)";
               	} elseif ($this->droppeditems_['flag'][$x] == 87)
                {
                	$this->killmail_ .= " (Drone Bay)";
                } elseif ($this->droppeditems_['flag'][$x] == -1)
				{
					$this->killmail_ .= " (In Container)";
				}
                $this->killmail_ .= "\r\n";
            }
        }

		$poswasfriendly = false;
        // If ignoring friendly POS Structures
        if ($this->isposkill_)
		{
			// This board has corp owners
        	if ( config::get('cfg_corpid'))
            {
				foreach(config::get('cfg_corpid') as $corp)
				{
					$thiscorp = new Corporation($corp);
					if ( $this->vcorp_ == $thiscorp->getName() )
					{
						$poswasfriendly = true;
						break;
					}
				}
            }
			// This board has alliance owners
			if(config::get('cfg_allianceid') && !$poswasfriendly)
			{
				foreach(config::get('cfg_allianceid') as $all)
				{
					$thisalliance = new Alliance($all);
					if ( $this->valliance_ == $thisalliance->getName() )
					{
						$poswasfriendly = true;
						break;
					}
				}
            }
        }

        if ( ( $this->API_IgnoreFriendPos_ == 0 ) &&  ( $poswasfriendly ) &&  ( $this->isposkill_ ) )
        {
        	if ( ( $this->API_NoSpam_ == 0 ) && ( $this->iscronjob_ ) )
        	{
            	// do not write to $this->Output_
            } else {
            	$this->Output_ .= "Killmail ID:".$this->killid_." containing friendly POS structure has been ignored.<br>";
            }
            $this->ignoredmails_++;
        } elseif ( ( $this->API_IgnoreEnemyPos_ == 0 ) &&  ( !$poswasfriendly ) &&  ( $this->isposkill_ ) )
        {
        	if ( ( $this->API_NoSpam_ == 0 ) && ( $this->iscronjob_ ) )
            {
                // do not write to $this->Output_
            } else {
                $this->Output_ .= "Killmail ID:".$this->killid_." containing enemy POS structure been ignored.<br>";
            }
            $this->ignoredmails_++;
        } elseif ( ( $this->API_IgnoreNPC_ == 0 ) && ($hasplayersonmail == false) )
        {
            if ( ( $this->API_NoSpam_ == 0 ) && ( $this->iscronjob_ ) )
            {
                // do not write to $this->Output_
            } else {
                $this->Output_ .= "Killmail ID:".$this->killid_." containing only NPCs has been ignored.<br>";
            }
            $this->ignoredmails_++;
        } elseif ( ( $this->API_IgnoreCorpFF_ == 0 ) && ($this->corpFF_ == true ) )
        {
            if ( ( $this->API_NoSpam_ == 0 ) && ( $this->iscronjob_ ) )
            {
               	// do not write to $this->Output_
           	} else {
               	$this->Output_ .= "Killmail ID:".$this->killid_." containing corporation friendly fire has been ignored.<br>";
            }
            $this->ignoredmails_++;
        } elseif ( ( $this->API_IgnoreAllianceFF_ == 0 ) && ($this->allianceFF_ == true ) )
        {
            if ( ( $this->API_NoSpam_ == 0 ) && ( $this->iscronjob_) )
            {
               	 // do not write to $this->Output_
            } else {
                $this->Output_ .= "Killmail ID:".$this->killid_." containing alliance friendly fire has been ignored.<br>";
            }
            $this->ignoredmails_++;
        } else {
            $this->postimportmail();
        }

        // clear destroyed/dropped arrays
        unset($this->destroyeditems_ );
        unset($this->droppeditems_);
    }

    function postimportmail()
    {
        if ( ( isset( $this->killmail_ ) ) && ( !$this->killmailExists_ ) )
        {
            $parser = new Parser( $this->killmail_, $this->killid_);
			$parser->setTrust(1);
            //$killid = $parser->parse( true );

			if (config::get('filter_apply'))
        	{
            	$filterdate = config::get('filter_date');
            	$year = substr($this->killmail_, 0, 4);
            	$month = substr($this->killmail_, 5, 2);
            	$day = substr($this->killmail_, 8, 2);
           	 	$killstamp = mktime(0, 0, 0, $month, $day, $year);
            	if ($killstamp < $filterdate)
            	{
                	$killid = -3;
            	}
            	else
            	{
                	$killid = $parser->parse(true);
            	}
        	} else {
            	$killid = $parser->parse(true);
        	}

            if ( $killid <= 0)
            {
                if ( $killid == 0 )
                {
                    $this->Output_ .= "Killmail ID:".$this->killid_." is malformed.<br>";
					$this->malformedmails_++;

                    if ($errors = $parser->getError())
                    {
                        foreach ($errors as $error)
                        {
                            $this->Output_ .= 'Error: '.$error[0];
                            if ($error[1])
                            {
                                $this->Output_ .= ' The text lead to this error was: "'.$error[1].'"<br>';
                            }
                        }

						$this->Output_ .= '<br/>';
                    }
                }
				if ($killid == -4)
            	{
					$this->Output_ .= "Killmail ID:".$this->killid_. " has already been deleted so will not be reposted.<br>";
					$this->ignoredmails_++;
            	}

				if ($killid == -3)
            	{
                	$filterdate = kbdate("j F Y", config::get("filter_date"));
                	//$html = "Killmail older than $filterdate ignored.";
					$this->Output_ .= "Killmail ID:".$this->killid_. " has been ignored as mails before $filterdate are restricted.<br>";
					$this->ignoredmails_++;
            	}

                if ( $killid == -2 )
				{
                    $this->Output_ .= "Killmail ID:".$this->killid_. " is not related to ".config::get('cfg_kbtitle').".<br>";
					$this->ignoredmails_++;
               	}
				// Killmail exists - as we're here and the mail was posted, it is not a verified mail, so verify it now.
                if ( $killid == -1 )
                {
                    if ( ( $this->API_NoSpam_ == 0 ) && ( $this->iscronjob_ ) )
                    {
                    // do not write to $this->Output_
                    } else {
                        // $this->Output_ .= "Killmail already exists <a href=\"?a=kill_detail&amp;kll_id=".$parser->dupeid_."\">here</a>.<br>";
						// write API KillID to kb3_kills killID column row $parser->dupeid_
						$this->VerifyKill($this->killid_, $parser->getDupeID());
						$this->verified_++;
                    }
                }
            } else {
                $qry = DBFactory::getDBQuery();;
                $qry->execute( "insert into kb3_log	values( ".$killid.", '".KB_SITE."','API ".APIVERSION."',UTC_TIMESTAMP() )" );
                $this->Output_ .= "API Killmail ID:".$this->killid_. " successfully imported <a href=\"?a=kill_detail&amp;kll_id=".$killid."\">here</a> as KB ID:". $killid ."<br>";

				// Now place killID (API) into killboard row $killid
				//$this->VerifyKill($this->killid_, $killid);

				// mail forward
				event::call('killmail_imported', $this);

				// For testing purposes
				//$this->Output_ .= str_replace("\r\n", "<br>", $this->killmail_);

                if (config::get('API_Comment')) { // for the Eve-Dev Comment Class
                    $comments = new Comments($killid);
                    $comments->addComment("Captain Thunks API " . APIVERSION, config::get('API_Comment'));
                }
                $this->mailcount_++;
            }
        }
    }

    function loaddata($refid, $keystring, $typestring)
    {
        $url = API_SERVER."/" . $typestring . "/KillLog.xml.aspx";

        if ($refid != 0)
            $keystring .= '&beforeKillID=' . $refid;
		//TODO: change the all the $keystring chains to arrays
		$keypairs = explode('&', $keystring);
		$keys = array();
		foreach($keypairs as $val)
		{
			$pair = explode("=", $val);
			$keys[$pair[0]] = $pair[1];
		}
        $path = '/' . $typestring . '/Killlog.xml.aspx';
        $fp = @fsockopen(API_SERVER, 80);

		$http = new http_request($url);
		$http->set_useragent("PHPApi");
		foreach($keys as $key => $val) $http->set_postform($key, $val);

		$contents = $http->get_content();

		$start = strpos($contents, "?>");
		if ($start !== FALSE)
		{
			$contents = substr($contents, $start + strlen("\r\n\r\n"));
		}

		return $contents;
    }

    function mystrripos($haystack, $needle, $offset=0)
    {
        if($offset<0)
        {
            $temp_cut = strrev(  substr( $haystack, 0, abs($offset) )  );
        } else {
            $temp_cut = strrev(  substr( $haystack, $offset )  );
        }
        $pos = strlen($haystack) - (strpos($temp_cut, strrev($needle)) + $offset + strlen($needle));
        if ($pos == strlen($haystack)) { $pos = 0; }

        if(strpos($temp_cut, strrev($needle))===false)
        {
            return false;
        } else return $pos;
    }

    function getlastkillid($data)
    {
        $mylastkillid = 0;
        $startpoint = 0;
        $endpoint = 0;

        $startpoint = $this->mystrripos($data, 'row killID="');
        if ( $startpoint != "0" )
        {
            $startpoint = $startpoint + 12;
            $endpoint = strpos($data, '"', $startpoint);
            $mylength = $endpoint-$startpoint;
            $mylastkillid = substr($data, $startpoint, $mylength);
        }
        return $mylastkillid;
    }

    function getAllianceName($v)
    {
        $alliancenamereturn = "";

        $counter = count($this->alliancearray_['Name']);
        for ($x = 0; $x < $counter; $x++)
        {
            if ($this->alliancearray_['allianceID'][$x] == $v)
                $alliancenamereturn = $this->alliancearray_['Name'][$x];
        }

        return $alliancenamereturn;
    }

	function VerifyKill($killid, $mailid)
	{
		$qry = DBFactory::getDBQuery();;
        $qry->execute( "UPDATE `kb3_kills` SET `kll_external_id` = '" . $killid . "' WHERE `kb3_kills`.`kll_id` =" . $mailid . " LIMIT 1" );
	}

	function isKillIDVerified($killid)
	{
		$qry = DBFactory::getDBQuery();;
        $qry->execute( "SELECT * FROM `kb3_kills` WHERE `kll_external_id` =" . $killid );
		$row = $qry->getRow();
		return $row['kll_external_id'];
	}

	/**
	 * Check if the previous fetches had errors
	 */
	private function fetchErrors($keyindex)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT log_errorcode, log_timestamp FROM kb3_apilog WHERE log_site = '"
			.KB_SITE."' AND log_keyname = '".
			addslashes(config::get('API_Name_'.$keyindex)).
			"' ORDER BY log_timestamp DESC LIMIT 1");
		if($qry->recordCount())
		{
			$row = $qry->getRow();
			if(strtotime($row['log_timestamp']) < time() - 7 * 24 * 60 * 60)
				$errorcode = $row['log_errorcode'];
			else $errorcode = 0;
		}
		else $errorcode = 0;
		// Don't let the cron keep checking jobs that have returned an
		if(($errorcode >= 200 && $errorcode < 300 || $errorcode == 105) &&
				$this->iscronjob_)
			return $errorcode;
		else return false;
	}
}
