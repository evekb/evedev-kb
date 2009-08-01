<?php
//! Creates a new Alliance or fetches an existing one from the database.
class Alliance
{
    //! Create a new Alliance object from the given $id.
    
    /*!
     * \param $id The alliance ID.
     */
    function Alliance($id = null)
    {
        $this->id_ = $id;
        $this->executed_ = false;
    }

	//! Return the alliance CCP ID.
	function getExternalID()
	{
		if(!$this->externalid_) $this->execQuery();
		return $this->externalid_;
	}

	//! Return the alliance ID.
	function getID()
    {
        return $this->id_;
    }
    //! Return the alliance name stripped of all non-ASCII non-alphanumeric characters.
    function getUnique()
    {
		if(!$this->name_) $this->execQuery();
        return preg_replace('/[^a-zA-Z0-9]/', '', $this->name_);
    }
    //! Return the alliance name.
    function getName()
    {
        if(!$this->name_) $this->execQuery();
        return $this->name_;
    }
    //! Fetch the alliance details from the database using the id given on construction.
    function execQuery()
    {
        if (!$this->executed_)
        {
			$qry = new DBQuery();
            $qry->execute("select * from kb3_alliances where all_id = " . $this->id_);
            $row = $qry->getRow();
			$this->name_ = $row['all_name'];
			$this->externalid_ = intval($row['all_external_id']);
			$this->executed_ = true;
        }
    }
    //! Add a new alliance to the database or update the details of an existing one.
    
    /*!
     * \param $name An alliance name for this object.
     */
    function add($name, $externalid = false)
    {
        $qry = new DBQuery();
        $qry->execute("select * from kb3_alliances where all_name = '".slashfix($name)."'");

        if ($qry->recordCount() == 0)
        {
			if(intval($externalid))
				$qry->execute("insert into kb3_alliances ".
					"(all_id, all_name, all_external_id) values ".
					"(null, '".slashfix($name)."', ".intval($externalid).")");
            $qry->execute("insert into kb3_alliances ".
				"(all_id, all_name) values ".
				"(null, '".slashfix($name)."')");
            $this->id_ = $qry->getInsertID();
        }
        else
        {
            $row = $qry->getRow();
            $this->id_ = $row['all_id'];
			$this->name_ = slashfix($name);
			$this->externalid_ = intval($row['all_external_id']);
        }
    }
	//! Set the CCP external ID for this alliance.
	function setExternalID($externalid)
	{
		$externalid = intval($externalid);
		if($externalid && $this->id_)
		{
			$this->execQuery();
			$qry = new DBQuery();
			if($qry->execute("UPDATE kb3_alliances SET all_external_id = ".$externalid." where all_id = ".$this->id_))
			{
				$this->externalid_ = $externalid;
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
}
?>