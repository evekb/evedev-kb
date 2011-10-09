<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class Kill
{
	private $id_ = 0;
	private $externalid_ = null;
	public $involvedparties_ = array();
	public $destroyeditems_ = array();
	public $droppeditems_ = array();
	public $VictimDamageTaken = 0;
	private $fullinvolved_ = false;
	private $timestamp_ = null;
	private $victim_ = null;
	private $dmgtaken = null;
	private $iskloss_ = 0;
	private $victimship_ = null;
	private $dupeid_ = 0;
	private $hash = false;
	private $mail = null;
	private $trust = 0;
	private $executed = false;

	function Kill($id = 0, $external = false)
	{
		$id = intval($id);
		if($id && $external)
		{
			$qry = DBFactory::getDBQuery();
			$qry->execute("SELECT kll_id FROM kb3_kills WHERE kll_external_id = ".$id);
			if($qry->recordCount())
			{
				$result = $qry->getRow();
				$this->id_ = $result['kll_id'];
				$this->externalid_ = $id;
			}
			else
			{
				$this->id_ = null;
				$this->externalid = null;
			}
		}
		else
		{
			$this->id_ = $id;
			$this->externalid_ = null;
		}
	}

	//! Set internal variables.

	/*!
	 * \param $arr Array of values indexed by internal variable name.
	 */
	function setArray($arr)
	{
		foreach($arr as $key=>$val) $this->$key = $val;
	}

	function set($var, $value)
	{
		$this->$var = $value;
	}
	//! Get the internal ID of this kill.

	//! \return integer value for the internal kill ID.
	function getID()
	{
		return $this->id_;
	}

	//! Get the external ID of this kill.

	//! \return integer value for the external kill ID.
	function getExternalID()
	{
		if(is_null($this->externalid_)) $this->execQuery();
		return $this->externalid_;
	}
	//! Return the dropped items array for this kill.
	public function getDroppedItems()
	{
		return $this->droppeditems_;
	}
	//! Return the destroyed items array for this kill.
	public function getDestroyedItems()
	{
		return $this->destroyeditems_;
	}
	function getTimeStamp()
	{
		if(is_null($this->timestamp_)) $this->execQuery();
		return $this->timestamp_;
	}
	//! Return the victim Pilot object.
	/*
	 * \return Pilot
	*/
	function getVictim()
	{
		if(is_null($this->victim_)) $this->execQuery();
		return $this->victim_;
	}
	//! Return the amount of damage taken by the victim.
	function getDamageTaken()
	{
		if(is_null($this->dmgtaken)) $this->execQuery();
		return $this->dmgtaken;
	}

	function getVictimName()
	{
		if(!isset($this->victimname_)) $this->execQuery();
		if(isset($this->victim_)) return $this->victim_->getName();
		return $this->victimname_;
	}

	function getVictimID()
	{
		if(!isset($this->victimid_)) $this->execQuery();
		if(isset($this->victim_)) return $this->victim_->getID();
		return $this->victimid_;
	}

	function getVictimExternalID()
	{
		if(!isset($this->plt_ext_)) $this->execQuery();
		if(isset($this->victim_)) return $this->victim_->getExternalID();
		return $this->plt_ext_;
	}

	function getVictimPortrait($size = 32)
	{
		$this->execQuery();
		if(isset($this->victim_)) return $this->victim_->getPortraitURL();
		$plt = new Pilot($this->victimid_);
		return $plt->getPortraitURL($size);
	}

	function getVictimCorpID()
	{
		if(!isset($this->victimcorpid_)) $this->execQuery();
// Removing this until the victim set is the victim status at the time of the kill
//		if(isset($this->victim_)) return $this->victim_->getCorp()->getID();
		return $this->victimcorpid_;
	}

	function getVictimCorpName()
	{
		if(!isset($this->victimcorpname_)) $this->execQuery();
// Removing this until the victim set is the victim status at the time of the kill
//		if(isset($this->victim_)) return $this->victim_->getCorp()->getName();
		return $this->victimcorpname_;
	}

	function getVictimAllianceName()
	{
		if(!isset($this->victimalliancename_)) $this->execQuery();
// Removing this until the victim set is the victim status at the time of the kill
//		if(isset($this->victim_)) return $this->victim_->getCorp()->getAlliance()->getName();
		return $this->victimalliancename_;
	}

	function getVictimFactionName()
	{
		$this->execQuery();
//		if(isset($this->victim_))
//		{
//			if($this->victim_->getCorp()->getAlliance()->isFaction())
//				return $this->victim_->getCorp()->getAlliance()->getName();
//			else return "None";
//		}
		$alliance = new Alliance($this->victimallianceid_);
		if($alliance->isFaction())
			return $alliance->getName();
		else return "None";
	}

	function getVictimAllianceID()
	{
		if(!isset($this->victimallianceid_)) $this->execQuery();
// Removing this until the victim set is the victim status at the time of the kill
//		if(isset($this->victim_)) return $this->victim_->getCorp()->getAlliance()->getID();
		return $this->victimallianceid_;
	}

	function getVictimShip()
	{
		if(!isset($this->victimship_)) $this->execQuery();
		return $this->victimship_;
	}

	function getSystem()
	{
		if(!isset($this->solarsystem_)) $this->execQuery();
		return $this->solarsystem_;
	}

	function getFBPilotID()
	{
		if(isset($this->fbpilotid_)) return $this->fbpilotid_;
		$this->execQuery();
		if(isset($this->fbpilot_)) return $this->fbpilot_->getID();
		if (!$this->fbpilotid_) return "null";
		else return $this->fbpilotid_;
	}

	function getFBPilotName()
	{
		if(!isset($this->fbpilotname_)) $this->execQuery();
		if(isset($this->fbpilot_)) return $this->fbpilot_->getName();
		return $this->fbpilotname_;
	}

	function getFBCorpID()
	{
		if(isset($this->fbcorpid_)) return $this->fbcorpid_;
		$this->execQuery();
		if(isset($this->fbpilot_)) return $this->fbpilot_->getCorp()->getID();
		if (!$this->fbcorpid_) return "null";
		else return $this->fbcorpid_;
	}

	function getFBCorpName()
	{
		if(!isset($this->fbcorpname_)) $this->execQuery();
		if(isset($this->fbpilot_)) return $this->fbpilot_->getCorp()->getName();
		return $this->fbcorpname_;
	}

	function getFBAllianceID()
	{
		if(isset($this->fballianceid_)) return $this->fballianceid_;
		$this->execQuery();
		if(isset($this->fbpilot_)) return $this->fbpilot_->getCorp()->getAlliance()->getID();
		if (!$this->fballianceid_) return "null";
		else return $this->fballianceid_;
	}

	function getFBAllianceName()
	{
		if(!isset($this->fballiancename_)) $this->execQuery();
		return $this->fballiancename_;
	}

	function getISKLoss()
	{
		if(!isset($this->iskloss_)) $this->execQuery();
		return $this->iskloss_;
	}
	function getKillPoints()
	{
		if($this->killpoints_) return $this->killpoints_;
		$this->execQuery();
		$this->killpoints_ = $this->calculateKillPoints();
		return $this->killpoints_;
	}

	function getSolarSystemName()
	{
		if(isset($this->solarsystemname_)) return $this->solarsystemname_;
		if(isset($this->solarsystem_)) return $this->solarsystem_->getName();
		$this->execQuery();
		return $this->solarsystem_->getName();
	}

	function getSolarSystemSecurity()
	{
		if(isset($this->solarsystemsecurity_))return $this->solarsystemsecurity_;
		if(isset($this->solarsystem_)) return $this->solarsystem_->getSecurity();
		$this->execQuery();
		return $this->solarsystem_->getSecurity();
	}

	function getVictimShipName()
	{
		if(!isset($this->victimshipname_))
		{
			$this->execQuery();
			$this->victimshipname_ = $this->victimship_->getName();
		}
		return $this->victimshipname_;
	}

	function getVictimShipExternalID()
	{
		if(!isset($this->victimshipexternalid_))
		{
			$this->execQuery();
			$this->victimshipexternalid_ = $this->victimship_->getExternalID();
		}
		return $this->victimshipexternalid_;
	}

	function getVictimShipClassName()
	{
		if(!isset($this->victimshipclassname_))
		{
			$this->execQuery();
			$this->victimshipclassname_ = $this->victimship_->getClass()->getName();
		}
		return $this->victimshipclassname_;
	}

	function getVictimShipValue()
	{
		return $this->victimshipvalue_;
	}

	function getVictimShipImage($size)
	{
		 return imageURL::getURL('Ship', $this->victimshipexternalid_, $size);
	}

	function getVictimShipValueIndicator()
	{
		// value is now raw
		$value = $this->getVictimShipValue()/1000000;

		if ($value >= 0 && $value <= 1)
			$color = 'gray';
		elseif ($value > 1 && $value <= 15)
			$color = 'blue';
		elseif ($value > 15 && $value <= 25)
			$color = 'green';
		elseif ($value > 25 && $value <= 40)
			$color = 'yellow';
		elseif ($value > 40 && $value <= 80)
			$color = 'red';
		elseif ($value > 80 && $value <= 250)
			$color = 'orange';
		elseif ($value > 250)
			$color = 'purple';

		return IMG_URL.'/ships/ship-'.$color.'.gif';
	}
	//! Check if the victim is in a Faction.
	function getIsVictimFaction()
	{
		$this->execQuery();
		$factions = array("Amarr Empire", "Minmatar Republic", "Caldari State", "Gallente Federation");

		return (in_array($this->victimalliancename_, $factions));
	}

	function getRawMail()
	{
		if(!is_null($this->mail)) return $this->mail;

		if (config::get('km_cache_enabled') && file_exists(KB_PAGECACHEDIR."/".$this->getID().".txt"))
		{
			$this->mail = file_get_contents(KB_PAGECACHEDIR."/".$this->getID().".txt");
			return $this->mail;
		}

		$this->execQuery();
		if(!$this->valid_) return "The specified kill ID is not valid.";
		if ($this->isClassified())
		{
			return 'Killmail not yet available, try again in '.round($this->getClassifiedTime()/3600, 2).' hrs.';
		}

		$ship = $this->getVictimShip();
		$shipclass = $ship->getClass();

		$mail .= substr(str_replace('-', '.' , $this->getTimeStamp()), 0, 16)."\r\n\r\n";
		if ( in_array($shipclass->getID(), array(35, 36, 37, 38)) ) // Starbase (so this is a POS mail)
		{
			$mail .= "Corp: ".$this->getVictimCorpName()."\r\n";
			if($this->getIsVictimFaction()) $mail .= "Alliance: None\r\n";
			else $mail .= "Alliance: ".$this->getVictimAllianceName()."\r\n";
			$mail .= "Faction: ".$this->getVictimFactionName()."\r\n";
			//$ship = $this->getVictimShip();
			$mail .= "Destroyed: ".$ship->getName()."\r\n";
			if($this->getVictimName() == $this->getSystem()->getName())
				$mail .= "Moon: Unknown\r\n";
			else
				$mail .= "Moon: ".$this->getVictimName()."\r\n";
			$mail .= "System: ".$this->getSystem()->getName()."\r\n";
			$mail .= "Security: ".$this->getSystem()->getSecurity(true)."\r\n";
			$mail .= "Damage Taken: ".$this->dmgtaken."\r\n\r\n";
			$mail .= "Involved parties:\r\n\r\n";
		}
		else
		{
			$mail .= "Victim: ".$this->getVictimName()."\r\n";
			$mail .= "Corp: ".$this->getVictimCorpName()."\r\n";
			if($this->getIsVictimFaction()) $mail .= "Alliance: None\r\n";
			else $mail .= "Alliance: ".$this->getVictimAllianceName()."\r\n";
			$mail .= "Faction: ".$this->getVictimFactionName()."\r\n";
			//$ship = $this->getVictimShip();
			$mail .= "Destroyed: ".$ship->getName()."\r\n";
			$system = $this->getSystem();
			$mail .= "System: ".$system->getName()."\r\n";
			$mail .= "Security: ".$system->getSecurity(true)."\r\n";
			$mail .= "Damage Taken: ".$this->dmgtaken."\r\n\r\n";
			$mail .= "Involved parties:\r\n\r\n";
		}

		foreach ($this->involvedparties_ as $inv)
		{
			$pilot = new Pilot($inv->getPilotID());
			$corp = new Corporation($inv->getCorpID());
			$alliance = new Alliance($inv->getAllianceID());

			$weapon = $inv->getWeapon();
			$ship = $inv->getShip();
			if ($pilot->getName() == $weapon->getName())
			{
				$name = $pilot->getName()." / ".$corp->getName();
			}
			else
			{
				$name = $pilot->getName();
			}

			$mail .= "Name: ".$name;
			if ($pilot->getID() == $this->getFBPilotID())
			{
				$mail .= " (laid the final blow)";
			}
			$mail .= "\r\n";

			if ($pilot->getName() != $weapon->getName())
			{
				$mail .= "Security: ".$inv->getSecStatus()."\r\n";
				$mail .= "Corp: ".$corp->getName()."\r\n";
				if ($alliance->isFaction())
				{
					$mail .= "Alliance: None\r\n";
					$mail .= "Faction: ".$alliance->getName()."\r\n";
				}
				else
				{
					$mail .= "Alliance: ".$alliance->getName()."\r\n";
					$mail .= "Faction: None\r\n";
				}
				$mail .= "Ship: ".$ship->getName()."\r\n";
				$mail .= "Weapon: ".$weapon->getName()."\r\n";
				$mail .= "Damage Done: ".$inv->dmgdone_."\r\n";
			}
			else
			{
				$mail .= "Damage Done: ".$inv->dmgdone_."\r\n";
			}
			$mail .= "\r\n";
		}

		if (count($this->destroyeditems_) > 0)
		{
			$mail .= "\r\nDestroyed items:\r\n\r\n";

			foreach($this->destroyeditems_ as $destroyed)
			{
				$item = $destroyed->getItem();
				$mail .= $item->getName();
				if ($destroyed->getQuantity() > 1)
					$mail .= ", Qty: ".$destroyed->getQuantity();
				if ($destroyed->getLocationID() == 4) // cargo
					$mail .= " (Cargo)";
				if ($destroyed->getLocationID() == 6) // drone
					$mail .= " (Drone Bay)";
				$mail .= "\r\n";
			}
		}

		if (count($this->droppeditems_) > 0)
		{
			$mail .= "\r\nDropped items:\r\n\r\n";

			foreach($this->droppeditems_ as $dropped)
			{
				$item = $dropped->getItem();
				$mail .= $item->getName();
				if ($dropped->getQuantity() > 1)
					$mail .= ", Qty: ".$dropped->getQuantity();
				if ($dropped->getLocationID() == 4) // cargo
					$mail .= " (Cargo)";
				if ($dropped->getLocationID() == 6) // drone
					$mail .= " (Drone Bay)";
				$mail .= "\r\n";
			}
		}

		if ($this->id_ && config::get('km_cache_enabled')) file_put_contents(KB_MAILCACHEDIR."/".$this->getID().".txt", $mail);

		$this->mail = $mail;

		return $mail;
	}

	function getDupe($checkonly = false)
	{
		if (!$checkonly)
		{
			if($this->dupeid_ != 0) return $this->dupeid_;
			$this->execQuery();
		}
		$this->dupeid_ = 0;
		$qry = DBFactory::getDBQuery(true);
		if (!$this->getFBPilotID() || !$this->victimid_)
			return 0;
		if($this->externalid_)
		{
			$sql = "SELECT kll_id FROM kb3_kills WHERE kll_external_id = ".
				$this->externalid_;
			$qry->execute($sql);
			if($qry->recordCount())
			{
				$row = $qry->getRow();
				$this->dupeid_ = $row['kll_id'];
				return $row['kll_id'];
			}
		}
		if($this->hash)
		{
			$sql = "SELECT kll_id FROM kb3_mails WHERE kll_hash = 0x".
				bin2hex($this->hash);
			$qry->execute($sql);
			if($qry->recordCount())
			{
				$row = $qry->getRow();
				$this->dupeid_ = $row['kll_id'];
				return $row['kll_id'];
			}
		}
		$sql = "SELECT kll_id
                    FROM kb3_kills
                    WHERE kll_timestamp ='".$this->timestamp_."'
                    AND kll_victim_id = ".$this->victimid_."
                    AND kll_ship_id = ".$this->victimship_->getID()."
                    AND kll_system_id = ".$this->solarsystem_->getID()."
                    AND kll_fb_plt_id = ".$this->getFBPilotID()."
                    AND kll_dmgtaken = ".intval($this->dmgtaken);
		$sql .= "             AND kll_id != ".$this->id_;
		$qry->execute($sql);
		$qryinv = DBFactory::getDBQuery(true);

		while ($row = $qry->getRow())
		{
			$kll_id = $row['kll_id'];
			// No involved parties found to differentiate kills
			if(empty($this->involvedparties_))
			{
				$this->dupeid_ = $kll_id;
				return $kll_id;
			}

			// Check that all involved parties we know of are on the kill
			// and did the same damage.
			$invList = array();
			foreach($this->involvedparties_ as $inv)
				$invList[] = '('.$inv->getPilotID().','.intval($inv->dmgdone_).')';
			$sql = 'SELECT COUNT(*) as count FROM kb3_inv_detail WHERE ind_kll_id = '.
				$kll_id.' AND (ind_plt_id,ind_dmgdone) IN ('.implode(',', $invList).')';

			$qryinv->execute($sql);
			$row = $qryinv->getRow();
			if($row['count'] == count($this->involvedparties_))
			{
				$this->dupeid_ = $kll_id;
				return $kll_id;
			}
		}
	}

	function execQuery()
	{
		if (!$this->executed)
		{
			$qry = DBFactory::getDBQuery();

			$sql = "select kll.kll_id, kll.kll_timestamp, plt.plt_name,
                          crp.crp_name, ali.all_name, ali.all_id, kll.kll_ship_id,
                          kll.kll_system_id, kll.kll_ship_id, kll.kll_external_id,
                          kll.kll_victim_id, plt.plt_externalid, kll.kll_isk_loss,
                          kll.kll_crp_id, kll.kll_points, kll.kll_isk_loss,
                          fbplt.plt_id as fbplt_id,
                          fbplt.plt_externalid as fbplt_externalid,
                          fbcrp.crp_id as fbcrp_id,
                          fbali.all_id as fbali_id,
                          fbplt.plt_name as fbplt_name,
                          fbcrp.crp_name as fbcrp_name,
                          fbali.all_name as fbali_name,
                          kll_dmgtaken
                     from kb3_kills kll, kb3_pilots plt, kb3_corps crp,
                          kb3_alliances ali, kb3_alliances fbali, kb3_corps fbcrp,
                          kb3_pilots fbplt, kb3_inv_detail fb
                    where kll.kll_id = '".$this->id_."'
                      and plt.plt_id = kll.kll_victim_id
                      and crp.crp_id = kll.kll_crp_id
                      and ali.all_id = kll.kll_all_id
					  and fb.ind_kll_id = kll.kll_id
					  and fb.ind_plt_id = kll.kll_fb_plt_id
                      and fbali.all_id = fb.ind_all_id
                      and fbcrp.crp_id = fb.ind_crp_id
                      and fbplt.plt_id = kll.kll_fb_plt_id";

			$qry->execute($sql);
			$row = $qry->getRow();
			if (!$row)
			{
				$this->valid_ = false;
				return false;
			}
			else
			{
				$this->valid_ = true;
			}

			$this->timestamp_ = $row['kll_timestamp'];
			$this->setSolarSystem(new SolarSystem($row['kll_system_id']));
			$this->setVictim(new Pilot($row['kll_victim_id'], $row['plt_externalid'], $row['plt_name'], $row['kll_crp_id']));
			$this->setVictimID($row['kll_victim_id']);
			$this->setVictimName($row['plt_name']);
			$this->setVictimCorpID($row['kll_crp_id']);
			$this->setVictimCorpName($row['crp_name']);
			$this->setVictimAllianceID($row['all_id']);
			$this->setVictimAllianceName($row['all_name']);
			$this->setVictimShip(new Ship($row['kll_ship_id']));
			$this->setFBPilot(new Pilot($row['fbplt_id'], $row['fbplt_externalid'], $row['fbplt_name'], $row['fbcrp_id']));
			$this->setFBPilotID($row['fbplt_id']);
			$this->setFBPilotName($row['fbplt_name']);
			$this->setFBCorpID($row['fbcrp_id']);
			$this->setFBCorpName($row['fbcrp_name']);
			$this->setFBAllianceID($row['fbali_id']);
			$this->setFBAllianceName($row['fbali_name']);
			$this->setKillPoints($row['kll_points']);
			$this->setExternalID($row['kll_external_id']);
			$this->setISKLoss($row['kll_isk_loss']);
			//$this->plt_ext_ = $row['plt_externalid'];
			$this->fbplt_ext_ = $row['fbplt_externalid'];
			$this->VictimDamageTaken = $row['kll_dmgtaken'];
			$this->dmgtaken = $row['kll_dmgtaken'];

			// involved
			if($this->fullinvolved_)
			{
				$sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status,
					ind_shp_id, ind_wep_id, typeName, ind_dmgdone,
					shp_id, shp_name, shp_externalid, shp_class, scl_class,
					plt_name, plt_externalid, crp_name, crp_external_id, all_name, all_external_id
					from kb3_inv_detail
					join kb3_pilots on ind_plt_id = plt_id
					join kb3_corps on ind_crp_id = crp_id
					join kb3_alliances on ind_all_id = all_id
					join kb3_ships on ind_shp_id = shp_id
					join kb3_ship_classes on shp_class = scl_id
					join kb3_invtypes on ind_wep_id = typeID
					where ind_kll_id = ".$this->getID()."
					order by ind_order";

				$qry->execute($sql) or die($qry->getErrorMsg());
				while ($row = $qry->getRow())
				{
					$pilot = new Pilot($row['ind_plt_id'], $row['plt_externalid'], $row['plt_name'], $row['ind_crp_id']);

					$corp = new Corporation($row['ind_crp_id']);
					$corp->name_ = $row['crp_name'];
					$corp->alliance_ = $row['ind_all_id'];
					$corp->externalid_ = $row['crp_external_id'];

					$alliance = new Alliance($row['ind_all_id']);
					$alliance->name_ = $row['all_name'];
					$alliance->externalid_ = $row['all_external_id'];


					$ship->shipclass_ = new ShipClass($row['shp_class'], $row['scl_class']);
					$ship->shipclass_->setName($row['scl_class']);

					$ship = new Ship($row['shp_id'], $row['shp_externalid'], $row['shp_name'], $ship->shipclass_);
					//$ship->externalid_ = $row['shp_externalid'];
					//$ship->shipname_ = $row['shp_name'];

					$weapon = new Item($row['ind_wep_id']);
					$weapon->row_['typeName'] = $row['typeName'];
					$weapon->row_['typeID'] = $row['ind_wep_id'];
					$weapon->row_['itm_externalid'] = $row['ind_wep_id'];

					$involved = new DetailedInv($pilot,
						$row['ind_sec_status'],
						$corp,
						$alliance,
						$ship,
						$weapon,
						$row['ind_dmgdone']);
					array_push($this->involvedparties_, $involved);
				}
			}
			else
			{
				$sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status,
					ind_shp_id, ind_wep_id, ind_dmgdone
					from kb3_inv_detail
					where ind_kll_id = ".$this->getID()."
					order by ind_order";

				$qry->execute($sql) or die($qry->getErrorMsg());
				while ($row = $qry->getRow())
				{
					$involved = new InvolvedParty($row['ind_plt_id'],
						$row['ind_crp_id'],
						$row['ind_all_id'],
						$row['ind_sec_status'],
						new Ship($row['ind_shp_id']),
						new Item($row['ind_wep_id']),
						$row['ind_dmgdone']);
					array_push($this->involvedparties_, $involved);
				}
			}
			$destroyedlist = new ItemList(null, true);
			$destroyedlist->addKillDestroyed($this->id_);
			while($item = $destroyedlist->getItem())
			{
				$destroyed = new DestroyedItem($item,
					$item->row_['itd_quantity'],
					$item->row_['itl_location'],
					$item->row_['itd_itl_id']);
				array_push($this->destroyeditems_, $destroyed);
			}
			$droppedlist = new ItemList(null, true);
			$droppedlist->addKillDropped($this->id_);
			while($item = $droppedlist->getItem())
			{
				$dropped = new DroppedItem($item,
					$item->row_['itd_quantity'],
					$item->row_['itl_location'],
					$item->row_['itd_itl_id']);
				array_push($this->droppeditems_, $dropped);
			}
		}
		$this->executed = true;
	}

	function isClassified()
	{
		if(!$this->timestamp_) $this->execQuery();
		if (config::get('kill_classified'))
		{
			if (user::role('classified_see'))
			{
				return false;
			}

			$offset = config::get('kill_classified')*3600;
			if (config::get('date_gmtime'))
			{
				$time = time()-date('Z');
			}
			else
			{
				$time = time();
			}
			if (strtotime($this->timestamp_) > $time-$offset)
			{
				return true;
			}
		}
		return false;
	}

	function getClassifiedTime()
	{
		if (config::get('kill_classified'))
		{
			$offset = config::get('kill_classified')*3600;
			if (config::get('date_gmtime'))
			{
				$time = time()-date('Z');
			}
			else
			{
				$time = time();
			}
			if (strtotime($this->timestamp_) > $time-$offset)
			{
				return ($offset-$time+strtotime($this->timestamp_));
			}
		}
		return 0;
	}

	function getInvolvedPartyCount()
	{
		if(isset($this->involvedcount_)) return $this->involvedcount_;
		$qry = DBFactory::getDBQuery();
		$qry->execute("select count(*) inv from kb3_inv_detail where ind_kll_id = ". $this->id_);
		$result = $qry->getRow();
		$this->involvedcount_ = $result['inv'];
		return $result['inv'];
	}

	// Set the number of involved parties - used by killlist
	function setInvolvedPartyCount($invcount = 0)
	{
		$this->involvedcount_ = $invcount;
	}

	function setDetailedInvolved()
	{
		$this->fullinvolved_ = true;
	}
	function exists()
	{
		if(!isset($this->valid_)) $this->execQuery();
		return $this->valid_;
	}

	//! Count all kills by board owner related to this kill
	function relatedKillCount()
	{
		// No details for classified kills.
		if($this->isClassified()) {
			return 0;
		}
		if($this->relatedkillcount_) {
			return $this->relatedkillcount_;
		}

		if(config::get('cfg_pilotid') && config::get('cfg_allianceid')
			|| config::get('cfg_pilotid') && config::get('cfg_corpid')
			|| config::get('cfg_corpid') && config::get('cfg_allianceid')) {
			$sql ="SELECT COUNT(DISTINCT ind_kll_id) AS kills FROM kb3_inv_detail INNER JOIN
				kb3_kills ON (kll_id = ind_kll_id) WHERE
				ind_timestamp <= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) + 60 * 60))."'
				AND ind_timestamp >= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) - 60 * 60))."'
				AND kll_system_id = ".$this->getSystem()->getID();
			$sqlinv = array();
			if(config::get('cfg_allianceid'))
				$sqlinv[] = "ind_all_id in (".implode(",", config::get('cfg_allianceid')).")";
			if(config::get('cfg_corpid'))
				$sqlinv[] = "ind_crp_id in (".implode(",", config::get('cfg_corpid')).")";
			if(config::get('cfg_pilotid'))
				$sqlinv[] = "ind_plt_id in (".implode(",", config::get('cfg_pilotid')).")";
			$sql .= " AND (".implode(" OR ", $sqlinv).")";
		} else if(config::get('cfg_allianceid')) {
			$sql ="SELECT COUNT(DISTINCT ina_kll_id) AS kills FROM kb3_inv_all INNER JOIN
				kb3_kills ON (kll_id = ina_kll_id) WHERE
				ina_all_id in (".implode(",", config::get('cfg_allianceid')).") AND
				ina_timestamp <= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) + 60 * 60))."'
				AND ina_timestamp >= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) - 60 * 60))."'
				AND kll_system_id = ".$this->getSystem()->getID();
		} else if(config::get('cfg_corpid')) {
			$sql ="SELECT COUNT(DISTINCT inc_kll_id) AS kills FROM kb3_inv_crp INNER JOIN
				kb3_kills ON (kll_id = inc_kll_id) WHERE
				inc_crp_id in (".implode(",", config::get('cfg_corpid')).") AND
				inc_timestamp <= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) + 60 * 60))."'
				AND inc_timestamp >= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) - 60 * 60))."'
				AND kll_system_id = ".$this->getSystem()->getID();
		} else if(config::get('cfg_pilotid')) {
			$sql ="SELECT COUNT(DISTINCT ind_kll_id) AS kills FROM kb3_inv_detail INNER JOIN
				kb3_kills ON (kll_id = ind_kll_id) WHERE
				ind_plt_id in (".implode(",", config::get('cfg_pilotid')).") AND
				ind_timestamp <= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) + 60 * 60))."'
				AND ind_timestamp >= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) - 60 * 60))."'
				AND kll_system_id = ".$this->getSystem()->getID();
		} else {
			$sql ="SELECT COUNT(kll_id) AS kills FROM kb3_kills WHERE
				kll_timestamp <= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) + 60 * 60))."'
				AND kll_timestamp >= '".(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) - 60 * 60))."'
				AND kll_system_id = ".$this->getSystem()->getID();
		}
		$sql .= " /* related kill count */ ";
		$qry = DBFactory::getDBQuery();
		if(!$qry->execute($sql)) {
			return 0;
		}
		$res=$qry->getRow();
		$this->relatedkillcount_ = $res['kills'];
		// Do not cache between page loads.
		return $this->relatedkillcount_;
	}

	/**
	 * Count all losses by board owner related to this kill
	 *
	 * @return integer
	 */
	function relatedLossCount()
	{
		// No details for classified kills.
		if($this->isClassified()) {
			return 0;
		}
		if($this->relatedlosscount_) {
			return $this->relatedlosscount_;
		}
		$sql="SELECT count(kll.kll_id) AS losses FROM kb3_kills kll ";
		$sql.="WHERE kll.kll_system_id = ".$this->getSystem()->getID().
			" AND kll.kll_timestamp <= '".
			(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) + 60 * 60)).
			"' AND kll.kll_timestamp >= '".
			(date('Y-m-d H:i:s',strtotime($this->getTimeStamp()) - 60 * 60))."'";
		$sqlInv = array();
		$sqlVic = array();
		$inv = false;
		if(config::get('cfg_allianceid')) {
			$sqlInv[] = "EXISTS (SELECT * FROM kb3_inv_detail WHERE ind_kll_id = kll.kll_id".
				" AND ind_all_id NOT IN (".implode(",", config::get('cfg_allianceid')).") LIMIT 1)";
			$sqlVic[] = "kll.kll_all_id IN (".implode(",", config::get('cfg_allianceid')).")";
			$inv = true;
		}
		if(config::get('cfg_corpid')) {
			$sqlInv[] = "EXISTS (SELECT * FROM kb3_inv_detail WHERE ind_kll_id = kll.kll_id".
				" AND ind_crp_id NOT IN (".implode(",", config::get('cfg_corpid')).") LIMIT 1)";
			$sqlVic[] .= "kll.kll_crp_id IN (".implode(",", config::get('cfg_corpid')).")";
			$inv = true;
		}
		if(config::get('cfg_pilotid')) {
			$sqlInv[] = "EXISTS (SELECT * FROM kb3_inv_detail WHERE ind_kll_id = kll.kll_id".
				" AND ind_plt_id NOT IN (".implode(",", config::get('cfg_pilotid')).") LIMIT 1)";
			$sqlVic[] .= "kll.kll_victim_id IN (".implode(",", config::get('cfg_pilotid')).")";
			$inv = true;
		}
		if($inv) {
			$sql .= " AND (".implode(' OR ', $sqlInv).") AND (".implode(' OR ', $sqlVic).") ";
		}
		$sql .= "/* related loss count */";
		$qry = DBFactory::getDBQuery();
		if(!$qry->execute($sql)) {
			return 0;
		}
		$res=$qry->getRow();
		$this->relatedlosscount_ = $res['losses'];
		return $this->relatedlosscount_;
	}

	function countComment($kll_id)
	{
		if(isset($this->commentcount_)) return $this->commentcount_;
		$qry = DBFactory::getDBQuery();
		$sql = "SELECT count(id) as comments FROM kb3_comments WHERE kll_id = '$kll_id' AND (site = '".KB_SITE."' OR site IS NULL)";
		// return 0 if query fails. May be incorrect but is harmless here
		if(!$qry->execute($sql)) return 0;
		$result = $qry->getRow();
		$this->commentcount_ = $result['comments'];
		return $result['comments'];
	}

	//! Set the number of comments - used by killlist
	function setCommentCount($comcount = 0)
	{
		$this->commentcount_ = $comcount;
	}

	function setID($id)
	{
		$this->id_ = $id;
	}

	function setTimeStamp($timestamp)
	{
		$this->timestamp_ = $timestamp;
	}

	function setSolarSystem($solarsystem)
	{
		$this->solarsystem_ = $solarsystem;
	}

	function setSolarSystemName($solarsystemname)
	{
		$this->solarsystemname_ = $solarsystemname;
	}

	function setSolarSystemSecurity($solarsystemsecurity)
	{
		$this->solarsystemsecurity_ = $solarsystemsecurity;
	}

	function setExternalID($externalid)
	{
		if($externalid) $this->externalid_ = $externalid;
		else $this->externalid_ = 0;
	}

	function setVictim($victim)
	{
		$this->victim_ = $victim;
	}

	function setVictimID($victimid)
	{
		$this->victimid_ = $victimid;
	}

	function setVictimName($victimname)
	{
		$this->victimname_ = $victimname;
	}

	function setVictimCorpID($victimcorpid)
	{
		$this->victimcorpid_ = $victimcorpid;
	}

	function setVictimCorpName($victimcorpname)
	{
		$this->victimcorpname_ = $victimcorpname;
	}

	function setVictimAllianceID($victimallianceid)
	{
		$this->victimallianceid_ = $victimallianceid;
	}

	function setVictimAllianceName($victimalliancename)
	{
		$this->victimalliancename_ = $victimalliancename;
	}

	function setVictimShip($victimship)
	{
		$this->victimship_ = $victimship;
	}

	function setVictimShipName($victimshipname)
	{
		$this->victimshipname_ = $victimshipname;
	}

	function setVictimShipExternalID($victimshipexternalid)
	{
		$this->victimshipexternalid_ = $victimshipexternalid;
	}

	function setVictimShipClassName($victimshipclassname)
	{
		$this->victimshipclassname_ = $victimshipclassname;
	}

	function setVictimShipValue($victimshipvalue)
	{
		$this->victimshipvalue_ = $victimshipvalue;
	}

	function setFBPilot($fbpilot)
	{
		$this->fbpilot_ = $fbpilot;
	}

	function setFBPilotID($fbpilotid)
	{
		$this->fbpilotid_ = $fbpilotid;
	}

	function setFBPilotName($fbpilotname)
	{
		$npc = strpos($fbpilotname, "#");
		if ($npc === false)
		{
			$this->fbpilotname_ = $fbpilotname;
		}
		else
		{
			$name = explode("#", $fbpilotname);
			$plt = new Item($name[2]);
			$this->fbpilotname_ = $plt->getName();
		}
	}

	function setFBCorpID($fbcorpid)
	{
		$this->fbcorpid_ = $fbcorpid;
	}

	function setFBCorpName($fbcorpname)
	{
		$this->fbcorpname_ = $fbcorpname;
	}

	function setFBAllianceID($fballianceid)
	{
		$this->fballianceid_ = $fballianceid;
	}

	function setFBAllianceName($fballiancename)
	{
		$this->fballiancename_ = $fballiancename;
	}
	function setKillPoints($killpoints)
	{
		$this->killpoints_ = $killpoints;
	}
	//! Set the ISK loss value for this kill.
	function setISKLoss($isk)
	{
		$this->iskloss_ = $isk;
	}
	//! Calculate the current cost of a ship loss excluding blueprints.

	//! \param $update set true to update all-time summaries.
	function calculateISKLoss($update = true)
	{
		$value = 0;
		foreach($this->destroyeditems_ as $itd)
		{
			$item = $itd->getItem();
			if(strpos($item->getName(), "Blueprint") === FALSE) $value += $itd->getValue() * $itd->getQuantity();
		}
		if(config::get('kd_droptototal'))
		{
			foreach($this->droppeditems_ as $itd)
			{
				$item = $itd->getItem();
				if(strpos($item->getName(), "Blueprint") === FALSE) $value += $itd->getValue() * $itd->getQuantity();
			}
		}
		$value += $this->victimship_->getPrice();
		if($update)
		{
			$qry = DBFactory::getDBQuery();
			$qry->execute("UPDATE kb3_kills SET kll_isk_loss = '$value' WHERE
				kll_id = '".$this->id_."'");
			if($this->iskloss_)
			{
				summaryCache::update($this, $value - $this->iskloss_);
			}
		}
		$this->iskloss_ = $value;
		return $value;
	}

	function calculateKillPoints()
	{
		$ship = $this->getVictimShip();
		$shipclass = $ship->getClass();
		$vicpoints = $shipclass->getPoints();
		$maxpoints = round($vicpoints * 1.2);

		foreach ($this->involvedparties_ as $inv)
		{
			$shipinv = $inv->getShip();
			$shipclassinv = $shipinv->getClass();
			$invpoints += $shipclassinv->getPoints();
		}

		$gankfactor = $vicpoints / ($vicpoints + $invpoints);
		$points = ceil($vicpoints * ($gankfactor / 0.75));

		if ($points > $maxpoints) $points = $maxpoints;

		$points = round($points, 0);
		return $points;
	}

	function add($id = null)
	{
		// If value isn't already calculated then do so now. Don't update the
		// stored value since at this point it does not exist.
		if(!$this->iskloss_) $this->calculateISKLoss(false);

		// Start a transaction here to capture the duplicate check.
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);
		// Set these to make sure we don't try to load the kill from the db before it exists.
		$this->executed = true;
		$this->valid_ = true;
		//Always recalculate the hash ourselves before posting.
		$this->hash = false;
		$this->getHash(false,false);

		$this->getDupe(true);
		if ($this->dupeid_ == 0)
		{
			$this->realadd();
		}
//		elseif (config::get('readd_dupes'))
//		{
//			$this->id_ = $this->dupeid_;
//			$this->remove(false);
//			$this->realadd($this->dupeid_);
//			$this->id_ = -1;
//		}
		else
		{
			$this->id_ = -1;
		}
		$qry->autocommit(true);
		return $this->id_;
	}

	function realadd($id = null)
	{
		if ( $this->timestamp_ == "" || !$this->getVictim()->getID() || !$this->victimship_->getID() || !$this->solarsystem_->getID() ||
			!$this->victimallianceid_ || !$this->victimcorpid_ || !$this->getFBAllianceID() || !$this->getFBCorpID() ||
			!$this->getFBPilotID() ) return 0;
		if ($id == null)
		{
			$qid = 'null';
		}
		else
		{
			$qid = $id;
		}
		if (!$this->dmgtaken)
		{
			$this->dmgtaken = 0;
		}

		$qry = DBFactory::getDBQuery();
		$sql = "INSERT INTO kb3_kills
            (kll_id , kll_timestamp , kll_victim_id , kll_all_id , kll_crp_id , kll_ship_id , kll_system_id , kll_fb_plt_id , kll_points , kll_dmgtaken, kll_external_id, kll_isk_loss)
            VALUES (".$qid.",
                    date_format('".$this->timestamp_."', '%Y.%m.%d %H:%i:%s'),
            ".$this->victimid_.",
            ".$this->victimallianceid_.",
            ".$this->victimcorpid_.",
            ".$this->victimship_->getID().",
            ".$this->solarsystem_->getID().",
            ".$this->getFBPilotID().",
            ".$this->calculateKillPoints().",
            ".$this->dmgtaken.", ";
		if($this->externalid_) $sql .= $this->externalid_.", ";
		else $sql .= "NULL, ";
		$sql .= $this->getISKLoss()." )";
		$qry->autocommit(false);
		if(!$qry->execute($sql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			//If the query is causing errors here there's no point going on
			return false;
		}

		if ($id)
		{
			$this->id_ = $id;
		}
		else
		{
			$this->id_ = $qry->getInsertID();
		}
		if(!$this->id_)
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}
		// involved
		$order = 0;
		$invall = array();
		$invcrp = array();
		$involveddsql = 'insert into kb3_inv_detail
                    (ind_kll_id, ind_timestamp, ind_plt_id, ind_sec_status, ind_all_id, ind_crp_id, ind_shp_id, ind_wep_id, ind_order, ind_dmgdone )
                    values ';
		$involvedasql = 'insert into kb3_inv_all
                    (ina_kll_id, ina_all_id, ina_timestamp) values ';
		$involvedcsql = 'insert into kb3_inv_crp
                    (inc_kll_id, inc_crp_id, inc_timestamp) values ';
		$notfirstd = false;
		$notfirsta = false;
		$notfirstc = false;
		usort($this->involvedparties_, array('Kill', 'involvedComparator'));
		foreach ($this->involvedparties_ as $inv)
		{
			$ship = $inv->getShip();
			$weapon = $inv->getWeapon();
			if (!$inv->getPilotID() || $inv->getSecStatus() == "" || !$inv->getAllianceID() || !$inv->getCorpID() || !$ship->getID() || !$weapon->getID())
			{
				$this->remove();
				return 0;
			}

			if (!$inv->dmgdone_)
			{
				$inv->dmgdone_ = 0;
			}
			if($notfirstd) $involveddsql .= ", ";
			$involveddsql .= "( ".$this->getID().", date_format('".$this->timestamp_."', '%Y.%m.%d %H:%i:%s'), "
				.$inv->getPilotID().", '".$inv->getSecStatus()."', "
				.$inv->getAllianceID().", ".$inv->getCorpID().", ".$ship->getID().", "
				.$weapon->getID().", ".$order++.", ".$inv->dmgdone_.")";
			$notfirstd = true;
			if(!in_array($inv->getAllianceID(), $invall))
			{
				if($notfirsta) $involvedasql .= ", ";
				$involvedasql .= "( ".$this->getID().", ".$inv->getAllianceID()
					.", date_format('".$this->timestamp_."', '%Y.%m.%d %H:%i:%s'))";
				$notfirsta = true;
				$invall[] = $inv->getAllianceID();
			}
			if(!in_array($inv->getCorpID(), $invcrp))
			{
				if($notfirstc) $involvedcsql .= ", ";
				$involvedcsql .= "( ".$this->getID().", ".$inv->getCorpID()
					.", date_format('".$this->timestamp_."', '%Y.%m.%d %H:%i:%s'))";
				$notfirstc = true;
				$invcrp[] = $inv->getCorpID();
			}

		}
		if($notfirstd && !$qry->execute($involveddsql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}
		if($notfirsta && !$qry->execute($involvedasql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}
		if($notfirstc && !$qry->execute($involvedcsql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}

		// destroyed
		$notfirstitd=false;
		$itdsql = "insert into kb3_items_destroyed (itd_kll_id, itd_itm_id, itd_quantity, itd_itl_id) values ";
		foreach ($this->destroyeditems_ as $dest)
		{
			$item = $dest->getItem();
			$loc_id = $dest->getLocationID();
			if (!is_numeric($this->getID()) || !is_numeric($item->getID()) || !is_numeric($dest->getQuantity()) || !is_numeric($loc_id))
			{
				trigger_error('error with destroyed item.', E_USER_WARNING);
				var_dump($dest);
				exit;
				continue;
			}

			if($notfirstitd) $itdsql .= ", ";
			$itdsql .= "( ".$this->getID().", ".$item->getID().", ".$dest->getQuantity().", ".$loc_id." )";
			$notfirstitd = true;
		}
		if($notfirstitd &&!$qry->execute($itdsql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}

		// dropped
		$notfirstitd=false;
		$itdsql = "insert into kb3_items_dropped (itd_kll_id, itd_itm_id, itd_quantity, itd_itl_id) values ";
		foreach ($this->droppeditems_ as $dest)
		{
			$item = $dest->getItem();
			$loc_id = $dest->getLocationID();
			if (!is_numeric($this->getID()) || !is_numeric($item->getID()) || !is_numeric($dest->getQuantity()) || !is_numeric($loc_id))
			{
				trigger_error('error with dropped item.', E_USER_WARNING);
				var_dump($dest);
				exit;
				continue;
			}

			if($notfirstitd) $itdsql .= ", ";
			$itdsql .= "( ".$this->getID().", ".$item->getID().", ".$dest->getQuantity().", ".$loc_id." )";
			$notfirstitd = true;
		}
		if($notfirstitd &&!$qry->execute($itdsql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}

		$sql = "INSERT INTO kb3_mails (  `kll_id`, `kll_timestamp`, `kll_external_id`, `kll_hash`, `kll_trust`, `kll_modified_time`)".
			"VALUES(".$this->getID().", '".$this->getTimeStamp()."', ";
		if($this->externalid_) $sql .= $this->externalid_.", ";
		else $sql .= "NULL, ";
			$sql .= "'".$qry->escape($this->getHash(false, false))."', 0, UTC_TIMESTAMP())";
		if(!$qry->execute($sql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}

		//Update cache tables.
		summaryCache::addKill($this);
		$qry->autocommit(true);
		// call the event that we added this mail
		event::call('killmail_added', $this);
		cache::notifyKillAdded();
		return $this->id_;
	}

	function remove($delcomments = true, $permanent = true)
	{
		if (!$this->id_)
			return;
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);
		summaryCache::delKill($this);

		event::call('killmail_delete', $this);

		$qry->execute("delete from kb3_kills where kll_id = ".$this->id_);
		$qry->execute("delete from kb3_inv_detail where ind_kll_id = ".$this->id_);
		$qry->execute("delete from kb3_inv_all where ina_kll_id = ".$this->id_);
		$qry->execute("delete from kb3_inv_crp where inc_kll_id = ".$this->id_);
		$qry->execute("delete from kb3_items_destroyed where itd_kll_id = ".$this->id_);
		$qry->execute("delete from kb3_items_dropped where itd_kll_id = ".$this->id_);
		// Don't remove comments when readding a kill
		if ($delcomments)
		{
			$qry->execute("delete from kb3_comments where kll_id = ".$this->id_);
			if ($permanent)
				$qry->execute("UPDATE kb3_mails SET kll_trust = -1, kll_modified_time = UTC_TIMESTAMP() WHERE kll_id = ".$this->id_);
			else
				$qry->execute("DELETE FROM kb3_mails WHERE kll_id = ".$this->id_);
		}
		$qry->autocommit(true);
	}

	function addInvolvedParty($involved)
	{
		array_push($this->involvedparties_, $involved);
	}

	function addDestroyedItem($destroyed)
	{
		array_push($this->destroyeditems_, $destroyed);
	}

	function addDroppedItem($dropped)
	{
		array_push($this->droppeditems_, $dropped);
	}

	/*! Return the array of involved parties.
	*
	* \return InvolvedParty[].
	*
	*/
	function getInvolved()
	{
		if(!$this->involvedparties_) $this->execQuery();
		return $this->involvedparties_;
	}
	function setHash($hash)
	{
		if(strlen($hash) > 16) $this->hash = pack("H*", $hash);
		else $this->hash = $hash;
	}
	function getHash($hex = false, $update = true)
	{
		if($this->hash)
		{
			if($hex) return bin2hex($this->hash);
			else return $this->hash;
		}
		$qry = DBFactory::getDBQuery();
		// Get the mail and trust as well since we're fetching the row anyway.
		if($this->id_)
			$qry->execute("SELECT kll_hash, kll_trust FROM kb3_mails WHERE kll_id = ".$this->id_);
		if($qry->recordCount())
		{
			$row = $qry->getRow();
			$this->hash = $row['kll_hash'];
			$this->trust = $row['kll_trust'];
		}
		else
		{
			$this->hash = Parser::hashMail($this->getRawMail());
			if($this->hash === false) return false;
			if($update)
			{
				if($this->id_ && $this->externalid_)
				{
					$sql = "INSERT IGNORE INTO kb3_mails (  `kll_id`, `kll_timestamp`, ".
						"`kll_external_id`, `kll_hash`, `kll_trust`, `kll_modified_time`)".
						"VALUES(".$this->getID().", '".$this->getTimeStamp()."', ".
						$this->externalid_.", '".$qry->escape($this->hash)."', ".
						$this->trust.", UTC_TIMESTAMP())";
				}
				else if($this->id_)
				{
					$sql = "INSERT IGNORE INTO kb3_mails (  `kll_id`, `kll_timestamp`, ".
						"`kll_hash`, `kll_trust`, `kll_modified_time`)".
						"VALUES(".$this->getID().", '".$this->getTimeStamp()."', ".
						"'".$qry->escape($this->hash)."', ".
						$this->trust.", UTC_TIMESTAMP())";
				}
				if($this->id_) $qry->execute($sql);
			}
		}
		if($hex) return bin2hex($this->hash);
		else return $this->hash;
	}
	function setRawMail($mail)
	{
		$this->mail = $mail;
	}
	public function setTrust($trust)
	{
		$this->trust = intval($trust);
	}
	public function getTrust()
	{
		if(!is_null($this->trust)) return $this->trust;
		if(!$this->getHash()) return $this->trust;
		$this->trust = 0;
		return $this->trust;
	}
	private static function involvedComparator($a, $b)
	{
		if($a->dmgdone_ != $b->dmgdone_) {
			return $a->dmgdone_ > $b->dmgdone_ ? -1 : 1;
		} else {
			$pilota = new Pilot($a->getPilotID());
			$pilotb = new Pilot($b->getPilotID());
			return strcmp($pilota->getName(), $pilotb->getName());
		}
	}
}

class InvolvedParty
{
	protected $pilotid_;
	protected $corpid_;
	protected $allianceid_;
	protected $secstatus_;
	protected $ship_;
	protected $weapon_;
	public $dmgdone_;

	function InvolvedParty($pilotid, $corpid, $allianceid, $secstatus, $ship, $weapon, $dmgdone = 0)
	{
		$this->pilotid_ = $pilotid;
		$this->corpid_ = $corpid;
		$this->allianceid_ = $allianceid;
		$this->secstatus_ = $secstatus;
		$this->ship_ = $ship;
		$this->weapon_ = $weapon;
		$this->dmgdone_ = $dmgdone;
	}

	function getPilotID()
	{
		return $this->pilotid_;
	}

	function getCorpID()
	{
		return $this->corpid_;
	}

	function getAllianceID()
	{
		return $this->allianceid_;
	}

	function getSecStatus()
	{
		return number_format($this->secstatus_, 1);
	}

	function getShip()
	{
		return $this->ship_;
	}

	function getWeapon()
	{
		return $this->weapon_;
	}

	function getDamageDone()
	{
		return $this->dmgdone_;
	}
}

class DestroyedItem
{
	function DestroyedItem($item, $quantity, $location, $locationID = null)
	{
		$this->item_ = $item;
		$this->quantity_ = $quantity;
		$this->location_ = $location;
		$this->locationID_ = $locationID;
	}

	function getItem()
	{
		return $this->item_;
	}

	function getQuantity()
	{
		if ($this->quantity_ == "") $this->quantity = 1;
		return $this->quantity_;
	}
	//! Return value formatted into millions or thousands.
	function getFormattedValue()
	{
		if (!isset($this->value))
		{
			$this->getValue();
		}
		if ($this->value > 0)
		{
			$value = $this->value * $this->getQuantity();
			// Value Manipulation for prettyness.
			if (strlen($value) > 6) // Is this value in the millions?
			{
				$formatted = round($value / 1000000, 2);
				$formatted = number_format($formatted, 2);
				$formatted = $formatted." M";
			}
			elseif (strlen($value) > 3) // 1000's ?
			{
				$formatted = round($value / 1000, 2);

				$formatted = number_format($formatted, 2);
				$formatted = $formatted." K";
			}
			else
			{
				$formatted = number_format($value, 2);
				$formatted = $formatted." isk";
			}
		}
		else
		{
			$formatted = "0 isk";
		}
		return $formatted;
	}

	function getValue()
	{
		if (isset($this->value))
		{
			return $this->value;
		}
		if ($this->item_->row_['itm_value'])
		{
			$this->value = $this->item_->row_['itm_value'];
			return $this->item_->row_['itm_value'];
		}
		elseif ($this->item_->row_['baseprice'])
		{
			$this->value = $this->item_->row_['baseprice'];
			return $this->item_->row_['baseprice'];
		}
		$this->value = 0;
		$qry = DBFactory::getDBQuery();
		$qry->execute("select basePrice, price
					from kb3_invtypes
					left join kb3_item_price on kb3_invtypes.typeID=kb3_item_price.typeID
					where kb3_invtypes.typeID='".$this->item_->getID()."'");
		if ($row = $qry->getRow())
		{
			if ($row['price'])
			{
				$this->value = $row['price'];
			}
			else
			{
				$this->value = $row['basePrice'];
			}
		}
		return $this->value;

		//returns the value of an item
		$value = 0; 				// Set 0 value incase nothing comes back
		$id = $this->item_->getID(); // get Item ID
		$qry = DBFactory::getDBQuery();
		$qry->execute("select itm_value from kb3_items where itm_id= '".$id."'");
		$row = $qry->getRow();
		$value = $row['itm_value'];
		if ($value == '')
		{
			$value = 0;
		}
		return $value;
	}

	function getLocationID()
	{
		if(!is_null($this->locationID_)) return $this->locationID_;
		$id = false;
		if (strlen($this->location_) < 2)
		{
			$id = $this->item_->getSlot();
		}
		else
		{
			$qry = DBFactory::getDBQuery();
			$qry->execute("select itl_id from kb3_item_locations where itl_location = '".$this->location_."'");
			$row = $qry->getRow();
			$id = $row['itl_id'];
		}
		return $id;
	}
}

class DroppedItem extends DestroyedItem
{
	function DroppedItem($item, $quantity, $location, $locationID = null)
	{
		$this->item_ = $item;
		$this->quantity_ = $quantity;
		$this->location_ = $location;
		$this->locationID_ = $locationID;
	}
}

class DetailedInv extends InvolvedParty
{
	private $pilot_;
	private $corp_;
	private $alliance_;
	
	function DetailedInv($pilot, $secstatus, $corp, $alliance, $ship, $weapon, $dmgdone = 0)
	{
		$this->pilot_ = $pilot;
		$this->secstatus_ = $secstatus;
		$this->corp_ = $corp;
		$this->alliance_ = $alliance;
		$this->ship_ = $ship;
		$this->weapon_ = $weapon;
		$this->dmgdone_ = $dmgdone;
	}

	function getPilot()
	{
		return $this->pilot_;
	}

	function getPilotID()
	{
		return $this->pilot_->getID();
	}

	function getCorp()
	{
		return $this->corp_;
	}

	function getCorpID()
	{
		return $this->corp_->getID();
	}

	function getAlliance()
	{
		return $this->alliance_;
	}

	function getAllianceID()
	{
		return $this->alliance_->getID();
	}

}
