<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 *
 * @package EDK
 */


/**
 * Creates a new Alliance or fetches an existing one from the database.
 */
class Alliance
{
	static private $cache = array();

	private $id = false;
	private $externalid = false;
	private $executed = false;
	private $name = null;

    /**
     * Create a new Alliance object from the given $id.
	 *
	 * @param integer $id The alliance ID.
	 * @param boolean $external true/false. Whether the given id is internal or external
	 *
     */
    function Alliance($id = 0, $external = false)
	{
		if($external)
			$this->externalid = intval($id);
		else
			$this->id = intval($id);
    }

	/**
	 * Return the alliance CCP ID.
	 *
	 * @return integer
	 */
	function getExternalID()
	{
		if($this->externalid)
			return $this->externalid;

		$this->execQuery();

		if($this->externalid)
			return $this->externalid;

		$myID = new API_NametoID();
		$myID->setNames($this->getName());
		$myID->fetchXML();
		$myNames = $myID->getNameData();
		if($this->setExternalID($myNames[0]['characterID']))
			return $this->externalid;
		else return 0;
	}

	/**
	 * Return the alliance ID.
	 *
	 * @return integer
	 */
	function getID()
    {
		if($this->id) return $this->id;
		elseif($this->externalid)
		{
			$this->execQuery();
			return $this->id;
		}
		else return 0;
    }
    /**
     * Return the alliance name stripped of all non-ASCII non-alphanumeric characters.
	 *
	 * @return string
	 */
    function getUnique()
    {
		if(is_null($this->name)) $this->execQuery();
        return preg_replace('/[^a-zA-Z0-9]/', '', $this->name);
    }
    /**
     * Return the alliance name.
	 *
	 * @return string
	 */
    function getName()
    {
        if(is_null($this->name)) $this->execQuery();
        return $this->name;
    }
    /**
     * Fetch the alliance details from the database using the id given on construction.
	 *
	 * If no record is found but we have an external ID then the result
	 * will be fetched from CCP.
	 */
    function execQuery()
    {
        if (!$this->executed)
        {
			if( isset( self::$cache[ (int)$this->id ] ) ) {
				$this->id = self::$cache[ (int)$this->id ]->id;
				$this->externalid = self::$cache[ (int)$this->id ]->externalid;
				$this->executed = self::$cache[ (int)$this->id ]->executed;
				$this->name = self::$cache[ (int)$this->id ]->name;
			} else {
				$qry = DBFactory::getDBQuery();
				$sql = "select * from kb3_alliances where ";
				if($this->externalid) $sql .= "all_external_id = ".$this->externalid;
				else $sql .= "all_id = ".$this->id;
				$qry->execute($sql);
				if($this->externalid && !$qry->recordCount()) $this->fetchAlliance();
				else if($qry->recordCount())
				{
					$row = $qry->getRow();
					$this->id = $row['all_id'];
					$this->name = $row['all_name'];
					$this->externalid = intval($row['all_external_id']);

					self::$cache[ (int)$this->id ] = $this;
				}
			}

			$this->executed = true;
        }
    }
    /**
     * Add a new alliance to the database or update the details of an existing one.
	 *
	 * @param string $name An alliance name for this object.
     */
    function add($name, $externalid = false)
    {
        $qry = DBFactory::getDBQuery();
		$name = $qry->escape(stripslashes($name));
        $qry->execute("select * from kb3_alliances where all_name = '".$name."'");

        if ($qry->recordCount() == 0)
        {
			$externalid = intval($externalid);
			if(!$externalid)
			{
				$allname = str_replace(" ", "%20", $name );
				$myID = new API_NametoID();
				$myID->setNames($allname);
				$myID->fetchXML();
				$myNames = $myID->getNameData();
				$externalid = intval($myNames[0]['characterID']);
			}
			// If we have an external id then check it isn't already in use
			// If we find it then update the old alliance with the new name
			// then return.
			if($externalid)
			{
				$qry->execute("SELECT * FROM kb3_alliances WHERE all_external_id = ".$externalid);
				if ($qry->recordCount() > 0)
				{
					$row = $qry->getRow();
					$qry->execute("UPDATE kb3_alliances SET all_name = '".$name."' WHERE all_external_id = ".$externalid);

					$this->id = $row['all_id'];
					$this->name = $name;
					$this->externalid = $row['all_external_id'];

					self::$cache[ (int)$this->id ] = $this;

					return $this->id;
				}
				$qry->execute("insert into kb3_alliances ".
					"(all_id, all_name, all_external_id) values ".
					"(null, '".$name."', ".$externalid.")");
			}
            else $qry->execute("insert into kb3_alliances ".
				"(all_id, all_name) values ".
				"(null, '".$name."')");
            $this->id = $qry->getInsertID();
        }
        else
        {
            $row = $qry->getRow();
            $this->id = $row['all_id'];
			$this->name = $row['all_name'];
			$this->externalid = intval($row['all_external_id']);
        }
		self::$cache[ (int)$this->id ] = $this;
    }
	/**
	 * Set the CCP external ID for this alliance.
	 *
	 * @param integer $externalid
	 * @param boolean $update If true and the ID exists, update the existing entry.
	 *
	 * @return integer
	 */
	function setExternalID($externalid, $update = true)
	{
		$externalid = intval($externalid);
		if($externalid && $this->id)
		{
			$this->execQuery();
			$qry = DBFactory::getDBQuery();
			// Check if an alliance already exists with this external id and merge the two if so
			// i.e. the name has changed.
			$qry->execute("SELECT * FROM kb3_alliances WHERE all_external_id = ".$externalid);
			if ($qry->recordCount() > 0)
			{
				if(!$update) return false;

				$row = $qry->getRow();
				// The already existing alliance is this one.
				if($row['all_id'] == $this->id) return $this->id;

				$newid = $row['all_id'];
				$qry->execute("UPDATE kb3_corps SET crp_all_id = $newid WHERE crp_all_id = ".$this->id);
				$qry->execute("UPDATE kb3_inv_detail SET ind_all_id = $newid WHERE ind_all_id = ".$this->id);
				$qry->execute("UPDATE kb3_inv_all SET ina_all_id = $newid WHERE ina_all_id = ".$this->id);
				$qry->execute("UPDATE kb3_kills SET kll_all_id = $newid WHERE kll_all_id = ".$this->id);
				$qry->execute("DELETE FROM kb3_alliances WHERE all_id = ".$this->id);
				$qry->execute("UPDATE kb3_alliances SET all_name = '".$qry->escape($this->name)."' WHERE all_external_id = ".$externalid);

				$this->id = $newid;
				$this->externalid = $externalid;
				self::$cache[ (int)$this->id ] = $this;
				return $this->id;
			}
			else if($qry->execute("UPDATE kb3_alliances SET all_external_id = ".$externalid." WHERE all_id = ".$this->id))
			{
				$this->externalid = $externalid;
				self::$cache[ (int)$this->id ] = $this;
				return $this->id;
			}
		}
		return false;
	}
	/**
	 * Check if this is a Faction.
	 *
	 * @return boolean
	 */
	function isFaction()
	{
		$factions = array("Amarr Empire", "Minmatar Republic", "Caldari State", "Gallente Federation");
		return (in_array($this->getName(), $factions));
	}

	/**
	 * Return the faction ID.
	 *
	 * @return integer The faction ID or 0 if this is not a faction.
	 */
	function getFactionID()
	{
		if(!$this->isFaction()) return 0;
		return $this->getExternalID();
	}
	/**
	 * Return the URL for the alliance's portrait.
	 *
     * @param integer $size The desired portrait size.
	 * @return string URL for a portrait.
     */
	function getPortraitURL($size = 128)
	{
		if(file_exists("img/alliances/".$this->getUnique().".png"))
		{
			if ($size == 128)
				return IMG_HOST."/img/alliances/".$this->getUnique().".png";
			else if(CacheHandler::exists($this->getUnique()."_$size.png", 'img'))
				return KB_HOST."/".CacheHandler::getExternal($this->getUnique()."_$size.png", 'img');
			return '?a=thumb&amp;type=alliance&amp;id='.$this->getUnique().'&amp;size='.$size;
		}
		return imageURL::getURL('Alliance', $this->externalid, $size);
	}

	/**
	 * Fetch the alliance name from CCP using the stored external ID.
	 */
	private function fetchAlliance()
	{
		if(!$this->externalid) return false;

		$myID = new API_IDtoName();
		$myID->setIDs($this->externalid);
		$myID->fetchXML();
		$myNames = $myID->getIDData();

		$this->add($myNames[0]['name'], intval($myNames[0]['characterID']));
	}
}
