<?php

use EDK\ESI\ESI;
use EsiClient\UniverseApi;
use EsiClient\SearchApi;
use Swagger\Client\Model\GetSearchOk;

/**
 * A collection of helper methods for interacting with the ESI API
 * 
 */
class ESI_Helpers
{
    
    public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_DEFAULT = 60;
    public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MAX = 200;
    public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MIN = 10;
    
    public static function getTypeNameById($id, $update = false)
    {
        $id = intval($id);
        $sql = 'select inv.typeName from kb3_invtypes inv where inv.typeID = ' . $id;

        $qry = DBFactory::getDBQuery();
        $qry->execute($sql);
        if($qry->recordCount())
        {
            $row = $qry->getRow();

            return $row['typeName'];
        }
        else
        {
            $Item = Item::fetchItem($id);
            return $Item->getName();
        }
    }
    
    
    /**
     * Get the name of a moon by its ID
     * 
     * @param int $id the moon ID
     * @return mixed the name of the moon or false if not found
     */
    public static function getMoonName($id)
    {
        if ($id != 0)
        {
            $qry = DBFactory::getDBQuery();
            $sql = 'select itemName FROM kb3_moons WHERE itemID = '.$id;

            $qry->execute($sql);
            $row = $qry->getRow();

            return $row['itemName'];
        } else {
            return false;
        }
    }
        
        
    /**
     * Get the ID of a moon by its name
     * 
     * @param string $moonName the name of the moon
     * @return mixed the moon's ID or false if not found
     */
    public static function getMoonID($moonName)
    {
        if (!is_null($moonName))
        {
            $qry = DBFactory::getDBQuery();
            $sql = "select itemID FROM kb3_moons WHERE itemName = '".$qry->escape($moonName)."'";

            $qry->execute($sql);
            $row = $qry->getRow();

            return $row['itemID'];
        } else {
            return false;
        }
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
        
        return $KillmailsApi->getKillmailsKillmailIdKillmailHash($hash, $killId);
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
        $entitiesMatching = $SearchApi->getSearch(array($entityType), $entityName, $EdkEsi->getDataSource(), null, true);

        $getter = GetSearchOk::getters()[$entityType];
		
        if(!is_null($entitiesMatching->$getter()) && count($entitiesMatching->$getter()) == 1)
        {
            return reset($entitiesMatching->$getter());
        }
        
        return null;
    }
    
    /**
     * sets the maximum number of kills to process per run (if not already set),
     * based on the time limit set in the PHP configuration
     */
    public static function autoSetMaxNumberOfKillsToProcess()
    {
        // has the maximum number of kills to process already been set?
        if(is_numeric(config::get('maxNumberOfKillsPerRun')))
        {
            return;
        }

        $timeLimit = ini_get('max_execution_time');
        $maxNumberOfKillsPerRun = self::$MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_DEFAULT;

        if($timeLimit !== FALSE)
        {
            // on average, we can fetch 2 kills per second (due to CREST response time limitations)
            $maxNumberOfKillsPerRun = min(array(floor($timeLimit * 0.8), self::$MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MAX));
        }

        config::set('maxNumberOfKillsPerRun', $maxNumberOfKillsPerRun);
    }
}
