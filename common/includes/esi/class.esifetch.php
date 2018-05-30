<?php

/**
 * @package EDK
 */
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);
namespace EDK\ESI;

use EDK\ESI\ESI;
use EsiClient\KillmailsApi;
use Swagger\Client\ApiException;
use EsiParserException;

class ESIFetchException extends \Exception {}
/**
 * Imports kills using an ESI SSO token.
 *
 * @author Snitsh Ashor
 * @author Salvoxia
 */
class ESIFetch extends ESISSO 
{    
    /** @param \Swagger\Client\Model\GetCorporationsCorporationIdKillmailsRecent200Ok[] JSON formatted raw data from the API */
    protected $killLog;
  
    protected $page;
    protected $numberOfPages = 1;
    /** @param array of posted kills with external ID */
    private $posted = array();
    /** @param array of skipped kills with external ID */
    private $skipped = array();
    /** @param int accumulated number of kills returned by zKB */
    protected $numberOfKillsFetched = 0;
    
    /** @param array of texts created by fetcher during posting */
    private $parsemsg = array();
    
    /** @param boolean flag indicating whether NPC only kills should be ignored */
    protected $ignoreNPCOnly = FALSE;
    
    /** @param int maximum number of cycles tried to fetch to get new kills before stopping as a safety measure */
    public static $MAXIMUM_NUMBER_OF_CYCLES = 100;
    public static $NUMBER_OF_KILLS_PER_CALL = 500;
    
    /** field for counting the number of kills fetched from ESI; we need to keep track for not running into PHP's time limit */
    protected static $NUMBER_OF_KILLS_FETCHED_FROM_ESI = 0;
    /** the maximum number of seconds to be spent on fetching kills for this particular configuration */
    protected $maximumProcessingTime = 55;

    /** the ID of thje last fetched kill */
    protected $lastKillID;
   
    /**
     * gets a fetch configuration from the database, using
     * the given ID as key
     * @param int $id
     * @return \ZKBFetch
     */
    public static function getByID($id)
    {
        $ESIFetch = new ESIFetch($id);
        return $ESIFetch;
    }
    
    /**
     * gets all enabled ESISSO configurations from the database
     * @return array of \ZKBFetch objects
     */
    public static function getAll($orderByLastFetchTimestamp = false)
    {
        $resultObjects = array();
        
        $qry = \DBFactory::getDBQuery(true);
        if ($orderByLastFetchTimestamp) 
        {
            $qry->execute('SELECT id FROM kb3_esisso WHERE isEnabled = 1 ORDER by lastKillFetchTimestamp ASC');
        } 
        
        else
        {
            $qry->execute('SELECT id FROM kb3_esisso WHERE isEnabled = 1 ORDER by id');
        }
        while($result = $qry->getRow())
        {
            $resultObjects[] = ESIFetch::getByID($result['id']);
        }
        
        return $resultObjects;
    }
    
    public function setLastKillID($lastKillID)
    {
        $this->lastKillID = $lastKillID;
        
        if(is_null($this->id))
        {
            return;
        }
        $updateParams = new \DBPreparedQuery();
        $updateParams->prepare('UPDATE kb3_esisso SET lastKillID = ? WHERE id = ?');
        $types = 'si';
        $arr = array(&$types, &$this->lastKillID, &$this->id);
        $updateParams->bind_params($arr);
        if(!$updateParams->execute())
        {
            return false;
        }
        
        return true;
    }

    public function getLastKillID()
    {
        return $this->lastKillID;
    }
    
    public function updateLastFetchTimestamp()
    {
        if(is_null($this->id))
        {
            return false;
        }
        $qry = \DBFactory::getDBQuery();
        $sql = 'UPDATE kb3_esisso SET lastKillFetchTimestamp = NOW() WHERE id = '.$this->id;
        if(!$qry->execute($sql))
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
     * Reads the ESI killmails using the current ESI SSO configuration
     * @throws ESIFetchException
     * @throws \Swagger\Client\ApiException
     */
    public function fetch()
    {
        if (is_null($this->id)) 
        {
            return false;
        }
        
        // create killmail representation
        // get instance
        try
        {
            $EdkEsi = new ESI();
            $EdkEsi->setAccessToken($this->accessToken);
            $KillmailsApi = new KillmailsApi($EdkEsi);
            $headers = array();
            if($this->keyType == ESISSO::KEY_TYPE_PILOT)
            {
                list($this->killLog, , $headers) = $KillmailsApi->getCharactersCharacterIdKillmailsRecentWithHttpInfo($this->characterID, $EdkEsi->getDataSource(), null, $this->page);
            }
            
            else
            {
                $Pilot = new \Pilot(0, $this->characterID);
                $Corporation = $Pilot->getCorp();
                list($this->killLog, , $headers) = $KillmailsApi->getCorporationsCorporationIdKillmailsRecentWithHttpInfo($Corporation->getExternalID(), $EdkEsi->getDataSource(), null, $this->page);
            }
            $this->resetFailCount();
            $this->numberOfPages = $headers['X-Pages'];
        }

        catch(ApiException $e)
        {
            $this->increaseFailCount();
            throw new ESIFetchException(ESI::getApiExceptionReason($e), $e->getCode());
        }
    }
    
    /**
     * Increases the failCount for this SSO key
     */
    protected function increaseFailCount()
    {
        $qry = \DBFactory::getDBQuery();
        $sql = 'UPDATE kb3_esisso SET failCount = failCount+1 WHERE id = '.$this->id;
        $qry->execute($sql);
    }
    
    /**
     * Resets the failCount for this SSO key
     */
    protected function resetFailCount()
    {
        $qry = \DBFactory::getDBQuery();
        $sql = 'UPDATE kb3_esisso SET failCount = 0 WHERE id = '.$this->id;
        $qry->execute($sql);
    }
    
    /**
     * processes all kills for this fetch cycle
     * @throws ESIFetchException
     */
    public function processApi()
    {
        // remember the timestamp we started with
        $this->page = 0;
        $this->killLog = array(); 
        // initialize fetch counter
        $cyclesFetched = 0;
        $startKill = $this->lastKillID;
        $previousParseCount = 0;
        $latestKillIdFetched = 0;
        // we need this loop to keep fetching until we don't get any data (because there is no new data)
        // or we get data containing a kill with a timestamp newer than the timestamp we started with
        $previousLog = array();
        // start time to calculate Processing time
        $time_start = microtime(true);
        $this->updateLastFetchTimestamp();
        // this will first fetch the oldest kills available, then work its way to the newest ones
        do
        {
            $this->page = $this->page + 1;
            try
            {
                $previousLog = $this->killLog;
                $this->fetch();
                // no kills received
                if(empty($this->killLog))
                {
                    break;
                }
                $oldestKillIdOnPage = array_values(array_slice($this->killLog, -1))[0]->getKillmailId();
                $newestKillIdOnPage = $this->killLog[0]->getKillmailId();
                //Check if we reached the end which seems to append an empty array
                //  OR if the latest kill ID fetched is the last kill ID we posted - in which case we need to process the previously fetched kills (if any)
                if ($this->page == $this->numberOfPages
                        || ($this->killLog[0]->getKillmailId() > 0 && $oldestKillIdOnPage <= $startKill && $newestKillIdOnPage > $startKill))
                {
                    break;
                }
                
            }
            catch(ESIFetchException $e)
            {
                $this->parsemsg[] = $e->getMessage();
                break;
            }
            catch(Exception $e)
            {
                throw $e;
            }

            $cyclesFetched++;


            if($cyclesFetched >= self::$MAXIMUM_NUMBER_OF_CYCLES)
            {
                $this->parsemsg[] = "Stopped fetching after ".(self::$MAXIMUM_NUMBER_OF_CYCLES*self::$NUMBER_OF_KILLS_PER_CALL)." kills.";
                break;
            }
            //  the first fetch ever does not have a start kill and needs to        || if we have a start kill, continue fetching until the oldest kill fetched is less 
            //  to continue fetching until the end, which seems to append an empty  || than the start kill, and the newest is either newer or the same (no new kills)
            //  array                                                               || 
        }  while(($startKill == 0 && $this->page == $this->numberOfPages )|| ($startKill > 0 && !($startKill > $oldestKillIdOnPage && $startKill <= $newestKillIdOnPage)));

        //If the last call returned ampty check the previous one
        if(empty($this->killLog))
        {
            if($cyclesFetched > 1) {
                $this->killLog = $previousLog;
            } else {
                $this->parsemsg[] = "Did not get any Kills.";
            }
        }
        // add kills to accumulated number of kills fetched from zKB
        $this->numberOfKillsFetched += count($this->killLog);
    
        // loop over all kills
        foreach(array_reverse($this->killLog) AS $killData)
        {
            // check if we reached the maximum number of kills we may fetch
            if((microtime(true)-$time_start) >= $this->maximumProcessingTime)
            {
                $this->parsemsg[] = "Stopped parsing after reaching maximum processing time of ".$this->maximumProcessingTime." seconds.";
                break;
            }
            
            try
            {
                $this->processKill($killData);
            }

            catch(ESIFetchException $e)
            {
                $this->parsemsg[] = $e->getMessage();
            }
            
            catch(EsiParserException $e)
            {
                $this->parsemsg[] = "Error communicating with ESI: ".$e->getMessage();
            }
        }
        
        if (count(array_merge($this->posted, $this->skipped))) 
        {
            $this->lastKillID = max(array_merge($this->posted, $this->skipped));
            $this->setLastKillID($this->lastKillID);
        }

        $output = "<div>"
                  .count($this->posted)." kill".(count($this->posted) == 1 ? "" : "s")." posted, "
                  .count($this->skipped)." skipped.<br></div>";
                  if ($this->getParseMessages()) {
                          $output .= implode("<br />", $this->getParseMessages());
                  }
        if (count($this->posted))
        {
            foreach ($this->posted as $killid) 
            {
                // simple URLs cannot handle links with an external kill ID, always use
                // default URL scheme
                \edkURI::usePath(false);
                $output .= "<div><a href='"
                           .\edkURI::page('kill_detail', $killid, 'kll_ext_id')
                           ."'>Kill ".$killid."</a></div>";
                // reset URL scheme to configured setting
                \edkURI::usePath(\Config::get('cfg_pathinfo'));  
            }
        }
        return $output;
    }
    
    
    
    /**
     * processes a single kill from the zKB API
     * @param json $killData a json decoeded kill
     */
    protected function processKill($killData)
    {
        $qry = \DBFactory::getDBQuery();
        
        $id = $killData['killmail_id'];
        $hash = $killData['killmail_hash'];

        // Check for duplicate by external ID
        $qry->execute('SELECT kll_id FROM kb3_kills WHERE kll_external_id = '.$id);
        if($qry->recordCount())
        {
            // kill is already known
            $this->skipped[] = $id;
            return;
        }
        
        // create the kill
        $Kill = new \Kill();
        // set external ID
        $Kill->setExternalID($id);
        $Kill->setCrestHash(strval($hash));

        $EsiParser = new \EsiParser($Kill->getExternalID(), $Kill->getCrestHash());
        $EsiParser->setAllowNpcOnlyKills(!$this->ignoreNPCOnly);
        try
        {
            $killId = $EsiParser->parse();
        } 
        
        catch(ApiException $e)
        {
            // ESI error due to incorrect ESI hash
            if($e->getCode() == 422 && config::get('skipNonVerifyableKills'))
            {
                $this->skipped[] = $id;
                throw new ESIFetchException($e->getMessage().", KillID = ".$killData->killmail_id);
            }

            else
            {
                throw $e;
            }
        }

        catch (EsiParserException $e) 
        {
             // tried posting an NPC only kill when not allowed (-5)
            // kill deleted permanently (-4)
            // kill too old to be posted (-3)
            // kill already posted, but not detected during pre-check (should not happen) (-1)
            if($e->getCode() < 0)
            {
                $this->skipped[] = $id;
                return;
            }

            else
            {
                $this->skipped[] = $id;
                throw new ESIFetchException($e->getMessage().", KillID = ".$id);
            }
        }
        
        catch(KillException $e)
        {
            $this->skipped[] = $id;
            throw new ESIFetchException($e->getMessage().", KillID = ".$id);
        }
        self::$NUMBER_OF_KILLS_FETCHED_FROM_ESI++;
        if ($id > $this->lastKillID) 
        {
            $this->lastKillID = $id;
        }

        if($killId > 0)
        {
            $this->posted[] = $id;
            $logaddress = "ESI:".$id." : ".$hash;
            $baseUrlEndIndex = strpos($logaddress, 'api/');
            if ($baseUrlEndIndex !== FALSE) 
            {
                $logaddress = substr($logaddress, 0, $baseUrlEndIndex);
                $logaddress .= "kill/$killId/";
            }
            \logger::logKill($killId, $logaddress);
        }
        
        // duplicate after all
        else
        {
            $this->skipped[] = $id;
        }

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
    * return the accumulated number of kills by zKB
    */
   function getNumberOfKillsFetched()
   {
       return $this->numberOfKillsFetched;
   }
   
   
   
   /**
    * return all kill IDs of kills that have been skipped
    * @return array of kill IDs for skipped kills
    */
   function getSkipped()
   {
       return $this->skipped;
   }
   
   
   function getMaximumProcessingTime() 
   {
       return $this->maximumProcessingTime;
   }

   function setMaximumProcessingTime($maximumProcessingTime) 
   {
       $this->maximumProcessingTime = $maximumProcessingTime;
   }


}
