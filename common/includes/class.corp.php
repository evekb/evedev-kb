<?php
require_once('class.alliance.php');
require_once('class.pilot.php');
require_once('class.dbprepared.php');

//! Creates a new Corporation or fetches an existing one from the database.
class Corporation
{
	private $id;
	private $externalid;
	private $name;
	private $alliance;
	private $updated;

	//! Create a new Corporation object from the given $id.

    /*!
     * \param $id The corporation ID.
	 * \param $externalIDFlag true if the id is the external id.
     */
	function Corporation($id = 0, $externalIDFlag = false)
	{
		if($externalIDFlag) $this->externalid=intval($id);
		else $this->id = intval($id);
	}
	//! Return true if this corporation is an NPC corporation.
	function isNPCCorp()
	{
		global $corp_npc;
		if (in_array($this->getName(), $corp_npc))
			return true;
		if($this->externalid > 1000001 && $this->externalid < 1000183)
			return true;
		// These are NPC alliances but they may show up as corps on mails.
		if($this->externalid > 500000 && $this->externalid < 500021)
			return true;
	}

	//! Return the corporation name stripped of all non-ASCII non-alphanumeric characters.
	function getUnique()
	{
		if(!$this->name) $this->execQuery();
		return preg_replace('/[^a-z0-9]/', '', strtolower($this->getName()));
	}
	//! Return a URL for the icon of this corporation.

	/*! If a cached image exists then return the direct url. Otherwise return
	 *  a link to the thumbnail page.
	 *
	 * \param $size The size in pixels of the image needed.
	 */
	function getPortraitURL($size = 64)
	{
		$this->getExternalID();

		if($this->isNPCCorp() || file_exists('img/corps/'.$this->getUnique().'.png')) {
			if($this->externalid && file_exists(KB_CACHEDIR.'/img/corps/'.substr($this->externalid,0,2).'/'.$this->externalid.'_'.$size.'.png'))
				return KB_CACHEDIR.'/img/corps/'.substr($this->externalid,0,2).'/'.$this->externalid.'_'.$size.'.png';

			if($size == 128)
			{
				if($this->externalid > 1000001 && $this->externalid < 1000183)
					return 'img/corps/c'.$this->externalid.'.png';
				else return 'img/corps/'.$this->getUnique().'.png';
			}
			elseif($this->externalid > 1000001 && $this->externalid < 1000183)
				return '?a=thumb&amp;type=npc&amp;id=c'.$this->externalid.'&amp;size='.$size;
			else return '?a=thumb&amp;type=npc&amp;id='.$this->getUnique().'&amp;size='.$size;
		}
		else {
			if($this->externalid && file_exists(KB_CACHEDIR.'/img/corps/'.substr($this->externalid,0,2).'/'.$this->externalid.'_'.$size.'.png'))
				return KB_CACHEDIR.'/img/corps/'.substr($this->externalid,0,2).'/'.$this->externalid.'_'.$size.'.png';
		}
		return '?a=thumb&amp;type=corp&amp;id='.$this->externalid.'&amp;size='.$size;
	}

	//! Return the corporation CCP ID.

	/*! When populateList is true, the lookup will return 0 in favour of getting the
	 *  external ID from CCP. This helps the kill_detail page load times.
	 */
	function getExternalID()
	{
		if($this->externalid) return $this->externalid;
		$this->execQuery();
		if(!$populateList)
		{
			if($this->externalid) return $this->externalid;

			$corpname = str_replace(" ", "%20", $this->getName() );
			require_once("common/includes/class.eveapi.php");
			$myID = new API_NametoID();
			$myID->setNames($corpname);
			$myID->fetchXML();
			$myNames = $myID->getNameData();
			if($this->setExternalID($myNames[0]['characterID']))
				return $this->externalid;
			else return 0;
		}
		else return 0;
	}

	//! Return the corporation ID.
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
	//! Return the corporation name.
	function getName()
	{
		if(!$this->name) $this->execQuery();
		return $this->name;
	}

	//! Return an alliance object for the alliance this corporation belongs to.
	function getAlliance()
	{
		if(!$this->alliance) $this->execQuery();
		return new Alliance($this->alliance);
	}
	//! Lookup a corporation name and set this object to use the details found.

    /*!
     * \param $name The corporation name to look up.
     */
	function lookup($name)
	{
		$qry = DBFactory::getDBQuery();;
		$qry->execute("select * from kb3_corps
                       where crp_name = '".slashfix($name)."'");
		$row = $qry->getRow();
		$this->id = intval($row['crp_id']);
		$this->name = $row['crp_name'];
		$this->externalid = intval($row['crp_external_id']);
		$this->alliance = $row['crp_all_id'];
	}
	//! Search the database for the corporation details for this object.

	/*!
	 * If no record is found but we have an external ID then the result
	 * will be fetched from CCP.
	 */
	function execQuery()
	{
		$qry = DBFactory::getDBQuery();;
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
		}
	}
	//! Add a new corporation to the database or update the details of an existing one.

    /*!
     * \param $name The name of the new corporation.
     * \param $alliance The alliance this corporation belongs to.
     * \param $timestamp The timestamp the corporation's details were updated.
     * \param $externalid The external CCP ID for the corporation.
     */
	function add($name, $alliance, $timestamp, $externalid = 0, $loadExternals = true)
	{
		$name = slashfix($name);
		$qry = DBFactory::getDBQuery(true);;
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
				require_once("common/includes/class.eveapi.php");
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
					$this->name = $name;
					$this->externalid = $row['crp_external_id'];
					$this->alliance = $row['crp_all_id'];
					$this->updated = strtotime($row['crp_updated']." UTC");
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
			$this->updated = strtotime($row['crp_updated']." UTC");
			if ($row['crp_all_id'] != $alliance->getID() && $this->isUpdatable($timestamp))
			{
				$sql = 'update kb3_corps
	                           set crp_all_id = '.$alliance->getID().', ';
				if(intval(externalid))
					$sql .= 'crp_external_id = '.intval(externalid).', ';
				$sql .= "crp_updated = date_format( '".
					$timestamp."','%Y.%m.%d %H:%i:%s') ".
					"where crp_id = ".$this->id;
				$qry->execute($sql);
				$this->alliance = $alliance;
			}
		}

		return $this->id;
	}
	//! Return whether this corporation was updated before the given timestamp.

    /*!
     * \param $timestamp A timestamp to compare this corporation's details with.
     */
	function isUpdatable($timestamp)
	{
		$timestamp = preg_replace("/\./","-",$timestamp);
		if(isset($this->updated))
			if(is_null($this->updated) || strtotime($timestamp." UTC") > $this->updated) return true;
			else return false;
		$qry = DBFactory::getDBQuery();;
		$qry->execute("select crp_id from kb3_corps
		               where crp_id = ".$this->id."
		               and ( crp_updated < date_format( '".$timestamp."', '%Y.%m.%d %H:%i' )
			           or crp_updated is null )");
		return $qry->recordCount() == 1;
	}

	//! Set the CCP external ID for this corporation.

	//! \param $externalid The new external id to set for this corp.
	//! If the same externalid already exists then that corp name is changed to
	//! the new one.
	function setExternalID($externalid)
	{
		$externalid = intval($externalid);
		if($externalid && $this->id)
		{
			$this->execQuery();
			$qry = DBFactory::getDBQuery(true);;
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
				$qry->execute("UPDATE kb3_corps SET crp_name = '".$this->name."' where crp_id = ".$old_id);
				$qry->execute("DELETE FROM kb3_corps WHERE crp_id = ".$this->id);
				$qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$this->id);
				$qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$old_id);
				$qry->autocommit(true);
				$this->id = $old_id;
				return true;
			}
			if($qry->execute("UPDATE kb3_corps SET crp_external_id = ".$externalid." where crp_id = ".$this->id))
			{
				$this->externalid = $externalid;
				return true;
			}
		}
		return false;
	}

	//! Returns an array of pilots we know to be in this corp.
	function getMemberList()
	{
		$qry = DBFactory::getDBQuery();;
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

	//! Fetch corporation name and alliance from CCP using the stored external ID.
	private function fetchCorp()
	{
		if(is_null($this->external_id)) return false;

		require_once("common/includes/class.eveapi.php");
		$myID = new API_IDtoName();
		$myID->setIDs($this->external_id);
		$myID->fetchXML();
		$myNames = $myID->getIDData();
		if(empty($myNames[0]['name'])) return false;

		$myAPI = new API_CorporationSheet();
		$myAPI->setCorpID($this->external_id);
		$result = $myAPI->fetchXML();
		if(!empty($result)) return false;

		$alliance = new Alliance($myAPI->getAllianceID(), true);

		$this->add(slashfix($myNames[0]['name']), $alliance,
			$myAPI->getCurrentTime(), intval($myNames[0]['characterID']));
	}
}