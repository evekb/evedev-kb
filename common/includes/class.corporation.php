<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * Creates a new Corporation or fetches an existing one from the database.
 * @package EDK
 */
class Corporation extends Entity
{
	/** @var integer */
	protected $id = null;
	/** @var integer */
	protected $externalid = null;
	/** @var string */
	protected $name = null;
	/** @var Alliance */
	private $alliance = null;
	/** @var integer */
	private $updated = null;

	/**
	 * Create a new Corporation object from the given $id.
	 *
     * @param integer $id The corporation ID.
	 * @param boolean $externalIDFlag true if the id is the external id.
	*/
	function Corporation($id = 0, $externalIDFlag = false)
	{
		if($externalIDFlag) $this->externalid=intval($id);
		else $this->id = intval($id);
	}
	/**
	 * Return true if this corporation is an NPC corporation.
	 *
	 * @return boolean True if this corporation is an NPC corporation.
	 */
	function isNPCCorp()
	{
		if($this->externalid > 1000001 && $this->externalid < 1000183)
			return true;
		// These are NPC alliances but they may show up as corps on mails.
		else if($this->externalid > 500000 && $this->externalid < 500021)
			return true;
		else return false;
	}

	/**
	 * Return a URL for the icon of this corporation.
	 *
	 * If a cached image exists then return the direct url. Otherwise return
	 * a link to the thumbnail page.
	 *
	 * @param integer $size The size in pixels of the image needed.
	 * @return string The URL for this corporation's logo.
	 */
	function getPortraitURL($size = 64)
	{
		if(!$this->externalid) $this->getExternalID();

		// NPC alliances can be recorded as corps on killmails.
		if($this->externalid > 500000 && $this->externalid < 500021)
			return imageURL::getURL('Alliance', $this->externalid, $size);

		return imageURL::getURL('Corporation', $this->externalid, $size);
	}

	/**
	 * Return a URL for the details page of this Corporation.
	 *
	 * @return string The URL for this Corporation's details page.
	 */
	function getDetailsURL()
	{
		if ($this->getExternalID()) {
			return edkURI::page('corp_detail', $this->externalid, 'crp_ext_id');
		} else {
			return edkURI::page('corp_detail', $this->id, 'crp_id');
		}
	}

	/**
	 * Return the corporation CCP ID.
	 * When populateList is true, the lookup will return 0 in favour of getting
	 * the external ID from CCP. This helps the kill_detail page load times.
	 *
	 * @param boolean $populateList
	 * @return integer
	 */
	function getExternalID($populateList = false)
	{
		if($this->externalid) return $this->externalid;
		$this->execQuery();
		if(!$populateList)
		{
			if($this->externalid) return $this->externalid;

			$myID = new API_NametoID();
			$myID->setNames($this->getName());
			$myID->fetchXML();
			$myNames = $myID->getNameData();
			if($this->setExternalID($myNames[0]['characterID']))
				return $this->externalid;
			else return 0;
		}
		else return 0;
	}

	/**
	 * Return an Alliance object for the alliance this corporation belongs to.
	 *
	 * @return Alliance
	 */
	function getAlliance()
	{
		if(!$this->alliance) $this->execQuery();
		return new Alliance($this->alliance);
	}
	/**
	 * Lookup a corporation name and set this object to use the details found.
	 *
     * @param string $name The corporation name to look up.
	*/
	function lookup($name)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("select * from kb3_corps
                       where crp_name = '".slashfix($name)."'");
		$row = $qry->getRow();
		$this->id = intval($row['crp_id']);
		$this->name = $row['crp_name'];
		$this->externalid = intval($row['crp_external_id']);
		$this->alliance = $row['crp_all_id'];
	}
	/**
	 * Search the database for the corporation details for this object.
	 *
	 * If no record is found but we have an external ID then the result
	 * will be fetched from CCP.
	*/
	function execQuery()
	{
		// TODO: Should we double the size and record by external id as well?
		// We can't rely on having an external id but if it was used more
		// extensively in EDK then we could cache by external id if we have it
		// and internal id only when we do not.
		if( $this->id && $this->isCached() ) {
			$cache = $this->getCache();
			$this->id = $cache->id;
			$this->externalid = $cache->externalid;
			$this->name = $cache->name;
			$this->alliance = $cache->alliance;
		} else {
			$qry = DBFactory::getDBQuery();
			$sql = "select * from kb3_corps where ";
			if($this->externalid) $sql .= "crp_external_id = ".$this->externalid;
			else $sql .= "crp_id = ".$this->id;
			$qry->execute($sql);
			// If we have an external ID but no local record then fetch from CCP.
			if($this->externalid && !$qry->recordCount()) $this->fetchCorp();
			else if($qry->recordCount())
			{
				$row = $qry->getRow();
				$this->id = intval($row['crp_id']);
				$this->name = $row['crp_name'];
				$this->externalid = intval($row['crp_external_id']);
				$this->alliance = $row['crp_all_id'];
				$this->putCache();
			}
		}
	}
	/**
	 * Add a new corporation to the database or update the details of an existing one.
	 *
     * @param string $name The name of the new corporation.
     * @param Alliance $alliance The alliance this corporation belongs to.
     * @param string $timestamp The timestamp the corporation's details were updated.
     * @param integer $externalid The external CCP ID for the corporation.
	 * @return integer
	 */
	function add($name, $alliance, $timestamp, $externalid = 0, $loadExternals = true)
	{
		$name = slashfix($name);
		$qry = DBFactory::getDBQuery(true);
		$qry->execute("select * from kb3_corps
		               where crp_name = '".$name."'");
		// If the corp name is not present in the db add it.
		if ($qry->recordCount() == 0)
		{
			$externalid = intval($externalid);
			// If no external id is given then look it up.
			if(!$externalid && $loadExternals)
			{
				$corpname = str_replace(" ", "%20", $name );
				$myID = new API_NametoID();
				$myID->setNames($corpname);
				$myID->fetchXML();
				$myNames = $myID->getNameData();
				$externalid = $myNames[0]['characterID'];
			}
			// If we have an external id then check it isn't already in use
			// If we find it then update the old corp with the new name and
			// return.
			if($externalid)
			{
				$qry->execute("SELECT * FROM kb3_corps WHERE crp_external_id = ".$externalid);
				if ($qry->recordCount() > 0)
				{
					$row = $qry->getRow();
					$qry->execute("UPDATE kb3_corps SET crp_name = '".$name."' WHERE crp_external_id = ".$externalid);

					$this->id = $row['crp_id'];
					$this->name = stripslashes($name);
					$this->externalid = $row['crp_external_id'];
					$this->alliance = $row['crp_all_id'];
					if(!is_null($row['crp_updated'])) $this->updated = strtotime($row['crp_updated']." UTC");
					else $this->updated = null;
					if(!$this->updated) $this->updated = 0;
					// Now check if the alliance needs to be updated.
					if ($row['crp_all_id'] != $alliance->getID() && $this->isUpdatable($timestamp))
					{
						$sql = 'update kb3_corps
									   set crp_all_id = '.$alliance->getID().', ';
						$sql .= "crp_updated = date_format( '".
							$timestamp."','%Y.%m.%d %H:%i:%s') ".
							"where crp_id = ".$this->id;
						$qry->execute($sql);
						$this->alliance = $alliance;
					}
					return $this->id;
				}
			}
			// Neither corp name or external id was found so add this corp as new
			if($externalid) $qry->execute("insert into kb3_corps ".
					"(crp_name, crp_all_id, crp_external_id, crp_updated) ".
					"values ('".$name."',".$alliance->getID().
					", ".$externalid.", date_format('".$timestamp.
					"','%Y.%m.%d %H:%i:%s'))");
			else $qry->execute("insert into kb3_corps ".
					"(crp_name, crp_all_id, crp_updated) ".
					"values ('".$name."',".$alliance->getID().
					",date_format('".$timestamp."','%Y.%m.%d %H:%i:%s'))");
			$this->id = $qry->getInsertID();
			$this->name = $name;
			$this->alliance = $alliance->getID();
			if($externalid) $this->externalid = $externalid;
			$this->updated = strtotime(preg_replace("/\./","-",$timestamp)." UTC");
		}
		else
		{
			$row = $qry->getRow();
			$this->id = $row['crp_id'];
			$this->name = $row['crp_name'];
			$this->externalid = $row['crp_external_id'];
			$this->alliance = $row['crp_all_id'];
			if(!is_null($row['crp_updated'])) $this->updated = strtotime($row['crp_updated']." UTC");
			else $this->updated = null;
			if ($row['crp_all_id'] != $alliance->getID() && $this->isUpdatable($timestamp))
			{
				$sql = 'update kb3_corps
	                           set crp_all_id = '.$alliance->getID().', ';
				$sql .= "crp_updated = date_format( '".
					$timestamp."','%Y.%m.%d %H:%i:%s') ".
					"where crp_id = ".$this->id;
				$qry->execute($sql);
				$this->alliance = $alliance;
			}
			if(!$this->externalid && intval($externalid) > 0)
			{
				$this->setExternalID(intval($externalid));
			}
		}
		return $this->id;
	}
	/**
	 * Return whether this corporation was updated before the given timestamp.
	 *
     * @param string $timestamp A timestamp to compare this corporation's details with.
	*/
	function isUpdatable($timestamp)
	{
		$timestamp = preg_replace("/\./","-",$timestamp);
		if(isset($this->updated))
			if(is_null($this->updated) || strtotime($timestamp." UTC") > $this->updated) return true;
			else return false;
		$qry = DBFactory::getDBQuery();
		$qry->execute("select crp_id from kb3_corps
		               where crp_id = ".$this->id."
		               and ( crp_updated < date_format( '".$timestamp."', '%Y-%m-%d %H:%i' )
			           or crp_updated is null )");
		return $qry->recordCount() == 1;
	}

	/**
	 * Set the CCP external ID for this corporation.
	 *
	 * If the same externalid already exists then that corp name is changed to
	 * the new one.
	 *
	 * @param integer $externalid The new external id to set for this corp.
	 * @return boolean
	 */
	function setExternalID($externalid)
	{
		$externalid = intval($externalid);
		if($externalid && $this->id)
		{
			$this->execQuery();
			$qry = DBFactory::getDBQuery(true);
			$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id = ".$externalid." AND crp_id <> ".$this->id);
			if($qry->recordCount())
			{
				$result = $qry->getRow();
				$old_id = $result['crp_id'];
				$qry->autocommit(false);
				$qry->execute("UPDATE kb3_pilots SET plt_crp_id = ".$old_id." WHERE plt_crp_id = ".$this->id);
				$qry->execute("UPDATE kb3_kills SET kll_crp_id = ".$old_id." WHERE kll_crp_id = ".$this->id);
				$qry->execute("UPDATE kb3_inv_detail SET ind_crp_id = ".$old_id." WHERE ind_crp_id = ".$this->id);
				$qry->execute("UPDATE kb3_inv_crp SET inc_crp_id = ".$old_id." WHERE inc_crp_id = ".$this->id);
				$qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$this->id);
				$qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$old_id);
				$qry->execute("DELETE FROM kb3_corps WHERE crp_id = ".$this->id);
				$qry->execute("UPDATE kb3_corps SET crp_name = '".$qry->escape($this->name)."' where crp_id = ".$old_id);
				$qry->autocommit(true);
				$this->id = $old_id;
				$this->putCache();
				return true;
			}
			if($qry->execute("UPDATE kb3_corps SET crp_external_id = ".$externalid." where crp_id = ".$this->id))
			{
				$this->externalid = $externalid;
				$this->putCache();
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns an array of pilots we know to be in this corp.
	 *
	 * @return Pilot
	 */
	function getMemberList()
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT plt_id FROM kb3_pilots
                       WHERE plt_crp_id = " . $this->id);

		if ($qry->recordCount() < 1)
			return null;
		else
		{
			$list = array();
			while ($row = $qry->getRow())
			{
				$pilot = new Pilot($row['plt_id']);
				$list[] = $pilot;
			}
		}
		return $list;
	}

	/**
	 * Fetch corporation name and alliance from CCP using the stored external ID.
	 *
	 * @return boolean TRUE on success, FALSE on failure.
	 */
	public function fetchCorp()
	{
		if(!$this->externalid) $this->execQuery();
		if(!$this->externalid) return false;

		$myID = new API_IDtoName();
		$myID->setIDs($this->externalid);
		$myID->fetchXML();
		$myNames = $myID->getIDData();
		if(empty($myNames[0]['name'])) return false;

		$myAPI = new API_CorporationSheet();
		$myAPI->setCorpID($this->externalid);
		$result = $myAPI->fetchXML();
		if(!empty($result)) return false;

		$alliance = new Alliance($myAPI->getAllianceID(), true);

		$this->add(slashfix($myNames[0]['name']), $alliance,
			$myAPI->getCurrentTime(), intval($myNames[0]['characterID']));
		return true;
	}
}