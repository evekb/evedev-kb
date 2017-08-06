<?php

/**
 * @package EDK
 */
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);
namespace EDK\ESI;

use EDK\ESI\ESI;
use EsiClient\KillmailsApi;
use Swagger\Client\Model\GetCharactersCharacterIdKillmailsRecent200Ok;

class ESIFetchException extends \Exception {}
/**
 * Imports kills using an ESI SSO token.
 *
 * @author Snitsh Ashor
 * @author Salvoxia
 */
class ESIFetch extends ESISSO 
{
    /* @param array list of external alliance ID */
    protected $allianceIds = array();
    /** @param array list of external corp ID */
    protected $corporationIds = array();
    /** @param array  of external pilotId */
    protected $pilotIds = array();
    /** @param string additional modifiers */
    protected $additionalModifiers = '';
    
    /** @param JSON formatted raw data from the zkb API */
    protected $killLog;
    protected $maxID = 0;
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
    public static $MAXIMUM_NUMBER_OF_CYCLES = 10;
    public static $NUMBER_OF_KILLS_PER_CALL = 500;
    
    /** field for counting the number of kills fetched from ESI; we need to keep track for not running into PHP's time limit */
    protected static $NUMBER_OF_KILLS_FETCHED_FROM_ESI = 0;
    private $processTimeout = 55;
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
     * gets all ESISSO configurations from the database
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
        if (!$this->id) 
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
            $this->killLog = $KillmailsApi->getCharactersCharacterIdKillmailsRecent($this->characterID, $EdkEsi->getDataSource(), self::$NUMBER_OF_KILLS_PER_CALL, $this->maxID);
        }

        catch(Exception $e)
        {
            throw new ESIFetchException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * processes all kills for this fetch cycle
     * @throws ESIFetchException
     */
    public function processApi()
    {
        // remember the timestamp we started with
        $this->maxID = null;
        $this->killLog = array(); 
        // initialize fetch counter
        $cyclesFetched = 0;
        $startKill = $this->lastKillID;
        $previousParseCount = 0;
        // we need this loop to keep fetching until we don't get any data (because there is no new data)
        // or we get data containing a kill with a timestamp newer than the timestamp we started with
        $previousLog = array();
        // start time to calculate Processing time
        $time_start = microtime(true);
        $this->updateLastFetchTimestamp();
        do
        {
            try
            {
                $previousLog = $this->killLog;
                $this->fetch();
                //Check if we reached the end which seems to append an empty array:
                if (count(array_slice($this->killLog, -1)) == 0) 
                {
                    break;
                }
                $oldest = array_values(array_slice($this->killLog, -1))[0];
                $this->maxID=($oldest->getKillmailId());
            }
            catch(ESIFetchException $e)
            {
                $this->parsemsg[] = $e->getMessage();
            }
            catch(Exception $e)
            {
                throw $e;
            }
	    $cyclesFetched++;
            //Check if the last known is in the current killlog
            if ($this->maxID <= $startKill && $this->killLog[0]->getKillmailId() > $startKill) {
                break;
            } elseif ($this->maxID <= $startKill && $cyclesFetched > 1) {
                $this->killLog = $previousLog;
                break;
            }

            if($cyclesFetched >= self::$MAXIMUM_NUMBER_OF_CYCLES)
            {
                $this->parsemsg[] = "Stopped fetching after ".(self::$MAXIMUM_NUMBER_OF_CYCLES*self::$NUMBER_OF_KILLS_PER_CALL)." kills.";
                break;
            }
        }  while($startKill > 0 && $this->maxID > $startKill);

        //If the last call returned ampty check the previous one
        if(count($this->killLog) <= 1)
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
            if((microtime(true)-$time_start) >= $this->processTimeout)
            {
                $this->parsemsg[] = "Stopped parsing after ".$this->processTimeout." seconds.";
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
                $this->parsemsg[] = "Error communicating with ESI, aborting!";
                $this->parsemsg[] = $e->getMessage();
                break;
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
            foreach ($this->posted as $killid) {
                $output .= "<div><a href='"
                           .\edkURI::page('kill_detail', $killid, 'kll_ext_id')
                           ."'>Kill ".$killid."</a></div>";
                   
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
        catch(ApiExtection $e)
        {
            $this->skipped[] = $id;
            throw new ESIFetchException($e->getMessage().", KillID = ".$id);
        }

        catch (EsiParserException $e) 
        {
            // CREST error due to incorrect CREST hash
            if($e->getCode() == 403)
            {
                // check if kills with invalid CREST hash should be posted as non-verified kills
                if(!config::get('skipNonVerifyableKills'))
                {
                    // reset external ID and CREST has so the kill is not API verified
                    $Kill->setExternalID(null);
                    $Kill->setCrestHash(null);
                }
                else
                {
                    $this->skipped[] = $id;
                    throw new ESIFetchException($e->getMessage());
                }
            }
            // tried posting an NPC only kill when not allowed
            else if($e->getCode() == -5)
            {
                $this->skipped[] = $id;
                return;
            }
            
            // post kill using provided information, without using CREST
            $this->skipped[] = $killData->killID;
            throw new ESIFetchException($e->getMessage().", KillID = ".$killData->killID);
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

   function setTimeout($timeout)
   {
       $this->processTimeout = $timeout;
   }
}
