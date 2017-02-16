<?php

use EDK\ESI\ESI;
use EsiClient\UniverseApi;
use Swagger\Client\Model\GetSearchOk;

/**
 * A collection of helper methods for interacting with the ESI API
 * 
 */
class ESI_Helpers
{
    /**
     * Fetches the type with the given ID from ESI, adds it to the database
     * along with dogma attributes and effects
     * @param int $typeId
     * @return \Item
     */
    public static function fetchItem($typeId)
    {
        // create EDK ESI client
        $EdkEsi = new ESI();
        $UniverseApi = new UniverseApi($EdkEsi);
        
        try 
        {
            $typeInfo = $UniverseApi->getUniverseTypesTypeId($typeId, $EdkEsi->getDataSource());
        } 
        catch (ApiException $e) 
        {
            // fallback: Use generic item name
            // this database entry will be corrected with the next database update
            // store the item in the database
            $typeName = "Unknown Type ".$typeId;

            $query = new DBPreparedQuery();
            $query->prepare('INSERT INTO kb3_invtypes (`typeID`, `typeName`) VALUES (?, ?)');
            $types = 'is';
            $arr2 = array(&$types, &$typeId, &$typeName);
            $query->bind_params($arr2);
            $query->execute();

            return Item::lookup($typeName);
        }

        $typeName = $typeInfo->getTypeName();
        if($typeName == NULL)
        {
            $typeName = "Unknown Item ".$typeId;
        }
        
        $description = $typeInfo->getTypeDescription();
        $iconId = $typeInfo->getGraphicId();
        
        // this is no yet available via ESI
        $dogma = null;
        $mass = null;
        $volume = null;
        $capactiy = null;
        $portionSize = null;        
       
        // store the item in the database
        $query = new DBPreparedQuery();
        $query->prepare('INSERT INTO kb3_invtypes (`typeID`, `typeName`, `icon`, `description`, `mass`, `volume`, `capacity`, `portionSize` ) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $types = 'isssdddi';
        $arr2 = array(&$types, &$typeId, &$typeName, &$iconId, &$description, &$mass, &$volume, &$capacity, &$portionSize);
        $query->bind_params($arr2);
        $query->execute();

        if($dogma != NULL)
        {
            $query = DBFactory::getDBQuery();

            // store attributes in database
            if($dogma>attributes != NULL && is_array($dogma->attributes))
            {                        
                $attributeInserts = array();
                foreach($dogma->attributes AS $attributeInfo)
                {
                    $attributeInserts[] = '('.$typeId.', '.$query->escape($attributeInfo->attribute->id_str).',  '.$query->escape($attributeInfo->value).')';
                }

                if(count($attributeInserts) > 0) 
                {
                    $sql = 'REPLACE INTO kb3_dgmtypeattributes (`typeID`, `attributeID`, `value`) VALUES '. implode(", ", $attributeInserts);
                    $query->execute($sql);
                }
            }

            // store effects in database
            if($dogma->effects != NULL && is_array($dogma->effects))
            {                        
                $effectInserts = array();
                foreach($dogma->effects AS $effectInfo)
                {
                    $effectInserts[] = "(".$typeId.", ".$query->escape($effectInfo->effect->id_str).",  ".(int) $effectInfo->isDefault.")";
                }

                if(count($effectInserts) > 0) 
                {
                    $sql = 'REPLACE INTO kb3_dgmtypeeffects (`typeID`, `effectID`, `isDefault`) VALUES '. implode(", ", $effectInserts);
                    $query->execute($sql);
                }
            }
        }
        return self::lookup($typeName);
    } 
    
    
    /**
     * Fetches the kill with the given killID and hash from ESI
     * @param int $killId the external ID of the kill to fetch
     * @param string $hash the has of the kill to fetch
     * @return \Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOk
     * 
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public static function fetchKill($killId, $hash)
    {
        $EdkEsi = new ESI();
        $KillmailsApi = new \EsiClient\KillmailsApi($EdkEsi);
        return $KillmailsApi->getKillmailsKillmailIdKillmailHash($killId, $hash);
    }
    
    
    /**
     * Fetches the pilot with the given external ID from ESI and adds
     * him to the database.
     * 
     * @param int $externalId the external ID of the pilot to fetch
     * @return \Pilot
     * 
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    private function fetchPilot($externalId)
    {
        if (!$this->externalid) {
            return false;
        }
            $apiInfo = new API_CharacterInfo();
            $apiInfo->setID($this->externalid);
            $result = $apiInfo->fetchXML();

            if($result == "") {
                $data = $apiInfo->getData();
                if(isset($data['alliance']) && isset($data['allianceID']))
                                {
                                    $this->alliance = Alliance::add($data['alliance'], $data['allianceID']);
                                }
                                else {
                                    $this->alliance = Alliance::add('None');
                                }
                                
                $this->corp = Corporation::add($data['corporation'],
                    $this->alliance, $apiInfo->getCurrentTime(),
                    $data['corporationID']);
                $this->name = $data['characterName'];
                $Pilot = Pilot::add($data['characterName'], $this->corp, $apiInfo->getCurrentTime(), $data['characterID']);
                                $this->id = $Pilot->getID();
            } else {
                return false;
            }
    }
    
    /**
     * Formats the given DateTime to an EDK compatible timestamp string
     *
     * @param \DateTime $DateTime the DateTime to format
     * @return string the timestamp in EDK timestamp format (Y-m-d H:i:s);
     */
    public static function formatDateTime($DateTime)
    {
        return $DateTime->format('Y-m-d H:i:s');
    }
    
    /**
     * Formats the given RFC7231 timestamp as EDK compatible.
     *
     * @param string $rfc7231Timestamp the ISO formatted timestamp string
     * @return string the timestamp in EDK timestamp format (%Y-%m-%d %H:%M:%S);
     */
    public static function formatRFC7231Timestamp($rfc7231Timestamp)
    {
        $DateTime = \DateTime::createFromFormat('D, d M Y H:i:s O+', $rfc7231Timestamp, new DateTimeZone('UTC'));
        return $DateTime->format('Y-m-d H:m:i');
    }
    
    /**
     * Accepts an array if entity IDs to resolve to entity names.
     * <br/>
     * Uses the universe/names ESI endpoint, which is able to translate
     * IDs of various entity types to names.
     * This is possible, because all entity types use non-overlapping
     * ID ranges, making them globally unique.
     * <br/>
     * IDs that cannot be translated are missing in returned mapping-
     * 
     * @param int[] $entityIds an array of entity IDs
     * @return array an indexed array, using the input IDs as index
     * 
     * @throws \Swagger\Client\ApiException on ESI communication error
     */
    public static function resolveEntityIds($entityIds)
    {
        // create ESI client
        $EdkEsi = new ESI();
        $UniverseApi = new UniverseApi($EdkEsi);
        
        // wrap IDs into container
        // this used to work and is the intended way - seems broken!
        //$EsiUniverseIds = new PostUniverseNamesIds();
        //$EsiUniverseIds->setIds($entityIds);
        
        // resolve IDs to names
        $EsiUniverseNames = $UniverseApi->postUniverseNames($entityIds, $EdkEsi->getDataSource());
        
        // create mapping
        $idNameMapping = array();
        foreach($EsiUniverseNames as $EsiUniverseName)
        {
            $idNameMapping[$EsiUniverseName->getId()] = $EsiUniverseName->getName();
        }
        
        return $idNameMapping;
    }
    
    /**
     * Accepts the name and type of an entity to find using ESI's
     * search endpoint. This will perform an exact search for the given
     * entity type and name and only return an ID, if an exact match was found.
     * 
     * @param string $entityName the name of the entity
     * @param string $entityType the type of the entity, allowed values: agent, 
     *                           alliance, character, constellation, corporation, 
     *                           faction, inventorytype, region, solarsystem, station, 
     *                           wormhole
     * @return int the external ID for the given entity or <code>null</code>
     * @throws \Swagger\Client\ApiException on ESI communication error
     */
    public static function getExternalIdForEntity($entityName, $entityType)
    {
        // search for the corp in order to get the external ID
        $EdkEsi = new ESI();
        $SearchApi = new SearchApi($EdkEsi);
        $entitiesMatching = $SearchApi->getSearch($entityName, array($entityType), null, true, $EdkEsi->getDataSource());

        if(count($entitiesMatching) == 1)
        {
            $getter = GetSearchOk::getters()[$entityType];
            return $entitiesMatching->$getter();
        }
        
        return null;
    }
}
