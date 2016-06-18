<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * thrown whenever anything goes wrong while handling a kill
 */
class KillException extends Exception {}

/**
 * @package EDK
 */
class Kill extends Cacheable
{
    
        /** @const the base URL for the public CREST killmail endpoint */
        public static $CREST_KILLMAIL_ENDPOINT = '/killmails/';
        
        
        
	/**
	 * The ID for this kill
	 * @var integer
	 */
	private $id = 0;
	/**
	 * The external ID from CCP for this kill
	 * @var integer
	 */
	private $externalid = null;
    /** @var string the crest hash for this kill */
    private $crestHash = null;
	public $involvedparties_ = array();
	public $destroyeditems_ = array();
	public $droppeditems_ = array();
	private $fullinvolved = false;
	private $timestamp = null;
	private $dmgtaken = null;
	private $iskloss = 0;
	private $killpoints = null;
	private $victimid = null;
	private $victimcorp = null;
	private $victimcorpid = null;
	private $victimalliance = null;
	private $victimallianceid = null;
	private $victimshipid = null;
	private $fbpilotid = null;
	private $fbcorpid = null;
	private $fballianceid = null;
	private $solarsystemid = null;
	private $dupeid = 0;
	private $hash = false;
	private $mail = null;
	private $trust = 0;
	private $executed = false;
	private $involvedcount = null;
	private $valid = null;
    private $xCoordinate = null;
    private $yCoordinate = null;
    private $zCoordinate = null;
    /**
     * the nearest location for this kill
     * @var Location 
     */
    private $nearestCelestial = null;
    private $distanceToNearestCelestial = null;

	/**
	 * @param integer $id The ID for this kill
	 * @param boolean $external If true then $id is treated as an external ID.
	 */
	function __construct($id = 0, $external = false)
	{
		$id = intval($id);
		if($id && $external) {
			$qry = DBFactory::getDBQuery(true);
			$qry->execute("SELECT kll_id FROM kb3_kills WHERE kll_external_id = ".$id);
			if($qry->recordCount()) {
				$result = $qry->getRow();
				$this->id = $result['kll_id'];
				$this->externalid = $id;
			} else {
				$this->id = null;
				$this->externalid = null;
			}
		} else {
			$this->id = $id;
			$this->externalid = null;
		}
	}

	/**
	 * Set internal variables.
	 *
	 * @param array $arr Array of values indexed by internal variable name.
	 */
	function setArray($arr)
	{
		foreach($arr as $key=>$val) {
			$this->$key = $val;
		}
	}

	function set($var, $value)
	{
		$this->$var = $value;
	}
	/**
	 * Get the internal ID of this kill.
	 *
	 * @return integer integer value for the internal kill ID.
	 */
	function getID()
	{
		return $this->id;
	}

	/**
	 * Get the external ID of this kill.
	 *
	 * @return integer integer value for the external kill ID.
	 */
	function getExternalID()
	{
		if(is_null($this->externalid)) {
			$this->execQuery();
		}
		return $this->externalid;
	}
	/**
	 * Return the dropped items array for this kill.
	 *
	 * @return array dropped items array for this kill.
	 */
	public function getDroppedItems()
	{
		return $this->droppeditems_;
	}
	/**
	 * Return the destroyed items array for this kill.
	 *
	 * @return array destroyed items array for this kill.
	 */
	public function getDestroyedItems()
	{
		return $this->destroyeditems_;
	}
	/**
	 * @return string
	 */
	function getTimeStamp()
	{
		if(is_null($this->timestamp)) {
			$this->execQuery();
		}
		return $this->timestamp;
	}
	/**
	 * Return the victim Pilot object.
	 *
	 * @return Pilot
	*/
	function getVictim()
	{
		if(!is_null($this->victim)) {
			return $this->victim;
		}
		if(is_null($this->victimid)) {
			$this->execQuery();
		}

		$this->victim = Cacheable::factory('Pilot', $this->victimid);
		return $this->victim;
	}
	/**
	 * Return the victim Corporation.
	 *
	 * @return Corporation
	*/
	function getVictimCorp()
	{
		if(!is_null($this->victimcorp)) {
			return $this->victimcorp;
		}
		if(is_null($this->victimcorpid)) {
			$this->execQuery();
		}

		$this->victimcorp = Cacheable::factory('Corporation', $this->victimcorpid);
		return $this->victimcorp;
	}
	/**
	 * Return the victim Alliance.
	 *
	 * @return Alliance
	*/
	function getVictimAlliance()
	{
		if(!is_null($this->victimalliance)) {
			return $this->victimalliance;
		}
		if(is_null($this->victimallianceid)) {
			$this->execQuery();
		}

		$this->victimalliance = Cacheable::factory('Alliance', $this->victimallianceid);
		return $this->victimalliance;
	}
	/**
	 * Return the amount of damage taken by the victim.
	 * @return integer
	 */
	function getDamageTaken()
	{
		if(is_null($this->dmgtaken)) $this->execQuery();
		return $this->dmgtaken;
	}

	/**
	 * Return the victim's name.
	 * @return string
	 */
	function getVictimName()
	{
		if(isset($this->victimname)) return $this->victimname;
		return $this->getVictim()->getName();
	}

	/**
	 * Return victim Pilot's ID.
	 * @return integer
	 */
	function getVictimID()
	{
		if(!isset($this->victimid)) $this->execQuery();
		return $this->victimid;
	}

	/**
	 * Return victim Pilot's external ID.
	 * @return integer
	 */
	function getVictimExternalID()
	{
		return $this->getVictim()->getExternalID();
	}

	/**
	 * Return victim Pilot's portrait.
	 * @return string
	 */
	function getVictimPortrait($size = 32)
	{
		return $this->getVictim()->getPortraitURL($size);
	}

	/**
	 * Return victim Corporation's ID
	 * @return integer
	 */
	function getVictimCorpID()
	{
		if(!isset($this->victimcorpid)) {
			$this->execQuery();
		}
		return $this->victimcorpid;
	}

	/**
	 * Return victim Corporation's name
	 * @return string
	 */
	function getVictimCorpName()
	{
		return $this->getVictimCorp()->getName();
	}

	/**
	 * Return victim Alliance's name
	 * @return string
	 */
	function getVictimAllianceName()
	{
		return $this->getVictimAlliance()->getName();
	}

	/**
	 * Return victim Faction's name
	 * @return string
	 */
	function getVictimFactionName()
	{
		if($this->getVictimAlliance()->isFaction()) {
			return $this->getVictimAlliance()->getName();
		} else {
			return "None";
		}
	}

	/**
	 * Return victim Alliance's ID
	 * @return integer
	 */
	function getVictimAllianceID()
	{
		if(!isset($this->victimallianceid)) {
			$this->execQuery();
		}
		return $this->victimallianceid;
	}

	/**
	 * Return the SolarSystem this kill took place in.
	 * @return SolarSystem
	 */
	function getSystem()
	{
		if(isset($this->solarsystem)) {
			return $this->solarsystem;
		}
		if(!isset($this->solarsystemid)) {
			$this->execQuery();
		}
		$this->solarsystem = Cacheable::factory('SolarSystem', $this->solarsystemid);
		return $this->solarsystem;
	}

	/**
	 * @return integer
	 */
	function getFBPilotID()
	{
		if(!isset($this->fbpilotid)) {
			$this->execQuery();
		}
		return $this->fbpilotid;
	}

	/**
	 * Return the Final Blow dealer's name.
	 * @return string
	 */
	function getFBPilotName()
	{
		$fbpilot = Cacheable::factory('Pilot', $this->getFBPilotID());
		return $fbpilot->getName();
	}

	/**
	 * Return the Final Blow dealer's Corporation ID.
	 * @return integer
	 */
	function getFBCorpID()
	{
		if(!isset($this->fbcorpid)) {
			$this->execQuery();
		}
		return $this->fbcorpid;
	}

	/**
	 * Return the Final Blow dealer's Corporation Name.
	 * @return string
	 */
	function getFBCorpName()
	{
		$fbcorp = Cacheable::factory('Corporation', $this->getFBCorpID());
		return $fbcorp->getName();
	}

	/**
	 * Return the Final Blow dealer's Alliance ID.
	 * @return integer
	 */
	function getFBAllianceID()
	{
		if(!isset($this->fballianceid)) {
			$this->execQuery();
		}
		return $this->fballianceid;
	}

	/**
	 * Return the Final Blow dealer's Alliance ID.
	 * @return integer
	 */
	function getFBAllianceName()
	{
		$alliance = Cacheable::factory('Alliance', $this->getFBAllianceID());
		return $alliance->getName();
	}

	/**
	 * @return float
	 */
	function getISKLoss()
	{
		if(!isset($this->iskloss)) {
			$this->execQuery();
		}
		return $this->iskloss;
	}

	/**
	 * @return integer
	 */
	function getKillPoints()
	{
		if(!isset($this->killpoints)) {
			$this->execQuery();
		}
		$this->killpoints = $this->calculateKillPoints();
		return $this->killpoints;
	}

	/**
	 * Get name for this Kill's SolarSystem.
	 * @return string
	 */
	function getSolarSystemName()
	{
		return $this->getSystem()->getName();
	}

	/**
	 * Get Security level for this Kill's SolarSystem.
	 * @return float
	 */
	function getSolarSystemSecurity()
	{
		return $this->getSystem()->getSecurity();
	}

	/**
	 * Return the victim's Ship.
	 * @return Ship
	 */
	function getVictimShip()
	{
		if(isset($this->victimship)) {
			return $this->victimship;
		}
		if(!isset($this->victimshipid)) {
			$this->execQuery();
		}
		//TODO: Find out how this can happen and stop it.
		if(!isset($this->victimshipid)) {
			trigger_error("No victim ship id set", E_USER_ERROR);
			return "";
		}
		$this->victimship = Cacheable::factory('Ship', $this->victimshipid);
		return $this->victimship;
	}

	/**
	 * Return the victim's Ship.
	 * @return Ship
	 */
	function getVictimShipID()
	{
		if(!isset($this->victimshipid)) {
			$this->execQuery();
		}
		return $this->victimshipid;
	}

	/**
	 * Return the name of the victim's Ship type.
	 * @return string
	 */
	function getVictimShipName()
	{
		return $this->getVictimShip()->getName();
	}

	/**
	 * Return the external ID of the victim's Ship type.
	 * @return integer
	 */
	function getVictimShipExternalID()
	{
		return $this->getVictimShip()->getID();
	}

	/**
	 * Return the name of the victim's Shipclass name.
	 * @return string
	 */
	function getVictimShipClassName()
	{
		return $this->getVictimShip()->getClass()->getName();
	}

	/**
	 * Return the current value of the victim's ship
	 * @return float
	 */
	function getVictimShipValue()
	{
		return $this->getVictimShip()->getPrice();
	}

	/**
	 * Return an image for the victim's ship.
	 * @param integer $size
	 * @return string
	 */
	function getVictimShipImage($size)
	{
		 return $this->getVictimShip()->getImage($size);
	}

	/**
	 * Check if the victim is in a Faction.
	 *
	 * @return boolean
	 */
	function getIsVictimFaction()
	{
		return $this->getVictimAlliance()->isFaction();
	}

	/**
	 * Return the raw killmail for this kill.
	 *
	 * @return string
	 */
	function getRawMail()
	{
		if(!is_null($this->mail)) return $this->mail;

		if (config::get('km_cache_enabled')
				&& file_exists(KB_PAGECACHEDIR."/".$this->getID().".txt")) {
			$this->mail = file_get_contents(
					KB_PAGECACHEDIR."/".$this->getID().".txt");
			return $this->mail;
		}

		if (!$this->timestamp) {
			$this->execQuery();
		}
		if(!$this->valid) {
			return "The specified kill ID is not valid.";
		}
		if ($this->isClassified()) {
			return 'Killmail not yet available, try again in '
					.round($this->getClassifiedTime()/3600, 2).' hrs.';
		}

		static $locations;
		if(!isset($locations)) {
			$qry = DBFactory::getDBQuery();
			$qry->execute("SELECT itl_flagID, itl_flagText FROM kb3_item_locations");
			while($row = $qry->getRow()) {
				$locations[$row['itl_flagID']] = $row['itl_flagText'];
			}
		}

		$ship = $this->getVictimShip();
		$shipclass = $ship->getClass();
		if(!$this->getVictimCorpName()) {
			$corp = new Corporation($this->victimcorpid);
			$this->victimcorpname = $corp->getName();
		}
		if(!$this->getVictimAllianceName()) {
			$all = new Alliance($this->victimallianceid);
			$this->victimalliancename = $all->getName();
		}

		if (!$this->getVictimName()) {
			throw new KillException("Invalid mail, victim name blank");
		} else if (!$this->getVictimCorpName()) {
			throw new KillException("Invalid mail, victim corporation blank");
		} else if (!$this->getVictimAllianceName()
				&& !$this->getVictimFactionName()) {
			throw new KillException("Invalid mail, victim alliance blank");
		} else if (!$ship->getName()) {
			throw new KillException("Invalid mail, ship blank");
		} else if (!$this->getSystem()->getName()) {
			throw new KillException("Invalid mail, system blank",´);
		}

		$mail = substr(str_replace('-', '.' , $this->getTimeStamp()), 0, 19)."\r\n\r\n";
		// Starbase (so this is a POS mail)
		if ( in_array($shipclass->getID(), array(35, 36, 37, 38)) ) {
			$mail .= "Corp: ".$this->getVictimCorpName()."\r\n";
			if($this->getIsVictimFaction()) $mail .= "Alliance: None\r\n";
			else $mail .= "Alliance: ".$this->getVictimAllianceName()."\r\n";
			$mail .= "Faction: ".$this->getVictimFactionName()."\r\n";
			//$ship = $this->getVictimShip();
			$mail .= "Destroyed: ".$ship->getName()."\r\n";
			if($this->getVictimName() == $this->getSystem()->getName())
				$mail .= "Moon: Unknown\r\n";
			else
                        {
                            // is the victim's name a moon?
                            $moonID = API_Helpers::getMoonID($this->getVictimName());
                            if($moonID)
                            {
                                $mail .= "Moon: ".$this->getVictimName()."\r\n";
                            }
                            
                            else
                            {
                                // try parsing the victim's name in case it's the format
                                // <corporationName> - <moonName>
                                $namePieces = explode(" - ", $this->getVictimName());
                                if(is_array($namePieces) && count($namePieces) > 2)
                                {
                                    // remove first part, which is the corp name
                                    array_splice($namePieces, 0, 1);
                                    $mail .= "Moon: ".implode(" - ", $namePieces)."\r\n";
                                }
                            }
                            
                            
                        }
				
			$mail .= "System: ".$this->getSystem()->getName()."\r\n";
			$mail .= "Security: ".$this->getSystem()->getSecurity(true)."\r\n";
			$mail .= "Damage Taken: ".$this->dmgtaken."\r\n\r\n";
			$mail .= "Involved parties:\r\n\r\n";
		} else {
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

		foreach ($this->involvedparties_ as $inv) {
			/* @var $inv InvolvedParty */
			$pilot = new Pilot($inv->getPilotID());
			$corp = new Corporation($inv->getCorpID());
			$alliance = new Alliance($inv->getAllianceID());

			$weapon = $inv->getWeapon();
			$ship = $inv->getShip();

			// Split these into multiple ifs so the error tells us where the
			// problem was.
			if (!$pilot->getName()) {
				throw new KillException("Invalid mail, invalid involved pilot (ID: ".$pilot->getID().")");
			} else if (!$corp->getName()) {
				throw new KillException("Invalid mail, invalid involved corporation (ID: ".$corp->getID().")");
			} else if (!$alliance->getName()) {
				throw new KillException("Invalid mail, invalid involved alliance (ID: ".$alliance->getID().")");
			} else if (!$weapon->getName()) {
				throw new KillException("Invalid mail, invalid involved weapon (ID: ".$weapon->getID().")");
			} else if (!$ship->getName()) {
				throw new KillException("Invalid mail, invalid involved ship (ID: ".$ship->getID().")");
			}
			if ($pilot->getName() == $weapon->getName()) {
				$name = $pilot->getName()." / ".$corp->getName();
			} else {
				$name = $pilot->getName();
			}

			$mail .= "Name: ".$name;
			if ($pilot->getID() == $this->getFBPilotID()) {
				$mail .= " (laid the final blow)";
			}
			$mail .= "\r\n";

			if ($pilot->getName() != $weapon->getName()) {
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
				$mail .= "Damage Done: ".$inv->getDamageDone()."\r\n";
			} else {
				$mail .= "Damage Done: ".$inv->getDamageDone()."\r\n";
			}
			$mail .= "\r\n";
		}

		if (count($this->destroyeditems_) > 0) {
			$mail .= "\r\nDestroyed items:\r\n\r\n";

			foreach($this->destroyeditems_ as $destroyed) {
				$item = $destroyed->getItem();
				$mail .= $item->getName();
				if ($destroyed->getQuantity() > 1) {
					$mail .= ", Qty: ".$destroyed->getQuantity();
				}
                                
                                if ($destroyed->getSingleton() == InventoryFlag::$SINGLETON_COPY) {
					$mail .= " (Copy)";
                                }
                                
                                $flagID = InventoryFlag::collapse($destroyed->getLocationID());
				if ($destroyed->getLocationID() == InventoryFlag::$CARGO) {
					$mail .= " (Cargo)";
				} else if ($destroyed->getLocationID() == InventoryFlag::$DRONE_BAY) {
					$mail .= " (Drone Bay)";
				} else if ($destroyed->getLocationID() == InventoryFlag::$IMPLANT) {
					$mail .= " (Implant)";
				} else if ($destroyed->getLocationID() == InventoryFlag::$OTHER) {
					$mail .= " (Other)";
				}
				$mail .= "\r\n";
			}
		}

		if (count($this->droppeditems_) > 0)
		{
			$mail .= "\r\nDropped items:\r\n\r\n";

			foreach($this->droppeditems_ as $dropped) {
				$item = $dropped->getItem();
				$mail .= $item->getName();
				if ($dropped->getQuantity() > 1) {
					$mail .= ", Qty: ".$dropped->getQuantity();
				}
                                
                                if ($dropped->getSingleton() == InventoryFlag::$SINGLETON_COPY) {
					$mail .= " (Copy)";
                                }
                                
				if ($dropped->getLocationID() == InventoryFlag::$CARGO) {
					$mail .= " (Cargo)";
				} else if ($dropped->getLocationID() == InventoryFlag::$DRONE_BAY) {
					$mail .= " (Drone Bay)";
				} else if ($dropped->getLocationID() == InventoryFlag::$IMPLANT) {
					$mail .= " (Implant)";
				} else if ($dropped->getLocationID() == InventoryFlag::$OTHER) {
					$mail .= " (Other)";
				}
				$mail .= "\r\n";
			}
		}

		if ($this->id && config::get('km_cache_enabled')) {
			file_put_contents(KB_MAILCACHEDIR."/".$this->getID().".txt", $mail);
		}

		$this->mail = $mail;

		return $mail;
	}

	/**
	 * Check if this kill is a duplicate and return the id if so.
	 *
	 * @param boolean $checkonly
	 * @return integer
	 */
	function getDupe($checkonly = false)
	{
		if (!$checkonly) {
			if($this->dupeid != 0) {
				return $this->dupeid;
			}
			// Don't call execQuery unless we're missing information.
			if (!$this->timestamp) {
				$this->execQuery();
			}
		}
		$this->dupeid = 0;
		$qry = DBFactory::getDBQuery(true);
		if (!$this->fbpilotid || !$this->victimid) {
			return 0;
		}
		if($this->externalid) {
			$sql = "SELECT kll_id FROM kb3_kills WHERE kll_external_id = ".
				$this->externalid;
			$qry->execute($sql);
			if($qry->recordCount()) {
				$row = $qry->getRow();
				$this->dupeid = $row['kll_id'];
				return $row['kll_id'];
			}
		}
		if($this->hash) {
			$sql = "SELECT kll_id FROM kb3_mails WHERE kll_hash = 0x".
				bin2hex($this->hash);
			$qry->execute($sql);
			if($qry->recordCount()) {
				$row = $qry->getRow();
				$this->dupeid = $row['kll_id'];
				return $row['kll_id'];
			}
		}
		$sql = "SELECT kll_id"
				." FROM kb3_kills"
				." WHERE kll_timestamp ='".$this->timestamp."'";
		// use corp id for pos to catch all the old mails with missing moons.
		if($this->getVictimShip()->getClass()->getID() >= 35
						&& $this->getVictimShip()->getClass()->getID() <= 38) {
			$sql .= " AND kll_crp_id = ".$this->victimcorpid;
		} else {
			$sql .= " AND kll_victim_id = ".$this->victimid;
		}
		$sql .= " AND kll_ship_id = ".$this->victimship->getID()
					." AND kll_system_id = ".$this->solarsystem->getID()
					." AND kll_fb_plt_id = ".$this->fbpilotid
					." AND kll_dmgtaken = ".intval($this->dmgtaken)
					." AND kll_id != ".$this->id;
		$qry->execute($sql);
		$qryinv = DBFactory::getDBQuery(true);

		while ($row = $qry->getRow()) {
			$kll_id = $row['kll_id'];
			// No involved parties found to differentiate kills
			if(empty($this->involvedparties_)) {
				$this->dupeid = $kll_id;
				return $kll_id;
			}

			// Check that all involved parties we know of are on the kill
			// and did the same damage.
			$invList = array();
			foreach($this->involvedparties_ as $inv)
				$invList[] = '('.$inv->getPilotID().','.intval($inv->getDamageDone()).')';
			$sql = 'SELECT COUNT(*) as count FROM kb3_inv_detail WHERE ind_kll_id = '.
				$kll_id.' AND (ind_plt_id,ind_dmgdone) IN ('.implode(',', $invList).')';

			$qryinv->execute($sql);
			$row = $qryinv->getRow();
			if($row['count'] == count($this->involvedparties_)) {
				$this->dupeid = $kll_id;
				return $kll_id;
			}
		}
	}

	private function execQuery()
	{
		if (!$this->executed) {
			if ($this->isCached()) {
				$cache = $this->getCache();
				if ($cache->valid) {
					$this->id = $cache->id;
					$this->externalid = $cache->externalid;
                                        $this->crestHash = $cache->crestHash;
					$this->involvedparties_ = $cache->involvedparties_;
					$this->destroyeditems_ = $cache->destroyeditems_;
					$this->droppeditems_ = $cache->droppeditems_;
					$this->fullinvolved = $cache->fullinvolved;
					$this->timestamp = $cache->timestamp;
					$this->victimid = $cache->victimid;
					$this->dmgtaken = $cache->dmgtaken;
					$this->iskloss = $cache->iskloss;
					$this->killpoints = $cache->killpoints;
					$this->victimcorpid = $cache->victimcorpid;
					$this->victimallianceid = $cache->victimallianceid;
					$this->victimshipid = $cache->victimshipid;
					$this->fbpilotid = $cache->fbpilotid;
					$this->fbcorpid = $cache->fbcorpid;
					$this->fballianceid = $cache->fballianceid;
					$this->solarsystemid = $cache->solarsystemid;
					$this->dupeid = $cache->dupeid;
					$this->hash = $cache->hash;
					$this->mail = $cache->mail;
					$this->trust = $cache->trust;
					$this->executed = $cache->executed;
					$this->involvedcount = $cache->involvedcount;
					$this->valid = $cache->valid;
                                        $this->xCoordinate = $cache->xCoordinate;
                                        $this->yCoordinate = $cache->yCoordinate;
                                        $this->zCoordinate = $cache->zCoordinate;
                                        $this->nearestCelestial = $cache->nearestCelestial;
                                        $this->distanceToNearestCelestial = $cache->distanceToNearestCelestial;
					return $this->valid;
				}
			}
			$qry = DBFactory::getDBQuery();

			$sql = "select kll.kll_id, kll.kll_external_id, kll.kll_timestamp,
						kll.kll_victim_id, kll.kll_crp_id, kll.kll_all_id,
						kll.kll_ship_id, kll.kll_system_id,
						kll.kll_points, kll.kll_isk_loss, kll.kll_dmgtaken, 
                                                kll.kll_x, kll.kll_y, kll.kll_z,
						fb.ind_plt_id as fbplt_id,
						fb.ind_crp_id as fbcrp_id,
						fb.ind_all_id as fbali_id
					from kb3_kills kll, kb3_inv_detail fb
					where kll.kll_id = '".$this->id."'
						and fb.ind_kll_id = kll.kll_id
						and fb.ind_plt_id = kll.kll_fb_plt_id";

			$qry->execute($sql);
			$row = $qry->getRow();
			if (!$row) {
				$this->valid = false;
				return false;
			} else {
				$this->valid = true;
			}
                        

			$this->timestamp = $row['kll_timestamp'];
			$this->solarsystemid = (int)$row['kll_system_id'];
			$this->victimid = (int)$row['kll_victim_id'];
			$this->victimcorpid = (int)$row['kll_crp_id'];
			$this->victimallianceid = (int)$row['kll_all_id'];
			$this->victimshipid = (int)$row['kll_ship_id'];
			$this->fbpilotid = (int)$row['fbplt_id'];
			$this->fbcorpid = (int)$row['fbcrp_id'];
			$this->fballianceid = (int)$row['fbali_id'];
			$this->externalid = (int)$row['kll_external_id'];
			$this->iskloss = (float)$row['kll_isk_loss'];
			$this->dmgtaken = (int)$row['kll_dmgtaken'];
			$this->killpoints = (int)$row['kll_points'];
                        $this->xCoordinate = (float)$row['kll_x'];
                        $this->yCoordinate = (float)$row['kll_y'];
                        $this->zCoordinate = (float)$row['kll_z'];

			$sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status,
				ind_shp_id, ind_wep_id, ind_dmgdone
				from kb3_inv_detail
				where ind_kll_id = ".$this->getID()."
				order by ind_order";

			$qry->execute($sql) or die($qry->getErrorMsg());
			while ($row = $qry->getRow())
			{
				$involved = new InvolvedParty((int)$row['ind_plt_id'],
					(int)$row['ind_crp_id'],
					(int)$row['ind_all_id'],
					(float)$row['ind_sec_status'],
					(int)$row['ind_shp_id'],
					(int)$row['ind_wep_id'],
					(int)$row['ind_dmgdone']);
				$this->involvedparties_[] = $involved;
			}
			$destroyedlist = new ItemList(null, true);
			$destroyedlist->addKillDestroyed($this->id);
			while($item = $destroyedlist->getItem()) {
				$destroyed = new DestroyedItem($item,
					$item->getAttribute('itd_quantity'),
                                        $item->getAttribute('itd_singleton'),
					$item->getAttribute('itl_flagText'),
					$item->getAttribute('itd_itl_id'));
				$this->destroyeditems_[] = $destroyed;
			}
			$droppedlist = new ItemList(null, true);
			$droppedlist->addKillDropped($this->id);
			while($item = $droppedlist->getItem()) {
				$dropped = new DestroyedItem($item,
					$item->getAttribute('itd_quantity'),
                                        $item->getAttribute('itd_singleton'),
					$item->getAttribute('itl_flagText'),
					$item->getAttribute('itd_itl_id'));
				$this->droppeditems_[] = $dropped;
			}
                        
                        // try to calculate the nearest celestial and the distance from it
                        $this->calculateNearestCelestial();
			$this->executed = true;
			$this->putCache();
		}
	}
        
        /**
         * calculations the nearest celestial for this kill and the distance from it
         */
        protected function calculateNearestCelestial()
        {
                // do we have kill coordinates?
                if($this->nearestCelestial == null && $this->xCoordinate !== (float)0 && $this->yCoordinate !== (float)0 && $this->zCoordinate != (float)0)
                {
                    $locationsIdsInSystem = $this->getSystem()->getLocationIDs();
                    
                    $distanceCorrectedByRadius = null;
                    foreach($locationsIdsInSystem AS $locationId)
                    {
                        // for each celestial in that system, calculate the euclidean distance between its coordinates
                        // and the kill's coordinates
                        $Location = Location::getByID($locationId);
                        $celestialXCoordiante = $Location->getXCoordinate();
                        $celestialYCoordiante = $Location->getYCoordinate();
                        $celestialZCoordiante = $Location->getZCoordinate();
                        $distance = sqrt(pow(($this->xCoordinate - $celestialXCoordiante), 2) + pow(($this->yCoordinate - $celestialYCoordiante), 2) + pow(($this->zCoordinate - $celestialZCoordiante), 2));

                        if(is_null($this->distanceToNearestCelestial) || $distance < $this->distanceToNearestCelestial)
                        {
                            $this->distanceToNearestCelestial = $distance;
                            $locationRadius = $Location->getRadius();
                            if(!is_null($locationRadius))
                            {
                                $distanceCorrectedByRadius = $this->distanceToNearestCelestial - $locationRadius;
                            }
                            $this->nearestCelestial = $Location;
                        }
                    }
                    // actually, use the distance corrected by the celstial's radius
                    if(!is_null($distanceCorrectedByRadius))
                    {
                        if($distanceCorrectedByRadius < 0)
                        {
                            $distanceCorrectedByRadius = 0;
                        }
                        $this->distanceToNearestCelestial = $distanceCorrectedByRadius;
                    }
                }
        }

	/**
	 * Check if this kill is still within the classified period.
	 *
	 * @return boolean
	 */
	function isClassified()
	{
		if (config::get('kill_classified')) {
			if (user::role('classified_see')) {
				return false;
			} else if($this->getClassifiedTime() > 0) {
				return true;
			}
		}
		else return false;
	}

	/** Return the time left until this kill is not classified.
	 *
	 * @return integer
	 */
	function getClassifiedTime()
	{
		if (config::get('kill_classified') &&
				strtotime($this->getTimeStamp()." UTC") >
				time() - config::get('kill_classified') * 3600) {
			return (config::get('kill_classified') * 3600
				- time() + strtotime($this->getTimeStamp()." UTC"));
		}
		return 0;
	}

	/**
	 * Return the count of pilots involved in this kill.
	 *
	 * @return integer
	 */
	function getInvolvedPartyCount()
	{
		if(isset($this->involvedcount)) {
			return $this->involvedcount;
		}
		$qry = DBFactory::getDBQuery();
		$qry->execute(
				"select count(*) inv from kb3_inv_detail where ind_kll_id = "
				.$this->getID());
		$result = $qry->getRow();
		$this->involvedcount = (int)$result['inv'];
		return $result['inv'];
	}

	/**
	 * Set the number of involved parties - used by killlist
	 * @param integer $invcount
	 */
	function setInvolvedPartyCount($invcount = 0)
	{
		$this->involvedcount = $invcount;
	}

	/**
	 * @deprecated
	 */
	function setDetailedInvolved()
	{
	}

	/**
	 * Return true if this kill exists and is valid.
	 * @return boolean
	 */
	function exists()
	{
		if(!isset($this->valid)) {
			$this->execQuery();
		}
		return $this->valid;
	}

	/**
	 * Count all kills by board owner related to this kill
	 *
	 * @return integer
	 */
	function relatedKillCount()
	{
		// No details for classified kills.
		if($this->isClassified()) {
			return 0;
		}
		if($this->relatedkillcount) {
			return $this->relatedkillcount;
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
		$this->relatedkillcount = $res['kills'];
		// Do not cache between page loads.
		return $this->relatedkillcount;
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
		if($this->relatedlosscount) {
			return $this->relatedlosscount;
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
		$this->relatedlosscount = $res['losses'];
		// Do not cache between page loads.
		return $this->relatedlosscount;
	}

	function countComment()
	{
		if(isset($this->commentcount)) {
			return $this->commentcount;
		}
		$qry = DBFactory::getDBQuery();
		$sql = "SELECT count(id) as comments FROM kb3_comments "
				."WHERE kll_id = '$kll_id' AND (site = '".KB_SITE
				."' OR site IS NULL)";
		// return 0 if query fails. May be incorrect but is harmless here
		if(!$qry->execute($sql)) {
			return 0;
		}
		$result = $qry->getRow();
		$this->commentcount = $result['comments'];
		// Do not cache between page loads.
		return $result['comments'];
	}

	/**
	 * Set the number of comments - used by killlist
	 */
	function setCommentCount($comcount = 0)
	{
		$this->commentcount = $comcount;
	}

	function setID($id)
	{
		$this->id = $id;
	}

	function setTimeStamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	function setSolarSystem($solarsystem)
	{
		$this->solarsystem = $solarsystem;
	}

	function setSolarSystemName($solarsystemname)
	{
		$this->solarsystemname = $solarsystemname;
	}

	function setSolarSystemSecurity($solarsystemsecurity)
	{
		$this->solarsystemsecurity = $solarsystemsecurity;
	}

	function setExternalID($externalid)
	{
		if($externalid) $this->externalid = $externalid;
		else $this->externalid = 0;
	}

	function setVictim($victim)
	{
		$this->victim = $victim;
	}

	function setVictimID($victimid)
	{
		$this->victimid = $victimid;
	}

	function setVictimName($victimname)
	{
		$this->victimname = $victimname;
	}

	function setVictimCorpID($victimcorpid)
	{
		$this->victimcorpid = $victimcorpid;
	}

	function setVictimCorpName($victimcorpname)
	{
		$this->victimcorpname = $victimcorpname;
	}

	function setVictimAllianceID($victimallianceid)
	{
		$this->victimallianceid = $victimallianceid;
	}

	function setVictimAllianceName($victimalliancename)
	{
		$this->victimalliancename = $victimalliancename;
	}

	function setVictimShip($victimship)
	{
		$this->victimship = $victimship;
	}

	function setVictimShipName($victimshipname)
	{
		$this->victimshipname = $victimshipname;
	}

	function setVictimShipExternalID($victimshipexternalid)
	{
		$this->victimshipexternalid = $victimshipexternalid;
	}

	function setVictimShipClassName($victimshipclassname)
	{
		$this->victimshipclassname = $victimshipclassname;
	}

	function setVictimShipValue($victimshipvalue)
	{
		$this->victimshipvalue = $victimshipvalue;
	}

	function setFBPilot($fbpilot)
	{
		$this->fbpilot = $fbpilot;
	}

	function setFBPilotID($fbpilotid)
	{
		$this->fbpilotid = $fbpilotid;
	}

	function setFBPilotName($fbpilotname)
	{
		$npc = strpos($fbpilotname, "#");
		if ($npc === false) {
			$this->fbpilotname = $fbpilotname;
		} else {
			$name = explode("#", $fbpilotname);
			$plt = new Item($name[2]);
			$this->fbpilotname = $plt->getName();
		}
	}

	function setFBCorpID($fbcorpid)
	{
		$this->fbcorpid = $fbcorpid;
	}

	function setFBCorpName($fbcorpname)
	{
		$this->fbcorpname = $fbcorpname;
	}

	function setFBAllianceID($fballianceid)
	{
		$this->fballianceid = $fballianceid;
	}

	function setFBAllianceName($fballiancename)
	{
		$this->fballiancename = $fballiancename;
	}
	function setKillPoints($killpoints)
	{
		$this->killpoints = $killpoints;
	}
	/**
	 * Set the ISK loss value for this kill.
	 */
	function setISKLoss($isk)
	{
		$this->iskloss = $isk;
	}
	/**
	 * Calculate the current cost of a ship loss excluding blueprints.
	 * @param boolean $update set true to update all-time summaries.
	 * @return float
	 */
	function calculateISKLoss($update = true)
	{
		// Make sure the kill is initialised before we change anything.
		$this->execQuery();
		$value = 0;
		foreach($this->destroyeditems_ as $itd) {
			$item = $itd->getItem();
			if(strpos($item->getName(), "Blueprint") === FALSE) $value += $itd->getValue() * $itd->getQuantity();
		}
		if(config::get('kd_droptototal')) {
			foreach($this->droppeditems_ as $itd) {
				$item = $itd->getItem();
				if(strpos($item->getName(), "Blueprint") === FALSE) $value += $itd->getValue() * $itd->getQuantity();
			}
		}
		$value += $this->getVictimShip()->getPrice();
		if($update) {
			$qry = DBFactory::getDBQuery();
			$qry->execute("UPDATE kb3_kills SET kll_isk_loss = '$value' WHERE
				kll_id = '".$this->id."'");
			if($this->iskloss) {
				summaryCache::update($this, $value - $this->iskloss);
			}
		}
		$this->iskloss = $value;
		return $value;
	}

	/**
	 * Return the killpoints for this kill.
	 * @return integer
	 */
	function calculateKillPoints()
	{
		if (!$this->involvedparties_) {
			$this->execQuery();
		}

		$ship = $this->getVictimShip();
		$shipclass = $ship->getClass();
		$vicpoints = $shipclass->getPoints();
		$maxpoints = round($vicpoints * 1.2);
		$invpoints = 0;

		foreach ($this->involvedparties_ as $inv) {
			$shipinv = $inv->getShip();
			$shipclassinv = $shipinv->getClass();
			$invpoints += $shipclassinv->getPoints();
		}

		if($vicpoints + $invpoints > 0) {
			$gankfactor = $vicpoints / ($vicpoints + $invpoints);
			$points = ceil($vicpoints * ($gankfactor / 0.75));
		} else {
			$points = 0;
		}
		if ($points > $maxpoints) {
			$points = $maxpoints;
		}

		$points = round($points, 0);
		return $points;
	}

	function add($id = null)
	{
		// If value isn't already calculated then do so now. Don't update the
		// stored value since at this point it does not exist.
		if(!$this->iskloss) {
			$this->calculateISKLoss(false);
		}

		// Start a transaction here to capture the duplicate check.
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);
		// Set these to make sure we don't try to load the kill from the db before it exists.
		$this->executed = true;
		$this->valid = true;
		//Always recalculate the hash ourselves before posting.
		$this->hash = false;
		$this->getHash(false,false);

		$this->getDupe(true);
		if ($this->dupeid == 0) {
			$this->realadd();
		} else {
			$this->id = -1;
		}
		$qry->autocommit(true);
		return $this->id;
	}

	/**
	 * Really add the kill.
	 * @param integer $id If set, use the given id to post this kill.
	 * @return integer
	 */
	protected function realadd($id = null)
	{
		if ( $this->timestamp == "" || !$this->getVictim()->getID()
				|| !$this->victimship->getName() || !$this->solarsystem->getID()
				|| !$this->victimallianceid || !$this->victimcorpid
				|| !$this->getFBPilotID() || !$this->getHash(false, false)) {
			return 0;
		}
		// TODO: Redo accounting for ammo (see kill_detail).
//		// Check slot counts.
//		$locations = array();
//		foreach ($this->droppeditems_ as $dest) {
//			$locations[$dest->getLocationID()] += $dest->getQuantity();
//		}
//		foreach ($this->destroyeditems_ as $dest) {
//			$locations[$dest->getLocationID()] += $dest->getQuantity();
//		}
//		$dogma = Cacheable::factory('dogma', $this->victimship->getID());
//		$lowcount = (int)$dogma->attrib['lowSlots']['value'];
//		$medcount = (int)$dogma->attrib['medSlots']['value'];
//		$hicount = (int)$dogma->attrib['hiSlots']['value'];
//		// Is there anything flyable that has no rig slots?
//		$rigcount = (int)($dogma->attrib['rigSlots']['value'] ?
//				$dogma->attrib['rigSlots']['value'] : 3);
//		$subcount = 5;
//		if ($lowcount
//				&& ($locations[1] > $hicount
//				|| $locations[2] > $medcount
//				||  $locations[3] > $lowcount
//				|| $locations[5] > $rigcount)
//				) {
//			return 0;
//		} else if ((!$lowcount && $locations[7])
//				&& ($locations[7] > $subcount
//				|| $locations[5] > $rigcount)
//				) {
//			return 0;
//		}

		if ($id == null) {
			$qid = 'null';
		} else {
			$qid = $id;
		}
		if (!$this->dmgtaken) {
			$this->dmgtaken = 0;
		}
                if(is_null($this->xCoordinate))
                {
                    $this->xCoordinate = 0;
                }
                if(is_null($this->yCoordinate))
                {
                    $this->yCoordinate = 0;
                }
                if(is_null($this->zCoordinate))
                {
                    $this->zCoordinate = 0;
                }
                
                $mysqlTimestamp = toMysqlDateTime($this->timestamp);
		$qry = DBFactory::getDBQuery();
		$sql = "INSERT INTO kb3_kills
            (kll_id , kll_timestamp , kll_victim_id , kll_all_id , kll_crp_id , kll_ship_id , kll_system_id , kll_fb_plt_id , kll_points , kll_dmgtaken, kll_external_id, kll_isk_loss, kll_x, kll_y, kll_z, kll_location)
            VALUES (".$qid.",
            '".$mysqlTimestamp."',
            ".$this->victimid.",
            ".$this->victimallianceid.",
            ".$this->victimcorpid.",
            ".$this->victimship->getID().",
            ".$this->solarsystem->getID().",
            ".$this->getFBPilotID().",
            ".$this->calculateKillPoints().",
            ".$this->dmgtaken.", ";
		if($this->externalid) $sql .= $this->externalid.", ";
		else $sql .= "NULL, ";
		$sql .= $this->getISKLoss().",
            ".$this->xCoordinate.",
            ".$this->yCoordinate.",
            ".$this->zCoordinate.",";
            if(!is_null($this->getNearestCelestial())) $sql .= $this->nearestCelestial->getID();
            else $sql .= "NULL";
            $sql .= " )";
                        $qry->autocommit(false);
		if(!$qry->execute($sql)) {
			return $this->rollback($qry);
		}

		if ($id) {
			$this->id = $id;
		} else {
			$this->id = $qry->getInsertID();
		}
		if(!$this->id) {
			return $this->rollback($qry);
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

		// Make sure involved parties are ordered by damage done.
		usort($this->involvedparties_, array('Kill','involvedComparator'));

		foreach ($this->involvedparties_ as $inv) {
			$ship = $inv->getShip();
			$weapon = $inv->getWeapon();
			if (!$inv->getPilotID()
					|| !$inv->getAllianceID() || !$inv->getCorpID()
					|| !$ship->getName() || !$weapon->getID()) {
				return $this->rollback();
			}

			if($notfirstd) $involveddsql .= ", ";
			$involveddsql .= "( ".$this->getID().", '".$mysqlTimestamp."', "
					.$inv->getPilotID().", '".$inv->getSecStatus()."', "
					.$inv->getAllianceID().", ".$inv->getCorpID().", "
					.$ship->getID().", ".$weapon->getID().", ".$order++.", "
					.$inv->getDamageDone().")";
			$notfirstd = true;
			if(!in_array($inv->getAllianceID(), $invall)) {
				if($notfirsta) $involvedasql .= ", ";
				$involvedasql .= "( ".$this->getID().", ".$inv->getAllianceID()
					.", '".$mysqlTimestamp."')";
				$notfirsta = true;
				$invall[] = $inv->getAllianceID();
			}
			if(!in_array($inv->getCorpID(), $invcrp)) {
				if($notfirstc) $involvedcsql .= ", ";
				$involvedcsql .= "( ".$this->getID().", ".$inv->getCorpID()
					.", '".$mysqlTimestamp."')";
				$notfirstc = true;
				$invcrp[] = $inv->getCorpID();
			}

		}
		if($notfirstd && !$qry->execute($involveddsql))
			return $this->rollback($qry);
		if($notfirsta && !$qry->execute($involvedasql))
			return $this->rollback($qry);
		if($notfirstc && !$qry->execute($involvedcsql))
			return $this->rollback($qry);
		// destroyed
		$notfirstitd=false;
		$itdsql = "insert into kb3_items_destroyed (itd_kll_id, itd_itm_id, itd_quantity, itd_itl_id, itd_singleton) values ";
		foreach ($this->destroyeditems_ as $dest)
		{
			$item = $dest->getItem();
			$loc_id = $dest->getLocationID();
			if (!is_numeric($this->getID()) || !is_numeric($item->getID()) || !is_numeric($dest->getQuantity()) || !is_numeric($loc_id) || !is_numeric($dest->getSingleton()))
			{
				trigger_error('error with destroyed item.', E_USER_WARNING);
				var_dump($dest);
				exit;
				continue;
			}

			if($notfirstitd) $itdsql .= ", ";
			$itdsql .= "( ".$this->getID().", ".$item->getID().", ".$dest->getQuantity().", ".$loc_id.", ".$dest->getSingleton()." )";
			$notfirstitd = true;
		}
		if($notfirstitd &&!$qry->execute($itdsql))
			return $this->rollback($qry);

		// dropped
		$notfirstitd=false;
		$itdsql = "insert into kb3_items_dropped (itd_kll_id, itd_itm_id, itd_quantity, itd_itl_id, itd_singleton) values ";
		foreach ($this->droppeditems_ as $dest)
		{
			$item = $dest->getItem();
			$loc_id = $dest->getLocationID();
			if (!is_numeric($this->getID()) || !is_numeric($item->getID()) || !is_numeric($dest->getQuantity()) || !is_numeric($loc_id) || !is_numeric($dest->getSingleton()))
			{
				trigger_error('error with dropped item.', E_USER_WARNING);
				var_dump($dest);
				exit;
				continue;
			}

			if($notfirstitd) $itdsql .= ", ";
			$itdsql .= "( ".$this->getID().", ".$item->getID().", ".$dest->getQuantity().", ".$loc_id.", ".$dest->getSingleton()." )";
			$notfirstitd = true;
		}
		if($notfirstitd &&!$qry->execute($itdsql))
			return $this->rollback($qry);
                
                // try calculate CREST hash
                if(!$this->crestHash)
                {
                    $this->crestHash = $this->calculateCrestHash();
                }

		$sql = "INSERT INTO kb3_mails (  `kll_id`, `kll_timestamp`, `kll_external_id`, `kll_hash`, `kll_trust`, `kll_modified_time`, `kll_crest_hash`)".
			"VALUES(".$this->getID().", '".$this->getTimeStamp()."', ";
		if($this->externalid) $sql .= $this->externalid.", ";
                else $sql .= "NULL, ";
                $sql .= "'".$qry->escape($this->getHash(false, false))."', 0, UTC_TIMESTAMP(), ";
                
                // add CREST hash
                if($this->crestHash)
                {
                    $sql .= "'$this->crestHash'";
                }
                
                else
                {
                    $sql .= "NULL";
                }
                $sql .= ")";
                
		if(!@$qry->execute($sql))
			return $this->rollback($qry);

		//Update cache tables.
		summaryCache::addKill($this);
		$qry->autocommit(true);
		// call the event that we added this mail
		event::call('killmail_added', $this);
		cache::notifyKillAdded();
		return $this->id;
	}

	function remove($delcomments = true, $permanent = true)
	{
		if (!$this->id) {
			return;
		}
		$qry = DBFactory::getDBQuery();
		$qry->autocommit(false);

		event::call('killmail_delete', $this);
		summaryCache::delKill($this);

		$qry->execute("delete from kb3_inv_detail where ind_kll_id = ".$this->id);
		$qry->execute("delete from kb3_inv_all where ina_kll_id = ".$this->id);
		$qry->execute("delete from kb3_inv_crp where inc_kll_id = ".$this->id);
		$qry->execute("delete from kb3_items_destroyed where itd_kll_id = ".$this->id);
		$qry->execute("delete from kb3_items_dropped where itd_kll_id = ".$this->id);
		// Don't remove comments when readding a kill
		if ($delcomments)
		{
			$qry->execute("delete from kb3_comments where kll_id = ".$this->id);
			if ($permanent)
				$qry->execute("UPDATE kb3_mails SET kll_trust = -1, kll_modified_time = UTC_TIMESTAMP() WHERE kll_id = ".$this->id);
			else
				$qry->execute("DELETE FROM kb3_mails WHERE kll_id = ".$this->id);
		}
		$qry->execute("delete from kb3_kills where kll_id = ".$this->id);
		$qry->autocommit(true);

		$this->valid = false;
		Cacheable::delCache($this);
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

	/** Return the array of involved parties.
	*
	* @return mixed InvolvedParty[].
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
		if($this->id)
			$qry->execute("SELECT kll_hash, kll_trust FROM kb3_mails WHERE kll_id = ".$this->id);
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
				if($this->id && $this->externalid)
				{
					$sql = "INSERT IGNORE INTO kb3_mails (  `kll_id`, `kll_timestamp`, ".
						"`kll_external_id`, `kll_hash`, `kll_trust`, `kll_modified_time`)".
						"VALUES(".$this->getID().", '".$this->getTimeStamp()."', ".
						$this->externalid.", '".$qry->escape($this->hash)."', ".
						$this->trust.", UTC_TIMESTAMP())";
				}
				else if($this->id)
				{
					$sql = "INSERT IGNORE INTO kb3_mails (  `kll_id`, `kll_timestamp`, ".
						"`kll_hash`, `kll_trust`, `kll_modified_time`)".
						"VALUES(".$this->getID().", '".$this->getTimeStamp()."', ".
						"'".$qry->escape($this->hash)."', ".
						$this->trust.", UTC_TIMESTAMP())";
				}
				if($this->id) $qry->execute($sql);
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
	private function rollback(&$qry)
	{
		// Since MyISAM doesn't support transactions, let's try to remove
		// anything that made it in.
		if ($this->id) {
			$qry->execute("DELETE FROM kb3_inv_detail WHERE ind_kll_id = ".$this->id);
			$qry->execute("DELETE FROM kb3_inv_all WHERE ina_kll_id = ".$this->id);
			$qry->execute("DELETE FROM kb3_inv_crp WHERE inc_kll_id = ".$this->id);
			$qry->execute("DELETE FROM kb3_items_destroyed WHERE itd_kll_id = ".$this->id);
			$qry->execute("DELETE FROM kb3_items_dropped WHERE itd_kll_id = ".$this->id);
			$qry->execute("DELETE FROM kb3_kills WHERE kll_id = ".$this->id);
		}
		$qry->rollback();
		$qry->autocommit(true);
		$this->id = 0;
		return false;
	}

	/**
	 * Update this kill's external ID.
	 * @param integer $extID
	 */
	public function updateExternalID($extID)
	{
		$this->execQuery();

		$qry = DBFactory::getDBQuery();
		if(@$qry->execute("UPDATE kb3_kills SET kll_external_id = ".
				$this->externalid." WHERE kll_id = ".$this->id)) 
        {
			$qry->execute("UPDATE kb3_mails SET kll_external_id = ".
					$this->externalid.", kll_modified_time = UTC_TIMESTAMP()".
					" WHERE kll_id = ".$this->id.
					" AND kll_external_id IS NULL");

            $this->externalid = $extID;
            $this->putCache();
        }
	}
        
        /**
	 * Get the crest hash of this kill.
	 *
	 * @return string the crest hash for this kill
	 */
	function getCrestHash()
	{
		if(is_null($this->crestHash) && $this->id)
        {
		    $qry = new DBPreparedQuery();
            $qry->prepare('SELECT kll_crest_hash FROM kb3_mails WHERE kll_id = ?');

            $resultArray = array(
                &$this->crestHash
            );
            // bind results
            $qry->bind_results($resultArray);

            // bind parameter
            $params = array('i', &$this->id);
            $qry->bind_params($params);
            $qry->execute();
            if($qry->recordCount())
            {
                $qry->fetch();
            }
        }

        // kill has not been posted via CREST
        if(!$this->crestHash)
        {
             // calculate the crest hash
            $this->crestHash = $this->calculateCrestHash();
            // if successfully calculated
            if($this->crestHash)
            {
                // update the crest hash in the database
                // let's not do this just yet, first need to be sure it's working properly
                //$this->updateCrestHash($this->crestHash);
            }
        }

        if($this->crestHash && $this->id)
        {
            $this->putCache();
        }
		
		return $this->crestHash;
	}
        
        
        /**
	 * Get the crest URL of this kill.
	 *
	 * @return string the crest URL for this kill
	 */
	function getCrestUrl()
	{
		if(is_null($this->externalid)) 
        {
            $this->execQuery();
		}

        if($this->getCrestHash() && !is_null($this->externalid))
        {
            return CREST_PUBLIC_URL . self::$CREST_KILLMAIL_ENDPOINT . $this->externalid.'/'.$this->getCrestHash().'/';
        }
        return NULL;
	}
        
    /**
     * sets the kill's CREST hash
     * @param string $crestHash
     */
    function setCrestHash($crestHash)
	{
		$this->crestHash = $crestHash;
	}
        
    /**
     * returns the xCoordinate
     * @return float the x coordinate of the kill
     */
    public function getXCoordinate()
    {
        if(!isset($this->xCoordinate)) 
        {
                $this->execQuery();
        }
        return $this->xCoordinate;
    }
        
    /**
     * returns the yCoordinate
     * @return float the y coordinate of the kill
     */
    public function getYCoordinate()
    {
        if(!isset($this->yCoordinate)) 
        {
                $this->execQuery();
        }
        return $this->yCoordinate;
    }

    /**
     * returns the zCoordinate
     * @return float the z coordinate of the kill
     */
    public function getZCoordinate()
    {
        if(!isset($this->zCoordinate)) 
        {
                $this->execQuery();
        }
        return $this->zCoordinate;
    }

    /** 
     * sets the kill's x coordinate
     * @param mixed $xCoordinate the kill's x coordinate, will be interpreted as float
     */
    public function setXCoordinate($xCoordinate)
    {
        $this->xCoordinate = $xCoordinate;
    }

    /** 
     * sets the kill's y coordinate
     * @param mixed $yCoordinate the kill's y coordinate, will be interpreted as float
     */
    public function setYCoordinate($yCoordinate)
    {
        $this->yCoordinate = $yCoordinate;
    }

    /** 
     * sets the kill's z coordinate
     * @param mixed $zCoordinate the kill's z coordinate, will be interpreted as float
     */
    public function setZCoordinate($zCoordinate)
    {
        $this->zCoordinate = $zCoordinate;
    }

    /**
     * gets the name of the nearest celestial the kill happened
     * @return string the name of the nearest celestial the kill happened at, or an empty string if no coordinates are available
     */
    public function getNearestCelestialName()
    {
        if(!isset($this->nearestCelestial))
        {
                $this->calculateNearestCelestial();
        }

        if(isset($this->nearestCelestial))
        {
            return $this->nearestCelestial->getName();
        }

        return null;
    }

    /**
     * gets the nearest celestial the kill happened
     * @return \Location the nearest celestial the kill happened at, or null if no coordinates are available
     */
    public function getNearestCelestial()
    {
        if(!isset($this->nearestCelestial))
        {
                $this->calculateNearestCelestial();
        }
        return $this->nearestCelestial;
    }

    /**
     * gets the length of the line segment between the kill's 
     * coordinates and the nearest celstial in kilometers
     * @return float the distance to the nearest celstial in kilometers
     */
    public function getDistanceToNearestCelestial()
    {
        if(!isset($this->distanceToNearestCelestial))
        {
                $this->execQuery();
        }
        return $this->distanceToNearestCelestial;
    }

    /**
     * gets the distance to the nearest celstial, but in short form with a quantifier (k, M, AU etc)
     * @return string the distance to the nearest celstial, but in short form with a quantifier (k, M, AU etc)
     */
    public function getDistanceToNearestCelestialFormatted()
    {
        if(!isset($this->distanceToNearestCelestial))
        {
                $this->execQuery();
        }
        if(isset($this->distanceToNearestCelestial))
        {
            // all coordinates are in meters
            $distanceToNearestCelestialFormatted = round($this->distanceToNearestCelestial, 1) . ' m';
            // AU
            if($this->distanceToNearestCelestial > 149597870700)
            {
                $distanceToNearestCelestialFormatted = round($this->distanceToNearestCelestial/149597870700, 1) . ' AU';
            }

            // millions kilometers
            else if($this->distanceToNearestCelestial > 1000000000)
            {
                $distanceToNearestCelestialFormatted = round($this->distanceToNearestCelestial/1000000000, 1) . 'M km';
            }

            // thousands kilomters
            else if($this->distanceToNearestCelestial > 1000000)
            {
                $distanceToNearestCelestialFormatted = round($this->distanceToNearestCelestial/1000000, 1) . 'k km';
            }

            // kilomters
            else if($this->distanceToNearestCelestial > 1000)
            {
                $distanceToNearestCelestialFormatted = round($this->distanceToNearestCelestial/1000, 1) . ' km';
            }

            return $distanceToNearestCelestialFormatted;
        }

        return null;
    }
        
        /**
	 * Update this kill's CREST hash.
	 * @param string $crestHash
	 */
	public function updateCrestHash($crestHash)
	{
		$this->execQuery();
                
		$qry = new DBPreparedQuery();
        $qry->prepare('UPDATE kb3_mails SET kll_crest_hash = ? WHERE kll_id = ?');
        $params = array(
            'si',
            &$crestHash,
            &$this->id
        );
                
        $qry->bind_params($params);
                
		if(@$qry->execute())
        {
            $this->crestHash = $crestHash;
            $this->putCache();
        }
	}
        
    /**
     * tries to calculate the CREST hash using the external
     * ID and some specific kill information
     */
    public function calculateCrestHash()
    {
        // we need the external kill ID
        if(!$this->getExternalID())
        {
            return NULL;
        }
        $finalBlowPilotId = $this->getFBPilotID();
        $finalBlowPilot = Pilot::getByID($finalBlowPilotId);
        $victimPilot = $this->getVictim();
        $victimShip = $this->getVictimShip();
        $time = $this->getTimeStamp();

        // check for timestmap existance
        $defaultTimezone = date_default_timezone_get();
        // set UTC as default timezone
        date_default_timezone_set('UTC');
        $timestamp = strtotime($time);
        // restore default timezone
        date_default_timezone_set($defaultTimezone);
        if($timestamp === FALSE || $timestamp < 0)
        {
            return NULL;
        }


        // check final blow pilot, victim pilot and victim ship
        if(!$finalBlowPilot || !$victimPilot || !$victimShip)
        {
            return NULL;
        }

        // prepare the ship type ID
        $shipTypeID = $victimShip->getID();
        if(!$shipTypeID)
        {
            return NULL;
        }

        // prepare the victim's characterID
        $victimCharacterId = $victimPilot->getExternalID();
        // make sure this is actually a real character ID; mustn be NULL, mustn't be less than 90M
        if(!$victimCharacterId || $victimCharacterId < 90000000)
        {
            $victimCharacterId = "None";
        }

        // prepare the final blow pilot's characterID
        $finalBlowCharacterId = $finalBlowPilot->getExternalID();
        // make sure this is actually a real character ID; mustn be NULL, mustn't be less than 3M
        // player characters are above 90M, Drifters are above 3M
        if(!$finalBlowCharacterId || $finalBlowCharacterId < 3000000)
        {
            $finalBlowCharacterId = "None";
        }

        // prepare the timestamp
        // this should be (unixtime * 10000000) + 116444736000000000 
        // but let's cut some zeros in order to support 32bit systems
        $timestamp = $timestamp  + 1644473600;
        $timestamp = '1'.$timestamp.'0000000';

        return sha1($victimCharacterId . $finalBlowCharacterId . $shipTypeID . $timestamp);
    }
        
	/**
	 * Compares two InvolvedParty objects for sorting by damage then name.
	 * @param InvolvedParty $a
	 * @param InvolvedParty $b
	 * @return int -1, 0, or 1
	 */
	static private function involvedComparator($a, $b)
	{
		return $b->getDamageDone() - $a->getDamageDone();
	}

	/**
	 * Return a new object by ID. Will fetch from cache if enabled.
	 *
	 * @param mixed $id ID to fetch
	 * @return Kill
	 */
	static function getByID($id)
	{
		return Cacheable::factory(get_class(), $id);
	}
        
        
        
}
