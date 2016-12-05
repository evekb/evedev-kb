<?php

use Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOk;
use Swagger\Client\ApiException;
/**
 * ESI Kill Parser
 * @author Salvoxia <salvoxia@blindfish.info>
 */
class EsiParserException extends Exception{}

/**
 * Parses ESI kill representations ({@link \Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOk})
 * into kill objects and posts the kill
 */
class EsiParser
{    
    /** @var \Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOk the ESI kill representation */
    protected $EsiKill;
    /** @var string the crest hash value */
    protected $crestHash;
    protected $externalID = 0;
    protected $dupeid_ = 0;
    protected $hash = null;
    protected $trust = 0;
    /** @var array an indexed array, using the input IDs as index */
    protected $idNameMapping;

    /** @var boolean isNPCOnly flag indicating the killmail has only NPCs as involved parties */
    private $isNPCOnly = true;
    /** @var boolean allowNpcOnlyKills flag indicating whether killmails with only NPCs as involved parties may be posted */
    private $allowNpcOnlyKills = true;

    /**
    * Creates and initializes the parser for the given kill Id and hash. 
    *
    */
    function __construct($killId, $hash)
    {                
        $this->externalID = $killId;
        $this->crestHash = $hash;
    }

    /**
     * Parses and posts the kill
     * @return mixed the internal kill ID if posted successfully, <code>false</code> if an error occurs while adding the kill to the database
     * @throws EsiParserException if there's an error parsing the kill
     * @throws ApiException if there's an error while communicating with ESI 
     */
    function parse()
    {
        // create killmail representation
        // get instance
        try
        {
            $this->EsiKill = ESI_Helpers::fetchKill($this->externalID, $this->crestHash);
        }

        catch(ApiException $e)
        {
            throw new EsiParserException($e->getMessage(), $e->getCode());
        }
        
        // gather all involved entity IDs for bulk translating to names
        $entityIds = self::getEntityIds($this->EsiKill);
        $this->idNameMapping = ESI_Helpers::resolveEntityIds($entityIds);
        

        $timestamp = ESI_Helpers::formatDateTime($this->EsiKill->getKillmailTime());
        
        // Check hashes.
        $hash = self::hashMail($this->killmailRepresentation);

        $trust = null;
        $kill_id = null;
        $checkHash = new DBPreparedQuery();
        $checkHash->prepare('SELECT kll_id, kll_trust FROM kb3_mails WHERE kll_timestamp = ? AND kll_hash = ?');
        $arr = array(&$kill_id, &$trust);
        $checkHash->bind_results($arr);
        $types = 'ss';
        $arr2 = array(&$types, &$timestamp, &$hash);
        $checkHash->bind_params($arr2);
        $checkHash->execute();

        if($checkHash->recordCount())
        {
            $checkHash->fetch();
            $this->dupeid_ = $kill_id;
            // We still want to update the external ID if we were given one.            
            if($this->externalID)
            { 
                $Position = $this->EsiKill->getVictim()->getPosition();
                $x = $Position->getX();
                $y = $Position->getY();
                $z = $Position->getZ();
                
                // update the kill's coordinates, if the we don't know them already
                $updateParams = new \DBPreparedQuery();
                $updateParams->prepare("UPDATE kb3_kills"
                        ." JOIN kb3_mails ON kb3_mails.kll_id = kb3_kills.kll_id"
                        ." SET kb3_kills.kll_external_id = ?"
                            .", kb3_mails.kll_external_id = ?"
                            .", kll_modified_time = UTC_TIMESTAMP()"
                            .", kb3_kills.kll_x = ?"
                            .", kb3_kills.kll_y = ?"
                            .", kb3_kills.kll_z = ?"
                        ." WHERE kb3_kills.kll_id = ?"
                        ." AND (kb3_kills.kll_external_id IS NULL OR kb3_kills.kll_x = 0)");
                $types = 'iidddi';
                $arr = array(&$types, &$this->externalID, &$this->externalID, &$x, &$y, &$z, &$this->dupeid_);
                $updateParams->bind_params($arr);
                $updateParams->execute();
                
                // update trust level
                if($trust >= 0 && $this->trust && $trust > $this->trust) 
                {
                    $updateTrust = new \DBPreparedQuery();
                    $updateTrust->prepare('UPDATE kb3_mails SET kll_trust = ? WHERE kll_id = ?');
                    $types = 'ii';
                    $arr = array(&$types, &$this->trust, &$this->dupeid_);
                    $updateTrust->bind_params($arr);
                    $updateTrust->execute();
                }
            }
                        
            // we also want to update the CREST hash
            $updateTrust = new \DBPreparedQuery();
            $updateTrust->prepare('UPDATE kb3_mails SET kll_crest_hash = ? WHERE kll_id = ?');
            $types = 'si';
            $arr = array(&$types, &$this->crestHash, &$this->dupeid_);
            $updateTrust->bind_params($arr);
            $updateTrust->execute();

                
            if($trust < 0)
            {
                throw new EsiParserException("That mail has been deleted permanently. Kill id was ".$this->getDupeID(), -4);
            }
            
            throw new EsiParserException("That killmail has already been posted <a href=\"".edkURI::page('kill_detail', $this->getDupeID(), 'kll_id')."\">here</a>.", -1);
        }
        
        // Check external IDs
        else if($this->externalID)
        {
            $checkExternalId = new \DBPreparedQuery();
            $checkExternalId->prepare('SELECT kll_id FROM kb3_kills WHERE kll_external_id = ?');
            $arr = array(&$kill_id);
            $checkExternalId->bind_results($arr);
            $types = 'i';
            $arr2 = array(&$types, &$this->externalID);
            $checkExternalId->bind_params($arr2);
            $checkExternalId->execute();

            if($checkExternalId->recordCount() > 0)
            {
                $checkExternalId->fetch();
                throw new EsiParserException("That killmail has already been posted <a href=\"?a=kill_detail&kll_id=".$kill_id."\">here</a>.", -1);
            }
        }
        $this->hash = $hash;

        // Filtering
        if(config::get('filter_apply'))
        {
            $filterdate = config::get('filter_date');
            if ($timestamp < $filterdate) 
            {
                $filterdate = kbdate("j F Y", config::get("filter_date"));
                throw new EsiParserException("You are not allowed to post killmails older than" .$filterdate, -3);
            }
        }

        // create the kill
        $Kill = new \Kill();
        // set external ID
        $Kill->setExternalID($this->externalID);
        // set timestamp
        $Kill->setTimeStamp($timestamp);
        // set CREST hash
        $Kill->setCrestHash($this->crestHash);

        // handle solarSystem
        $solarSystemID = $this->EsiKill->getSolarSystemId();
        $solarSystem = SolarSystem::getByID($solarSystemID);
        if (!$solarSystem->getName()) 
        {
            throw new EsiParserException("Unknown solar system ID: ".$solarSystemID);
        }
        $Kill->setSolarSystem($solarSystem);

        // handle victim details
        $this->processVictim($Kill);
        $this->processInvolved($Kill);
        $this->processItems($Kill);
        
        if($this->isNPCOnly && !$this->allowNpcOnlyKills)
        {
            throw new EsiParserException("Kill is a loss to NPCs only, but posting NPC kills is not allowed!", -5);
        }
        return $Kill->add();
    }


    /**
     * Calculates the EDK legacy killmail hash for uniquely identifying a kill
     * @param GetKillmailsKillmailIdKillmailHashOk $EsiKill the ESI kill representation to hash
     * @return string the killmail hash
     * @throws EsiParserException if any entity ID cannot be resolved to a name
     */
    public static function hashMail($EsiKill = null)
    {
        if(is_null($EsiKill)) return false;

        $involvedParties = $EsiKill->getAttackers();
        $Victim = $EsiKill->getVictim();
        $invListDamage = array();
        foreach($involvedParties AS $Attacker)
        {
            $invListDamage[] = $Attacker->getDamageDone();
            
            $involvedPartyName = "";
            if(null !== $Attacker->getCharacterId())
            {
                if(!isset($this->idNameMapping[$Attacker->getCharacterId()]))
                {
                    throw new EsiParserException("Unable to resolve involved party ID ".$Attacker->getCharacterId().", Kill-ID: ".$this->externalID);
                }
                $involvedPartyName = $this->idNameMapping[$Attacker->getCharacterId()];
            }

            // use "shipTypeName / corpName" for compatibility with legacy parser mails
            else
            {       
                // required for NPCs without corp
                $corpName = "Unknown";
                if(null !== $Attacker->getFactionId())
                {
                    if(!isset($this->idNameMapping[$Attacker->getFactionId()]))
                    {
                        throw new EsiParserException("Unable to resolve involved party faction ID ".$Attacker->getFactionId().", Kill-ID: ".$this->externalID);
                    }
                    $corpName = $this->idNameMapping[$Attacker->getFactionId()];
                }

                if(null !== $Attacker->getCorporationId())
                {
                    if(!isset($this->idNameMapping[$Attacker->getCorporationId()]))
                    {
                        throw new EsiParserException("Unable to resolve involved party corporation ID ".$Attacker->getCorporationId().", Kill-ID: ".$this->externalID);
                    }
                    $corpName = $this->idNameMapping[$Attacker->getCorporationId()];
                }
                $InvolvedShip = new \Item($Attacker->getShipTypeId());
                $involvedPartyName = $InvolvedShip->getName()." / ".$corpName;
            }

            if($Attacker->getFinalBlow() === true)
            {
                // add the string " (laid the final blow)" to keep compatibility with legacy parser mails
                $involvedPartyName .= " (laid the final blow)";
            }
            $invListName[] = $involvedPartyName;
        }
        // Sort the involved list by damage done then alphabetically.
        array_multisort($invListDamage, SORT_DESC, SORT_NUMERIC, $invListName, SORT_ASC, SORT_STRING);

                
        // timestamp
        $hashIn = ESI_Helpers::formatDateTime($EsiKill->getKillmailTime());
        // cut off seconds from timestamp to keep compatibility with legacy parser mails
        $hashIn = substr($hashIn, 0, 16);
        
        // victim's name
        // was it a player?
        
        if(null !== $Victim->getCharacterId())
        {
            if(!isset($this->idNameMapping[$Victim->getCharacterId()]))
            {
                throw new EsiParserException("Unable to resolve victim ID ".$Victim->getCharacterId());
            }
            $hashIn .= $this->idNameMapping[$Attacker->getCharacterId()];
        }
        
        // was it a pos structure?
        else if(null !== $EsiKill->getMoonId())
        {
            $moonName = \API_Helpers::getMoonName($EsiKill->getMoonId());
            // cut off the first two characters (again, to keep compatibility with legacy parser killmails)
            $hashIn .= substr($moonName, 2, strlen($moonName)-1);
        }
        
        else
        {
            return false;
        }
        
        // destroyed ship
        $VictimShip = new \Item($Victim->getShipTypeId());
        $hashIn .= $VictimShip->getName();
        // solar system
        $SolarSystem = new \SolarSystem($EsiKill->getSolarSystemId());
        $hashIn .= $SolarSystem->getName();
        // damage taken
        $hashIn .= $Victim->getDamageTaken();
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
     * extracts and sets victim details in the given kill
     * reference; uses $this->killmailRepresentation as source
     * @param Kill $Kill reference to the kill to update
     * @throws EsiParserException
     */
    protected function processVictim(&$Kill)
    {
        $Victim = $this->EsiKill->getVictim();
        $timestamp = \ESI_Helpers::formatDateTime($this->EsiKill->getKillmailTime());

        // If we have no character ID and no name then it's a structure or NPC
        //    - if we have a moonID (anchored at a moon) call it corpname - moonname
        //    - if we don't have a moonID call it corpname - systemname
        if (!$Victim->getCorporationId() && !$Victim->getFactionId()) 
        {
            throw new EsiParserException("Insufficient victim corpiration information provided! Kill-ID: ".$this->externalID);
        }
        
        $characterId = $Victim->getCharacterId();
        $corporationId = $Victim->getCorporationId();
        $allianceId = $Victim->getAllianceId();
        $factionId = $Victim->getFactionId();
        
        // character ID could not be resolved to a name
        if($characterId !== null && !isset($this->idNameMapping[$characterId]))
        {
            throw new EsiParserException("Unable to resolve victim character ID ".$characterId.", Kill-ID: ".$this->externalID);
        }
        // corp ID is present, but could not be resolved to a name
        if(null !== $corporationId && !isset($this->idNameMapping[$corporationId]))
        {
            throw new EsiParserException("Unable to resolve victim corporation ID ".$corporationId.", Kill-ID: ".$this->externalID);
        }
        // alliance ID is present, but could not be resolved to a name
        if(null !== $allianceId && !isset($this->idNameMapping[$allianceId]))
        {
            throw new EsiParserException("Unable to resolve victim alliance ID ".$allianceId.", Kill-ID: ".$this->externalID);
        }
       
        // get alliance
        if(null !== $allianceId) 
        {
            $Alliance = \Alliance::add($this->idNameMapping[$allianceId], $allianceId);
        } 
        
        else if(null !== $factionId) 
        {
            $Faction = Cacheable::factory('Faction', $factionId);
            $Alliance = \Alliance::add($Faction->getName(), $factionId);
        } 
        
        else 
        {
            $Alliance = \Alliance::add("None");
        }

        // get corp
        // if corp is not present, use faction
        if(null !== $corporationId)
        {
            $Corp = \Corporation::add($this->idNameMapping[$corporationId], $Alliance, $timestamp, $corporationId);
        }   

        else if(null !== $factionId)
        {
            // try getting the corp from our database
                $Faction = Cacheable::factory('Faction', $factionId);
                
                // harcoded workaround for the "Unknown" faction (for sleepers) which is not contained in the SDE, hopefully this can be removed soon!
                if($factionId == 500021)
                {
                    $factionName = "Unknown";
                }
                
                else
                {
                    $factionName = $Faction->getName();
                }
                        
                $Corp = Corporation::add($factionName, $Alliance, $timestamp, $factionId);
        }
        
        // NPCs without Corp/Alliance/Faction (e.g. Rogue Drones)
        else
        {
            $Corp = Corporation::add("Unknown", $Alliance, $timestamp);
        }

        // victim's name
        if(is_null($characterId))
        {
            if(null !== $this->EsiKill->getMoonId())
            {
                $moonName = \API_Helpers::getMoonName($this->EsiKill->getMoonId());
                $victimName = $Corp->getName()." - ".$moonName;
            }

            else
            {
                $victimName = $Corp->getName()." - ".$Kill->getSystem()->getName();
            }
        }
        
        if(isset($victimName))
        {
            $Pilot = Pilot::add($victimName, $Corp, $timestamp, $characterId, false);
        }
        
        else
        {
            $Pilot = Pilot::add($this->idNameMapping[$characterId], $Corp, $timestamp, $characterId, false);
        }

        // handle victim's ship
        $Ship = Ship::getByID($Victim->getShipTypeId());


        // set values in $Kill
        $Kill->setVictim($Pilot);
        $Kill->setVictimID($Pilot->getID());
        $Kill->setVictimCorpID($Corp->getID());
        $Kill->setVictimAllianceID($Alliance->getID());
        $Kill->setVictimShip($Ship);
        $Kill->set('dmgtaken', $Victim->getDamageTaken());
        
        $Position = $Victim->getPosition();
        // older kills might not have a position
        if(!is_null($Position))
        {
            $Kill->setXCoordinate($Position->getX());
            $Kill->setYCoordinate($Position->getY());
            $Kill->setZCoordinate($Position->getZ());
        }
    }


    /**
     * processes and adds all involved parties in the given killmail representation
     * @param Kill $Kill reference to the kill to update
     * @throws EsiParserException
     */
    protected function processInvolved(&$Kill)
    {
        $involvedParties = $this->EsiKill->getAttackers();
        $timestamp = \ESI_Helpers::formatDateTime($this->EsiKill->getKillmailTime());

        foreach($involvedParties AS $involvedParty)
        {
            // sanity check
            if (!$involvedParty->getShipTypeId()
                    && !$involvedParty->getWeaponTypeId()
                    && !$involvedParty->getCharacterId()) 
            {
                throw new EsiParserException("Error processing involved party. Kill-ID: ".$this->externalID);
            }
            
            $characterId = $involvedParty->getCharacterId();
            $corporationId = $involvedParty->getCorporationId();
            $allianceId = $involvedParty->getAllianceId();
            $factionId = $involvedParty->getFactionId();
            
            // character ID could not be resolved to a name
            if(null !== $characterId && !isset($this->idNameMapping[$characterId]))
            {
                throw new EsiParserException("Unable to resolve involved party character ID ".$characterId.", Kill-ID: ".$this->externalID);
            }
            // corp ID is present, but could not be resolved to a name
            if(null !== $corporationId && !isset($this->idNameMapping[$corporationId]))
            {
                throw new EsiParserException("Unable to resolve involved party corporation ID ".$corporationId.", Kill-ID: ".$this->externalID);
            }
            // alliance ID is present, but could not be resolved to a name
            if(null !== $allianceId && !isset($this->idNameMapping[$allianceId]))
            {
                throw new EsiParserException("Unable to resolve involved party alliance ID ".$allianceId.", Kill-ID: ".$this->externalID);
            }
          

            $isNPC = FALSE;

            // get involved party's ship
            if(!$involvedParty->getShipTypeId())
            {
                $Ship = Ship::lookup("Unknown");
            }

            else
            {
                $Ship = Ship::getByID($involvedParty->getShipTypeId());
            }
                
            $Weapon = Cacheable::factory('Item', $involvedParty->getWeaponTypeId());
            
                    
            // get alliance
            $Alliance = Alliance::add("None");
            if (null !== $allianceId) 
            {
                $Alliance = Alliance::add($this->idNameMapping[$allianceId], $allianceId);
            }
            // only use faction as alliance if no corporation is given (faction NPC)
            else if (null !== $factionId && null !== $corporationId) 
            {
                $Faction = Cacheable::factory('Faction', $factionId);
                $Alliance = Alliance::add($Faction->getName(), $factionId);
            }

            // get corp
            // if corp is not present, use faction
            if(null !== $corporationId)
            {
                $Corp = Corporation::add(strval($this->idNameMapping[$corporationId]), $Alliance, $timestamp, $corporationId);
            }   

            else if(null !== $factionId)
            {
                // try getting the corp from our database
                $Faction = Cacheable::factory('Faction', $factionId);
                
                // harcoded workaround for the "Unknown" faction (for sleepers) which is not contained in the SDE, hopefully this can be removed soon!
                if($factionId == 500021)
                {
                    $factionName = "Unknown";
                }
                
                else
                {
                    $factionName = $Faction->getName();
                }
                        
                $Corp = Corporation::add($factionName, $Alliance, $timestamp, $factionId);
            }

            // NPCs without Corp/Alliance/Faction (e.g. Rogue Drones)
            else
            {
                $Corp = \Corporation::add("Unknown", $Alliance, $timestamp);
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
            // Fix for case that involved party is an actual pilot without corp
            // FoxFour is to blame!
            if(null !== $characterId && null === $corporationId)
            {
                $Pilot = new \Pilot($id, $characterId);
                $Corp = $Pilot->getCorp();
            }

            // special case:
            // NPC/Tower/other structure
            if(null === $characterId && null === $involvedParty->getWeaponTypeId() && null === $allianceId)
            {
                $Alliance = $Corp->getAlliance();
                $Ship = Ship::getByID($involvedParty->getShipTypeId());
                $Weapon = Item::getByID($involvedParty->getShipTypeId());
                if(!$Weapon->getName())
                {
                    throw new EsiParserException("Involved party is an NPC with a ship type not found in the database! Kill-ID: ".$this->externalID);
                }
                $involvedPartyName = $Corp->getName().' - '.$Weapon->getName();
                // citadels are no NPCs!
                if($Ship->getClass()->getID() != ShipClass::$SHIP_CLASS_ID_CITADELS)
                {
                    $isNPC = TRUE;
                }
                $characterId = 0;
            }
                  
            if(!$characterId)
            {
                $Pilot = Pilot::add($involvedPartyName, $Corp, $timestamp, $characterId, false);
            }
            
            else
            {
                $Pilot = \Pilot::add($this->idNameMapping[$characterId], $Corp, $timestamp, $characterId, false);
            }

            // create involvedParty
            $IParty = new InvolvedParty($Pilot->getID(), $Corp->getID(),
            $Alliance->getID(),  $involvedParty->getSecurityStatus(),
                    $Ship->getID(), $Weapon->getID(),
                    $involvedParty->getDamageDone());

            $Kill->addInvolvedParty($IParty);

            if($involvedParty->getFinalBlow() === TRUE)
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
        $items = $this->EsiKill->getVictim()->getItems();
        // TODO implement proper CCP flags!
        foreach($items AS $EsiItem)
        {
            // we use this nested construct for perhaps later changing
            // the way we process single items and nested items
            $this->processItem($EsiItem, $Kill);
        }
    }
        
        
    /**
     * Accepts an ESI VictimItem representation (top-level, may have child items)
     * and adds it and all contained items to the given kill
     * 
     * @param \Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOkVictimItems1 $EsiItem
     * @param Kill $Kill the kill reference
     */
    protected function processItem($EsiItem, &$Kill)
    {
        // we will add this item with the given flag, even if it's not in our database
        // that way, when the database is updated, the item will display correctly
        $Item = Item::getByID($EsiItem->getItemTypeId());
        $location = $EsiItem->getFlag();
        $singleton = $EsiItem->getSingleton();

        if($EsiItem->getQuantityDropped() > 0) 
        {
           $Kill->addDroppedItem(new \DestroyedItem($Item, $EsiItem->getQuantityDropped(), $singleton, '', $location));
        }
        
        if($EsiItem->getQuantityDestroyed()) 
        {
            $Kill->addDestroyedItem(new \DestroyedItem($Item, $EsiItem->getQuantityDestroyed(), $singleton, '',  $location));
        }

        // process container-items
        // check, if $EsiItem is a root-level item, that may have items inside
        if(count($EsiItem->getItems()) > 0)
        {
            foreach($EsiItem->getItems() AS $ItemInContainer)
            {
                $this->processContainerItem($ItemInContainer, $Kill, $location);
            }
        }
    }
    
    
    /**
     * Accepts an ESI VictimItem representation (must not have child items)
     * and adds it to the given kill using the given parent inventory location
     * of destroyed items
     * @param \Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOkVictimItems $EsiItem
     * @param Kill $Kill the kill reference
     * @param int $parentItemLocation the item location of the parent item (for containers)
     */
    protected function processContainerItem($EsiItem, &$Kill, $parentItemLocation)
    {
        // we will add this item with the given flag, even if it's not in our database
        // that way, when the database is updated, the item will display correctly
        $Item = Item::getByID($EsiItem->getItemTypeId());
        $singleton = $EsiItem->getSingleton();

        if($EsiItem->getQuantityDropped() > 0) 
        {
           $Kill->addDroppedItem(new \DestroyedItem($Item, $EsiItem->getQuantityDropped(), $singleton, '', $parentItemLocation));
        }
        
        if($EsiItem->getQuantityDestroyed()) 
        {
            $Kill->addDestroyedItem(new \DestroyedItem($Item, $EsiItem->getQuantityDestroyed(), $singleton, '',  $parentItemLocation));
        }
    }
    
    /**
     * Gathers the IDs of the victim, all involved parties, their
     * corporations and alliances.
     * <br/>
     * The IDs will be globally unique across entity types. The output
     * can be used for bulk translating entity IDs to names.
     * 
     * @param GetKillmailsKillmailIdKillmailHashOk $EsiKill the ESI kill representations to get the entity IDs from
     * @return int[] an array of entity IDs
     */
    protected static function getEntityIds($EsiKill)
    {
        $entityIds = array();
        
        // victim IDs
        $Victim = $EsiKill->getVictim();
        $characterId = $Victim->getCharacterId();
        $corporationId = $Victim->getCorporationId();
        $allianceId = $Victim->getAllianceId();
        
        if(!is_null($characterId) && !in_array($characterId, $entityIds)) $entityIds[] = $characterId;
        if(!is_null($corporationId) && !in_array($corporationId, $entityIds)) $entityIds[] = $corporationId;
        if(!is_null($allianceId) && !in_array($allianceId, $entityIds)) $entityIds[] = $allianceId;
        
        // involved party IDs
        $InvolvedParties = $EsiKill->getAttackers();
        foreach($InvolvedParties as $InvolvedParty)
        {
            $characterId = $InvolvedParty->getCharacterId();
            $corporationId = $InvolvedParty->getCorporationId();
            $allianceId = $InvolvedParty->getAllianceId();
            $factionId = $InvolvedParty->getFactionId();

            if(!is_null($characterId) && !in_array($characterId, $entityIds)) $entityIds[] = $characterId;
            if(!is_null($corporationId) && !in_array($corporationId, $entityIds)) $entityIds[] = $corporationId;
            if(!is_null($allianceId) && !in_array($allianceId, $entityIds)) $entityIds[] = $allianceId;
        }
        
        
        return $entityIds;
    }
}
