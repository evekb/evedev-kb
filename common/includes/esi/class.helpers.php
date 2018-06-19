<?php

use EDK\ESI\ESI;
use EsiClient\UniverseApi;
use EsiClient\SearchApi;
use EsiClient\CharacterApi;
use EsiClient\CorporationApi;
use EsiClient\AllianceApi;
use Swagger\Client\Model\GetSearchOk;

use phpFastCache\CacheManager;

/**
 * A collection of helper methods for interacting with the ESI API
 * 
 */
class ESI_Helpers
{
    
    public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_DEFAULT = 60;
    public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MAX = 200;
    public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MIN = 10;
    
    /** the PHPFastCache instance */
    protected static $CACHE_INSTANCE;
    /** int the number of seconds to cache ID to name mappings */
    protected static $ID_NAME_MAPPING_CACHE_TIME = 120;
    /** string the cache key prefix for caching ID to name resolutions */
    const ID_NAME_MAPPING_CACHE_KEY_PREFIX = 'idNameMapping.';
    
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
     * First, tries to get ID-name-resolutions from the local cache.
     * Then uses the universe/names ESI endpoint, which is able to translate
     * IDs of various entity types to names.
     * This is possible, because all entity types use non-overlapping
     * ID ranges, making them globally unique.
     * <br/>
     * IDs that cannot be translated are missing in returned mapping.
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
        
        self::initCacheHandler();
        // create mapping
        $idNameMapping = array();
        // resolve as many IDs as possible using local cache
        self::resolveEntityIdsFromCache($entityIds, $idNameMapping);
        
        // wrap IDs into container
        // this used to work and is the intended way - seems broken!
        //$EsiUniverseIds = new PostUniverseNamesIds();
        //$EsiUniverseIds->setIds($entityIds);
        
        // resolve IDs to names
        $EsiUniverseNames = $UniverseApi->postUniverseNames($entityIds, $EdkEsi->getDataSource());
  
        foreach($EsiUniverseNames as $EsiUniverseName)
        {
            $idNameMapping[$EsiUniverseName->getId()] = $EsiUniverseName->getName();
            // store in cache
            self::putIdNameMappingIntoCache($EsiUniverseName->getId(), $EsiUniverseName->getName(), $EsiUniverseName->getCategory());
        }
        
        return $idNameMapping;
    }
    
    
    /**
     * Tries to look up the given entity IDs in the local cache to resolve to a name.
     * <br>
     * If an ID is found in the cache, it gets removed from the $enttiyIds array and the mapping
     * is stored in the $idNameMapping array.
     * 
     * @param int[] $entityIds an array of entity IDs to resolve
     * @param array $idNameMapping a key-value array for the output
     */
    protected static function resolveEntityIdsFromCache($entityIds, &$idNameMapping)
    {
        foreach($entityIds as $key => $entityId)
        {
            $cachedMapping = self::getIdNameMappingFromCache($entityId);
            if(!is_null($cachedMapping))
            {
                $idNameMapping[$cachedMapping['id']] = $cachedMapping['name'];
                unset($entityIds[$key]);
            }
        }
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
        $entitiesMatching = $SearchApi->getSearch(array($entityType), $entityName, $EdkEsi->getDataSource(), null, null, null, true);

        $getter = GetSearchOk::getters()[$entityType];
		
        if(!is_null($entitiesMatching->$getter()) && count($entitiesMatching->$getter()) == 1)
        {
            $method = $entitiesMatching->$getter();
            return reset($method);
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
    
    /**
     * initialize the phpFastCache handler for caching ID-to-name resolutions
     */
    protected static function initCacheHandler()
    {
        if(isset(self::$CACHE_INSTANCE))
        {
            return;
        }
        // use Memcached
        if(defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true) 
        {
            self::$CACHE_INSTANCE = CacheManager::getInstance('memcache', ['servers' => [
                [
                  'host' => \Config::get('cfg_memcache_server'),
                  'port' => \Config::get('cfg_memcache_port'),
                  // 'sasl_user' => false, // optional
                  // 'sasl_password' => false // optional
                ],
            ]]);
        } 

        // use Redis
        elseif(defined('DB_USE_REDIS') && DB_USE_REDIS == true) 
        {
            self::$CACHE_INSTANCE =  CacheManager::getInstance('redis', [
                'host' => \Config::get('cfg_redis_server'),
                'port' => \Config::get('cfg_redis_port'),
            ]);
        } 
        
        // fall back to file caching
        else 
        {
            self::$CACHE_INSTANCE =  CacheManager::getInstance('files', [
              "path" => getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR . DIRECTORY_SEPARATOR . 'esi',
            ]);
        }
    }
    
    
     /**
     * Tries to get the object for the given key from the cache handler.
     * 
     * @param string $id the key for the object to retrieve
     * @return array the cached array with the keys id, name and type
     */
    protected static function getIdNameMappingFromCache($id)
    {   
        $cacheKey = self::ID_NAME_MAPPING_CACHE_KEY_PREFIX.$id;
        if(isset(self::$CACHE_INSTANCE))
        {
            $CachedObject = self::$CACHE_INSTANCE->getItem($cacheKey);
            if(!is_null($CachedObject->get()))
            {
                return $CachedObject->get();
            }
            return null;
        }
    }
    
    /**
     * Stores the given data under the given key with the given
     * expiration date in the cache.
     * 
     * @param string $id the entity ID for which to store the mapping
     * @param string $name the entity name for the given ID
     * @param string $type the entity type, one of character, corporation, alliance, faction
     */
    protected static function putIdNameMappingIntoCache($id, $name, $type)
    {
        if(isset(self::$CACHE_INSTANCE))
        {
            $cacheKey = self::ID_NAME_MAPPING_CACHE_KEY_PREFIX.$id;
            
            $CachedObject = self::$CACHE_INSTANCE->getItem($cacheKey);
            $data = array('id' => $id, 'name' => $name, 'type' => $type);
            $CachedObject->set($data);
            $expirationTime = new DateTime();
            $expirationTime->add(new DateInterval('PT'.self::$ID_NAME_MAPPING_CACHE_TIME.'S'));
            $CachedObject->setExpirationDate($expirationTime);
            self::$CACHE_INSTANCE->save($CachedObject);
        }
    }
    
     /**
         * Executes a call against the ESI API to test connection.
      * 
         * @throws \Swagger\Client\ApiException
         */
        public static function testEsiApiConnection()
        {
            $API_TESTING_CHARACTER_ID = 800263173;
            
            // create EDK ESI client
            $EdkEsi = new ESI();
            $CharacterApi = new CharacterApi($EdkEsi);

            // only get the ESI character representation and the headers, we don't need the status code
            $CharacterApi->getCharactersCharacterIdWithHttpInfo($API_TESTING_CHARACTER_ID, $EdkEsi->getDataSource());
        }
        
        public static function isCurlSupported()
        {
            if(in_array  ('curl', get_loaded_extensions()))
            {
                // check for SSL support with cURL
                $version = curl_version();
                return ($version['features'] & CURL_VERSION_SSL) && in_array  ('openssl', get_loaded_extensions());
            }
            
            else
            {
                return false;
            }
        }
}
