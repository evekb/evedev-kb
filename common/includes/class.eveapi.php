<?php
// Report all PHP errors (bitwise 63 may be used in PHP 3)
@error_reporting(E_ALL ^ E_NOTICE);
define ("APIVERSION", "V3.3");

//
// Eve-Dev API Killmail parser by Captain Thunk! (ISK donations are all gratefully received)
//

require_once( "common/includes/class.kill.php" );
require_once( "common/includes/class.parser.php" );
require_once('common/includes/class.pilot.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once( "common/includes/db.php" );
require_once('common/includes/class.apicache.php');

// Checks for configuration of files and folders
if (!file_exists("cache/api")) 
{
    if (!mkdir("cache/api", 0777))
	{
		// creating folder failed - spam something about that
		echo "Failed to create folder 'cache/api' you should create the folder yourself and set chmod 777";
	}
} 

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
		$this->killmailExists_ = false;
		
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
		$this->API_CCPErrorCorrecting = config::get('API_CCPErrorCorrecting');
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
		
		if (is_file(getcwd().'/cache/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
		
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
					$file = fopen(getcwd().'/cache/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml', 'w+');
        			fwrite($file, $data);
       				fclose($file);
					//chmod the file so it can be altered by cronjobs in future
					@chmod(getcwd().'/cache/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml',0666);
				}
			} 
        } else { 
            // re-use cached XML
			$this->Output_ .= "<i>Using cached XML file for " . config::get('API_Name_'.$keyindex) . "</i><br><br>";
			$logsource = "Cache";
			
			if ($fp = @fopen(getcwd().'/cache/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml', 'r')) {
    	    	$data = fread($fp, filesize(getcwd().'/cache/api/'.config::get('API_Name_'.$keyindex).'_KillLog.xml'));
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
            return $this->Output_ .= "<i>Error getting XML data from api.eve-online.com</i><br><br>";

        if ( strlen($data) == 28 ) 
            return $this->Output_ .= "<i>Error contacting api.eve-online.com</i><br><br>";

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
		$qry = new DBQuery();
		if ($this->iscronjob_)
			$logtype = "Cron Job";
		else
			$logtype = "Manual";
			
        $qry->execute( "insert into kb3_apilog	values( '" . KB_SITE . "', '"
														. config::get('API_Name_'.$keyindex) . "',"
														. $this->mailcount_ . ","
														. $this->malformedmails_ . ","
														. $this->ignoredmails_ . ","
														. $this->verified_ . ","
														. $this->totalmails_ . ",'"
														. $logsource . "','"
														. $logtype . "',now() )" );
		
        return $this->Output_;

    }

    function startElement($parser, $name, $attribs) 
    {
		if($this->killmailExists_ && ($name != "ROW" ||  !is_numeric($attribs['KILLID']))) return;
		else $this->killmailExists_ = false;
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
						
						// Error Correction is on (0 = on, 1 = off(I know, just don't ask))
						if ( $this->API_CCPErrorCorrecting == 0 ) 
						{
							if ( ($this->charid_ != "0" ) && (strlen($this->pname_) == 0) )
							{ 
								// name is blank but ID is valid - convert ID into name
								$this->myIDName->clear();
								$this->myIDName->setIDs($this->charid_); 
								$this->Output_ .= $this->myIDName->fetchXML();
								$myNames = $this->myIDName->getIDData();
								$this->pname_ = $myNames[0]['name'];
							}
						} 
                        break;
					case "CORPORATIONID": 
                        $this->corporationID_ = $v;
						break;
                    case "CORPORATIONNAME": 
						$this->corporation_ = $v;
						
						// Error Correction is on (0 = on, 1 = off(I know, just don't ask))
						if ( $this->API_CCPErrorCorrecting == 0 ) 
						{
							if ( ($this->corporationID_ != "0" ) && (strlen($this->corporation_) == 0) ) 
							{ // name is blank but ID is valid - convert ID into name
								$this->myIDName->clear();
								$this->myIDName->setIDs($this->corporationID_); 
								$this->Output_ .= $this->myIDName->fetchXML();
								$myNames = $this->myIDName->getIDData();
								$this->corporation_ = $myNames[0]['name'];
							}
						}
                        break;
                    case "ALLIANCEID":
                        $this->allianceID_ = $v;
                        break;
                    case "ALLIANCENAME": 
						$this->alliance_ = $v;
						
						// Error Correction is on (0 = on, 1 = off(I know, just don't ask))
						//if ( $this->API_CCPErrorCorrecting == 0 ) 
						// conditional branch removed - ALWAYS fix alliance name bugs
						{
							if ( ($this->allianceID_ != "0" ) && (strlen($this->alliance_) == 0) )
							{ // name is blank but ID is valid - convert ID into name
								$this->myIDName->clear();
								$this->myIDName->setIDs($this->allianceID_); 
								$this->Output_ .= $this->myIDName->fetchXML();
								$myNames = $this->myIDName->getIDData();
								$this->alliance_ = $myNames[0]['name'];
							}
						}
						
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
                            $this->shipname_ = gettypeIDname($v);
                        }
                        break;
                    case "FINALBLOW": 
                        $this->finalblow_ = $v; 
                        break;
                    case "SECURITYSTATUS": 
                        //$this->security_ = $v;
						$this->security_ = round($v,2); // allows number to pass with strict settings (number is usually much longer than 5 chars as defined in DB)
                        break;
                    case "WEAPONTYPEID": 
                        $this->weapon_ = gettypeIDname($v);
                        break;
                    // for items
                    case "TYPEID": 
                        $this->typeid_ = gettypeIDname($v);
						
						// Missing Item correction
						if ($this->typeid_ == "")
						{
							$this->myIDName->clear();
							$this->myIDName->setIDs($v); 
							$this->Output_ .= $this->myIDName->fetchXML();
							$myNames = $this->myIDName->getIDData();
							//$this->typeid_ = "Item missing from DB: " . $myNames[0]['name'];
							$this->typeid_ = $myNames[0]['name'];
						}
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
						$this->killid_ = $v; // added v2.6 for help tracing erroneous mails
						$this->totalmails_++; // Count total number of mails in XML feed
						if ($this->isKillIDVerified($v) != null)
						{
							$this->killmailExists_ = true;
        unset($this->destroyeditems_ );
        unset($this->droppeditems_);
		$this->beforekillid_ = 0;
							return;
						} else {
							$this->killmailExists_ = false;
	                        $this->beforekillid_ = $v;
						}
                        break;
                    case "SOLARSYSTEMID": // convert to system name and fetch system security - DONE
                        $sql = 'select sys.sys_name, sys.sys_sec from kb3_systems sys where sys.sys_eve_id = '.$v;

                        $qry = new DBQuery();
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
						
						$this->moonname_ = getMoonName($v);	
						// Missing Moon DB correction
						if (($this->moonname_ == "") && ($this->moonid_ != 0))
						{
							$this->myIDName->clear();
							$this->myIDName->setIDs($v); 
							$this->Output_ .= $this->myIDName->fetchXML();
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
		if($this->killmailExists_ && $name != "RESULT") return;
		else $this->killmailExists_ = false;
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
                } elseif (($this->moonid_ == 0) && ($this->pname_ == "") && ($this->charid_ != 0)) {
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
					// update Victim portrait while we're here
                    $sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $this->pname_ . '"';

					$qry = new DBQuery();
                    $qry->execute($sql);
                    $row = $qry->getRow();
                    if ($qry->recordCount() != 0)
                    {
                        $pilot_id = $row['plt_id'];
                        $pilot_external_id = $row['plt_externalid'];

                        if ( $pilot_external_id == 0 && $pilot_id != 0)
                        {	
							// update DB with ID
                            $qry->execute("update kb3_pilots set plt_externalid = " . intval($this->charid_) . "
                                            where plt_id = " . $pilot_id);
                        } 
                    }
                }
				
				// update crp_external_id
				Update_CorpID($this->corporation_, $this->corporationID_);
				// update all_external_id
				if ($this->allianceID_ != 0)
					Update_AllianceID($this->alliance_, $this->allianceID_);
					
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
						// update Attacker portrait while we're here
                        $sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $this->pname_ . '"';

                        $qry = new DBQuery();
                        $qry->execute($sql);
                        $row = $qry->getRow();
                        if ($qry->recordCount() != 0)
                        {						
                            $pilot_id = $row['plt_id'];
                            $pilot_external_id = $row['plt_externalid'];

                            if ( $pilot_external_id == 0 && $pilot_id != 0 )
                            {	
								// update DB with ID
                                $qry->execute("update kb3_pilots set plt_externalid = " . intval($this->charid_) . "
                                                where plt_id = " . $pilot_id);
                            }
                        }
                    }
					
					// update crp_external_id
					Update_CorpID($this->corporation_, $this->corporationID_);
					// update all_external_id
					if ($this->allianceID_ != 0)
						Update_AllianceID($this->alliance_, $this->allianceID_);
						
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
                //$ApiCache->set('API_CachedUntil_' . $this->keyindex_, $this->cachetext_);
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

        // If ignoring friendly POS Structures
        if ($this->isposkill_) {
        // is board an alliance board?
        	if ( ALLIANCE_ID == 0)
            { 
				// no it's set as a corp
                $thiscorp = new Corporation(CORP_ID);
                if ( $this->vcorp_ == $thiscorp->getName() )
                	$poswasfriendly = true;
            } else { 
				// yes it's an Alliance board
            	$thisalliance = new Alliance(ALLIANCE_ID);
                if ( $this->valliance_ == $thisalliance->getName() )
                    $poswasfriendly = true;
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
            $parser = new Parser( $this->killmail_ );
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

            if ( $killid == 0 || $killid == -1 || $killid == -2 || $killid == -3 ) 
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
						
						//if ( $this->iscronjob_ )
						//{
						//	$this->Output_ .= $this->killmail_; // experimental - output the killmail as the API Parser understood it
						//} else {
						//	$this->Output_ .= str_replace("\r\n", "<br>", $this->killmail_);
						//}
						$this->Output_ .= '<br/>';
                    }
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
                    $this->Output_ .= "Killmail ID:".$this->killid_. " is not related to ".KB_TITLE.".<br>";
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
						$this->VerifyKill($this->killid_, $parser->dupeid_);
						$this->verified_++;
                    }
                } 
            } else {
                $qry = new DBQuery();
                $qry->execute( "insert into kb3_log	values( ".$killid.", '".KB_SITE."','API ".APIVERSION."',now() )" );
                $this->Output_ .= "API Killmail ID:".$this->killid_. " successfully imported <a href=\"?a=kill_detail&amp;kll_id=".$killid."\">here</a> as KB ID:". $killid ."<br>";
			
				// Now place killID (API) into killboard row $killid
				$this->VerifyKill($this->killid_, $killid);
				
				// mail forward
				event::call('killmail_imported', $this);

				// For testing purposes
				//$this->Output_ .= str_replace("\r\n", "<br>", $this->killmail_);
				
				if ( file_exists("common/includes/class.comments.php") ) 
				  	require_once( "common/includes/class.comments.php" );
                if (class_exists('Comments') && config::get('API_Comment')) { // for the Eve-Dev Comment Class
                    $comments = new Comments($killid);
                    $comments->addComment("Captain Thunks API " . APIVERSION, config::get('API_Comment'));
                }
                $this->mailcount_++;
            }
        }
    }

    function loaddata($refid, $keystring, $typestring)
    {
        $url = "http://api.eve-online.com/" . $typestring . "/KillLog.xml.aspx";

        if ($refid != 0)
            $keystring .= '&beforeKillID=' . $refid;

        $path = '/' . $typestring . '/Killlog.xml.aspx';
        $fp = fsockopen("api.eve-online.com", 80);

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
        $startpoint = intval;
        $endpoint = intval;

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
		$qry = new DBQuery();
        $qry->execute( "UPDATE `kb3_kills` SET `kll_external_id` = '" . $killid . "' WHERE `kb3_kills`.`kll_id` =" . $mailid . " LIMIT 1" );
	}
	
	function isKillIDVerified($killid)
	{
		$qry = new DBQuery();
        $qry->execute( "SELECT * FROM `kb3_kills` WHERE `kll_external_id` =" . $killid );
		$row = $qry->getRow();
		return $row['kll_external_id'];
	}
}

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

            $qry = new DBQuery();
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
        $fp = fsockopen("api.eve-online.com", 80);

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
            
        $qry = new DBQuery();
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

            foreach ($tempally as $key => $value)
            {
                switch ($key)
                {
                    case "allianceName":
                        //return $tempally;
						if ( $value == $name )
						{
							return $tempally;
						}
                        break;
                }
            }                  
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

            foreach ($tempally as $key => $value)
            {
                switch ($key)
                {
                    case "allianceID":
                        //return $tempally;
						if ( $value == $id )
						{
							return $tempally;
						}
                        break;
                }
            }                  
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
			$db = new DBQuery(true);
			$db->execute("UPDATE kb3_corps
							SET crp_all_id = 14");
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
// ****************                 API Conquerable Station/Outpost list - /eve/ConquerableStationList.xml.aspx                  ****************
// **********************************************************************************************************************************************

class API_ConquerableStationList 
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function getStations()
	{
		return $this->Stations_;
	}
	
    function fetchXML() 
    {
        $data = LoadGlobalData('/eve/ConquerableStationList.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/eve/ConquerableStationList.xml.aspx </i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->html;
    }

    function startElement($parser, $name, $attribs) 
    {
		global $Station;
		
        if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "STATIONID":
                            $Station['stationID'] = $v;
                            break;
                        case "STATIONNAME":  
                            $Station['stationName'] = $v;
                            break;
                        case "STATIONTYPEID":  
                            $Station['stationTypeID'] = $v;
                            break;
                        case "SOLARSYSTEMID":  
                            $Station['solarSystemID'] = $v;
                            break;
						case "CORPORATIONID":  
                            $Station['corporationID'] = $v;
                            break;
						case "CORPORATIONNAME":  
                            $Station['corporationName'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name) 
    {
		global $Station;
		global $tempvalue;
		
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_conq') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else {
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_eve_ConquerableStationList' , $this->CachedUntil_);
		}
		
        if ($name == "ROW")
		{
			$this->Stations_[] = $Station;
			$Station = array();
			unset($Station);
		}
    }

    function characterData($parser, $data) 
    {
        global $tempvalue;
        
		$tempvalue = $data;
    }
}

// **********************************************************************************************************************************************
// ****************                                   API Error list - /eve/ErrorList.xml.aspx                                   ****************
// **********************************************************************************************************************************************

class API_ErrorList 
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function getErrorList()
	{
		return $this->Error_;
	}
	
    function fetchXML() 
    {
        $data = LoadGlobalData('/eve/ErrorList.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/eve/ErrorList.xml.aspx </i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->html;
    }

    function startElement($parser, $name, $attribs) 
    {
		global $ErrorData;
		
        if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "ERRORCODE":
                            $ErrorData['errorCode'] = $v;
                            break;
                        case "ERRORTEXT":  
                            $ErrorData['errorText'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name) 
    {
		global $ErrorData;
		global $tempvalue;
		
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $tempvalue;
			ApiCache::set('API_eve_ErrorList' , $tempvalue);
		}
		
        if ($name == "ROW")
		{
			$this->Error_[] = $ErrorData;
			$ErrorData = array();
			unset($ErrorData);
		}
    }

    function characterData($parser, $data) 
    {
        global $tempvalue;
        
		$tempvalue = $data;
    }
}

// **********************************************************************************************************************************************
// ****************                                   API Jumps list - /map/Jumps.xml.aspx                                   ****************
// **********************************************************************************************************************************************

class API_Jumps 
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function getDataTime()
	{
		return $this->DataTime_;
	}
	
	function getJumps()
	{
		return $this->Jumps_;
	}
	
    function fetchXML() 
    {
        $data = LoadGlobalData('/map/Jumps.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/map/Jumps.xml.aspx </i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->html;
    }

    function startElement($parser, $name, $attribs) 
    {
		global $JumpData;
		
        if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "SOLARSYSTEMID":
                            $JumpData['solarSystemID'] = $v;
                            break;
                        case "SHIPJUMPS":  
                            $JumpData['shipJumps'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name) 
    {
		global $JumpData;
		global $tempvalue;
		
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "DATATIME")
			$this->DataTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $tempvalue;
			ApiCache::set('API_map_Jumps' , $tempvalue);
		}
		
        if ($name == "ROW")
		{
			$this->Jumps_[] = $JumpData;
			$JumpData = array();
			unset($JumpData);
		}
    }

    function characterData($parser, $data) 
    {
        global $tempvalue;
        
		$tempvalue = $data;
    }
}

// **********************************************************************************************************************************************
// ****************                                   API Kills list - /map/Kills.xml.aspx                                   ****************
// **********************************************************************************************************************************************

class API_Kills 
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function getDataTime()
	{
		return $this->DataTime_;
	}
	
	function getkills()
	{
		return $this->kills_;
	}
	
    function fetchXML() 
    {
        $data = LoadGlobalData('/map/Kills.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/map/Kills.xml.aspx </i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->html;
    }

    function startElement($parser, $name, $attribs) 
    {
		global $KillsData;
		
        if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "SOLARSYSTEMID":
                            $KillsData['solarSystemID'] = $v;
                            break;
                        case "SHIPKILLS":  
                            $KillsData['shipKills'] = $v;
                            break;
						case "FACTIONKILLS":  
                            $KillsData['factionKills'] = $v;
                            break;
						case "PODKILLS":  
                            $KillsData['podKills'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name) 
    {
		global $KillsData;
		global $tempvalue;
		
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "DATATIME")
			$this->DataTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $tempvalue;
			ApiCache::set('API_map_Kills' , $tempvalue);
		}
		
        if ($name == "ROW")
		{
			$this->kills_[] = $KillsData;
			$KillsData = array();
			unset($KillsData);
		}
    }

    function characterData($parser, $data) 
    {
        global $tempvalue;
        
		$tempvalue = $data;
    }
}

// **********************************************************************************************************************************************
// ****************                            API Alliance Sovereignty - /map/Sovereignty.xml.aspx                              ****************
// **********************************************************************************************************************************************

class API_Sovereignty
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function getDataTime()
	{
		return $this->DataTime_;
	}
	
	function getSovereignty()
	{
		return $this->Sovereignty_;
	}

    function fetchXML() 
    {
        $data = LoadGlobalData('/map/Sovereignty.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/map/Sovereignty.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->html;
    }

    function startElement($parser, $name, $attribs) 
    {
		global $SovereigntyData;
		
        if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "SOLARSYSTEMID":
                            $SovereigntyData['solarSystemID'] = $v;
                            break;
                        case "ALLIANCEID":  
                            $SovereigntyData['allianceID'] = $v;
                            break;
						case "CONSTELLATIONSOVEREIGNTY":  
                            $SovereigntyData['constellationSovereignty'] = $v;
                            break;
						case "SOVEREIGNTYLEVEL":  
                            $SovereigntyData['sovereigntyLevel'] = $v;
                            break;
						case "FACTIONID":  
                            $SovereigntyData['factionID'] = $v;
                            break;
						case "SOLARSYSTEMNAME":  
                            $SovereigntyData['solarSystemName'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name) 
    {
		global $SovereigntyData;
		global $tempvalue;
		
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "DATATIME")
			$this->DataTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_sovereignty') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else {
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_map_Sovereignty' , $this->CachedUntil_);
		}
			
        if ($name == "ROW")
		{
			$this->Sovereignty_[] = $SovereigntyData;
			$SovereigntyData = array();
			unset($SovereigntyData);
		}
    }

    function characterData($parser, $data) 
    {
		global $tempvalue;
        
		$tempvalue = $data;
    }
	
	function getSystemDetails($sysname)
	{
		if (!isset($this->Sovereignty_))
            $this->fetchXML();
        
        if (!isset($this->Sovereignty_))
            return false;
		
		$Sov = $this->Sovereignty_;
		
		foreach ($Sov as $myTempData)
		{
			if ($myTempData['solarSystemName'] == $sysname)
				return $myTempData;
		}
		
		return;
	}
	
	function getSystemIDDetails($sysID)
	{
		if (!isset($this->Sovereignty_))
            $this->fetchXML();
        
        if (!isset($this->Sovereignty_))
            return false;
			
		//echo var_dump($this->Sovereignty_);
		
		$Sov = $this->Sovereignty_;
		
		foreach ($Sov as $myTempData)
		{
			if ($myTempData['solarSystemID'] == $sysID)
				return $myTempData;
		}
		
		return false;
	}
}

// **********************************************************************************************************************************************
// ****************                               API Reference Types - /eve/RefTypes.xml.aspx                                   ****************
// **********************************************************************************************************************************************

class API_RefTypes
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function getRefTypes()
	{
		return $this->RefTypes_;
	}
	
    function fetchXML() 
    {
        $data = LoadGlobalData('/eve/RefTypes.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/eve/RefTypes.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->html;
    }

    function startElement($parser, $name, $attribs) 
    {
		global $RefTypeData;
		
        if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "REFTYPEID":
                            $RefTypeData['refTypeID'] = $v;
                            break;
                        case "REFTYPENAME":  
                            $RefTypeData['refTypeName'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name) 
    {
		global $RefTypeData;
		global $tempvalue;
		
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $tempvalue;
			ApiCache::set('API_eve_RefTypes' , $tempvalue);
		}
			
        if ($name == "ROW")
		{
			$this->RefTypes_[] = $RefTypeData;
			$RefTypeData = array();
			unset($RefTypeData);
		}
    }

    function characterData($parser, $data) 
    {
        global $tempvalue;
       
		$tempvalue = $data;
    }
}

// **********************************************************************************************************************************************
// ****************                           API Faction War Systems - /map/FacWarSystems.xml.aspx                              ****************
// **********************************************************************************************************************************************

class API_FacWarSystems
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function getFacWarSystems()
	{
		return $this->FacWarSystems_;
	}
	
    function fetchXML() 
    {
        $data = LoadGlobalData('/map/FacWarSystems.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/map/FacWarSystems.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->FacWarSystems_;
    }

    function startElement($parser, $name, $attribs) 
    {
		global $FacWarSystem;
		
        if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "SOLARSYSTEMID":
                            $FacWarSystem['solarSystemID'] = $v;
                            break;
                        case "SOLARSYSTEMNAME":  
                            $FacWarSystem['solarSystemName'] = $v;
                            break;
						case "OCCUPYINGFACTIONID":
                            $FacWarSystem['occupyingFactionID'] = $v;
                            break;
						case "OCCUPYINGFACTIONNAME":
                            $FacWarSystem['occupyingFactionName'] = $v;
                            break;
						case "CONTESTED":
                            $FacWarSystem['contested'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name) 
    {
		global $FacWarSystem;
		global $tempvalue;
		
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_facwarsystems') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else {
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_map_FacWarSystems' , $this->CachedUntil_);
		}
		
        if ($name == "ROW")
		{
			$this->FacWarSystems_[] = $FacWarSystem;
			$FacWarSystem = array();
			unset($FacWarSystem);
		}
    }

    function characterData($parser, $data) 
    {
        global $tempvalue;
       
		$tempvalue = $data;
    }
}

// **********************************************************************************************************************************************
// ****************                                  API Standings - /corp & char/Standings.xml.aspx                             ****************
// **********************************************************************************************************************************************
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
	function getAliianceAlliances()
	{
		return $this->AliianceAlliances_;
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

				$myKeyString = "userID=" . $this->API_userID_ . "&apiKey=" . $this->API_apiKey_ . "&characterID=" . $this->API_characterID_;
				
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
            return "<i>Error getting XML data from api.eve-online.com/Standings.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
		
		// sort the arrays (in descending order of standing)
		$this->Factions_ = $this->mysortarray($this->Factions_);
		$this->Characters_ = $this->mysortarray($this->Characters_);
		$this->Corporations_ = $this->mysortarray($this->Corporations_);
		$this->Alliances_ = $this->mysortarray($this->Alliances_);
		$this->Agents_ = $this->mysortarray($this->Agents_);
		$this->NPCCorporations_ = $this->mysortarray($this->NPCCorporations_);
		$this->AllianceCorporations_ = $this->mysortarray($this->AllianceCorporations_);
		$this->AllianceAlliances_ = $this->mysortarray($this->AliianceAlliances_);
		
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
			ApiCache::set( $this->API_characterID_ . '_Standings' , $this->characterDataValue);
		}
    }

    function characterData($parser, $data) 
    {
		$this->characterDataValue = $data;
    }
	
	function loaddata($keystring, $typestring)
    {
		$configvalue = $this->API_characterID_ . '_Standings';
		
		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');
		
        $url = "http://api.eve-online.com/" . $typestring . "/Standings.xml.aspx" . $keystring;
        $path = "/" . $typestring . "/Standings.xml.aspx";
			
		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($CachedTime == "")
    	{
        	$CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}
		
		if (is_file(getcwd().'/cache/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
			
		
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )// if API_UseCache = 1 (off) then don't use cache
    	{
        	$fp = fsockopen("api.eve-online.com", 80);

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
			
				if ($UseCaching == 0) // Save the file if we're caching (0 = true in Thunks world)
				{
					$file = fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'w+');
        			fwrite($file, $contents);
       				fclose($file);
					@chmod(getcwd().'/cache/api/'.$configvalue.'.xml',0666);
				} 
        	} 
		} else {
			// re-use cached XML
			if ($fp = @fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(getcwd().'/cache/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";  
    		}
		}
        return $contents;
    }
}

// **********************************************************************************************************************************************
// ****************                                 API Character Sheet - char/CharacterSheet.xml.aspx                           ****************
// **********************************************************************************************************************************************
// Incomplete - Does not read Certificates or Roles
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
			require_once('class.pilot.php');
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();
			
			$this->CharName_ = $usersname;  // $this->CharName_ is used later for config key value for caching
			
			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = new DBQuery();
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
			return "You are not logged in.";
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/CharacterSheet.xml.aspx  </i><br><br>";

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
							$tempdata['SkillName'] = gettypeIDname($v);
							$tempdata['GroupID'] = getgroupID($v);
							$tempdata['GroupName'] = getgroupIDname($tempdata['GroupID']);
							$tempdata['Rank'] = gettypeIDrank($v);
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
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			ApiCache::set( $this->CharName_ . '_CharacterSheet' , $this->characterDataValue);
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
		
        $url = "http://api.eve-online.com/char/CharacterSheet.xml.aspx" . $keystring;

        $path = '/char/CharacterSheet.xml.aspx';
		
		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($CachedTime == "")
    	{
        	$CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}
		
		if (is_file(getcwd().'/cache/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
			
		// if API_UseCache = 1 (off) then don't use cache
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )
    	{
        	$fp = fsockopen("api.eve-online.com", 80);

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
					$file = fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'w+');
        			fwrite($file, $contents);
       				fclose($file);
					@chmod(getcwd().'/cache/api/'.$configvalue.'.xml',0666);
				} 
        	} 
		} else {
			// re-use cached XML
			if ($fp = @fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(getcwd().'/cache/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";  
    		}
		}
        return $contents;
    }
}

// **********************************************************************************************************************************************
// ****************                                 API Skill Training Sheet - char/SkillInTraining.xml.aspx                     ****************
// **********************************************************************************************************************************************
class API_SkillInTraining 
{		

	function getSkillInTraining()
	{
		return $this->SkillInTraining_;
	}
	
	function getCurrentTQTime()
	{
		return $this->CurrentTQTime_;
	}
	
	function getTrainingEndTime()
	{
		return $this->TrainingEndTime_;
	}
	
	function getTrainingStartTime()
	{
		return $this->TrainingStartTime_;
	}
	
	function getTrainingTypeID()
	{
		return $this->TrainingTypeID_;
	}
	
	function getTrainingStartSP()
	{
		return $this->TrainingStartSP_;
	}
	
	function getTrainingDestinationSP()
	{
		return $this->TrainingDestinationSP_;
	}
	
	function getTrainingToLevel()
	{
		return $this->TrainingToLevel_;
	}
	
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getSPTrained()
	{
		return $this->SPTrained_;
	}
	
	function getTrainingTimeRemaining()
	{
		return $this->TrainingTimeRemaining_;
	}
	
	function fetchXML()
	{
		// is a player feed - take details from logged in user
		if (user::get('usr_pilot_id'))
    	{
			require_once('class.pilot.php');
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();
			
			$this->CharName_ = $usersname;
			
			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = new DBQuery();
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
			return "You are not logged in.";
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/SkillInTraining.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
		
		// Calculate Time Training remaining and amount of SP Done
		if ($this->SkillInTraining_ == 1)
		{
			$trainingleft = $this->TrainingEndTime_;

    		$now       = time();
			$gmmktime  = gmmktime();
    		$finaltime = $gmmktime - $now;

    		$year   = (int)substr($trainingleft, 0, 4);
    		$month  = (int)substr($trainingleft, 5, 2);
    		$day    = (int)substr($trainingleft, 8, 2);
    		$hour   = (int)substr($trainingleft, 11, 2) + (($finaltime > 0) ? floor($finaltime / 60 / 60) : 0); //2007-06-22 16:47:50
    		$minute = (int)substr($trainingleft, 14, 2);
    		$second = (int)substr($trainingleft, 17, 2);

    		$difference = gmmktime($hour, $minute, $second, $month, $day, $year) - $now;
			$timedone = $difference;
    		if ($difference >= 1) 
			{
        		$days = floor($difference/86400);
        		$difference = $difference - ($days*86400);
        		$hours = floor($difference/3600);
        		$difference = $difference - ($hours*3600);
        		$minutes = floor($difference/60);
        		$difference = $difference - ($minutes*60);
        		$seconds = $difference;
				$this->TrainingTimeRemaining_ = "$days Days, $hours Hours, $minutes Minutes and $seconds Seconds.";
    		} else {
        		$this->TrainingTimeRemaining_ = "Done !";
    		}
		
			// Calculate SP done by using the ratio gained from Time spent training so far.
    		$finaltime = strtotime($this->TrainingEndTime_) - strtotime($this->TrainingStartTime_); // in seconds
			$ratio =  1 - ($timedone / $finaltime);
			$this->SPTrained_ = ($this->TrainingDestinationSP_ - $this->TrainingStartSP_) * $ratio;
		}
		
		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
	}
	
	function startElement($parser, $name, $attribs) 
    {
		// Nothing to do here
    }				

    function endElement($parser, $name) 
    {
		// Details
		if ($name == "SKILLINTRAINING")
			$this->SkillInTraining_ = $this->characterDataValue;
		if ($name == "CURRENTTQTIME")
			$this->CurrentTQTime_ = $this->characterDataValue;
		if ($name == "TRAININGENDTIME")
			$this->TrainingEndTime_ = $this->characterDataValue;
		if ($name == "TRAININGSTARTTIME")
			$this->TrainingStartTime_ = $this->characterDataValue;
		if ($name == "TRAININGTYPEID")
			$this->TrainingTypeID_ = $this->characterDataValue;
		if ($name == "TRAININGSTARTSP")
			$this->TrainingStartSP_ = $this->characterDataValue;
		if ($name == "TRAININGDESTINATIONSP")
			$this->TrainingDestinationSP_ = $this->characterDataValue;
		if ($name == "TRAININGTOLEVEL")
			$this->TrainingToLevel_ = $this->characterDataValue;
			
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			ApiCache::set( $this->CharName_ . '_SkillInTraining' , $this->characterDataValue);
		}
    }

    function characterData($parser, $data) 
    {
		$this->characterDataValue = $data;
    }
	
	function loaddata($keystring)
    {
		$configvalue = $this->CharName_ . '_SkillInTraining';
		
		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');
		
        $url = "http://api.eve-online.com/char/SkillInTraining.xml.aspx" . $keystring;

        $path = '/char/SkillInTraining.xml.aspx';
		
		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($CachedTime == "")
    	{
        	$CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}
		
		if (is_file(getcwd().'/cache/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
			
		// if API_UseCache = 1 (off) then don't use cache
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )
    	{
        	$fp = fsockopen("api.eve-online.com", 80);

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
					$file = fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'w+');
        			fwrite($file, $contents);
       				fclose($file);
					@chmod(getcwd().'/cache/api/'.$configvalue.'.xml',0666);
				} 
        	} 
		} else {
			// re-use cached XML
			if ($fp = @fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(getcwd().'/cache/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";  
    		}
		}
        return $contents;
    }
}

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
			require_once('class.pilot.php');
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();
			
			$this->CharName_ = $usersname;
			
			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = new DBQuery();
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

                        	$qry = new DBQuery();
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
		
		if (is_file(getcwd().'/cache/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
			
		// if API_UseCache = 1 (off) then don't use cache
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )
    	{
        	$fp = fsockopen("api.eve-online.com", 80);

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
					$file = fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'w+');
        			fwrite($file, $contents);
       				fclose($file);
					@chmod(getcwd().'/cache/api/'.$configvalue.'.xml',0666);
				} 
        	} 
		} else {
			// re-use cached XML
			if ($fp = @fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(getcwd().'/cache/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";  
    		}
		}
        return $contents;
    }
}

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
			require_once('class.pilot.php');
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();
			
			$this->CharName_ = $usersname;
			
			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = new DBQuery();
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
			
			$myKeyString = "userID=" . $API_userID . "&apiKey=" . $API_apiKey . "&characterID=" . $API_charID . "&itemID=" . $this->API_itemID_;
				
			$data = $this->loaddata($myKeyString);
		} else {
			if (($this->API_userID_ != "") && ($this->API_apiKey_ != "") && ($this->API_charID_ != "") && ($this->API_itemID_ != ""))
			{
				$myKeyString = "userID=" . $this->API_userID_ . "&apiKey=" . $this->API_apiKey_ . "&characterID=" . $this->API_charID_ . "&itemID=" . $this->API_itemID_;
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
            return "<i>Error getting XML data from api.eve-online.com/StarbaseDetail.xml.aspx  </i><br><br>";

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
							$fueldata['typeName'] = gettypeIDname($v);
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

        $url = "http://api.eve-online.com/corp/StarbaseDetail.xml.aspx" . $keystring;

        $path = '/corp/StarbaseDetail.xml.aspx';
		
		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($CachedTime == "")
    	{
        	$CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}
		
		if (is_file(getcwd().'/cache/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
			
		// if API_UseCache = 1 (off) then don't use cache
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )
    	{
        	$fp = fsockopen("api.eve-online.com", 80);

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
					$file = fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'w+');
        			fwrite($file, $contents);
       				fclose($file);
					@chmod(getcwd().'/cache/api/'.$configvalue.'.xml',0666);
				} 
        	} 
		} else {
			// re-use cached XML
			if ($fp = @fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(getcwd().'/cache/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";  
    		}
		}
        return $contents;
    }
}

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
			$myKeyString = "corporationID=" . $this->API_corpID_;
			$this->CharName_ = $this->API_corpID_;
			$data = $this->loaddata($myKeyString);
			
		} elseif (user::get('usr_pilot_id')) {
			require_once('class.pilot.php');
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();
			
			$this->CharName_ = $usersname;
			
			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = new DBQuery();
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
			
			$myKeyString = "userID=" . $API_userID . "&apiKey=" . $API_apiKey . "&characterID=" . $API_charID;
				
			$data = $this->loaddata($myKeyString);
		} else {
			if (($this->API_userID_ != "") && ($this->API_apiKey_ != "") && ($this->API_charID_ != ""))
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
            return "<i>Error getting XML data from api.eve-online.com/CorporationSheet.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
		
		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
	}
	
	function startElement($parser, $name, $attribs) 
    {
		$this->characterDataValue = "";
		
		if ($name == "DESCRIPTION")
		{
			$this->DataIsDescription = true;
			//$this->characterDataValue = "";
		} else {
			$this->DataIsDescription = false;
		}
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
		//  This is a fix for some hosts that strip "<" and ">" from the API XML when it's put into a string. I have no idea why this happens, where or how - but this puts them back
		if ($this->DataIsDescription)
		{
			if ( $data == "<" )
			{
				$this->tagsareworking = true;
			}
		
			if (!$this->tagsareworking)
			{
				if ( ($data == "br") || ($data == "b") || ($data == "/a") || ($data == "/b") || ($data == "/font") || (substr($data,0,4)== "font") || (substr($data,0,6)== "a href"))
				{
					$data = "<" .$data.">";
				}
			}
			$this->characterDataValue .= $data;
		} else {
			$this->characterDataValue = $data;
		}
		
		
		//echo $data;
    }
	
	function loaddata($keystring)
    {	
		$configvalue = $this->CharName_ . '_CorporationSheet';
		
		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');

        $url = "http://api.eve-online.com/corp/CorporationSheet.xml.aspx" . $keystring;

        $path = '/corp/CorporationSheet.xml.aspx';
		
		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($CachedTime == "")
    	{
        	$CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}
		
		if (is_file(getcwd().'/cache/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;
			
		// if API_UseCache = 1 (off) then don't use cache
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )
    	{
        	$fp = fsockopen("api.eve-online.com", 80);

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
					$file = fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'w+');
        			fwrite($file, $contents);
       				fclose($file);
					@chmod(getcwd().'/cache/api/'.$configvalue.'.xml',0666);
				} 
        	} 
		} else {
			// re-use cached XML
			if ($fp = @fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(getcwd().'/cache/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";  
    		}
		}
        return $contents;
    }
}
// **********************************************************************************************************************************************
// ****************                                   API Name -> ID Conversion /eve/CharacterID.xml.aspx 	                     ****************
// **********************************************************************************************************************************************
class API_NametoID 
{		
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function setNames($names)
	{
		$this->API_Names_ = $names;
	}
	function getNameData()
	{
		return $this->NameData_;
	}
	
	function clear()
	{
		$this->NameData_ = array();
		unset($this->NameData_);
	}
	
	function fetchXML()
	{
		if ($this->API_Names_ != "") 
		{
			$myKeyString = "names=" . $this->API_Names_;
			$data = $this->loaddata($myKeyString);
		} else {
			return "No Names have been input.";
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/CharacterID.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
		
		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
	}
	
	function startElement($parser, $name, $attribs) 
    {
		global $NameData;
		
		if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "NAME":
                            $NameData['name'] = $v;
                            break;
                        case "CHARACTERID":  
                            $NameData['characterID'] = $v;
                            break;
                    }
                }
            }
        }
    }				

    function endElement($parser, $name) 
    {
		global $NameData;
		
		// Details
		if ($name == "ERROR")
			$this->html .= $this->characterDataValue;
		
		if ($name == "ROW")
		{
			$this->NameData_[] = $NameData;
			$NameData = array();
			unset($NameData);
		}
			
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL") // cache not needed for this process
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			//ApiCache::set( $this->CharName_ . 'CharacterID' , $this->characterDataValue);
		} 
    }

    function characterData($parser, $data) 
    {
		$this->characterDataValue = $data;
    }
	
	function loaddata($keystring)
    {	
        $url = "http://api.eve-online.com/eve/CharacterID.xml.aspx" . $keystring;

        $path = '/eve/CharacterID.xml.aspx';
		
		
        $fp = fsockopen("api.eve-online.com", 80);

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
        } 

        return $contents;
    }
}
// **********************************************************************************************************************************************
// ****************                                   API ID -> Name Conversion /eve/CharacterID.xml.aspx 	                     ****************
// **********************************************************************************************************************************************
class API_IDtoName 
{		
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}
	
	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}
	
	function setIDs($IDs)
	{
		$this->API_IDs_ = $IDs;
	}
	function getIDData()
	{
		return $this->NameData_;
	}
	
	function clear()
	{
		$this->NameData_ = array();
		unset($this->NameData_);
	}
	
	function fetchXML()
	{
		if ($this->API_IDs_ != "") 
		{
			$myKeyString = "ids=" . $this->API_IDs_;
			$data = $this->loaddata($myKeyString);
		} else {
			return "No IDs have been input.";
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/CharacterName.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);
		
		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
	}
	
	function startElement($parser, $name, $attribs) 
    {
		global $NameData;
		
		if ($name == "ROW") 
        { 
            if (count($attribs)) 
            {
                foreach ($attribs as $k => $v) 
                {
                    switch ($k) 
                    {
                        case "NAME":
                            $NameData['name'] = $v;
                            break;
                        case "CHARACTERID":  
                            $NameData['characterID'] = $v;
                            break;
                    }
                }
            }
        }
    }				

    function endElement($parser, $name) 
    {
		global $NameData;
		
		// Details
		if ($name == "ERROR")
			$this->html .= $this->characterDataValue;
		
		if ($name == "ROW")
		{
			$this->NameData_[] = $NameData;
			$NameData = array();
			unset($NameData);
		}
			
		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL") // cache not needed for this process
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			//ApiCache::set( $this->CharName_ . 'CharacterID' , $this->characterDataValue);
		} 
    }

    function characterData($parser, $data) 
    {
		$this->characterDataValue = $data;
    }
	
	function loaddata($keystring)
    {	
        $url = "http://api.eve-online.com/eve/CharacterName.xml.aspx" . $keystring;

        $path = '/eve/CharacterName.xml.aspx';
		
		
        $fp = fsockopen("api.eve-online.com", 80);

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
        } 

        return $contents;
    }
}

// **********************************************************************************************************************************************
// ****************                                API Server Status - /server/ServerStatus.xml.aspx                             ****************
// **********************************************************************************************************************************************

class API_ServerStatus
{
   function getCachedUntil()
   {
      return $this->CachedUntil_;
   }
   
   function getCurrentTime()
   {
      return $this->CurrentTime_;
   }
   
   function getserverOpen()
   {
      return $this->serverOpen_;
   }
   
   function getonlinePlayers()
   {
      return $this->onlinePlayers_;
   }

    function fetchXML() 
    {
        $data = LoadGlobalData('/server/ServerStatus.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/server/ServerStatus.xml.aspx</i><br><br>";

        xml_parser_free($xml_parser);
     
        return $this->html;
    }

	function startElement($parser, $name, $attribs)
    {
    // nothing to do here...
    }

    function endElement($parser, $name) 
    {
      global $tempvalue;
      
      if ($name == "CURRENTTIME")
         $this->CurrentTime_ = $tempvalue;
      if ($name == "SERVEROPEN")
         $this->serverOpen_ = $tempvalue;
      if ($name == "ONLINEPLAYERS")
         $this->onlinePlayers_ = $tempvalue;
      if ($name == "CACHEDUNTIL")
      {
         $this->CachedUntil_ = $tempvalue;
		 ApiCache::set( 'API_server_ServerStatus' , $tempvalue);
      }
    }
    
	function characterData($parser, $data) 
    {
      global $tempvalue;
        
      $tempvalue = $data;
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
	$temppath = substr($path, 0, strlen($path) - 14);
	$configvalue = "API" . str_replace("/", "_", $temppath);
		
	$CachedTime = ApiCache::get($configvalue);
	$UseCaching = config::get('API_UseCache');
		
	// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
	if ($CachedTime == "")
    {
        $CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    }
		
	if (is_file(getcwd().'/cache/api/'.$configvalue.'.xml'))
		$cacheexists = true;
	else
		$cacheexists = false;
	
	if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )// if API_UseCache = 1 (off) then don't use cache
    {
        $fp = fsockopen("api.eve-online.com", 80);

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
				$file = fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'w+');
        		fwrite($file, $contents);
       			fclose($file);
				@chmod(getcwd().'/cache/api/'.$configvalue.'.xml',0666);
			} 
        } 
	} else {
		// re-use cached XML
		if ($fp = @fopen(getcwd().'/cache/api/'.$configvalue.'.xml', 'r')) {
    	    $contents = fread($fp, filesize(getcwd().'/cache/api/'.$configvalue.'.xml'));
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
				
    $qry = new DBQuery();
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
				
    $qry = new DBQuery();
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
	
    $qry = new DBQuery();
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
				
    $qry = new DBQuery();
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
		$sql = 'select moon.itemID, moon.itemName from kb3_moons moon where moon.itemID = '.$id;

        $qry = new DBQuery();
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

    $qry = new DBQuery();
    $qry->execute($sql);
    $row = $qry->getRow();

    $pilot_id = $row['plt_id'];
    $pilot_charid = $row['plt_externalid'];

    if ( $pilot_id != 0 )	{
        return '<a href="?a=pilot_detail&amp;plt_id=' . $pilot_id . '" ><font size="2">Captain Thunk</font></a>';
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
		$qry = new DBQuery();
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
		$qry = new DBQuery();
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
?>