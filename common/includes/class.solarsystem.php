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
class SolarSystem extends Cacheable
{
	/**
	 * Whether the constructor has been executed.
	 * @var boolean
	 */
	private $executed = false;
	/**
	 * The sys_id for this system.
	 * @var integer
	 */
	private $id = 0;
	/**
	 * The data for this system.
	 * @var array
	 */
	private $row = array();

    function SolarSystem($id = 0)
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
        return $this->row['sys_name'];
    }

    function getSecurity($rounded = false)
    {
        $this->execQuery();
        $sec = $this->row['sys_sec'];

        if ($rounded)
        {
            if ($sec <= 0)
                return number_format(0.0, 1);
            else
                return number_format(round($sec, 1), 1);
        }
        else return $sec;
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
						   from kb3_systems sys, kb3_constellations con,
						   kb3_regions reg
						   where sys.sys_id = ".$this->id."
						   and con.con_id = sys.sys_con_id
						   and reg.reg_id = con.con_reg_id";
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
	 * @return Solarsystem|boolean
	 */
    static function lookup($name)
    {
			$qry = DBFactory::getDBQuery();
			$qry->execute("SELECT sys_id FROM kb3_systems "
			." WHERE sys_name = '".$qry->escape($name)."'");

			if (!$qry->recordCount()) {
					return false;
			} else {
				$row = $qry->getRow();
				return Cacheable::factory('SolarSystem', (int)$row['sys_id']);
			}
    }

	/**
	 * Return a new object by ID. Will fetch from cache if enabled.
	 *
	 * @param mixed $id ID to fetch
	 * @return Alliance
	 */
	static function getByID($id)
	{
		return Cacheable::factory(get_class(), $id);
	}
}
