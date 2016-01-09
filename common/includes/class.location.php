<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class Location extends Cacheable
{               
    /**
     * Whether the constructor has been executed.
     * @var boolean
     */
    private $executed = false;
    /**
     * The itemID for this system.
     * @var integer
     */
    private $id = 0;
    /**
     * The data for this location.
     * @var array
     */
    private $row = array();

    function __construct($id = 0)
    {
        $this->id = (int)$id;
    }

    function getID()
    {
        return $this->id;
    }

    function getExternalID()
    {
        return $this->id;
    }

    function getName()
    {
        $this->execQuery();
        return $this->row['itemName'];
    }

    function getSecurity($rounded = false)
    {
        $this->execQuery();
        $sec = $this->row['security'];

        if ($rounded)
        {
            if ($sec <= 0)
            {
                return number_format(0.0, 1);
            }
            else
            {
                return number_format(round($sec, 1), 1);
            }
        }
        
        return $sec;
    }
    
    /**
     * gets the radius of this location
     * @return float
     */
    function getRadius()
    {
        $this->execQuery();
        return (float)$this->row['radius'];
    }

    function getSolarSystemID()
    {
        $this->execQuery();
        return $this->row['sys_id'];
    }

    function getSolarSystemName()
    {
        $this->execQuery();
        return $this->row['sys_name'];
    }
    
    function getConstellationID()
    {
        $this->execQuery();
        return $this->row['con_id'];
    }

    function getConstellationName()
    {
        $this->execQuery();
        return $this->row['con_name'];
    }

    function getRegionID()
    {
        $this->execQuery();
        return $this->row['reg_id'];
    }

    function getRegionName()
    {
        $this->execQuery();
        return $this->row['reg_name'];
    }
    
    function getXCoordinate()
    {
        $this->execQuery();
        return (float) $this->row['x'];
    }
    
    function getYCoordinate()
    {
        $this->execQuery();
        return (float) $this->row['y'];
    }
    
    function getZCoordinate()
    {
        $this->execQuery();
        return (float) $this->row['z'];
    }

    private function execQuery()
    {
        if (!$this->executed)
        {
			if ($this->isCached()) {
				$cache = $this->getCache();
				$this->row = $cache->row;
				$this->executed = true;
			} else {
		        $qry = DBFactory::getDBQuery();
				$sql = "select *
						   FROM kb3_mapdenormalize mdn
                                                   INNER JOIN kb3_constellations con ON con.con_id = mdn.constellationID
                                                   INNER JOIN kb3_regions reg ON reg.reg_id = mdn.regionID
                                                   INNER JOIN kb3_systems sys ON sys.sys_id = mdn.solarSystemID
						   where mdn.itemID = ".$this->id;
				$qry->execute($sql);
				$this->row = $qry->getRow();
				$this->executed = true;
				$this->putCache();
			}
        }
    }

	/**
	 * Lookup a SolarSystem by name.
	 * 
	 * @param string $name
	 * @return Location|boolean
	 */
    static function lookup($name)
    {
			$qry = DBFactory::getDBQuery();
			$qry->execute("SELECT itemID FROM kb3_mapdenormalize "
			." WHERE itemID = '".$qry->escape($name)."'");

			if (!$qry->recordCount()) {
					return false;
			} else {
				$row = $qry->getRow();
				return Cacheable::factory('Location', (int)$row['itemID']);
			}
    }

    /**
     * Return a new object by ID. Will fetch from cache if enabled.
     *
     * @param mixed $id ID to fetch
     * @return Location
     */
    static function getByID($id)
    {
            return Cacheable::factory(get_class(), $id);
    }
    
    
    /**
     * Return the URL for an icon for this item.
     * @param integer $size
     * @param boolean $full Whether to return a full image tag.
     * @return string
     */
    public function getIcon($size, $full = true)
    {
            $this->execQuery();
            $img = imageURL::getURL('InventoryType', $this->row['typeID'], $size);

            if (!$full) 
            {
                    return $img;
            }
            
            return "<img src='$img' title=\"".$this->getName()."\" alt=\"".$this->getName()."\" style='width:{$size}px; height:{$size}px; border:0px' />";
    }
}
