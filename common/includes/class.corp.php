<?php
require_once('class.alliance.php');
require_once('class.pilot.php');
require_once('class.dbprepared.php');

//! Creates a new Corporation or fetches an existing one from the database.
class Corporation
{
//! Create a new Corporation object from the given $id.

    /*!
     * \param $id The corporation ID.
	 * \param $externalIDFlag true if the id is the external id.
     */
	function Corporation($id = 0, $externalIDFlag = false)
	{
		if($externalIDFlag) $this->externalid_=intval($id);
		else $this->id_ = intval($id);
		$this->executed_ = false;
	}
	//! Return true if this corporation is an NPC corporation.
	function isNPCCorp()
	{
		global $corp_npc;
		if (in_array($this->getName(), $corp_npc))
			return true;
		if($this->externalid_ > 1000001 && $this->externalid_ < 1000183)
			return true;
		// These are NPC alliances but they may show up as corps on mails.
		if($this->externalid_ > 500000 && $this->externalid_ < 500021)
			return true;
	}

	//! Return the corporation name stripped of all non-ASCII non-alphanumeric characters.
	function getUnique()
	{
		if(!$this->name_) $this->execQuery();
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
			if($this->externalid_ && file_exists(KB_CACHEDIR.'/img/corps/'.substr($this->externalid_,0,2).'/'.$this->externalid_.'_'.$size.'.png'))
				return KB_CACHEDIR.'/img/corps/'.substr($this->externalid_,0,2).'/'.$this->externalid_.'_'.$size.'.png';

			if($size == 128)
			{
				if($this->externalid_ > 1000001 && $this->externalid_ < 1000183)
					return 'img/corps/c'.$this->externalid_.'.png';
				else return 'img/corps/'.$this->getUnique().'.png';
			}
			elseif($this->externalid_ > 1000001 && $this->externalid_ < 1000183)
				return '?a=thumb&amp;type=npc&amp;id=c'.$this->externalid_.'&amp;size='.$size;
			else return '?a=thumb&amp;type=npc&amp;id='.$this->getUnique().'&amp;size='.$size;
		}
		else {
			if($this->externalid_ && file_exists(KB_CACHEDIR.'/img/corps/'.substr($this->externalid_,0,2).'/'.$this->externalid_.'_'.$size.'.jpg'))
				return KB_CACHEDIR.'/img/corps/'.substr($this->externalid_,0,2).'/'.$this->externalid_.'_'.$size.'.jpg';
		}
		return '?a=thumb&amp;type=corp&amp;id='.$this->externalid_.'&amp;size='.$size;
	}

	//! Return the corporation CCP ID.

	/*! When populateList is true, the lookup will return 0 in favour of getting the
	 *  external ID from CCP. This helps the kill_detail page load times.
	 */
	function getExternalID()
	{
		if($this->externalid_) return $this->externalid_;
		$this->execQuery();
		if(!$populateList)
		{
			if($this->externalid_) return $this->externalid_;

			$corpname = str_replace(" ", "%20", $this->getName() );
			require_once("common/includes/class.eveapi.php");
			$myID = new API_NametoID();
			$myID->setNames($corpname);
			$myID->fetchXML();
			$myNames = $myID->getNameData();
			if($this->setExternalID($myNames[0]['characterID']))
				return $this->externalid_;
			else return 0;
		}
		else return 0;
	}

	//! Return the corporation ID.
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
	//! Return the corporation name.
	function getName()
	{
		if(!$this->name_) $this->execQuery();
		return $this->name_;
	}

	//! Return an alliance object for the alliance this corporation belongs to.
	function getAlliance()
	{
		if(!$this->alliance_) $this->execQuery();
		return new Alliance($this->alliance_);
	}
	//! Lookup a corporation name and set this object to use the details found.

    /*!
     * \param $name The corporation name to look up.
     */
	function lookup($name)
	{
		$qry = new DBQuery();
		$qry->execute("select * from kb3_corps
                       where crp_name = '".slashfix($name)."'");
		$row = $qry->getRow();
		$this->id_ = intval($row['crp_id']);
		$this->name_ = $row['crp_name'];
		$this->externalid_ = intval($row['crp_external_id']);
		$this->alliance_ = $row['crp_all_id'];
	}
	//! Search the database for the corporation details for this object.
	function execQuery()
	{
		if (!$this->executed_)
		{
			$qry = new DBQuery();
			$sql = "select * from kb3_corps where ";
			if($this->externalid_) $sql .= "crp_external_id = ".$this->externalid_;
			else $sql .= "crp_id = ".$this->id_;
			$qry->execute($sql);
			$row = $qry->getRow();
			$this->id_ = intval($row['crp_id']);
			$this->name_ = $row['crp_name'];
			$this->externalid_ = intval($row['crp_external_id']);
			$this->alliance_ = $row['crp_all_id'];
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
		$qry = new DBQuery(true);
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

					$this->id_ = $row['crp_id'];
					$this->name_ = $name;
					$this->externalid_ = $row['crp_external_id'];
					$this->alliance_ = $row['crp_all_id'];
					$this->updated_ = strtotime($row['crp_updated']." UTC");
					if(!$this->updated_) $this->updated_ = 0;
					// Now check if the alliance needs to be updated.
					if ($row['crp_all_id'] != $alliance->getID() && $this->isUpdatable($timestamp))
					{
						$sql = 'update kb3_corps
									   set crp_all_id = '.$alliance->getID().', ';
						$sql .= "crp_updated = date_format( '".
							$timestamp."','%Y.%m.%d %H:%i:%s') ".
							"where crp_id = ".$this->id_;
						$qry->execute($sql);
						$this->alliance_ = $alliance;
					}
					return $this->id_;
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
			$this->id_ = $qry->getInsertID();
			$this->name_ = $name;
			$this->alliance_ = $alliance->getID();
			if($externalid) $this->externalid_ = $externalid;
			$this->updated_ = strtotime(preg_replace("/\./","-",$timestamp)." UTC");
		}
		else
		{
			$row = $qry->getRow();
			$this->id_ = $row['crp_id'];
			$this->name_ = $row['crp_name'];
			$this->externalid_ = $row['crp_external_id'];
			$this->alliance_ = $row['crp_all_id'];
			$this->updated_ = strtotime($row['crp_updated']." UTC");
			if ($row['crp_all_id'] != $alliance->getID() && $this->isUpdatable($timestamp))
			{
				$sql = 'update kb3_corps
	                           set crp_all_id = '.$alliance->getID().', ';
				if(intval(externalid))
					$sql .= 'crp_external_id = '.intval(externalid).', ';
				$sql .= "crp_updated = date_format( '".
					$timestamp."','%Y.%m.%d %H:%i:%s') ".
					"where crp_id = ".$this->id_;
				$qry->execute($sql);
				$this->alliance_ = $alliance;
			}
		}

		return $this->id_;
	}
	//! Return whether this corporation was updated before the given timestamp.

    /*!
     * \param $timestamp A timestamp to compare this corporation's details with.
     */
	function isUpdatable($timestamp)
	{
		$timestamp = preg_replace("/\./","-",$timestamp);
		if(isset($this->updated_))
			if(is_null($this->updated_) || strtotime($timestamp." UTC") > $this->updated_) return true;
			else return false;
		$qry = new DBQuery();
		$qry->execute("select crp_id from kb3_corps
		               where crp_id = ".$this->id_."
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
		if($externalid && $this->id_)
		{
			$this->execQuery();
			$qry = new DBQuery(true);
			$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id = ".$externalid." AND crp_id <> ".$this->id_);
			if($qry->recordCount())
			{
				$result = $qry->getRow();
				$old_id = $result['crp_id'];
				$qry->autocommit(false);
				$qry->execute("UPDATE kb3_pilots SET crp_id = ".$old_id." WHERE crp_id = ".$this->id_);
				$qry->execute("UPDATE kb3_kills SET kll_crp_id = ".$old_id." WHERE kll_crp_id = ".$this->id_);
				$qry->execute("UPDATE kb3_inv_detail SET ind_crp_id = ".$old_id." WHERE ind_crp_id = ".$this->id_);
				$qry->execute("UPDATE kb3_inv_crp SET inc_crp_id = ".$old_id." WHERE inc_crp_id = ".$this->id_);
				$qry->execute("UPDATE kb3_corps SET crp_name = '".$this->name_."' where crp_id = ".$old_id);
				$qry->execute("DELETE FROM kb3_corps WHERE crp_id = ".$this->id_);
				$qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$this->id_);
				$qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$old_id);
				$qry->autocommit(true);
				$this->id_ = $old_id;
				return true;
			}
			if($qry->execute("UPDATE kb3_corps SET crp_external_id = ".$externalid." where crp_id = ".$this->id_))
			{
				$this->externalid_ = $externalid;
				return true;
			}
		}
		return false;
	}

	//! Returns an array of pilots we know to be in this corp.
	function getMemberList()
	{
		$qry = new DBQuery();
		$qry->execute("SELECT plt_id FROM kb3_pilots
                       WHERE plt_crp_id = " . $this->id_);

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
}