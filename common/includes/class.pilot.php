<?php
require_once('class.corp.php');
require_once('class.item.php');
require_once('class.thumb.php');

//! Creates a new Pilot or fetches an existing one from the database.
class Pilot
{
    //! Create a new Pilot object from the given $id.

    /*!
     * \param $id The pilot ID.
	 * \param $externalIDFlag whether the id is external or internal
     */
    function Pilot($id = 0, $externalIDFlag = false)
    {
        if($externalIDFlag) $this->externalid_=intval($id);
        else $this->id_ = intval($id);
    }
	//! Return the alliance ID.
    function getID()
    {
            if($this->id_) return $this->id_;
            elseif($this->externalid_)
            {
                    $this->execQuery();
                    return $this->id_;
            }
            else return 0;
    }
	//! Return the pilot's CCP ID.
    function getExternalID()
    {
		if($this->externalid_) return $this->externalid_;
		elseif($this->id_)
		{
			$this->execQuery();
			if($this->externalid_) return $this->externalid_;
			require_once("class.api.php");
			$api = new Api();
			$id = $api->getCharId($this->getName());
			if ($id > 0) $this->setCharacterID($id);
			return $this->externalid_;
		}
		else return 0;
    }
    //! Return the pilot name.
    function getName()
    {
        if(!$this->name_) $this->execQuery();
        $pos = strpos($this->name_, "#");
        if ($pos === false)
        {
            return $this->name_;
        }
        else
        {
            $name = explode("#", $this->name_);
            $item = new Item($name[2]);
            return $item->getName();
        }
    }
    //! Return the URL for the pilot's portrait.

    /*!
     * \param $size The desired portrait size.
	 * \return URL for a portrait.
     */
    function getPortraitURL($size = 64)
    {
		if(!$this->externalid_) $this->execQuery();
        if (!$this->externalid_)
        {
        	return '?a=thumb&amp;id='.$this->id_.'&amp;size='.$size.'&amp;int=1';
        }
        else
        {
			if( file_exists('cache/portraits/'.$this->externalid_.'_'.$size.'.jpg'))
				return 'cache/portraits/'.$this->externalid_.'_'.$size.'.jpg';
			else return '?a=thumb&amp;id='.$this->externalid_.'&amp;size='.$size;
        }
    }
    //! Fetch the pilot details from the database using the id given on construction.
    function execQuery()
    {
        if (!$this->qry_)
        {
			if(!$this->externalid_ && !$this->id_)
			{
					$this->valid_ = false;
					return;
			}
            $this->qry_ = new DBQuery();
            $this->sql_ = 'select * from kb3_pilots plt, kb3_corps crp, kb3_alliances ali
            	  	       where crp.crp_id = plt.plt_crp_id
            		       and ali.all_id = crp.crp_all_id ';
            if($this->externalid_) $this->sql_ .= 'and plt.plt_externalid = '.$this->externalid_;
            else $this->sql_ .= 'and plt.plt_id = '.$this->id_;
            $this->qry_->execute($this->sql_) or die($this->qry_->getErrorMsg());
            //$this->row_ = $this->qry_->getRow();
            $row = $this->qry_->getRow();
            if (!$row)
                $this->valid_ = false;
            else
            {
                $this->valid_ = true;
                $this->id_ = $row['plt_id'];
                $this->name_ = $row['plt_name'];
                $this->corp_ = $row['plt_crp_id'];
                $this->externalid_ = intval($row['plt_externalid']);

            }
        }
    }
    //! Return the corporation this pilot is a member of.

    /*!
	 * \return Corporation object
     */
    function getCorp()
    {
        if(!$this->corp_) $this->execQuery();
        return new Corporation($this->corp_);
    }
	//! Check if the id given on construction is valid.

    /*!
	 * \return boolean - true for exists.
     */
    function exists()
    {
        $this->execQuery();
        return $this->valid_;
    }
    //! Add a new pilot to the database or update the details of an existing one.

    /*!
     * \param $name Pilot name
	 * \param $corp Corporation object for this pilot's corporation
	 * \param $timestamp time this pilot's corp was updated
	 * \param $externalID CCP external id
     */
    function add($name, $corp, $timestamp, $externalID = 0)
    {
		// Check if pilot exists with a non-cached query.
        $qry = new DBQuery(true);
		// Insert or update a pilot with a cached query to update cache.
		$qryI = new DBQuery();
        $qry->execute("select *
                        from kb3_pilots
                       where plt_name = '".slashfix($name)."'");

        if ($qry->recordCount() == 0)
        {
			$externalID = intval($externalID);
			// If no external id is given then look it up.
			if(!$externalID)
			{
				$pilotname = str_replace(" ", "%20", $name );
				require_once("common/includes/class.eveapi.php");
				$myID = new API_NametoID();
				$myID->setNames($pilotname);
				$myID->fetchXML();
				$myNames = $myID->getNameData();
				$externalID = intval($myNames[0]['characterID']);
			}
			// If we have an external id then check it isn't already in use.
			// If we find it then update the old corp with the new name and
			// return.
			if($externalID)
			{
				$qry->execute("SELECT * FROM kb3_pilots WHERE plt_externalid = ".$externalID);
				if ($qry->recordCount() > 0)
				{
					$row = $qry->getRow();
					$qryI->execute("UPDATE kb3_pilots SET plt_name = '".slashfix($name)."' WHERE plt_externalid = ".$externalID);

					$this->id_ = $row['plt_id'];
					$this->name_ = slashfix($name);
					$this->externalid_ = $row['plt_externalid'];
					$this->corp_ = $row['plt_crp_id'];

					// Now check if the corp needs to be updated.
					if ($row['plt_crp_id'] != $corp->getID() && $this->isUpdatable($timestamp))
					{
						$qryI->execute("update kb3_pilots
									 set plt_crp_id = ".$corp->getID().",
										 plt_updated = date_format( '".$timestamp."', '%Y.%m.%d %H:%i:%s') WHERE plt_externalid = ".$externalID);
					}
					return $this->id_;
				}
			}
            $qryI->execute("insert into kb3_pilots (plt_id, plt_name, plt_crp_id, plt_externalid, plt_updated) values ( null,
                                                        '".slashfix($name)."',
                                                        ".$corp->getID().",
                                                        ".$externalID.",
                                                        date_format( '".$timestamp."', '%Y.%m.%d %H:%i:%s'))");
            $this->id_ = $qry->getInsertID();
        }
        else
        {
            $row = $qry->getRow();
            $this->id_ = $row['plt_id'];
			$this->updated_ = strtotime($row['plt_updated']." UTC");
			if(!$this->updated_) $this->updated_ = 0;
			if ($this->isUpdatable($timestamp) && $row['plt_crp_id'] != $corp->getID())
            {
                $qryI->execute("update kb3_pilots
                             set plt_crp_id = ".$corp->getID().",
                                 plt_updated = date_format( '".$timestamp."', '%Y.%m.%d %H:%i:%s') where plt_id = ".$this->id_);
            }
            if (!$row['plt_externalid'] && $externalID) $this->setCharacterID($externalID);
        }

        return $this->id_;
    }
    //! Return whether this pilot was updated before the given timestamp.

    /*!
     * \param $timestamp A timestamp to compare this pilot's details with.
	 * \return boolean - true if update time was before the given timestamp.
     */
    function isUpdatable($timestamp)
    {
		if(isset($this->updated_))
			if(is_null($this->updated_) || strtotime($timestamp." UTC") > $this->updated_) return true;
			else return false;
        $qry = new DBQuery();
        $qry->execute("select plt_id
                        from kb3_pilots
                       where plt_id = ".$this->id_."
                         and ( plt_updated < date_format( '".$timestamp."', '%Y.%m.%d %H:%i')
                               or plt_updated is null )");

        return $qry->recordCount() == 1;
    }
	//! Set the CCP external ID for this pilot.

    /*!
     * \param $externalID CCP external ID for this pilot.
     */
    function setCharacterID($externalID)
    {
        if (!intval($externalID))
        {
            return false;
        }
        $this->externalid_ = intval($externalID);
        $qry = new DBQuery();
        $qry->execute("update kb3_pilots set plt_externalid = ".$this->externalid_."
                       where plt_id = ".$this->id_);
    }
}

class Pilots
{
	//! Add an array of pilots to be checked.

	//! \param $names array of corp names indexed by pilot name.
	function addNames($names)
	{
		$qry = new DBQuery(true);
		$checklist = array();
		foreach($names as $pilot =>$corp)
		{
			$qry->execute("SELECT 1 FROM kb3_pilots WHERE plt_name = '".$pilot."'");
			if(!$qry->recordCount()) $checklist[] = $pilot;
		}
		if(!count($checklist)) return;
		require_once("common/includes/class.eveapi.php");
		$position = 0;
		$myNames = array();
		$myID = new API_NametoID();
		while($position < count($checklist))
		{
			$namestring = str_replace(" ", "%20", implode(',',array_slice($checklist,$position, 500, true)));
			$namestring = str_replace("\'", "'", $namestring);
			$position +=500;
			$myID->setNames($namestring);
			$myID->fetchXML();
			$tempNames = $myID->getNameData();
			$myID->clear();
			if(!is_array($tempNames)) continue;
			$myNames = array_merge($myNames, $tempNames);
		}
		$newpilot = new Pilot();
		//$sql = '';
		if(!is_array($myNames)) {echo $myNames;die("name fetch error");}
		foreach($myNames as $name)
		{
			if(isset($names[slashfix($name['name'])]))
			{
				$newpilot->add(slashfix($name['name']), $names[slashfix($name['name'])], '0000-00-00', $name['characterID']);
				// Adding all at once is faster but skips checks for name/id clashes.
				//if($sql == '') $sql = "INSERT INTO kb3_pilots (plt_name, plt_crp_id, plt_externalid, plt_updated) values ('".slashfix($name['name'])."', ".$names[slashfix($name['name'])]->getID().', '.$name['characterID'].", '0000-00-00')";
				//else $sql .= ", ('".slashfix($name['name'])."', ".$names[slashfix($name['name'])]->getID().', '.$name['characterID'].", '0000-00-00')";
			}
		}
		if($sql) $qry->execute($sql);
	}
}