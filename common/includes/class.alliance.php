<?php
/*
 * $Id$
 */

//! Creates a new Alliance or fetches an existing one from the database.
class Alliance
{
	private $id = false;
	private $externalid = false;
	private $executed = false;
	private $name = null;

    //! Create a new Alliance object from the given $id.
    
    /*!
     * \param $id The alliance ID.
	 * \param $external true/false. Whether the given id is internal or external
     */
    function Alliance($id = 0, $external = false)
	{
		if($external) $this->externalid = intval($id);
		else $this->id = intval($id);
    }

	//! Return the alliance CCP ID.
	function getExternalID()
	{
		if($this->externalid) return $this->externalid;
		$this->execQuery();
		if($this->externalid) return $this->externalid;

		$allname = str_replace(" ", "%20", $this->getName() );
		require_once("common/includes/class.eveapi.php");
		$myID = new API_NametoID();
		$myID->setNames($allname);
		$myID->fetchXML();
		$myNames = $myID->getNameData();
		if($this->setExternalID($myNames[0]['characterID']))
			return $this->externalid;
		else return 0;
	}

	//! Return the alliance ID.
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
    //! Return the alliance name stripped of all non-ASCII non-alphanumeric characters.
    function getUnique()
    {
		if(is_null($this->name)) $this->execQuery();
        return preg_replace('/[^a-zA-Z0-9]/', '', $this->name);
    }
    //! Return the alliance name.
    function getName()
    {
        if(is_null($this->name)) $this->execQuery();
        return $this->name;
    }
    //! Fetch the alliance details from the database using the id given on construction.

	/*!
	 * If no record is found but we have an external ID then the result
	 * will be fetched from CCP.
	 */
    function execQuery()
    {
        if (!$this->executed)
        {
			$qry = DBFactory::getDBQuery();;
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
			}

			$this->executed = true;
        }
    }
    //! Add a new alliance to the database or update the details of an existing one.
    
    /*!
     * \param $name An alliance name for this object.
     */
    function add($name, $externalid = false)
    {
        $qry = DBFactory::getDBQuery();
		$name = $qry->escape($name);
        $qry->execute("select * from kb3_alliances where all_name = '".$name."'");

        if ($qry->recordCount() == 0)
        {
			$externalid = intval($externalid);
			if(!$externalid)
			{
				$allname = str_replace(" ", "%20", $name );
				require_once("common/includes/class.eveapi.php");
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
			$this->name = $name;
			$this->externalid = intval($row['all_external_id']);
        }
    }
	//! Set the CCP external ID for this alliance.
	function setExternalID($externalid)
	{
		$externalid = intval($externalid);
		if($externalid && $this->id)
		{
			$this->execQuery();
			$qry = DBFactory::getDBQuery();;
			if($qry->execute("UPDATE kb3_alliances SET all_external_id = ".$externalid." WHERE all_id = ".$this->id))
			{
				$this->externalid = $externalid;
				return true;
			}
		}
		return false;
	}
	//! Check if this is a Faction.
	function isFaction()
	{
		$factions = array("Amarr Empire", "Minmatar Republic", "Caldari State", "Gallente Federation");
		return (in_array($this->getName(), $factions));
	}

	function getFactionID()
	{
		if(!$this->isFaction()) return 0;
		return getExternalID();
	}
	//! Return the URL for the alliance's portrait.

    /*!
     * \param $size The desired portrait size.
	 * \return URL for a portrait.
     */
	function getPortraitURL($size = 128)
	{
		if ($size == 128 && file_exists("img/alliances/".$this->getUnique().".png"))
		{
			return "img/alliances/".$this->getUnique().".png";
		}
		else
		{
			if(CacheHandler::exists($this->getUnique()."_$size.png", 'img/alliances'))
				return CacheHandler::getExternal($this->getUnique()."_$size.png", 'img/alliances');
			else if($this->getExternalID())
				return '?a=thumb&amp;type=alliance&amp;id='.$this->getExternalID().'&amp;size='.$size;
			return '?a=thumb&amp;type=alliance&amp;id='.$this->getUnique().'&amp;size='.$size;
		}
	}

	/*!
	 * Fetch the alliance name from CCP using the stored external ID.
	 */
	private function fetchAlliance()
	{
		if(is_null($this->externalid)) return false;

		require_once("common/includes/class.eveapi.php");
		$myID = new API_IDtoName();
		$myID->setIDs($this->externalid);
		$myID->fetchXML();
		$myNames = $myID->getIDData();

		$this->add($myNames[0]['name'], intval($myNames[0]['characterID']));
	}
}
