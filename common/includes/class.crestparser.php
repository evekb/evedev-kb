<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
class CrestParserException extends Exception{}

/**
 * @package EDK
 */
class CrestParser
{
	private $error_ = array();
	/** @var string the URL to the crest representation */
    private $crestUrl;
    /** @var string the crest hash value */
    private $crestHash;
	private $externalID = 0;
	private $dupeid_ = 0;
	private $hash = null;
	private $trust = 0;
    /** @var Object (json decoded) */
    private $killmailRepresentation;
    /** @var boolean isNPCOnly flag indicating the killmail has only NPCs as involved parties */
    private $isNPCOnly = true;
    /** @var boolean allowNpcOnlyKills flag indicating whether killmails with only NPCs as involved parties may be posted */
    private $allowNpcOnlyKills = true;

    /**
    * 
    * @param string $crestUrl the URL to the crest representation of the kill
    */
	function __construct($crestUrl)
	{                
            $this->crestUrl = $crestUrl;
            // allow posting of CREST links using the old public-crest base URL
            $this->crestUrl = str_replace('https://public-crest.eveonline.com', CREST_PUBLIC_URL, $this->crestUrl);
            $this->crestUrl = preg_replace('#'.preg_quote('https://esi.tech.ccp.is') .'/(v\d|latest)/#', CREST_PUBLIC_URL.'/', $this->crestUrl);
	}
        
        
    function validateCrestUrl()
    {
        // should look like this:
        // https://crest-tq.eveonline.com/killmails/30290604/787fb3714062f1700560d4a83ce32c67640b1797/
        $urlPieces = explode("/", $this->crestUrl);
        if(count($urlPieces) < 6 || 
                substr($this->crestUrl, 0, strlen(CREST_PUBLIC_URL)) != CREST_PUBLIC_URL || 
                $urlPieces[3] != "killmails" ||
                !is_numeric($urlPieces[4]) ||
                strlen($urlPieces[5]) != 40)
        {

            throw new CrestParserException("Invalid CREST URL: ".$this->crestUrl);
        }        
    }
        
        
	function parse($checkauth = true)
	{
        $this->validateCrestUrl();

        $urlPieces = explode("/", $this->crestUrl);
        $this->externalID = (int)$urlPieces[4];
        $this->crestHash = $urlPieces[5];

        // create killmail representation
        // get instance
        try
        {
            $this->killmailRepresentation = SimpleCrest::getReferenceByUrl($this->crestUrl);
        }

        catch(Exception $e)
        {
            throw new CrestParserException($e->getMessage(), $e->getCode());
        }

		$qry = DBFactory::getDBQuery();

		// Check hashes with a prepared query.
		// Make it static so we can reuse the same query for feed fetches.
		static $timestamp;
		static $checkHash;
		static $hash;
		static $trust;
		static $kill_id;
		$timestamp = str_replace('.', '-', $this->killmailRepresentation->killTime);

		// Check hashes.
		$hash = self::hashMail($this->killmailRepresentation);
		if(!isset($checkHash))
		{
			$checkHash = new DBPreparedQuery();
			$checkHash->prepare('SELECT kll_id, kll_trust FROM kb3_mails WHERE kll_timestamp = ? AND kll_hash = ?');
			$arr = array(&$kill_id, &$trust);
			$checkHash->bind_results($arr);
			$types = 'ss';
			$arr2 = array(&$types, &$timestamp, &$hash);
			$checkHash->bind_params($arr2);
		}
		$checkHash->execute();

		if($checkHash->recordCount())
		{
			$checkHash->fetch();
			$this->dupeid_ = $kill_id;
			// We still want to update the external ID if we were given one.			
			if($this->externalID)
			{ 
                $victimDetails = self::getVictim($this->killmailRepresentation);
				$qry->execute("UPDATE kb3_kills"
						." JOIN kb3_mails ON kb3_mails.kll_id = kb3_kills.kll_id"
						." SET kb3_kills.kll_external_id = ".$this->externalID
						.", kb3_mails.kll_external_id = ".$this->externalID
						.", kll_modified_time = UTC_TIMESTAMP()"
                                                .", kb3_kills.kll_x = ".$victimDetails["x"]
                                                .", kb3_kills.kll_y = ".$victimDetails["y"]
                                                .", kb3_kills.kll_z = ".$victimDetails["z"]
						." WHERE kb3_kills.kll_id = ".$this->dupeid_
						." AND (kb3_kills.kll_external_id IS NULL OR kb3_kills.kll_x = 0)");
				
				if($trust >= 0 && $this->trust && $trust > $this->trust) {
					$qry->execute("UPDATE kb3_mails SET kll_trust = "
							.$this->trust." WHERE kll_id = ".$this->dupeid_);
				}
			}
                        
            // we also want to update the CREST hash
            $qry->execute("UPDATE kb3_mails SET kll_crest_hash = '"
                .$this->crestHash."' WHERE kll_id = ".$this->dupeid_);
				
			if($trust < 0)
            {
                throw new CrestParserException("That mail has been deleted. Kill id was "
            .$this->getDupeID(), -4);
            }
			throw new CrestParserException("That killmail has already been posted <a href=\""
						.edkURI::page('kill_detail', $this->getDupeID(), 'kll_id')
						."\">here</a>.", -1);
		}			
		// Check external IDs
		else if($this->externalID)
		{
			$qry->execute('SELECT kll_id FROM kb3_kills WHERE kll_external_id = '.$this->externalID);
			if($qry->recordCount())
			{
				$row = $qry->getRow();
				throw new CrestParserException("That killmail has already been posted <a href=\""
						."?a=kill_detail&kll_id=".$row['kll_id']
						."\">here</a>.", -1);
			}
		}
        $this->hash = $hash;

        // get timestamp
        $timestamp = $this->killmailRepresentation->killTime;

        // Filtering
        if(config::get('filter_apply'))
        {
            $filterdate = config::get('filter_date');
            if ($timestamp < $filterdate) {
                $filterdate = kbdate("j F Y", config::get("filter_date"));
                throw new CrestParserException("You are not allowed to post killmails older than" .$filterdate, -3);
            }
        }

        // create the kill
        $Kill = new Kill();
        // set external ID
        $Kill->setExternalID($this->externalID);
        // set timestamp
        $Kill->setTimeStamp($timestamp);
        // set CREST hash
        $Kill->setCrestHash($this->crestHash);

        // handle solarSystem
        $solarSystemID = (int)$this->killmailRepresentation->solarSystem->id;
        $solarSystem = SolarSystem::getByID($solarSystemID);
        if (!$solarSystem->getName()) {
            throw new CrestParserException("Unknown solar system ID: ".$solarSystemID);
        }
        $Kill->setSolarSystem($solarSystem);

        // handle victim details
        $this->processVictim($Kill);
        $this->processInvolved($Kill);
        $this->processItems($Kill);
        
        if($this->isNPCOnly && !$this->allowNpcOnlyKills)
        {
            throw new CrestParserException("Kill is a loss to NPCs only, but posting NPC kills is not allowed!", -5);
        }
        return $Kill->add();
    }

	function error($message, $debugtext = null)
	{
		$this->error_[] = array($message, $debugtext);
	}

	function getError()
	{
		if (count($this->error_))
		{
			return $this->error_;
		}
		return false;
	}

	/**
	 *
	 * @param mixed $mailRepresentation
	 * @return string
	 */
	public static function hashMail($mailRepresentation = null)
	{
		if(is_null($mailRepresentation)) return false;

		$involvedParties = self::getAttackers($mailRepresentation);
                $victim = self::getVictim($mailRepresentation);
		$invListDamage = array();
		foreach($involvedParties AS $attacker)
        {
            $invListDamage[] = $attacker["damageDone"];
            // TODO check for NPCs/POSs etc
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
                $involvedPartyName = $attacker["shipTypeName"]." / ".$corpName;
            }

            if($attacker["finalBlow"] === true)
            {
                // add the string " (laid the final blow)" to keep compatibility with legacy parser mails
                $involvedPartyName .= " (laid the final blow)";
            }
            $invListName[] = $involvedPartyName;
        }
        // Sort the involved list by damage done then alphabetically.
		array_multisort($invListDamage, SORT_DESC, SORT_NUMERIC, $invListName, SORT_ASC, SORT_STRING);

				
        // timestamp
		$hashIn = str_replace('.', '-', $mailRepresentation->killTime);
		// cut off seconds from timestamp to keep compatibility with legacy parser mails
		$hashIn = substr($hashIn, 0, 16);
		
		// victim's name
		// was it a player?
		if($victim["characterName"])
		{
			$hashIn .= $victim["characterName"];
		}
		
		// was it a pos structure?
		else if($victim["moonName"])
		{
			// cut off the first two characters (again, to keep compatibility with legacy parser killmails)
			$hashIn .= substr($victim["moonName"], 2, strlen($victim["moonName"])-1);
		}
		
		else
		{
			return false;
		}
		
		// destroyed ship
		$hashIn .= $victim["shipTypeName"];
		// solar system
		$hashIn .= (String) $mailRepresentation->solarSystem->name;
		// damage taken
		$hashIn .= $victim["damageTaken"];
		// list of involved parties
		$hashIn .= implode(',', $invListName);
                // list of involved parties' damage done
		$hashIn .= implode(',', $invListDamage);

		return md5($hashIn, true);
	}
	/**
	 * @return integer
	 */
	public function getDupeID()
	{
		return $this->dupeid_;
	}

	public function setTrust($trust)
	{
		$this->trust = intval($trust);
	}
    
    /**
     * Sets the flag whether to allow posting of kills containing
     * only NPCs as involved parties.
     * @param boolean $allowNpcOnlyKills
     */
    public function setAllowNpcOnlyKills($allowNpcOnlyKills)
	{
		$this->allowNpcOnlyKills = (boolean) $allowNpcOnlyKills;
	}
   
    /**
     * Returns whether posting of kills with only NPCs as involved parties is allowed.
     * 
     * @return boolean true if posting of NPC only kills is allowed, otherwise false
     */
    public function getAllowNpcOnlyKills()
    {
        return $this->allowNpcOnlyKills;
    }
        
    /**
     * @param mixed $mailRepresentation
     * @return array
     */
     public static function getAttackers($mailRepresentation) {
         $attackers = array();

         foreach($mailRepresentation->attackers as $attacker) {
             $involvedParty = array();
             $involvedParty["characterID"] = (int) @$attacker->character->id;
             $involvedParty["characterName"] = (string) @$attacker->character->name;
             $involvedParty["corporationID"] = (int) @$attacker->corporation->id;
             $involvedParty["corporationName"] = (string) @$attacker->corporation->name;
             $involvedParty["allianceID"] = (int) @$attacker->alliance->id;
             $involvedParty["allianceName"] = (string) @$attacker->alliance->name;
             $involvedParty["factionID"] = (int) @$attacker->faction->id;
             $involvedParty["factionName"] = (string) @$attacker->faction->name;
             $involvedParty["securityStatus"] = (float) $attacker->securityStatus;
             $involvedParty["damageDone"] = (int) @$attacker->damageDone;
             $involvedParty["finalBlow"] = (boolean) @$attacker->finalBlow;
             $involvedParty["weaponTypeID"] = (int) @$attacker->weaponType->id;
             $involvedParty["shipTypeID"] = (int) @$attacker->shipType->id;
             $involvedParty["shipTypeName"] = (string) @$attacker->shipType->name;
             $attackers[] = $involvedParty;
         }
     return $attackers;
    }
       
       
    /**
     * @param mixed $mailRepresentation
     * @return array
     */
     public static function getVictim($mailRepresentation)
     {
             $victim = array();
             $victim["shipTypeID"] = (int) @$mailRepresentation->victim->shipType->id;
             $victim["shipTypeName"] = (string) @$mailRepresentation->victim->shipType->name;
             $victim["characterID"] = (int) @$mailRepresentation->victim->character->id;
             $victim["characterName"] = (string) @$mailRepresentation->victim->character->name;
             $victim["corporationID"] = (int) @$mailRepresentation->victim->corporation->id;
             $victim["corporationName"] = (string) @$mailRepresentation->victim->corporation->name;
             $victim["allianceID"] = (int) @$mailRepresentation->victim->alliance->id;
             $victim["allianceName"] = (string) @$mailRepresentation->victim->alliance->name;
             $victim["factionID"] = (int) @$mailRepresentation->victim->faction->id;
             $victim["factionName"] = (string) @$mailRepresentation->victim->faction->name;
             $victim["damageTaken"] = (int) @$mailRepresentation->victim->damageTaken;
             $victim["moonName"] = (string) @$mailRepresentation->moon->name;
             $victim["moonID"] = (int) @$mailRepresentation->moon->id;
             $victim["x"] = (float) @$mailRepresentation->victim->position->x;
             $victim["y"] = (float) @$mailRepresentation->victim->position->y;
             $victim["z"] = (float) @$mailRepresentation->victim->position->z;
             return $victim;
     }
        
    /**
     * @param mixed $mailRepresentation
     * @return array
     */
    private static function getItems($itemsInMail)
    {
         $items = array();
         if($itemsInMail)
         {
             foreach($itemsInMail as $item) {
                 $itemDetails = array();
                 $itemDetails["typeID"] = (int) @$item->itemType->id;
                 $itemDetails["flag"] = (int) @$item->flag;
                 $itemDetails["qtyDropped"] = (int) @$item->quantityDropped;
                 $itemDetails["qtyDestroyed"] = (int) @$item->quantityDestroyed;
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
     * extracts and sets victim details in the given kill
     * reference; uses $this->killmailRepresentation as source
     * @param Kill $Kill reference to the kill to update
     * @throws CrestParserException
     */
    protected function processVictim(&$Kill)
    {
        $victimDetails = self::getVictim($this->killmailRepresentation);
        $timestamp = $this->killmailRepresentation->killTime;

        // If we have a character ID but no name then we give up - the needed
        // info is gone.
        // If we have no character ID and no name then it's a structure or NPC
        //	- if we have a moonID (anchored at a moon) call it corpname - moonname
        //	- if we don't have a moonID call it corpname - systemname
        if (!strlen($victimDetails['characterName']) && $victimDetails['characterID'] > 0) {
        throw new CrestParserException("Insufficient victim information provided! Kill-ID: ".$this->externalID);
        } else if (!$victimDetails['corporationID'] && !$victimDetails['factionID']) {
                throw new CrestParserException("Insufficient victim corpiration information provided! Kill-ID: ".$this->externalID);
        }

        // get alliance
        if ($victimDetails['allianceID'] > 0) {
                $Alliance = Alliance::add($victimDetails['allianceName'], $victimDetails['allianceID']);
        } else if ($victimDetails['factionID'] > 0) {
                $Alliance = Alliance::add($victimDetails['factionName'],$victimDetails['factionID']);
        } else {
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
                $victimName = $Corp->getName()." - ".$victimDetails["moonName"];
            }

            else
            {
                $victimName = $Corp->getName()." - ".$Kill->getSystem()->getName();
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
     * processes and adds all involved parties in the given killmail representation
     * @param Kill $Kill reference to the kill to update
     * @throws CrestParserException
     */
    protected function processInvolved(&$Kill)
    {
        $involvedParties = self::getAttackers($this->killmailRepresentation);
        $timestamp = $this->killmailRepresentation->killTime;

        foreach($involvedParties AS $involvedParty)
        {
            if (!$involvedParty['shipTypeID']
                            && !$involvedParty['weaponTypeID']
                            && !$involvedParty['characterID']
                            && !strlen($involvedParty['characterName'])) {
                    throw new CrestParserException("Error processing involved party. Kill-ID: ".$this->externalID);
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
                    $Alliance = Alliance::add($involvedParty['allianceName'], $involvedParty['allianceID']);
            }
            // only use faction as alliance if no corporation is given (faction NPC)
            else if ($involvedParty['factionID'] > 0 && strlen($involvedParty['corporationName']) > 0) 
            {		
                    $Alliance = Alliance::add($involvedParty['factionName'], $involvedParty['factionID']);
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
                $Corp = self::fetchCorp("Unknown", $Alliance, $timestamp);
            }

            // get ship class to determine whether it's a tower and 
            // we need to fetch the alliance via the corp
            $shipClassID = $Ship->getClass()->getID();
            if($shipClassID == 35           // small Tower
                || $shipClassID == 36   // medium Tower
                || $shipClassID == 37   // large Tower
                || $shipClassID == 38  // POS Module  
                || $shipClassID == ShipClass::$SHIP_CLASS_ID_CITADELS)  // Citadels 
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
                $Ship = Ship::getByID($involvedParty['shipTypeID']);
                $Weapon = Item::getByID($involvedParty['shipTypeID']);
                if(!$Weapon->getName())
                {
                    throw new CrestParserException("Involved party is an NPC with a ship type not found in the database! Kill-ID: ".$killData->killID);
                }
                $involvedPartyName = $Corp->getName().' - '.$Weapon->getName();
                // citadels are no NPCs!
                if($Ship->getClass()->getID() != ShipClass::$SHIP_CLASS_ID_CITADELS)
                {
                    $isNPC = TRUE;
                }
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

            $this->isNPCOnly = $this->isNPCOnly && $isNPC;
        }
    }
        
        
    /**
     * processes all dropped/destroyed items in that kill
     * and adds them as Dropped/Destroyed
     * @param type $Kill the kill to add the items to
     */
    protected function processItems(&$Kill)
    {
        $items = self::getItems($this->killmailRepresentation->victim->items);
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

        // Blueprint copy - in the cargohold
        // overrides all other locations
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
	 * Return corporation from cached list or look up a new name.
	 *
	 * @param string $corpName Corp name to look up.
	 * @return Corporation Corporation object matching input name.
	 */
	private static function fetchCorp($corpName, $Alliance = null, $timestamp = null)
	{
        $corp = Corporation::lookup($corpName);
        if (!$corp) {
            if ($Alliance == null) {      
                    // If the corporation is new and the alliance unknown (structure)
                    // fetch the alliance from the API.
                    $corp = Corporation::add($corpName, Alliance::add("None"), $timestamp);
                    if (!$corp->getExternalID()) {
                            $corp = false;
                    }
                    else {
                            $corp->execQuery();
                    }

            } else {
                    $corp = Corporation::add($corpName, $Alliance, $timestamp, 0, FALSE);
            }
        }

		return $corp;
	}
}
