<?php
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
	 * Corporation Short name
	 */
	private $shortname = null;

	/**
	 * Corporation CEO ID
	 */
	private $ceoid = null;
	/**
	 * HQ Station ID
	 */
	private $stationid = null;
	/**
	 * Description
	 */
	private $description = null;
	/**
	 * URL
	 */
	private $url = null;
	/**
	 * Tax Rate
	 */
	private $taxrate = null;
	/**
	 * Member Count
	 */
	private $membercount = null;
	/**
	 * Shares
	 */
	private $shares = null;

	/**
	 * Start Date (alliance)
	 */
	private $startdate = null;

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
	 * Return the short name.
	 *
	 * @return string
	 */
	function getStartDate()
	{
		if (is_null($this->startdate)) {
			$this->execQuery();
		}
		return $this->startdate;
	}

	/**
	 * Return the short name.
	 *
	 * @return string
	 */
	function getshortName()
	{
		if (is_null($this->shortname)) {
			$this->execQuery();
		}
		return $this->shortname;
	}

	/**
	 * Return the Ceo ID.
	 *
	 * @return string
	 */
	function getCeoID()
	{
		if (is_null($this->ceoid)) {
			$this->execQuery();
		}
		return $this->ceoid;
	}

	/**
	 * Return the Station ID.
	 *
	 * @return string
	 */
	function getStationID()
	{
		if (is_null($this->stationid)) {
			$this->execQuery();
		}
		return $this->stationid;
	}

	/**
	 * Return the Description.
	 *
	 * @return string
	 */
	function getDescription()
	{
		if (is_null($this->description)) {
			$this->execQuery();
		}
		return str_replace( "<br>", "<br />", $this->description );
	}

	/**
	 * Return the URL
	 *
	 * @return string
	 */
	function getURL()
	{
		if (is_null($this->url)) {
			$this->execQuery();
		}
		return $this->url;
	}
	
	/**
	 * Return the Station ID.
	 *
	 * @return string
	 */
	function getTaxRate()
	{
		if (is_null($this->taxrate)) {
			$this->execQuery();
		}
		return $this->taxrate;
	}

	/**
	 * Return the Member Count.
	 *
	 * @return string
	 */
	function getMemberCount()
	{
		if (is_null($this->membercount)) {
			$this->execQuery();
		}
		return $this->membercount;
	}

	/**
	 * Return the Shares
	 *
	 * @return string
	 */
	function getShares()
	{
		if (is_null($this->shares)) {
			$this->execQuery();
		}
		return $this->shares;
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
	static function lookup($name)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("select crp_id from kb3_corps where crp_name = '"
				.slashfix($name)."'");
		if($qry->recordCount()) {
			$row = $qry->getRow();
			return Cacheable::factory('Corporation', (int)$row['crp_id']);
		} else {
			return false;
		}
	}
	/**
	 * Lookup a corporation by external id and set this object to use the details found.
	 *
     * @param int $ext_id The External ID to lookup
	*/
	static function lookupByExternalID($ext_id)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("select crp_id from kb3_corps where crp_external_id=".(int)$ext_id);
		if($qry->recordCount()) {
			$row = $qry->getRow();
			return Cacheable::factory('Corporation', (int)$row['crp_id']);
		} else {
			return false;
		}
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
			
			$this->shortname = $cache->shortname;
			$this->ceoid = $cache->ceoid;
			$this->stationid = $cache->stationid;
			$this->description = $cache->description;
			$this->url = $cache->url;
			$this->taxrate = $cache->taxrate;
			$this->membercount = $cache->membercount;
			$this->shares = $cache->shares;
			$this->startdate = $cache->startdate;

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
				
				$this->shortname = $row['crp_short_name'];
				$this->ceoid = intval($row['crp_ceo_id']);
				$this->stationid = intval($row['crp_station_id']);
				$this->description = $row['crp_description'];
				$this->url = $row['crp_url'];
				$this->taxrate = intval($row['crp_taxrate']);
				$this->membercount = intval($row['crp_membercount']);
				$this->shares = intval($row['crp_shares']);
				$this->startdate = $row['crp_startdate'];

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
	 * @param boolean $loadExternals Whether to fetch unknown information from the API.
	 * @return Corporation
	 */
	static function add($name, $alliance, $timestamp, $externalid = 0, $loadExternals = true)
	{
		if (!$name) {
			trigger_error("Attempt to add a corporation with no name. Aborting.", E_USER_ERROR);
			// If things are going this wrong, it's safer to die and prevent more harm
			die;
		} else if (!$alliance->getID()) {
			trigger_error("Attempt to add a corporation with no alliance. Aborting.", E_USER_ERROR);
			// If things are going this wrong, it's safer to die and prevent more harm
			die;
		}
		$name = stripslashes($name);
		$externalid = (int) $externalid;
		$qry = DBFactory::getDBQuery(true);
		$qry->execute("select * from kb3_corps
		               where crp_name = '".$qry->escape($name)."'");
		// If the corp name is not present in the db add it.
		if (!$qry->recordCount()) {
			// If no external id is given then look it up.
			if (!$externalid && $loadExternals) {
				$myID = new API_NametoID();
				$myID->setNames($name);
				$myID->fetchXML();
				$myNames = $myID->getNameData();
				$externalid = (int) $myNames[0]['characterID'];
			}
			// If we have an external id then check it isn't already in use
			// If we find it then update the old corp with the new name and
			// return.
			if ($externalid) {
				$qry->execute("SELECT * FROM kb3_corps WHERE crp_external_id = "
								.$externalid);
				if ($qry->recordCount()) {
					$row = $qry->getRow();
					$qry->execute("UPDATE kb3_corps SET crp_name = '".$qry->escape($name)
									."' WHERE crp_external_id = ".$externalid);

					$crp = Corporation::getByID((int)$row['crp_id']);
					Cacheable::delCache($crp);
					$crp->name = $name;
					$crp->externalid = $row['crp_external_id'];
					if (!is_null($row['crp_updated'])) {
						$crp->updated = strtotime($row['crp_updated']." UTC");
					} else {
						$crp->updated = null;
					}
					// Now check if the alliance needs to be updated.
					if ($row['crp_all_id'] != $alliance->getID()
									&& $crp->isUpdatable($timestamp)) {
						$sql = 'update kb3_corps
									   set crp_all_id = '.$alliance->getID().', ';
						$sql .= "crp_updated = date_format( '".
										$timestamp."','%Y.%m.%d %H:%i:%s') ".
										"where crp_id = ".$crp->id;
						$qry->execute($sql);
						$crp->alliance = $alliance;
					}
					return $crp;
				}
			}
			// Neither corp name or external id was found so add this corp as new
			if ($externalid) {
				$qry->execute("insert into kb3_corps ".
								"(crp_name, crp_all_id, crp_external_id, crp_updated) ".
								"values ('".$qry->escape($name)."',".$alliance->getID().
								", ".$externalid.", date_format('".$timestamp.
								"','%Y.%m.%d %H:%i:%s'))");
			} else {
				$qry->execute("insert into kb3_corps ".
								"(crp_name, crp_all_id, crp_updated) ".
								"values ('".$qry->escape($name)."',".$alliance->getID().
								",date_format('".$timestamp."','%Y.%m.%d %H:%i:%s'))");
			}
			$crp = Corporation::getByID((int)$qry->getInsertID());
			$crp->name = $name;
			$crp->externalid = ((int)$externalid);
			$crp->alliance = $alliance->getID();
			$crp->updated = strtotime(preg_replace("/\./", "-", $timestamp)." UTC");

			return $crp;
		} else {
			$row = $qry->getRow();
			$crp = Corporation::getByID((int)$row['crp_id']);
			$crp->name = $row['crp_name'];
			$crp->externalid = (int) $row['crp_external_id'];
			$crp->alliance = $row['crp_all_id'];
			if (!is_null($row['crp_updated'])) {
				$crp->updated = strtotime($row['crp_updated']." UTC");
			} else {
				$crp->updated = null;
			}
			if ($row['crp_all_id'] != $alliance->getID()
							&& $crp->isUpdatable($timestamp)) {
				$sql = 'update kb3_corps set crp_all_id = '.$alliance->getID().', ';
				$sql .= "crp_updated = date_format( '".
								$timestamp."','%Y.%m.%d %H:%i:%s') ".
								"where crp_id = ".$crp->id;
				$qry->execute($sql);
				$crp->alliance = $alliance;
			}
			if (!$crp->externalid && $externalid) {
				$crp->setExternalID((int)$externalid);
			}
			return $crp;
		}
		return false;
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

		$myAPI = new API_CorporationSheet();
		$myAPI->setCorpID($this->externalid);
		$result = $myAPI->fetchXML();

		if($result == false) {
			return false;
		}

		$alliance = Alliance::add($myAPI->getAllianceName(),
						$myAPI->getAllianceID());

		if (!$alliance) {
			return false;
		}
		$crp = Corporation::add(slashfix($myAPI->getCorporationName()), $alliance,
				$myAPI->getCurrentTime(), intval($myAPI->getCorporationID()));

		$this->name = $crp->name;
		$this->alliance = $crp->alliance;
		$this->updated = $crp->updated;
		return true;
	}

	/**
	 * Return a new object by ID. Will fetch from cache if enabled.
	 *
	 * @param mixed $id ID to fetch
	 * @return Corporation
	 */
	static function getByID($id)
	{
		return Cacheable::factory(get_class(), $id);
	}
}