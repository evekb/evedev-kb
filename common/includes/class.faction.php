<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Represents an NPC faction
 * @package EDK
 */
class Faction extends Cacheable
{               
    /**
     * Whether the constructor has been executed.
     * @var boolean
     */
    protected $executed = false;
    /**
     * The factionID for this system.
     * @var integer
     */
    protected $id = 0;
    protected $factionName;
    protected $description;
    protected $raceIDs;
    protected $solarSystemID;
    protected $corporationID;
    protected $sizeFactor;
    protected $stationCount;
    protected $stationSystemCount;
    protected $militiaCorporationID;
    protected $iconID;

    function __construct($id = 0)
    {
        $this->id = (int)$id;
    }

    function getID()
    {
        return $this->id;
    }

    function getName() 
    {
        $this->execQuery();
        return $this->factionName;
    }

    function getDescription() 
    {
        $this->execQuery();
        return $this->description;
    }

    function getRaceIDs() 
    {
        $this->execQuery();
        return $this->raceIDs;
    }

    function getSolarSystemID() 
    {
        $this->execQuery();
        return $this->solarSystemID;
    }

    function getCorporationID() 
    {
        $this->execQuery();
        return $this->corporationID;
    }

    function getSizeFactor() 
    {
        $this->execQuery();
        return $this->sizeFactor;
    }

    function getStationCount() 
    {
        $this->execQuery();
        return $this->stationCount;
    }

    function getStationSystemCount() 
    {
        $this->execQuery();
        return $this->stationSystemCount;
    }

    function getMilitiaCorporationID() 
    {
        $this->execQuery();
        return $this->militiaCorporationID;
    }

    function getIconID() 
    {
        $this->execQuery();
        return $this->iconID;
    }

    private function execQuery()
    {
        if (!$this->executed)
        {
            if ($this->isCached()) 
            {
                $cache = $this->getCache();
                $this->id = $cache->getID();
                $this->factionName = $cache->factionName;
                $this->description = $cache->description;
                $this->raceIDs = $cache->raceIDs;
                $this->solarSystemID = $cache->solarSystemID;
                $this->corporationID = $cache->corporationID;
                $this->sizeFactor = $cache->sizeFactor;
                $this->stationCount = $cache->stationCount;
                $this->stationSystemCount = $cache->stationSystemCount;
                $this->militiaCorporationID = $cache->militiaCorporationID;
                $this->iconID = $cache->iconID;
                $this->executed = true;
            } 
            
            else 
            {
                $getFaction = new DBPreparedQuery();
                $getFaction->prepare('
                    SELECT 
                            factionID, factionName, description, raceIDs, solarSystemID, corporationID, sizeFactor, stationCount, stationSystemCount, militiaCorporationID, iconID
                        FROM kb3_factions 
                            WHERE factionID = ?');
                $arr = array(
                    &$this->id,
                    &$this->factionName,
                    &$this->description,
                    &$this->raceIDs,
                    &$this->solarSystemID,
                    &$this->corporationID,
                    &$this->sizeFactor,
                    &$this->stationCount,
                    &$this->stationSystemCount,
                    &$this->militiaCorporationID,
                    &$this->iconID,
                );
                $getFaction->bind_results($arr);
                $types = 'i';
                $arr2 = array(&$types, &$this->id);
                $getFaction->bind_params($arr2);
                $getFaction->execute();

                if($getFaction->recordCount())
                {
                    $getFaction->fetch();
                    $this->executed = true;
                    $this->putCache();
                }
            }
        }
    }

    /**
     * Lookup a faction by name.
     * 
     * @param string $name
     * @return mixed the faction or false if not found
     */
    static function lookup($name)
    {
        $getFactionByName = new DBPreparedQuery();
        $getFactionByName->prepare('
            SELECT 
                    factionID
                FROM kb3_factions 
                    WHERE factionName = ?');
        $factionId = null;
        $arr = array(&$factionId);
        $getFactionByName->bind_results($arr);
        $types = 's';
        $arr2 = array(&$types, &$name);
        $getFactionByName->bind_params($arr2);
        $getFactionByName->execute();

        if($getFactionByName->recordCount())
        {
            return Cacheable::factory('Faction', $factionId);
        }
        
        return false;
    }

    /**
     * Return a new object by ID. Will fetch from cache if enabled.
     *
     * @param mixed $id ID to fetch
     * @return Faction
     */
    static function getByID($id)
    {
        return Cacheable::factory(get_class(), $id);
    }
    
    /**
     * Checks whether the given ID belongs to a known faction.
     * 
     * @param int $id the ID to check
     * @return boolean <code>true</code> if the ID belongs to a known faction, otherwise <code>false</code>
     */
    public static function isFaction($id)
    {
        $Faction = Faction::getByID($id);
        return !is_null($Faction->getName());
    }
}
