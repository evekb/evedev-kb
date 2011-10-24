<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 *
 * A simple wrapper around variables.
 *
 * This is used by killlists to construct several kills at once from a single
 * db query.
 */
class KillWrapper extends Kill
{

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
	public $involvedparties_ = array();
	public $destroyeditems_ = array();
	public $droppeditems_ = array();
	private $fullinvolved = false;
	private $timestamp = null;
	private $dmgtaken = null;
	private $iskloss = 0;
	private $killpoints = null;
	private $victimid = null;
	private $victimcorpid = null;
	private $victimallianceid = null;
	private $victimshipid = null;
	private $fbpilotid = null;
	private $fbcorpid = null;
	private $fballianceid = null;
	private $hash = false;
	private $mail = null;
	private $trust = 0;
	private $involvedcount = null;
	private $valid = null;
	private $solarsystemid = null;
	private $solarsystemname = null;
	private $solarsystemsecurity = null;
	private $victimname = null;
	private $victimcorpname = null;
	private $victimalliancename = null;
	private $victimshipname = null;
	private $victimshipexternalid = null;
	private $victimshipclassname = null;
	private $victimshipvalue = null;
	private $fbpilotname = null;
	private $fbcorpname = null;
	private $fballiancename = null;
	private $victimexternalid = null;
	private $fbpilotexternalid = null;
	private $commentcount = null;

	/**
	 * @param integer $id The ID for this kill
	 * @param boolean $external If true then $id is treated as an external ID.
	 */
	function KillWrapper($id = 0, $external = false)
	{
		$id = intval($id);
		if ($id && $external) {
			$qry = DBFactory::getDBQuery();
			$qry->execute("SELECT kll_id FROM kb3_kills WHERE kll_external_id = " . $id);
			if ($qry->recordCount()) {
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
		foreach ($arr as $key => $val) {
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
		return $this->timestamp;
	}

	/**
	 * Return the victim Pilot object.
	 *
	 * @return Pilot
	 */
	function getVictim()
	{
		if (isset($this->victim)) {
			return $this->victim;
		}
		$this->victim = new Pilot($this->victimid);
		return $this->victim;
	}

	/**
	 * Return the victim Corporation.
	 *
	 * @return Corporation
	 */
	function getVictimCorp()
	{
		return $this->victimcorp;
	}

	/**
	 * Return the victim Alliance.
	 *
	 * @return Alliance
	 */
	function getVictimAlliance()
	{
		return $this->victimalliance;
	}

	/**
	 * Return the amount of damage taken by the victim.
	 * @return integer
	 */
	function getDamageTaken()
	{
		return $this->dmgtaken;
	}

	/**
	 * Return the victim's name.
	 * @return string
	 */
	function getVictimName()
	{
		return $this->victimname;
	}

	/**
	 * Return victim Pilot's ID.
	 * @return integer
	 */
	function getVictimID()
	{
		return $this->victimid;
	}

	/**
	 * Return victim Pilot's external ID.
	 * @return integer
	 */
	function getVictimExternalID()
	{
		return $this->victimexternalid;
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
		return $this->victimcorpid;
	}

	/**
	 * Return victim Corporation's name
	 * @return string
	 */
	function getVictimCorpName()
	{
		return $this->victimcorpname;
	}

	/**
	 * Return victim Alliance's name
	 * @return string
	 */
	function getVictimAllianceName()
	{
		return $this->victimalliancename;
	}

	/**
	 * Return victim Faction's name
	 * @return string
	 */
	function getVictimFactionName()
	{
		if ($this->getVictimAlliance()->isFaction()) {
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
		return $this->victimallianceid;
	}

	/**
	 * Return the SolarSystem this kill took place in.
	 * @return SolarSystem
	 */
	function getSystem()
	{
		if (isset($this->solarsystem)) {
			return $this->solarsystem;
		} else {
			$this->solarsystem = SolarSystem::getByID($this->solarsystemid);
		}

		return $this->solarsystem;
	}

	/**
	 * @return integer
	 */
	function getFBPilotID()
	{
		return $this->fbpilotid;
	}

	/**
	 * @return integer
	 */
	function getFBPilotExternalID()
	{
		return $this->fbpilotexternalid;
	}

	/**
	 * Return the Final Blow dealer's name.
	 * @return string
	 */
	function getFBPilotName()
	{
		return $this->fbpilotname;
	}

	/**
	 * Return the Final Blow dealer's Corporation ID.
	 * @return integer
	 */
	function getFBCorpID()
	{
		return $this->fbcorpid;
	}

	/**
	 * Return the Final Blow dealer's Corporation Name.
	 * @return string
	 */
	function getFBCorpName()
	{
		return $this->fbcorpname;
	}

	/**
	 * Return the Final Blow dealer's Alliance ID.
	 * @return integer
	 */
	function getFBAllianceID()
	{
		return $this->fballianceid;
	}

	/**
	 * Return the Final Blow dealer's Alliance ID.
	 * @return integer
	 */
	function getFBAllianceName()
	{
		return $this->fballiancename;
	}

	/**
	 * @return float
	 */
	function getISKLoss()
	{
		return $this->iskloss;
	}

	/**
	 * @return integer
	 */
	function getKillPoints()
	{
		return $this->killpoints;
	}

	/**
	 * Get name for this Kill's SolarSystem.
	 * @return string
	 */
	function getSolarSystemName()
	{
		return $this->solarsystemname;
	}

	/**
	 * Get Security level for this Kill's SolarSystem.
	 * @return float
	 */
	function getSolarSystemSecurity()
	{
		return $this->solarsystemsecurity;
	}

	/**
	 * Return the victim's Ship.
	 * @return Ship
	 */
	function getVictimShip()
	{
		return Ship::getByID($this->victimshipid);
	}

	/**
	 * Return the victim's Ship.
	 * @return Ship
	 */
	function getVictimShipID()
	{
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
		return $this->victimshipid;
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
		return $this->victimshipvalue;
	}

	/**
	 * Return an image for the victim's ship.
	 * @param integer $size
	 * @return string
	 */
	function getVictimShipImage($size)
	{
		return imageURL::getURL('Ship', $this->victimshipid, $size);
	}

	/**
	 * Check if the victim is in a Faction.
	 *
	 * @return boolean
	 */
	function getIsVictimFaction()
	{
		$alliance = new Alliance($this->getVictimAllianceID());
		return $alliance->isFaction();
	}

	/**
	 * Return the raw killmail for this kill.
	 *
	 * @return string
	 */
	function getRawMail()
	{
		$kill = Kill::getByID($this->id);
		return $kill->getRawMail();
	}

	/**
	 * Check if this kill is a duplicate and return the id if so.
	 * 
	 * @param boolean $checkonly
	 * @return integer
	 */
	function getDupe($checkonly = false)
	{
		trigger_error(__FUNCTION__ . " not implemented in this class", E_USER_ERROR);
	}

	private function execQuery()
	{
		trigger_error(__FUNCTION__ . " not implemented in this class", E_USER_ERROR);
	}

	/**
	 * Check if this kill is still within the classified period.
	 *
	 * @return boolean
	 */
	function isClassified()
	{
		if (config::get('kill_classified')) {
			if (user::role('classified_see'))
				return false;

			if ($this->getClassifiedTime() > 0)
				return true;
		}
		else
			return false;
	}

	/** Return the time left until this kill is not classified.
	 *
	 * @return integer
	 */
	function getClassifiedTime()
	{
		if (config::get('kill_classified') &&
				strtotime($this->getTimeStamp() . " UTC") >
				time() - config::get('kill_classified') * 3600) {
			return (config::get('kill_classified') * 3600
					- time() + strtotime($this->getTimeStamp() . " UTC"));
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
		return $this->involvedcount;
	}

	/**
	 * Set the number of involved parties - used by killlist
	 * @param integer $invcount
	 */
	function setInvolvedPartyCount($invcount = 0)
	{
		$this->involvedcount = $invcount;
	}

	function setDetailedInvolved()
	{
		$this->fullinvolved = true;
	}

	/**
	 * Return true if this kill exists and is valid.
	 * @return boolean
	 */
	function exists()
	{
		if (!isset($this->valid)) {
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
		if ($this->isClassified()) {
			return 0;
		}
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
		if ($this->isClassified()) {
			return 0;
		}
		return $this->relatedlosscount;
	}

	function countComment()
	{
		return $this->commentcount;
	}

	/**
	 * Set the number of comments - used by killlist
	 */
	function setCommentCount($comcount = 0)
	{
		$this->commentcount = $comcount;
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

	/**
	 * Calculate the current cost of a ship loss excluding blueprints.
	 * @param boolean $update set true to update all-time summaries.
	 * @return float
	 */
	function calculateISKLoss($update = true)
	{
		$value = 0;
		foreach ($this->destroyeditems_ as $itd) {
			$item = $itd->getItem();
			if (strpos($item->getName(), "Blueprint") === FALSE) {
				$value += $itd->getValue() * $itd->getQuantity();
			}
		}
		if (config::get('kd_droptototal')) {
			foreach ($this->droppeditems_ as $itd) {
				$item = $itd->getItem();
				if (strpos($item->getName(), "Blueprint") === FALSE) {
					$value += $itd->getValue() * $itd->getQuantity();
				}
			}
		}
		$value += $this->victimship->getPrice();
		if ($update) {
			$qry = DBFactory::getDBQuery();
			$qry->execute("UPDATE kb3_kills SET kll_isk_loss = '$value' WHERE
				kll_id = '" . $this->id . "'");
			if ($this->iskloss) {
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
			trigger_error("KillWrapper is not initialised", E_USER_ERROR);
			die;
		}

		$ship = $this->getVictimShip();
		$shipclass = $ship->getClass();
		$vicpoints = $shipclass->getPoints();
		$maxpoints = round($vicpoints * 1.2);

		foreach ($this->involvedparties_ as $inv) {
			$shipinv = $inv->getShip();
			$shipclassinv = $shipinv->getClass();
			$invpoints += $shipclassinv->getPoints();
		}

		if ($vicpoints + $invpoints > 0) {
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
		trigger_error(__FUNCTION__ . " not implemented in this class", E_USER_ERROR);
	}

	function remove($delcomments = true, $permanent = true)
	{
		trigger_error(__FUNCTION__ . " not implemented in this class", E_USER_ERROR);
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
		if (!$this->involvedparties_) {
			$this->execQuery();
		}
		return $this->involvedparties_;
	}

	function setHash($hash)
	{
		if (strlen($hash) > 16) {
			$this->hash = pack("H*", $hash);
		} else {
			$this->hash = $hash;
		}
	}

	function getHash($hex = false, $update = true)
	{
		if ($this->hash) {
			if ($hex) {
				return bin2hex($this->hash);
			} else {
				return $this->hash;
			}
		} else {
			trigger_error(__FUNCTION__ . " not implemented in this class",
					E_USER_ERROR);
		}
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
		return $this->trust;
	}

	/**
	 * Update this kill's external ID.
	 * @param integer $extID
	 */
	public function updateExternalID($extID)
	{
		trigger_error(__FUNCTION__ . " not implemented in this class", E_USER_ERROR);
	}

}