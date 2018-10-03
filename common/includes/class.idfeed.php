<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * EDK IDFeed Syndication reader class.
 * This class is used to fetch and read the feed from another EDK board. It
 *  adds all fetched kills to the board and returns the id of the highest kill
 *  fetched.
 *
 * 0.90 almost final - kills are returned in descending order which confuses
 * 'last kill returned' responses.
 * 0.91 final release version for 3.0 boards.
 *
 * 1.0.4 Involved party structures keep their name
 *         Kills are logged with source board's id.
 * 1.0.7 Better CCP API handling
 * 1.0.8 Handle NPC ships in API feeds.
 * 1.0.9 Add Implant location
 * 1.1.0 Fix Trust issues.
 * @package EDK
 */
class IDFeed
{
    private $url = '';
    private $xml = '';
    private $options = array();
    private $lastReturned = 0;
    private $lastInternalReturned = 0;
    private $posted = array();
    private $skipped = array();
    /** @param int accumulated number of kills present in the XML */
    protected $numberOfKillsFetched = 0;
    private $duplicate = array();
    private $time = '';
    private $cachedTime = '';
    private $parsemsg = array();
    private $errormsg = '';
    private $errorcode = 0;
    private $npcOnly = true;
    // flag indicating we're fetching from the EVE API (not from an IDFeed)
    private $isApiFetch = FALSE;
    /** flag indicating whether to skip NPC only losses */
    private $skipNpcOnly;

    /** field for counting the number of kills fetched from CREST; we need to keep track for not running into PHP's time limit */
    protected static $NUMBER_OF_KILLS_FETCHED_FROM_CREST = 0;

    /** 
     * flag indicating the slot for an item in an incoming feed/API
     * should be looked up if the incoming flag is zero
     * @var boolean
     */
    private $lookupSlotForIncomingZeroFlags = FALSE;

    /**
     * Fetch a new feed.
     */
    private function fetch()
    {
        if (!$this->url) {
            return false;
        }

        $this->posted = array();
        $this->skipped = array();
        $this->duplicate = array();
        $this->lastReturned = 0;
        $this->time = '';
        $this->cachedTime = '';

        if (strpos($this->url, "?") === false) {
            $options = "?";
        } else {
            $options = "&";
        }
        $first = true;
        foreach ($this->options as $key => $val) {
            if ($first) {
                $first = false;
            } else {
                $options .= "&";
            }
            $options .= $key."=".$val;
        }

        $http = new http_request($this->url.$options);
        $http->set_useragent("EDK IDFeedfetcher ".ID_FEED_VERSION);
        $http->set_timeout(300);
        $this->xml = $http->get_content();
        if ($http->get_http_code() != 200) {
            trigger_error("HTTP error ".$http->get_http_code()
                    ." while fetching feed from ".$this->url.$options.".", E_USER_WARNING);
            return false;
        }

        if ($this->xml) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Fetch a new feed and parse it.
     * Use the input parameters to fetch a feed.
     *
     * @param string $url The base URL of the feed to fetch
     * @return boolean
     */
    public function read($url = '')
    {
        $this->url = $url;
        if ($this->xml) {
            ;
        } else if (substr($url, 0, 4) != "http") {
            if (!$this->xml = file_get_contents($url)) {
                trigger_error("Error reading file.", E_USER_WARNING);
                return false;
            }
        } else {
            if (!$this->fetch()) {
                trigger_error("Error reading feed.", E_USER_WARNING);
                return false;
            }
        }
        if (strpos($this->xml, '<eveapi') === false) {
            trigger_error("Not a valid EVEAPI feed.", E_USER_WARNING);
            return false;
        }
        return $this->processFeed();
    }

    /**
     * Set the XML parsed by the idfeed.
     * @param string $xml
     * @return type
     */
    function setXML($xml)
    {
        $this->xml = $xml;
    }

    /**
     * Set the XML parsed by the idfeed.
     * @param string $xml
     * @return type
     */
    function setLogName($name)
    {
        $this->name = preg_replace("/[^\w\d _-]/", "", (string)$name);
    }

    /**
     * Set the type and ID of the feed to retrieve.
     * @param string $type pilot/corp/alliance.
     * @param integer $id
     * @return boolean false on failure.
     */
    function setID($type = '', $id = 0)
    {
        //Check id is int.
        $id = (int) $id;

        //Set to board owner.
        if ($type == '') {
            if (config::get('cfg_allianceid')) {
                $alls = array();
                foreach (config::get('cfg_allianceid') as $val) {
                    $all = new Alliance($val);
                    if (!$all->getExternalID()) return false;
                    $alls[] = $all->getExternalID();
                }
                $this->options['alliance'] = implode(',', $alls);
            }
            if (config::get('cfg_corpid')) {
                $crps = array();
                foreach (config::get('cfg_corpid') as $val) {
                    $crp = new Corporation($val);
                    if (!$crp->getExternalID()) return false;
                    $crps[] = $crp->getExternalID();
                }
                $this->options['corp'] = implode(',', $crps);
            }
            if (config::get('cfg_pilotid')) {
                $pilots = array();
                foreach (config::get('cfg_pilotid') as $val) {
                    $pilot = new Pilot($val);
                    if (!$pilot->getExternalID()) return false;
                    $pilots[] = $pilot->getExternalID();
                }
                $this->options['pilot'] = implode(',', $pilots);
            }
            return true;
        }
        else if ($id > 0) {
            if ($type == 'alliance') {
                $this->options['alliance'] = $id;
            } else if ($type == 'corporation') {
                $this->options['corp'] = $id;
            } else if ($type == 'pilot') {
                $this->options['pilot'] = $id;
            } else {
                return false;
            }

            return true;
        }
        return false;
    }

    function setName($type = '', $name = '')
    {
        //Set to board owner.
        if ($type == '') {
            return $this->setID();
        } else {
            $name = urlencode($name);
            if ($type == 'alliance') {
                $this->options['alliancename'] = $name;
            } elseif ($type == 'corporation') {
                $this->options['corpname'] = $name;
            } elseif ($type == 'pilot') {
                $this->options['pilotname'] = $name;
            } else {
                return false;
            }

            return true;
        }
        return false;
    }

    /**
     * Set the lowest kill ID you want returned.
     *
     * @param integer $id The minimum kill ID
     * @param boolean $internal Set true to use internal kill IDs instead of
     * CCP IDs.
     *
     * @return mixed False on error, True on success.
     */
    function setStartKill($id = 0, $internal = false)
    {
        $id = (int)$id;
        if (!$id) {
            return false;
        }
        if ($internal) {
            $this->options['lastintID'] = $id;
        } else {
            $this->options['lastID'] = $id;
        }
        return true;
    }

    function setRange($range = 0)
    {
        $range = (int)$range;
        if ($range <= 0) {
            return false;
        }
        $this->options['range'] = $range;
        return true;
    }

    /**
     * Set a starting date in unix timestamp format.
     */
    function setStartDate($date = 0)
    {
        if (!$date = (int)$date) {
            return false;
        }
        $this->options['startdate'] = $date;
        return true;
    }

    /**
     * Set an ending date in unix timestamp format.
     *
     * @param string $date YYYY-mm-dd hh:ss
     * @return boolean True if set successfully. False if not.
     */
    function setEndDate($date = 0)
    {
        if (!$date = (int)$date) {
            return false;
        }
        $this->options['enddate'] = $date;
        return true;
    }

    /**
     * Set the system to filter by.
     *
     * @param integer $systemID Eve system ID.
     *
     * @return mixed False on error, True on success.
     */
    function setSystem($systemID = 0)
    {
        if (!$systemID = (int)$systemID) {
            return false;
        }
        $this->options['system'] = $systemID;
        return true;
    }

    /**
     * Set the region to filter by.
     *
     * @param integer $regionID Eve region ID.
     *
     * @return mixed False on error, True on success.
     */
    function setRegion($regionID = 0)
    {
        if (!$regionID = (int)$regionID) {
            return false;
        }
        $this->options['region'] = $regionID;
        return true;
    }

    /**
     * Set true to include kills with no external ID;
     *
     * @param boolean $allkills
     * @return boolean 
     */
    function setAllKills($allkills = false)
    {
        if ($allkills) {
            $this->options['allkills'] = 1;
        } else {
            $this->options['allkills'] = 0;
        }
        return $this->options['allkills'];
    }


    /**
     * Fetch the External ID of the last kill returned.
     * @return int External ID of the last kill returned.
     */
    function getLastReturned()
    {
        return $this->lastReturned;
    }

    /**
     * Fetch the Internal ID of the last kill returned.
     * @return int Internal ID of the last kill returned.
     */
    function getLastInternalReturned()
    {
        return $this->lastInternalReturned;
    }

    /**
     * Return the xml input for this idfeed
     * @return string The xml input for this idfeed
     */
    function getXML()
    {
        return $this->xml;
    }

    /**
     * Return the code for the last error encountered.
     * @return int The code for the last error encountered.
     */
    function getErrorCode()
    {
        return $this->errorcode;
    }

    /**
     * Return the message for the last error encountered.
     * @return string The message for the last error encountered.
     */
    function getErrorMessage()
    {
        return $this->errormsg;
    }

    /**
     * Return any messages generated by parsing the xml.
     * @return string Text for any messages generated by parsing the xml.
     */
    function getParseMessages()
    {
        return $this->parsemsg;
    }

    /**
     * Process the set feed.
     * @return int The count of kills processed
     */
    function processFeed()
    {
        ESI_Helpers::autoSetMaxNumberOfKillsToProcess();
        // Remove error messages at the top.
        if (strpos($this->xml, "<?xml") !== 0) {
            $this->xml = substr($this->xml, strpos($this->xml, "<?xml"));
        }

        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($this->xml);
        if (!$sxe) {
            $this->errormsg = "XML error:\n";
            foreach (libxml_get_errors() as $error) {
                $this->errormsg .= "\t".$error->message."\n";
            }
            return false;
        }
        if (floatval($sxe['edkapi']) && $sxe['edkapi'] < 0.91) {
            return false;
        }
                
        // fetching from API?
        if(is_null($sxe['edkapi']))
        {
            $this->isApiFetch = TRUE;
            $this->skipNpcOnly = config::get('post_no_npc_only');
        }

        else
        {
            $this->skipNpcOnly = config::get('post_no_npc_only_feed');
        }

        // if we'r fetching from another IDFeed (not from API) and the version is either empty or below 1.2
        if ((!is_null($sxe['edkapi']) && strlen($sxe['edkapi']) == 0) || (floatval($sxe['edkapi']) && $sxe['edkapi'] < 1.2)) 
        {
            $this->lookupSlotForIncomingZeroFlags = TRUE;
        }
                
        $this->time = $sxe->currentTime;
        $this->cachedTime = $sxe->cachedUntil;
        if (isset($sxe->error)) {
            $this->errorcode = (int)$sxe->error['code'];
            $this->errormsg = strval($sxe->error);
            return 0;
        }
        // We need raw mails for the mailhash so temporarily disable
        // classification
        config::put('kill_classified', 0);
        $maxNumberOfKillsPerRun = config::get('maxNumberOfKillsPerRun');
        if (!is_null($sxe->result->rowset->row)) {
            $this->numberOfKillsFetched += count($sxe->result->rowset->row);
            foreach ($sxe->result->rowset->row as $row) 
            {
                // check if we reached the maximum number of kills we may fetch
                if(self::$NUMBER_OF_KILLS_FETCHED_FROM_CREST >= $maxNumberOfKillsPerRun)
                {
                    break;
                }
                try
                {
                    $this->processKill($row);
                }

                catch(EsiParserException $e)
                {
                    $this->parsemsg[] = $e->getMessage();
                    continue;
                }
                
                catch(\Swagger\Client\ApiException $e)
                {
                    $this->parsemsg[] = "Error communicating with ESI, aborting!";
                    $this->parsemsg[] = $e->getMessage();
                    break;
                }
            }
        }
        return count($this->posted) + count($this->skipped);
    }

    /**
     * @param SimpleXMLElement $row The row for a single kill.
     * @throws EsiParserException on error when parsing the kill
     * @throws \Swagger\Client\ApiException on error when fetching the kill from ESI
     */
    private function processKill($row)
    {
        $skip = false;
        $internalID = (int)$row['killInternalID'];
        $externalID = (int)$row['killID'];
        $id = 0;
        if (config::get('filter_apply') && config::get('filter_date')
                > strtotime(strval($row['killTime']))) {
            $skip = true;
        }
        if (!$skip && !$id = $this->killExists($row)) {
            $qry = DBFactory::getDBQuery();

            $kill = new Kill();
            if ($externalID) 
            {
                $kill->setExternalID($externalID);
            }


            $kill->setTimeStamp(strval($row['killTime']));

            $sys = SolarSystem::getByID((int)$row['solarSystemID']);
            if (!$sys->getName()) {
                return false;
            }
            $kill->setSolarSystem($sys);

            if (!$this->processVictim($row, $kill, strval($row['killTime']))) {
                $skip = true;
            }
            
            $this->npcOnly = true;
            if (!$skip) {
                foreach ($row->rowset[0]->row as $inv) {
                    if (!$this->processInvolved($inv, $kill,
                            strval($row['killTime']))) {
                        if ($internalID) {
                            $errorstring = "Involved Party error in kill:"
                                    ." External ID = $externalID, Internal ID"
                                    ." = $internalID";
                        } else {
                            $errorstring = "Involved Party error in kill: ID = "
                                    .$externalID;
                        }
                        $this->parsemsg[] = $errorstring;
                        $skip = true;
                        break;
                    }
                }
            }
            
            if ($this->npcOnly && $this->skipNpcOnly) 
            {
                $skip = true;
            }
            if (!$skip) {
                if (isset($row->rowset[1]->row[0])) {
                    foreach ($row->rowset[1]->row as $item) {
                        if(!$this->processItem($item, $kill))
                                                {
                                                    if ($internalID) {
                            $errorstring = "Error processing item with ID ".$item['typeID'].":"
                                    ." External ID = $externalID, Internal ID"
                                    ." = $internalID";
                                                    } else {
                                                        $errorstring =  "Error processing item with ID ".$item['typeID'].":"
                                                                        ."Involved Party error in kill: ID = "
                                                                        .$externalID;
                                                    }
                                                    $this->parsemsg[] = $errorstring;
                                                    $skip = true;
                                                    break;
                                                }
                    }
                }
                                
                // catch KillExceptions
                // will be thrown creating the raw mail, and thus creating the kill's hash, fatally fails
                $errorstring = "";
                $killException = null;

                // check if we can fetch from CREST
                if(!is_null($kill->getCrestUrl()))
                {
                    $EsiParser = new EsiParser($kill->getExternalID(), $kill->getCrestHash());
                    $EsiParser->setAllowNpcOnlyKills(!$this->skipNpcOnly);
                    try
                    {
                        $id = $EsiParser->parse();
                    }
                    
                    catch(ApiException $e)
                    {
                        // ESI error due to incorrect ESI hash
                        if($e->getCode() == 422)
                        {
                            // check if kills with invalid CREST hash should be posted as non-verified kills
                            if(!config::get('skipNonVerifyableKills'))
                            {
                                // reset external ID so the kill is not API verified
                                $kill->setExternalID(null);
                                try
                                {
                                    $id = $kill->add();
                                } 

                                catch (KillException $ex) 
                                {
                                    $killException = $ex;
                                    $id = 0;
                                }
                            }
                            else
                            {
                                $killException = new KillException("Corrupt kill information provided, skipping");
                            }
                        }
                        
                        else
                        {
                            throw $e;
                        }
                    }
                    catch (EsiParserException $e) 
                    {
                        $killException = $e;
                        $id = 0;

                        // tried posting an NPC only kill when not allowed (-5)
                        // kill deleted permanently (-4)
                        // kill too old to be posted (-3)
                        // kill already posted, but not detected during pre-check (should not happen) (-1)
                        if($e->getCode() < 0)
                        {
                            $id = $e->getCode();
                        }

                        else
                        {
                            throw $e;
                        }
                    }
                    
                    catch(KillException $e)
                    {
                        $killException = new KillException($e->getMessage().'Kill-ID: '.$kill->getExternalID(), $e->getCode());
                    }
                    self::$NUMBER_OF_KILLS_FETCHED_FROM_CREST++;
                }

                else
                {
                    try
                    {
                        $id = $kill->add();
                    } 

                    catch (KillException $ex) 
                    {
                        $killException = $ex;
                        $id = 0;
                    }
                }

                if ($id == 0) {
                    if ($internalID) {
                        $errorstring .= "Kill not added. External ID ="
                                ." $externalID, Internal ID = $internalID";
                    } else {
                        $errorstring .= "Kill not added. ID = $externalID";
                    }
                                        if(!is_null($killException))
                                        {
                                            $errorstring .= ". ".$killException->getMessage();
                                        }
                    $this->parsemsg[] = $errorstring;
                    $skip = true;
                } else if ($id < 0) {
                    $id = $kill->getDupe(true);
                    if ($externalID  && $id) {
                        $qry = DBFactory::getDBQuery(true);
                        $qry->execute( "INSERT IGNORE INTO kb3_mails (  `kll_id`,"
                                ." `kll_timestamp`, `kll_external_id`, `kll_hash`,"
                                ." `kll_trust`, `kll_modified_time`)"
                                ."VALUES(".$id.", '".$kill->getTimeStamp()."', "
                                .$externalID.", "
                                ."'".$qry->escape($kill->getHash(false, false))."',"
                                ." 0,"
                                ." UTC_TIMESTAMP())");
                         $qry->execute("UPDATE kb3_kills"
                            ." JOIN kb3_mails ON kb3_kills.kll_id ="
                            ." kb3_mails.kll_id SET kb3_kills.kll_external_id="
                            .$externalID.", kb3_kills.kll_x = ".$kill->getXCoordinate().","
                                                         . "kb3_kills.kll_y = ".$kill->getYCoordinate().","
                                                         . "kb3_kills.kll_z = ".$kill->getZCoordinate()." WHERE kb3_kills.kll_id = $id AND"
                            ." (kb3_kills.kll_external_id IS NULL OR kb3_kills.kll_x = 0)");
                   }
                                    $this->duplicate[] = array($externalID, $internalID, $id);
                                    $skip = true;
                } else {
                    $this->posted[] = array($externalID, $internalID,
                        $id);
                    // Prepare text for the log.
                    if($this->url) {
                        $logaddress = "ID:".$this->url;
                        if (strpos($logaddress, "?")) {
                            $logaddress = substr($logaddress, 0,
                                    strpos($logaddress, "?"));
                        }
                        if ($kill->getExternalID()) {
                            $logaddress .= "?a=kill_detail&kll_ext_id="
                                    .$kill->getExternalID();
                        } else if ($internalID) {
                            $logaddress .= "?a=kill_detail&kll_id=".$internalID;
                        }
                    } else if ($this->name) {
                        $logaddress = $this->name;
                        if ($kill->getExternalID()) {
                            $logaddress .= ":kll_ext_id="
                                    .$kill->getExternalID();
                        } else if ($internalID) {
                            $logaddress .= ":kll_id=".$internalID;
                        }
                    } else {
                        $logaddress = "ID: local input";
                    }

                    logger::logKill($id, $logaddress);
                }
            }
        } else {
            $skip = true;
                        // check whether we have to update the kill's coordinates
                        $Kill = new Kill($id);
                        if($Kill->getXCoordinate() === (float)0)
                        {
                            if($this->processVictim($row, $Kill, strval($row['killTime'])))
                            {
                                $qry = DBFactory::getDBQuery(true);
                                $qry->execute("UPDATE kb3_kills"
                                        ." JOIN kb3_mails ON kb3_kills.kll_id ="
                                        ." kb3_mails.kll_id SET kb3_kills.kll_x = ".$Kill->getXCoordinate().","
                                        . "kb3_kills.kll_y = ".$Kill->getYCoordinate().","
                                        . "kb3_kills.kll_z = ".$Kill->getZCoordinate()." WHERE kb3_kills.kll_id = ".$Kill->getID());
                            }
                        }
        }
        if ($skip) {
            $this->skipped[] = array($externalID, $internalID, $id);
        }
        if ($this->lastReturned < $externalID) {
            $this->lastReturned = $externalID;
        }
        if ($this->lastInternalReturned < $internalID) {
            $this->lastInternalReturned = $internalID;
        }
                $this->updateLastReturnedId();
                
    }
        
        /**
         * updates the last returned kill ID for this feed in the killboard's
         * configuration; this is called after processing each kill, in case
         * fetching hits the time limit and is aborted
         */
        private function updateLastReturnedId()
        {
            // does not apply for fetching from API
            if($this->isApiFetch)
            {
                return;
            }
            
            $feeds = config::get('fetch_idfeeds');
            foreach($feeds AS $i => $feed)
            {
                if($this->url == $feed['url'])
                {
                    $feed['lastkill'] = $this->lastInternalReturned;
                    $feeds[$i] = $feed;
                    break;
                }
            }
            config::set('fetch_idfeeds', $feeds);
        }

    /**
     * @param SimpleXMLElement $row
     * @param Kill $kill
     * @param string $time YYYY-mm-dd hh:ss
     * @return boolean false on error
     */
    private function processVictim($row, &$kill, $time)
    {
        // If we have a character ID but no name then we give up - the needed
        // info is gone.
        // If we have no character ID and no name then it's a structure or NPC
        //    - if we have a moonID (anchored at a moon) call it corpname - moonname
        //    - if we don't have a moonID call it corpname - systemname
        $victim = $row->victim;
        if (!strval($victim['characterName'])
                && (int)$victim['characterID']) {
            return false;
        } else if (!strval($victim['corporationName'])) {
            return false;
        }

        if ((int)$victim['allianceID']) {
            $alliance = Alliance::add(strval($victim['allianceName']),
                    (int)$victim['allianceID']);
        } else if ((int)$victim['factionID']) {
            $alliance = Alliance::add(strval($victim['factionName']),
                    (int)$victim['factionID']);
        } else {
            $alliance = Alliance::add("None");
        }
        $corp = Corporation::add(strval($victim['corporationName']), $alliance, $time,
                    (int)$victim['corporationID'], false);

        if (!strval($victim['characterName'])) {
            if ((int)$row['moonID']) {
                $name = ESI_Helpers::getMoonName((int)$row['moonID']);
                $name = strval($victim['corporationName'])." - ".$name;
            } else {
                $name = strval($victim['corporationName'])." - ".$kill->getSystem()->getName();
            }
        } else if (!(int)$victim['shipTypeID']) {
            return false;
        } else {
            $name = strval($victim['characterName']);
        }

        $pilot = Pilot::add($name, $corp, $time, (int)$victim['characterID'], false);
        $ship = Ship::getByID((int)$victim['shipTypeID']);

        // coordinates default to 0
        $xCoordinate = (float) @$victim['x'];
        $yCoordinate = (float) @$victim['y'];
        $zCoordinate = (float) @$victim['z'];
         
        $kill->setVictim($pilot);
        $kill->setVictimID($pilot->getID());
        $kill->setVictimCorpID($corp->getID());
        $kill->setVictimAllianceID($alliance->getID());
        $kill->setVictimShip($ship);
        $kill->set('dmgtaken', (int)$victim['damageTaken']);
        $kill->setXCoordinate($xCoordinate);
        $kill->setYCoordinate($yCoordinate);
        $kill->setZCoordinate($zCoordinate);
        return true;
    }

    /**
     * @param SimpleXMLElement $inv
     * @param Kill $kill
     * @param string $time YYYY-mm-dd hh:ss
     * @return boolean false on error
     */
    private function processInvolved($inv, &$kill, $time)
    {
        if (!(int)$inv['shipTypeID']
                && !(int)$inv['weaponTypeID']
                && !(int)$inv['characterID']
                && !(string)$inv['characterName']) {
            $errorMsg = "Involved party blank.";
                        if($kill->getExternalID())
                        {
                            $errorMsg .= " ID = ".$kill->getExternalID();
                        }
                        $this->parsemsg[] = $errorMsg;
            return false;
        }
                
                $controlTowerShipClasses = array(
            35, // small Tower
            36, // medium Tower
            37, // large Tower
            38, // POS Module  
            ShipClass::$SHIP_CLASS_ID_CITADELS        
        );
                
        $npc = false;
        $ship = Ship::getByID((int)$inv['shipTypeID']);
        $weapon = Cacheable::factory('Item', (int)$inv['weaponTypeID']);

        $alliance = Alliance::add("None");

        if ((int)$inv['allianceID']) {
            $alliance = Alliance::add(strval($inv['allianceName']),
                    (int)$inv['allianceID']);
        } else if ((int)$inv['factionID']) {
            $alliance = Alliance::add(strval($inv['factionName']),
                    (int)$inv['factionID']);
        }
                
        // get alliance from corp if ship is any kind of tower
        $shipClassID = $ship->getClass()->getID();
        if(in_array($shipClassID, $controlTowerShipClasses))  // POS Module/Tower  
        {
            $corpByName = Corporation::lookup(strval($inv['corporationName']));
            if($corpByName)
            {
                $alliance = $corpByName->getAlliance();
            }
        }
        $corp = Corporation::add(strval($inv['corporationName']), $alliance, $time,
                    (int)$inv['corporationID'], false);

        $charid = (int)$inv['characterID'];
        $charname = (string)$inv['characterName'];
        $loadPilotExternals = true;
        // Allow for blank names for consistency with CCP API.
                // @deprecated? API doesn't supply a character name here
        if (preg_match("/(Mobile (Large|Medium|Small) Warp Disruptor I?I?|\w+ Control Tower( \w+)?)/",
                $charname)) {
            $charname = $inv['corporationName'].' - '.$charname;
            $charid = 0;
        } 
                // @deprecated? API doesn't supply a weapon here
                else if ($charname == ""
                && (preg_match("/(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/",
                   $weapon->getName()))) {
            $charname = $inv['corporationName'].' - '.$weapon->getName();
            $charid = 0;
        } 
                // this should be up-to-date for current state of the API
        // needs verification for Mobile Warpdisruptors
                else if ($charname == ""
                && (preg_match("/(Mobile \w+ Warp|\w+ Control Tower( \w+)?)/",
                   $ship->getName()))) {
            $charname = $inv['corporationName'].' - '.$ship->getName();
            $weapon = $ship;
            $charid = 0;
        } else if ($charname == "" && !$charid) {
            // NPC ship
            $ship = Ship::getByID((int) $inv['shipTypeID']); 
            $weapon = Item::getByID((int) $inv['shipTypeID']);
            $charname = $weapon->getName();
            if($ship->getClass()->getID() != ShipClass::$SHIP_CLASS_ID_CITADELS)
            {
                $npc = true;
            }
            $charid = 0;
            if(!$charname)
            {
                $this->parsemsg[] = "Involved party is an NPC with a ship type not found in the database!";
                return false;
            }
        } else if ($charname == "" && $charid) {
            // Bugged kill
            $this->parsemsg[] = "Involved party has blank pilot name.";
            return false;
        }

        $pilot = Pilot::add((string)$charname, $corp, $time, $charid, false);

        $iparty = new InvolvedParty($pilot->getID(), $corp->getID(),
                $alliance->getID(), (float) $inv['securityStatus'],
                        $ship->getID(), $weapon->getID(),
                        (int) $inv['damageDone']);

        $kill->addInvolvedParty($iparty);
        if ((int)$inv['finalBlow'] == 1) {
            $kill->setFBPilotID($pilot->getID());
        }
        $this->npcOnly = $this->npcOnly && $npc;
        return true;
    }

    /**
     * @param SimpleXMLElement $item The element containing an Item.
     * @param Kill $kill The Kill to add the item to.
     * @param int $slot Set a default slot if none is specified.
     * @return boolean false on error
     */
    private function processItem($item, &$kill, $slot = null)
    {
            $typeID = (int)$item['typeID'];
            // we will add this item with the given flag, even if it's not in our database
            // that way, when the database is updated, the item will display correctly
            $Item = Item::getByID($typeID);
            
            // IDFeed always returns valid CCP flags
            $location = (int)$item['flag'];
            
            // if we fetch from a legacy IDFeed, a zero flag means
            // "fitted in the slot the item belongs in"
            if($location == 0 && $this->lookupSlotForIncomingZeroFlags)
            {
                $location = $Item->getSlot();
            }
            
            // we're fetching from an old IDFeed, not from the API
            if ($slot != null) 
            {
                $location = $slot;
            } 
            
            // singleton flag is set for copies in API and IDFeed (even old Feeds)
            $singleton = (int)$item['singleton'];
                       
            
            // don't get the item's slot if the location is 0!
            // that's just an unknown location, not the item's usual location

            if ((int)$item['qtyDropped']) {
                    $kill->addDroppedItem(new DestroyedItem($Item, (int)$item['qtyDropped'], $singleton, '', $location));
            }
            if ((int)$item['qtyDestroyed']) {
                    $kill->addDestroyedItem(new DestroyedItem($Item, (int)$item['qtyDestroyed'], $singleton, '', $location));
            }
            // Check for containers.
            if (isset($item->rowset)) {
                    foreach ($item->rowset->row as $subitem) {
                            $this->processItem($subitem, $kill, $location);
                    }
            }
            return true;
    }

    /**
     * Return the array of posted kill IDs.
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
     * Return an array of skipped kill IDs
     *
     * @return array
     */
    function getSkipped()
    {
        return $this->skipped;
    }

    /**
     * Return an array of duplicate kill IDs
     *
     * @return array
     */
    function getDuplicate()
    {
        return $this->duplicate;
    }

    function getTime()
    {
        return $this->time;
    }

    function getCachedTime()
    {
        return $this->cachedTime;
    }

    /**
     * Returns the id of a matching existing kill if found.
     * This does not guarantee non-existence as it only checks external id and
     * hash
     *
     * @param SimpleXML $row A SimpleXML object containing the kill.
     *
     * @return integer 0 if no match found, the kll_id if found.
     */
    private function killExists(&$row)
    {
        $qry = DBFactory::getDBQuery(true);
                if ((int)$row['killID'] > 0) {
            $qry->execute("SELECT kll_id FROM kb3_mails "
                    ."WHERE kll_external_id = ".(int)$row['killID']);
            if ($qry->recordCount()) {
                $qrow = $qry->getRow();
                $id = $qrow['kll_id'];
                return $id;
            }
        }
                
        if (strlen($row['hash']) > 1) {
            $qry->execute("SELECT kll_id, kll_external_id, kll_trust FROM kb3_mails"
                    ." WHERE kll_hash = 0x".$qry->escape(strval($row['hash'])));
            if ($qry->recordCount()) {
                $qrow = $qry->getRow();
                $id = (int)$qrow['kll_id'];
                if ((int)$row['killID']    && !(int)$qrow['kll_external_id']) 
                                {
                    $qry->execute("UPDATE kb3_kills JOIN kb3_mails ON kb3_kills.kll_id = ".
                            "kb3_mails.kll_id SET kb3_mails.kll_external_id = ".
                            (int)$row['killID'].", kb3_kills.kll_external_id = ".
                            (int)$row['killID']." WHERE kb3_mails.kll_id = $id AND ".
                            "kb3_mails.kll_external_id IS NULL AND kb3_mails.kll_trust != -1");                    
                }
                return $id;
            }
        }
        return 0;
    }

    public function errormsg()
    {
        return $this->errormsg;
    }
    /**
     *
     * @param KillList $killList
     * @return string KillList as XML
     */
    public static function killListToXML($killList)
    {
        $date = gmdate('Y-m-d H:i:s');
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
        <eveapi version='2' edkapi='".ID_FEED_VERSION."'>
        </eveapi>";
        $sxe = new SimpleXMLElement($xml);
        // Let's start making the xml.
        $sxe->addChild('currentTime', $date);
        $result = $sxe->addChild('result');
        $kills = $result->addChild('rowset');
        $kills->addAttribute('name', 'kills');
        $kills->addAttribute('key', 'killID');
        $kills->addAttribute('columns',
                'killID,solarSystemID,killTime,moonID,hash,trust');

        $count = 0;
        $timing = '';
        while ($kill = $killList->getKill()) {
            if (config::get('km_cache_enabled') && CacheHandler::exists($kill->getID().".xml",
                            'mails')) {
                $cachedRow = new SimpleXMLElement(CacheHandler::get($kill->getID().".xml",
                                        'mails'));
                IDFeed::addXMLElement($kills, $cachedRow);
                continue;
            }

            $count++;
            if ($kill->isClassified()) continue;
            $kill = Cacheable::factory('Kill', $kill->getID());
            $row = $kills->addChild('row');
            $row->addAttribute('killID', intval($kill->getExternalID()));
            $row->addAttribute('killInternalID', intval($kill->getID()));
            $row->addAttribute('solarSystemID', $kill->getSystem()->getExternalID());
            $row->addAttribute('killTime', $kill->getTimeStamp());
                        // check for POS structure
                        if(in_array($kill->getVictimShip()->getClass()->getID() , array(35, 36, 37, 38)))
                        {
                            // try to extract a moon ID from name 
                            $victimName = $kill->getVictimName();
                            if(substr_count($victimName, " - ") > 1)
                            {
                                $moonName = substr($victimName, strpos($victimName, " - ") + 3);
                            }
                            
                            else
                            {
                                $moonName = $victimName;
                            }
                            $moonId = ESI_Helpers::getMoonID($moonName);
                            if($moonId)
                            {
                                $row->addAttribute('moonID', $moonId);
                            }
                            
                            else
                            {
                                $row->addAttribute('moonID', '0');
                            }
                        }
                        
                        else
                        {
                            $row->addAttribute('moonID', '0');
                        }
                            
            
            $row->addAttribute('hash', bin2hex($kill->getHash()));
            $row->addAttribute('trust', $kill->getTrust());
            $victim = Pilot::getByID($kill->getVictimID());
            $victimCorp = Corporation::getByID($kill->getVictimCorpID());
            $victimAlliance = Alliance::getByID($kill->getVictimAllianceID());
            $victimrow = $row->addChild('victim');
            if ($victim->getName() == $kill->getVictimShipName() || $victim->getName() == $kill->getSolarSystemName()) {
                $victimrow->addAttribute('characterID', "0");
                $victimrow->addAttribute('characterName', "");
            } else {
                $victimrow->addAttribute('characterID', $victim->getExternalID());
                $victimrow->addAttribute('characterName', $victim->getName());
            }
            $victimrow->addAttribute('corporationID', $victimCorp->getExternalID());
            $victimrow->addAttribute('corporationName', $victimCorp->getName());
            if ($victimAlliance->isFaction()) {
                $victimrow->addAttribute('allianceID', 0);
                $victimrow->addAttribute('allianceName', '');
                $victimrow->addAttribute('factionID', $victimAlliance->getFactionID());
                $victimrow->addAttribute('factionName', $victimAlliance->getName());
            } else {
                $victimrow->addAttribute('allianceID', $victimAlliance->getExternalID());
                $victimrow->addAttribute('allianceName', $victimAlliance->getName());
                $victimrow->addAttribute('factionID', 0);
                $victimrow->addAttribute('factionName', '');
            }
            $victimrow->addAttribute('damageTaken', $kill->getDamageTaken());
            $victimrow->addAttribute('shipTypeID', $kill->getVictimShipExternalID());
                        // add X, Y and Z coordinates
                        $victimrow->addAttribute('x', $kill->getXCoordinate());
                        $victimrow->addAttribute('y', $kill->getYCoordinate());
                        $victimrow->addAttribute('z', $kill->getZCoordinate());
                        
            $involved = $row->addChild('rowset');
            $involved->addAttribute('name', 'attackers');
            $involved->addAttribute('columns',
                    'characterID,characterName,corporationID,corporationName,allianceID,allianceName,factionID,factionName,securityStatus,damageDone,finalBlow,weaponTypeID,shipTypeID');

            $qry = DBFactory::getDBQuery();
            $sql = "SELECT ind_sec_status, ind_all_id, ind_crp_id,
                ind_shp_id, ind_wep_id, ind_order, ind_dmgdone, plt_id, plt_name,
                plt_externalid, crp_name, crp_external_id,
                wtype.typeName AS wep_name FROM kb3_inv_detail
                JOIN kb3_pilots ON (plt_id = ind_plt_id)
                JOIN kb3_corps ON (crp_id = ind_crp_id)
                JOIN kb3_invtypes wtype ON (ind_wep_id = wtype.typeID)
                WHERE ind_kll_id = ".$kill->getID()." ORDER BY ind_order ASC";
            $qry->execute($sql);

            while ($inv = $qry->getRow()) {
                $invrow = $involved->addChild('row');
                if (strpos($inv['plt_name'], '- ') !== false) {
                    $inv['plt_name'] = substr($inv['plt_name'],
                            strpos($inv['plt_name'], '- ') + 2);
                } else if (strpos($inv['plt_name'], '#') !== false) {
                    $name = explode("#", $inv['plt_name']);
                    $inv['plt_name'] = $name[3];
                }
                if ($inv['plt_name'] == $inv['wep_name']) {
                    $invrow->addAttribute('characterID', 0);
                    $invrow->addAttribute('characterName', "");
                    $invrow->addAttribute('weaponTypeID', 0);
                    $invrow->addAttribute('shipTypeID', $inv['ind_wep_id']);
                } else {
                    $invrow->addAttribute('characterID', $inv['plt_externalid']);
                    $invrow->addAttribute('characterName', $inv['plt_name']);
                    $invrow->addAttribute('weaponTypeID', $inv['ind_wep_id']);
                    $invrow->addAttribute('shipTypeID', $inv['ind_shp_id']);
                }
                $invrow->addAttribute('corporationID', $inv['crp_external_id']);
                $invrow->addAttribute('corporationName', $inv['crp_name']);
                $invAlliance = Alliance::getByID($inv['ind_all_id']);
                if ($invAlliance->isFaction()) {
                    $invrow->addAttribute('allianceID', 0);
                    $invrow->addAttribute('allianceName', '');
                    $invrow->addAttribute('factionID', $invAlliance->getFactionID());
                    $invrow->addAttribute('factionName', $invAlliance->getName());
                } else {
                    if (strcasecmp($invAlliance->getName(), "None") == 0) {
                        $invrow->addAttribute('allianceID', 0);
                        $invrow->addAttribute('allianceName', "");
                    } else {
                        $invrow->addAttribute('allianceID', $invAlliance->getExternalID());
                        $invrow->addAttribute('allianceName', $invAlliance->getName());
                    }
                    $invrow->addAttribute('factionID', 0);
                    $invrow->addAttribute('factionName', '');
                }
                $invrow->addAttribute('securityStatus',
                        number_format($inv['ind_sec_status'], 1));
                $invrow->addAttribute('damageDone', $inv['ind_dmgdone']);
                if ($inv['plt_id'] == $kill->getFBPilotID()) {
                    $final = 1;
                } else {
                    $final = 0;
                }
                $invrow->addAttribute('finalBlow', $final);
            }
            $sql = "SELECT * FROM kb3_items_destroyed WHERE itd_kll_id = ".$kill->getID();
            $qry->execute($sql);
            $qry2 = DBFactory::getDBQuery();
            $sql = "SELECT * FROM kb3_items_dropped WHERE itd_kll_id = ".$kill->getID();
            $qry2->execute($sql);


            $droppedItems = $kill->getDroppedItems();
            $destroyedItems = $kill->getDestroyedItems();
            if ($qry->recordCount() || $qry2->recordCount()) {
                $items = $row->addChild('rowset');
                $items->addAttribute('name', 'items');
                $items->addAttribute('columns', 'typeID,flag,qtyDropped,qtyDestroyed, singleton');

                while ($iRow = $qry->getRow()) {
                    $itemRow = $items->addChild('row');
                    $itemRow->addAttribute('typeID', $iRow['itd_itm_id']);
                                        // no conversion needed, IDFeed always returns valid CCP flags
                                        $itemRow->addAttribute('flag', $iRow['itd_itl_id']);
                                        $itemRow->addAttribute('singleton', $iRow['itd_singleton']);

                    $itemRow->addAttribute('qtyDropped', 0);
                    $itemRow->addAttribute('qtyDestroyed', $iRow['itd_quantity']);
                }


                while ($iRow = $qry2->getRow()) {
                    $itemRow = $items->addChild('row');
                    $itemRow->addAttribute('typeID', $iRow['itd_itm_id']);
                    $itemRow->addAttribute('flag', $iRow['itd_itl_id']);
                                        $itemRow->addAttribute('singleton', $iRow['itd_singleton']);

                    $itemRow->addAttribute('qtyDropped', $iRow['itd_quantity']);
                    $itemRow->addAttribute('qtyDestroyed', 0);
                }
            }
            if (config::get('km_cache_enabled')) {
                CacheHandler::put($kill->getID().".xml", $row->asXML(), 'mails');
            }
            $timing .= $kill->getID().": ".(microtime(true) - $starttime)."<br />";
        }
        $sxe->addChild('cachedUntil', $date);
        return $sxe->asXML();
    }
    /**
     * Recursively add a SimpleXMLElement to another.
     *
     * @param SimpleXMLElement $dest
     * @param SimpleXMLElement $source
     */
    private static function addXMLElement(&$dest, $source)
    {
        $new_dest = $dest->addChild($source->getName(), $source[0]);

        foreach ($source->attributes() as $name => $value) {
            $new_dest->addAttribute($name, $value);
        }

        foreach ($source->children() as $child) {
            IDFeed::addXMLElement($new_dest, $child);
        }
    }
}
