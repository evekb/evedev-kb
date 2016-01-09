<?php

/**
 * @package EDK
 */

class ZKBFetchException extends Exception {}
/**
 * imports kills from zkillboard.
 *
 * @author Salvoxia
 */
class ZKBFetch 
{
    /** @param array list of external alliance ID */
    protected $allianceIds = array();
    /** @param array list of external corp ID */
    protected $corporationIds = array();
    /** @param array  of external pilotId */
    protected $pilotIds = array();
    /** @param string additional modifiers */
    protected $additionalModifiers = '';
    /** @param the URL to zkb API, containing all modifiers */
    public $url;
    
    /** @param JSON formatted raw data from the zkb API */
    protected $rawData;
    
    /** @param array of posted kills with external ID */
    private $posted = array();
    /** @param array of skipped kills with external ID */
    private $skipped = array();
    /** @param array of texts created by fetcher during posting */
    private $parsemsg = array();
    
    /** @param boolean flag indicating whether NPC only kills should be ignored */
    protected $ignoreNPCOnly = FALSE;
    
    /** @param int the database ID for this fetch configuration */
    protected $id;
    /** @param int the last timestamp for the last kill fetched from this url*/
    protected $lastKillTimestamp;
    /** @param int negative offset in hours to apply to the last kill timestamp before fetching */
    protected $killTimestampOffset;

    /** @param int default for the negative offset in hours to apply to the last kill timestamp before fetching */
    public static $KILL_TIMESTAMP_OFFSET_DEFAULT = 2;
    
    /** @param int maximum number of cycles tried to fetch to get new kills before stopping as a safety measure */
    public static $MAXIMUM_NUMBER_OF_CYCLES = 6;
   
    public function __construct($id = NULL)
    {
        $this->id = $id;
        $this->killTimestampOffset = self::$KILL_TIMESTAMP_OFFSET_DEFAULT;
    }
    
    /**
     * fetches all attributes from the database
     */
    public function execQuery()
    {
        if(is_null($this->id)) 
        {
            return;
        }
        
        $fetchParams = new DBPreparedQuery();
        $fetchParams->prepare('SELECT fetchID, url, lastKillTimestamp FROM kb3_zkbfetch WHERE fetchID = ?');
        $lastKillTimestamp = NULL;
        $arr = array(&$this->id, &$this->url, &$lastKillTimestamp);
        $fetchParams->bind_results($arr);
        $types = 'i';
        $arr2 = array(&$types, &$this->id);
        $fetchParams->bind_params($arr2);

        $fetchParams->execute();
        if($fetchParams->recordCount() > 0)
        {
            $fetchParams->fetch();
            $this->lastKillTimestamp = strtotime($lastKillTimestamp);
        }
    }
    
    /**
     * gets a fetch configuration from the database, using
     * the given ID as key
     * @param int $id
     * @return \ZKBFetch
     */
    public static function getByID($id)
    {
        $ZKBFetch = new ZKBFetch($id);
        return $ZKBFetch;
    }
    
    /**
     * adds a new fetch configuration to the database
     * @return int the ID for the new fetch configuration
     * @throws ZKBFetchException
     */
    public function add()
    {
        if(!is_null($this->id))
        {
            return $this->id;
        }
        
        // check url
        if(is_null($this->url) || strlen(trim($this->url)) < 1)
        {
            throw new ZKBFetchException("No URL given for ZKBFetch!");
        }
        
        // if no lastKillTimestamp given, set it to NOW
        if(is_null($this->lastKillTimestamp) || $this->lastKillTimestamp === 0)
        {
            // get current timestamp in UTC
            $this->lastKillTimestamp = time();
        }
        
        $fetchParams = new DBPreparedQuery();
        $fetchParams->prepare('INSERT INTO kb3_zkbfetch (`url`, `lastKillTimestamp`) VALUES (?, ?)');
        $types = 'ss';
        $timeString = strftime('%Y-%m-%d %H:%M:%S', $this->lastKillTimestamp);
        $arr2 = array(&$types, &$this->url, &$timeString);
        $fetchParams->bind_params($arr2);

        if(!$fetchParams->execute())
        {
            throw new ZKBFetchException("Error while adding ZKBFetch configuration: ".$fetchParams->getErrorMsg());
        }
        
        return $fetchParams->getInsertID();
    }
    
    /**
     * deletes the fetch configuration with the given ID
     * @param int $id
     */
    public static function delete($id)
    {
        $fetchParams = new DBPreparedQuery();
        $fetchParams->prepare('DELETE FROM kb3_zkbfetch WHERE fetchID = ?');
        $types = 'i';
        $arr = array(&$types, &$id);
        $fetchParams->bind_params($arr);

        return $fetchParams->execute();
    }
    
    /**
     * gets all ZKBFetch configurations from the database
     * @return array of \ZKBFetch objects
     */
    public static function getAll()
    {
        $resultObjects = array();
        
        $qry = DBFactory::getDBQuery(true);
        $qry->execute('SELECT fetchID FROM kb3_zkbfetch ORDER BY fetchID ASC');
        while($result = $qry->getRow())
        {
            $resultObjects[] = ZKBFetch::getByID($result['fetchID']);
        }
        
        return $resultObjects;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
        
        if(is_null($this->id))
        {
            return;
        }
        
        $updateParams = new DBPreparedQuery();
        $updateParams->prepare('UPDATE kb3_zkbfetch SET url = ? WHERE fetchID = ?');
        $types = 'si';
        $arr = array(&$types, &$this->url, &$this->id);
        $updateParams->bind_params($arr);
        if(!$updateParams->execute())
        {
            return false;
        }
        
        return true;
    }
    
    public function getUrl()
    {
        if(!is_null($this->id) && is_null($this->url))
        {
            $this->execQuery();
        }
        return $this->url;
    }
    
    
    public function getID()
    {
        return $this->id;
    }
    
    public function getLastKillTimestamp()
    {
        if(!is_null($this->id) && is_null($this->lastKillTimestamp))
        {
            $this->execQuery();
        }
        
        return $this->lastKillTimestamp;
    }
    
    
    public function setLastKillTimestamp($timestamp)
    {
        if(!is_numeric($timestamp))
        {
            return false;
        }
        $this->lastKillTimestamp = $timestamp;
        
        if(is_null($this->id))
        {
            return;
        }
        $timeString = strftime('%Y-%m-%d %H:%M:%S', $this->lastKillTimestamp);
        $updateParams = new DBPreparedQuery();
        $updateParams->prepare('UPDATE kb3_zkbfetch SET lastKillTimestamp = ? WHERE fetchID = ?');
        $types = 'si';
        $arr = array(&$types, &$timeString, &$this->id);
        $updateParams->bind_params($arr);
        if(!$updateParams->execute())
        {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * 
     * @param boolean $ignoreNPCOnlyKills flag indicating whether to ignore NPC only killmails
     */
    public function setIgnoreNpcOnlyKills($ignoreNPCOnlyKills)
    {
        if($ignoreNPCOnlyKills === TRUE)
        {
            $this->ignoreNPCOnly = TRUE;
        }
        
        else
        {
            $this->ignoreNPCOnly = FALSE;
        }
    }
    
    /**
     * 
     * @param int $killTimestampOffset negative offset in hours to apply to the last kill timestamp before fetching
     */
    public function setKillTimestampOffset($killTimestampOffset)
    {
        if(is_numeric($killTimestampOffset))
        {
            $this->killTimestampOffset = $killTimestampOffset;
        }
    }

    
    /**
     * reads the zkb API using $this->url
     * @throws Exception
     */
    public function fetch()
    {
        if (!$this->fetchUrl) 
        {
            return false;
        }
        
        // create killmail representation
        // get instance
        try
        {
            $this->rawData = SimpleCrest::getReferenceByUrl($this->fetchUrl);
        }

        catch(Exception $e)
        {
            throw new ZKBFetchException($e->getMessage(), $e->getCode());
        }
    }
    
    
    /**
     * executes verifications on $url to ensure
     * it's a valid URL for zKB API
     * @throws ZKBFetchException
     */
    protected function validateUrl()
    {
        $this->fetchUrl = $this->url;
        // remove XML modifier, we need JSON
        str_replace('xml/', '', $this->fetchUrl);
        // must end with a slash
        if(substr($this->fetchUrl, -1) != '/')
        {
            $this->fetchUrl .= '/';
        }

        // force API verified mails only
        if(strpos($this->fetchUrl, 'api-only') === FALSE)
        {
            $this->fetchUrl .= 'api-only/';
        }

        // add startTime, if not already in URL and if given and the URL is not for a specific kill
        if(strpos($this->fetchUrl, 'startTime') === FALSE && !is_null($this->lastKillTimestamp) && strlen(trim($this->lastKillTimestamp) > 0)
                && strpos($this->fetchUrl, 'killID') === FALSE)
        {
            $timestampFormattedForZkb = strftime("%Y%m%d%H%M", $this->lastKillTimestamp);
            $this->fetchUrl .= "startTime/$timestampFormattedForZkb/orderDirection/asc/";
        }

        $urlPieces = explode("/", $this->fetchUrl);

        if(count($urlPieces) < 5 || $urlPieces[3] != "api")
        {
            throw new ZKBFetchException("Invalid zBK API URL: ".$this->fetchUrl);
        }
    }
    
    
    /**
     * processes all kills for this fetch cycle
     * @throws ZKBFetchException
     */
    public function processApi()
    {
        // initialize rawData
        $this->rawData = array();
        
        // remember the timestamp we started with
        $startTimestamp = $this->lastKillTimestamp;
        
        // apply negative offset
        if(isset($this->killTimestampOffset) && is_numeric($this->killTimestampOffset) && isset($this->lastKillTimestamp) && is_numeric($this->lastKillTimestamp))
        {
            $this->lastKillTimestamp -= $this->killTimestampOffset*3600;
        }
        
        // initialize fetch counter
        $cyclesFetched = 0;
        
        $fetchUrlPreviousCycle = '';
        // we need this loop to keep fetching until we don't get any data (because there is no new data)
        // or we get data containing a kill with a timestamp newer than the timestamp we started with
        do
        {
            // validate and build the URL
            $this->validateUrl();
            // check if the fetch URL is the same as for the last cycle
            if($fetchUrlPreviousCycle == $this->fetchUrl)
            {
                // stop fetching, we're fetching the same bunch of kills all over again!
                // may happen if more than one kill matches the last kill timestamp
                break;
            }

            // fetch the raw data from zKB API
            try
            {
                $this->fetch();
            }
            
            catch(Exception $e)
            {
                $this->lastKillTimestamp = $startTimestamp;
                throw $e;
            }
            if(empty($this->rawData))
            {
                //throw new ZKBFetchException("Empty result returned by API ".$this->fetchUrl);
                // this is a valid case
                // set rawData to an empty array, so the loop doesn't complain
                $this->rawData = array();
            }

            // loop over all kills
            foreach($this->rawData AS $killData)
            {
                try
                {
                    $this->processKill($killData);
                }

                catch(ZKBFetchException $e)
                {
                    $this->parsemsg[] = $e->getMessage();
                }
            }
            
            // no timestamp given at all
            if($startTimestamp == NULL)
            {
                break;
            }
            
            // safety stop
            if($cyclesFetched >= self::$MAXIMUM_NUMBER_OF_CYCLES)
            {
                $this->parsemsg[] = "Stopped fetching before finding new kills due to safety limit (fetched ".(self::$MAXIMUM_NUMBER_OF_CYCLES*200)." kills in a row!). "
                        . "Try lowering your negative kill timestamp offset!";
                break;
            }
            
            $cyclesFetched++;
            // remember the URL we used during this cycle
            $fetchUrlPreviousCycle = $this->fetchUrl;
        }  while(count($this->rawData) > 1 && $this->lastKillTimestamp <= $startTimestamp);
        
        $this->setLastKillTimestamp($this->lastKillTimestamp);
    }
    
    
    
    /**
     * processes a single kill from the zKB API
     * @param json $killData a json decoeded kill
     */
    protected function processKill($killData)
    {
        // sometimes, the killData is malformed
        if((int)$killData->killID === 0)
        {
            throw new ZKBFetchException("Invalid format for kill, skipping");
        }
        
        $qry = DBFactory::getDBQuery();

        // Check hashes with a prepared query.
        // Make it static so we can reuse the same query for feed fetches.
        $checkHash;
        $hash;
        $trust;
        $killId;
        $timestamp = str_replace('.', '-', $killData->killTime);
        
        $this->lastKillTimestamp = strtotime($timestamp);
        
        // Check hashes.
        $hash = self::hashMail($killData);

        if(!isset($checkHash))
        {
                $checkHash = new DBPreparedQuery();
                $checkHash->prepare('SELECT kll_id, kll_trust FROM kb3_mails WHERE kll_timestamp = ? AND kll_hash = ?');
                $arr = array(&$killId, &$trust);
                $checkHash->bind_results($arr);
                $types = 'ss';
                $arr2 = array(&$types, &$timestamp, &$hash);
                $checkHash->bind_params($arr2);
        }
        $checkHash->execute();

        if($checkHash->recordCount())
        {
            $checkHash->fetch();
            $dupeid = $killId;
            $victimData = self::getVictim($killData);
            // We still want to update the external ID if we were given one.			
            // positive killIDs in zKB are the external IDs, so use it
            $qry->execute("UPDATE kb3_kills"
                            ." JOIN kb3_mails ON kb3_mails.kll_id = kb3_kills.kll_id"
                            ." SET kb3_kills.kll_external_id = ".$killData->killID
                            .", kb3_mails.kll_external_id = ".$killData->killID
                            .", kll_modified_time = UTC_TIMESTAMP()"
                            .", kb3_kills.kll_x = ".$victimData["x"]
                            .", kb3_kills.kll_y = ".$victimData["y"]
                            .", kb3_kills.kll_z = ".$victimData["z"]
                            ." WHERE kb3_kills.kll_id = ".$dupeid
                            ." AND (kb3_kills.kll_external_id IS NULL OR kb3_kills.kll_x = 0)");             

            // we also want to update the CREST hash
            // if zKB ever exposes the CREST hash (if it has one for that kill) via API...
            //$qry->execute("UPDATE kb3_mails SET kll_crest_hash = '"
            //                               .$this->crestHash."' WHERE kll_id = ".$this->dupeid_);

            if($trust < 0)
            {
                $this->skipped[] = $killData->killID;
                throw new ZKBFetchException("Kill ".$killData->killID." has been deleted. KillID was "
                                    .$dupeid, -4);
            }
            // kill is already known
            $this->skipped[] = $killData->killID;
            
            return;
        }	
        
        // Check for duplicate by external ID
        $qry->execute('SELECT kll_id FROM kb3_kills WHERE kll_external_id = '.$killData->killID);
        if($qry->recordCount())
        {
            // kill is already known
            $this->skipped[] = $killData->killID;
            return;
        }

        $this->hash = $hash;

        // Filtering
        if(config::get('filter_apply'))
        {
            $filterdate = config::get('filter_date');
            if ($timestamp < $filterdate) {
                $filterdate = kbdate("j F Y", config::get("filter_date"));
                $this->skipped[] = $killData->killID;
                throw new ZKBFetchException("Kill ".$killData->killID." is older than the oldes allowed date (" .$filterdate. ")", -3);
            }
        }

        // check for non-api kill
        if($killData->killID < 0)
        {
            throw new ZKBFetchException("Only API-verified kills are supported, this is a non-verified kill: ".$killData->killID);
        }
        
        // create the kill
        $Kill = new Kill();
        // set external ID
        $Kill->setExternalID($killData->killID);
        // set timestamp
        $Kill->setTimeStamp($timestamp);
        
        
        // handle solarSystem
        $solarSystemID = (int)$killData->solarSystemID;
        $solarSystem = SolarSystem::getByID($solarSystemID);
        if (!$solarSystem->getName()) 
        {
            $this->skipped[] = $killData->killID;
            throw new ZKBFetchException("Unknown solar system ID: ".$solarSystemID);
        }
        $Kill->setSolarSystem($solarSystem);

        // handle victim details
        try
        {
            $isNPCOnlyKill = FALSE;
            // this method sets the $isNPCOnlyKill flag
            $this->processInvolved($Kill, $killData, $isNPCOnlyKill);
            if($isNPCOnlyKill && $this->ignoreNPCOnly)
            {
                $this->skipped[] = $killData->killID;
                return;
            }
            $this->processVictim($Kill, $killData);
            $this->processItems($Kill, $killData);
        }
        
        catch(ZKBFetchException $e)
        {
            $this->skipped[] = $killData->killID;
            throw $e;
        }
        
        try
        {
            $killId = $Kill->add();
        }
        
        catch(KillException $e)
        {
            $this->skipped[] = $killData->killID;
            throw new ZKBFetchException($e->getMessage().", KillID = ".$killData->killID);
        }
        
        if($killId > 0)
        {
            $this->posted[] = $killData->killID;
            $logaddress = "ZKB:".$this->url;
            $baseUrlEndIndex = strpos($logaddress, 'api/');
            if ($baseUrlEndIndex !== FALSE) 
            {
                $logaddress = substr($logaddress, 0, $baseUrlEndIndex);
                $logaddress .= "kill/$killData->killID/";
            }
            logger::logKill($killId, $logaddress);
        }
        
        // duplicate after all
        else
        {
            $this->skipped[] = $killData->killID;
        }

    }
    
    
   /**
    * extracts and sets victim details in the given kill
    * reference; uses the json decded object $killData as source
    * @param Kill $Kill reference to the kill to update
    * @param Object $killData json decoded kill data from zKB API
    * @throws ZKBFetchException
    */
   protected function processVictim(&$Kill, $killData)
   {
       $victimDetails = self::getVictim($killData);
       $timestamp = $killData->killTime;

       // If we have a character ID but no name then we give up - the needed
       // info is gone.
       // If we have no character ID and no name then it's a structure or NPC
       //	- if we have a moonID (anchored at a moon) call it corpname - moonname
       //	- if we don't have a moonID call it corpname - systemname
       if (!strlen($victimDetails['characterName']) && $victimDetails['characterID'] > 0) {
                   throw new ZKBFetchException("Insufficient victim information provided! Kill-ID: ".$killData->killID);
       } else if (!$victimDetails['corporationID'] && !$victimDetails['factionID']) {
               throw new ZKBFetchException("Insufficient victim corporation information provided! Kill-ID: ".$killData->killID);
       }

      // get alliance
       if ($victimDetails['allianceID'] > 0) 
       {
            // first check for alliance by external ID
            $Alliance = new Alliance($victimDetails['allianceID'], TRUE);
            // fallback
            if(!$Alliance->getID())
            {
                $Alliance = Alliance::add($victimDetails['allianceName'], $victimDetails['allianceID']);
            }
       } 
       
       else if ($victimDetails['factionID'] > 0) 
       {
            $Alliance = new Alliance($victimDetails['factionID'], TRUE);
            if(!$Alliance->getID())
            {
                // fallback
                $Alliance = Alliance::add($victimDetails['factionName'],$victimDetails['factionID']);
            }
       } 
       
       else {
               $Alliance = Alliance::add("None");
       }
      


       // get corp
       // if corp is not present, use faction
       if($victimDetails['corporationID'] > 0)
       {
           $Corp = Corporation::add(strval($victimDetails['corporationName']), $Alliance, $timestamp, (int)$victimDetails['corporationID']);
       }   

       else
       {
           $Corp = Corporation::add(strval($victimDetails['factionName']), $Alliance, $timestamp, (int)$victimDetails['factionID']);
       }

       // victim's name
       if(strlen($victimDetails["characterName"]) == 0)
       {
           if($victimDetails["moonID"] > 0)
           {
               $moonName = API_Helpers::getMoonName($victimDetails["moonID"]);
               $victimName = $Corp->getName()." - ".$moonName;
           }

           else
           {
               $victimName = $Corp->getName()." - ".$Kill->getSolarSystemName();
           }
       }

       else
       {
           $victimName = $victimDetails["characterName"];
       }

       $Pilot = $pilot = Pilot::add($victimName, $Corp, $timestamp, $victimDetails["characterID"]);

       // handle victim's ship
       $Ship = Ship::getByID($victimDetails["shipTypeID"]);


       // set values in $Kill
       $Kill->setVictim($Pilot);
       $Kill->setVictimID($Pilot->getID());
       $Kill->setVictimCorpID($Corp->getID());
       $Kill->setVictimAllianceID($Alliance->getID());
       $Kill->setVictimShip($Ship);
       $Kill->set('dmgtaken', $victimDetails['damageTaken']);
       $Kill->setXCoordinate($victimDetails['x']);
       $Kill->setYCoordinate($victimDetails['y']);
       $Kill->setZCoordinate($victimDetails['z']);
   }
   
   /**
    * processes and adds all involved parties to the given kill reference;
    * source is the json decoded kill data from zKB API
    * @param Kill $Kill reference to the kill to update
    * @param Object $killData json decoded kill data
    * @param boolean flag that gets set if involved parties are NPCs only
    * @throws ZKBFetchException
    */
   protected function processInvolved(&$Kill, $killData, &$isNPCOnlyKill)
   {
       $involvedParties = self::getAttackers($killData);
       $timestamp = $killData->killTime;
       
       // pre-initialize with TRUE, will change it to FALSE as soon as we
       // encounter a non-NPC
       $isNPCOnlyKill = TRUE;
       foreach($involvedParties AS $involvedParty)
       {
           if (!$involvedParty['shipTypeID']
                           && !$involvedParty['weaponTypeID']
                           && !$involvedParty['characterID']
                           && !strlen($involvedParty['characterName'])) {
                   throw new ZKBFetchException("Error processing involved party. Kill-ID: ".$killData->killID);
           }

           $isNPC = FALSE;

           // get involved party's ship
           $Ship = new Ship();
           if(!$involvedParty['shipTypeID'])
           {
               $Ship = Ship::lookup("Unknown");
           }

           else
           {
               $Ship = Ship::getByID($involvedParty['shipTypeID']);
           }

           $Weapon = Cacheable::factory('Item', $involvedParty['weaponTypeID']);


           // get alliance
           $Alliance = Alliance::add("None");
           if ($involvedParty['allianceID'] > 0) 
           {
                $Alliance = new Alliance($involvedParty['allianceID'], TRUE);
                // fallback
                if(!$Alliance->getID())
                {
                    $Alliance = Alliance::add($involvedParty['allianceName'], $involvedParty['allianceID']);
                }
                
           }
           // only use faction as alliance if no corporation is given (faction NPC)
           else if ($involvedParty['factionID'] > 0 && strlen($involvedParty['corporationName']) > 0) 
           {		
               $Alliance = new Alliance($involvedParty['factionID'], TRUE); 
               if(!$Alliance->getID())
               {
                   $Alliance = Alliance::add($involvedParty['factionName'], $involvedParty['factionID']);
               }
           }           

           // get corp
           // if corp is not present, use faction
           if($involvedParty['corporationID'] > 0)
           {
               // try getting the corp from our database
                $Corp = Corporation::lookup(strval($involvedParty['corporationName']));
                // create new corp
                if(!$Corp)
                {
                    $Corp = Corporation::add(strval($involvedParty['corporationName']), $Alliance, $timestamp, (int)$involvedParty['corporationID']);
                }
           }   

           else if($involvedParty['factionID'] > 0)
           {
               // try getting the corp from our database
                $Corp = Corporation::lookup(strval($involvedParty['factionName']));
                // create new corp
                if(!$Corp)
                {
                    $Corp = Corporation::add(strval($involvedParty['factionName']), $Alliance, $timestamp, (int)$involvedParty['factionID']);
                }
           }

           // NPCs without Corp/Alliance/Faction (e.g. Rogue Drones)
           else
           {
               $Corp = $this->fetchCorp("Unknown", $Alliance, $timestamp);
           }

           // get ship class to determine whether it's a tower and 
           // we need to fetch the alliance via the corp
           $shipClassID = $Ship->getClass()->getID();
           if($shipClassID == 35           // small Tower
                   || $shipClassID == 36   // medium Tower
                   || $shipClassID == 37   // large Tower
                   || $shipClassID == 38)  // POS Module  
           {
               if($Alliance->getName() == "None")
               {
                   $Alliance = $Corp->getAlliance();
               }
           }

           // victim's name
           $involvedPartyName = $involvedParty['characterName'];
           $involvedCharacterID = $involvedParty['characterID'];
           $loadPilotExternals = true;

           // Fix for case that involved party is an actual pilot without corp
           // FoxFour is to blame!
           if($involvedCharacterID && strlen($involvedParty['characterName']) > 0 && $involvedParty['corporationID'] == 0)
           {
               $Pilot = Pilot::lookup($involvedParty['characterName']);
               if($Pilot)
               {
                   $Corp = $Pilot->getCorp();
               }
           }

           // special case:
           // NPC/Tower/other structure
           if(!$involvedCharacterID && !$involvedParty['weaponTypeID'] && !$involvedParty['allianceID'])                        
           {
                   $Alliance = $Corp->getAlliance();
                   $Ship = Ship::lookup("Unknown");
                   $Weapon = Item::getByID($involvedParty['shipTypeID']);
                   if(!$Weapon->getName())
                   {
                       throw new ZKBFetchException("Involved party is an NPC with a ship type not found in the database! Kill-ID: ".$killData->killID);
                   }
                   $involvedPartyName = $Corp->getName().' - '.$Weapon->getName();
                   $isNPC = TRUE;
                   $involvedCharacterID = 0;
                   $loadPilotExternals = false;
           }



           $Pilot = Pilot::add($involvedPartyName, $Corp, $timestamp, $involvedCharacterID, $loadPilotExternals);

           // create involvedParty
           $IParty = new InvolvedParty($Pilot->getID(), $Corp->getID(),
                           $Alliance->getID(),  $involvedParty['securityStatus'],
                                           $Ship->getID(), $Weapon->getID(),
                                           $involvedParty['damageDone']);

           $Kill->addInvolvedParty($IParty);

           if($involvedParty["finalBlow"] === TRUE)
           {
               $Kill->setFBPilotID($Pilot->getID());
           }

           if(!$isNPC)
           {
               $isNPCOnlyKill = FALSE;
           }
       }
   }
   
   
   
   /**
    * processes all dropped/destroyed items in that kill
    * and adds them as Dropped/Destroyed
    * @param type $Kill the kill to add the items to
    */
   protected function processItems(&$Kill, $killData)
   {
       $items = self::getItems($killData->items);
       // TODO implement proper CCP flags!
       foreach($items AS $item)
       {
           // we use this nested construct for perhaps later changing
           // the way we process single items and nested items
           $this->processItem($item, $Kill);
       }
   }
   
   /**
    * accepts an array with item information,
    * and adds items to the given kill
    * of destroyed items
    * @param array $item
    *              -typeID
    *              -flag
    *              -qtyDropped
    *              -qtyDestroyed
    *              -singleton
    * @param Kill $Kill the kill reference
    * @param int $parentItemLocation the item location of the parent item (for containers)
    */
   protected function processItem($item, &$Kill, $parentItemLocation = null)
   {
       $typeID = (int)$item['typeID'];
       // we will add this item with the given flag, even if it's not in our database
       // that way, when the database is updated, the item will display correctly
       $Item = Item::getByID($typeID);

       // if item has a parent, use the parent's flag
       if(!is_null($parentItemLocation))
       {
           $location = $parentItemLocation;
       }
       else 
       {
           $location = (int)$item['flag'];
       } 

       // Blueprint copy marker
       $singleton = (int)$item['singleton'];
       

       if($item['qtyDropped']) {
          $Kill->addDroppedItem(
              new DestroyedItem($Item, $item['qtyDropped'], $singleton, '', $location));
       }
       if($item['qtyDestroyed']) {
               $Kill->addDestroyedItem(
                   new DestroyedItem($Item, $item['qtyDestroyed'], $singleton, '',  $location));
       }

       // process container-items
       if(isset($item["items"]))
       {
           foreach($item["items"] AS $itemInContainer)
           {
               $this->processItem($itemInContainer, $Kill, $location);
           }
       }
   }
   
   
   /**
    * @param mixed $itemsInMail
    * @return array
    */
    private static function getItems($itemsInMail)
    {
        $items = array();
        if($itemsInMail)
        {
            foreach($itemsInMail as $item) {
                $itemDetails = array();
                $itemDetails["typeID"] = (int) @$item->typeID;
                $itemDetails["flag"] = (int) @$item->flag;
                $itemDetails["qtyDropped"] = (int) @$item->qtyDropped;
                $itemDetails["qtyDestroyed"] = (int) @$item->qtyDestroyed;
                $itemDetails["singleton"] = (int) @$item->singleton;
                // recursive call for containers -> we preserve the item tree here
                if (isset($item->items))
                {
                    $itemDetails["items"] = self::getItems($item->items);
                }
                $items[] = $itemDetails;
            }   
        }
        return $items;
    }
    
    
   /**
    * creates a legacy-parser-compatible md5 hash of the given kill
    * @param mixed $killData
    * @return string
    */
   public static function hashMail($killData = null)
   {
        if(is_null($killData)) return false;

        $involvedParties = self::getAttackers($killData);
        $victim = self::getVictim($killData);
        $invListDamage = array();
        foreach($involvedParties AS $attacker)
        {
             $invListDamage[] = $attacker["damageDone"];
             $involvedPartyName = "";
             if($attacker["characterName"])
             {
                 $involvedPartyName = $attacker["characterName"];
             }

             // use "shipTypeName / corpName" for compatibility with legacy parser mails
             else
             {       
                 // required for NPCs without corp
                 $corpName = "Unknown";
                 if(strlen($attacker["factionName"]) > 0)
                 {
                     $corpName = $attacker["factionName"];
                 }
                 if(strlen($attacker["corporationName"]) > 0)
                 {
                     $corpName = $attacker["corporationName"];
                 }
                 $Ship = Ship::getByID($attacker["shipTypeID"]);
                 $involvedPartyName = $Ship->getName()." / ".$corpName;
             }

             if($attacker["finalBlow"] === TRUE)
             {
                     // add the string " (laid the final blow)" to keep compatibility with legacy parser mails
                     $involvedPartyName .= " (laid the final blow)";
             }
            $invListName[] = $involvedPartyName;
        }
        // Sort the involved list by damage done then alphabetically.
        array_multisort($invListDamage, SORT_DESC, SORT_NUMERIC, $invListName, SORT_ASC, SORT_STRING);


        // timestamp
        $hashIn = str_replace('.', '-', $killData->killTime);
        // cut off seconds from timestamp to keep compatibility with legacy parser mails
        $hashIn = substr($hashIn, 0, 16);

        // victim's name
        // was it a player?
        if($victim["characterName"])
        {
            $hashIn .= $victim["characterName"];
        }

        // was it a pos structure?
        else if($victim["moonID"] != 0)
        {
            $moonName = API_Helpers::getMoonName($victim["moonID"]);
            // cut off the first two characters (again, to keep compatibility with legacy parser killmails)
            $hashIn .= substr($moonName, 2, strlen($moonName)-1);
        }

        else
        {
                return false;
        }

        // destroyed ship
        $VictimShip = Ship::getByID($victim["shipTypeID"]);
        $hashIn .= $VictimShip->getName();
        // solar system
        $SolarSystem = new SolarSystem($killData->solarSystemID);
        $hashIn .= (String) $SolarSystem->getName();
        // damage taken
        $hashIn .= $victim["damageTaken"];
        // list of involved parties
        $hashIn .= implode(',', $invListName);
        // list of involved parties' damage done
        $hashIn .= implode(',', $invListDamage);

        return md5($hashIn, true);
   }
   
   
   
   /**
    * @param mixed $killData
    * @return array
    */
    public static function getAttackers($killData) {
        $attackers = array();

        foreach($killData->attackers as $attacker) {
            $involvedParty = array();
            $involvedParty["characterID"] = (int) @$attacker->characterID;
            $involvedParty["characterName"] = (string) @$attacker->characterName;
            $involvedParty["corporationID"] = (int) @$attacker->corporationID;
            $involvedParty["corporationName"] = (string) @$attacker->corporationName;
            $involvedParty["allianceID"] = (int) @$attacker->allianceID;
            $involvedParty["allianceName"] = (string) @$attacker->allianceName;
            $involvedParty["factionID"] = (int) @$attacker->factionID;
            $involvedParty["factionName"] = (string) @$attacker->factionName;
            $involvedParty["securityStatus"] = (float) $attacker->securityStatus;
            $involvedParty["damageDone"] = (int) @$attacker->damageDone;
            $involvedParty["finalBlow"] = (boolean) @$attacker->finalBlow;
            $involvedParty["weaponTypeID"] = (int) @$attacker->weaponTypeID;
            $involvedParty["shipTypeID"] = (int) @$attacker->shipTypeID;
            $attackers[] = $involvedParty;
        }
        return $attackers;
    }
    
   /**
    * @param mixed $killData
    * @return array
    */
    public static function getVictim($killData)
    {
            $victim = array();
            $victim["shipTypeID"] = (int) @$killData->victim->shipTypeID;
            $victim["characterID"] = (int) @$killData->victim->characterID;
            $victim["characterName"] = (string) $killData->victim->characterName;
            $victim["corporationID"] = (int) $killData->victim->corporationID;
            $victim["corporationName"] = (string) $killData->victim->corporationName;
            $victim["allianceID"] = (int) @$killData->victim->allianceID;
            $victim["allianceName"] = (string) @$killData->victim->allianceName;
            $victim["factionID"] = (int) $killData->victim->factionID;
            $victim["factionName"] = (string) $killData->victim->factionName;
            $victim["damageTaken"] = (int) $killData->victim->damageTaken;
            $victim["moonID"] = (int) $killData->moonID;
            $victim["x"] = (float) $killData->position->x;
            $victim["y"] = (float) $killData->position->y;
            $victim["z"] = (float) $killData->position->z;
            return $victim;
    }
   
   
   /**
    * Return any messages generated by parsing json data
    * @return array Text for any messages generated by parsing json data
    */
   function getParseMessages()
   {
           return $this->parsemsg;
   }
   
   
   /**
    * return all kill IDs of kills that have been posted
    * @return array of kill IDs for posted kills
    */
   function getPosted()
   {
       return $this->posted;
   }
   
   
   
   /**
    * return all kill IDs of kills that have been skipped
    * @return array of kill IDs for skipped kills
    */
   function getSkipped()
   {
       return $this->skipped;
   }

}
