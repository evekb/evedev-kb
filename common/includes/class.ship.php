<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Contains the attributes of a Ship and standard methods to manipulate Ships.
 * @package EDK
 */
class Ship extends Cacheable
{
	/** @var boolean */
	private $executed = false;
	/** @var integer */
	private $id = 0;
	/** @var string */
	private $shipname = null;
	/** @var ShipClass */
	private $shipclass = null;
	/** @var integer */
	private $shiptechlevel = null;
	/** @var boolean */
	private $shipisfaction = null;
	/** @var float */
	private $value = 0;

	/**
	 * Construct the Ship object.
	 *
	 * A Ship object can be constructed from an ID and further details fetched
	 * from the db. It can also be constructed by passing more details to the
	 * constructor.
	 *
	 * @param integer $id The Ship ID.
	 * @param null unused.
	 * @param string $name The Ship name.
	 * @param ShipClass $class The ShipClass for this Ship.
	 */
	function Ship($id = 0, $externalID = null, $name = null, $class = null)
	{
		if ($id) {
			$this->id = (int)$id;
		}
		if (isset($name)) {
			$this->shipname = $name;
		}
		if (isset($class)) {
			$this->shipclass = $class;
		}
	}

	/**
	 * Return the id for this Ship.
	 *
	 * @return integer id for this Ship.
	 */
	function getID()
	{
		if ($this->id) {
			return $this->id;
		} else if (isset($this->externalid)) {
			$this->execQuery();
			return $this->id;
		}
		return 0;
	}

	/**
	 * Return the external id for this Ship.
	 * 
	 * @deprecated
	 * @return integer external id for this Ship.
	 */
	function getExternalID()
	{
		return $this->getID();
	}

	/**
	 * Return the name of this Ship.
	 *
	 * @return string name of this Ship.
	 */
	function getName()
	{
		if (is_null($this->shipname)) {
			$this->execQuery();
		}
		return $this->shipname;
	}

	/**
	 * Return the ShipClass for this Ship.
	 *
	 * @return ShipClass object for this Ship.
	 */
	function getClass()
	{
		if (is_null($this->shipclass)) {
			$this->execQuery();
		}
		return $this->shipclass;
	}

	/**
	 * Return the tech level of this Ship.
	 *
	 * @return integer tech level for this Ship.
	 */
	function getTechLevel()
	{
		if ($this->shiptechlevel === null) {
			$this->execQuery();
			$attrib = dogma::getByID($this->id)->attrib['techLevel'];
			if($attrib) {
				$this->shiptechlevel = (int)$attrib['value'];
			}
			if(!$this->shiptechlevel) {
				$this->shiptechlevel = 1;
			}
			$this->putCache();
		}
		return $this->shiptechlevel;
	}

	/**
	 * Return if this Ship is faction.
	 *
	 * @return boolean factionality for this Ship.
	 */
	function isFaction()
	{
		if ($this->shipisfaction === null) {
			$this->execQuery();
			$attrib = dogma::getByID($this->id)->attrib['metaLevel'];
			if($attrib) {
				$metalevel = (int)$attrib['value'];
				$this->shipisfaction = ($metalevel > 0 && $metalevel != 5);
			}
			if(!$this->shipisfaction) {
				$this->shipisfaction = false;
			}
			$this->putCache();
		}
		return $this->shipisfaction;
	}

	/**
	 * Return the URL for a portrait of this Ship.
	 *
	 * @param integer $size the size of the image to return.
	 * @return string containing valid URL for a portrait of this Ship.
	 */
	function getImage($size)
	{
		if (is_null($this->id)) {
			$this->execQuery();
		}

		return imageURL::getURL('Ship', $this->id, $size);
	}

	/**
	 * Return the base price of this Ship.
	 *
	 * @return float a number representing the baseprice of this Ship.
	 */
	function getPrice()
	{
		if (!$this->value) {
			$this->execQuery();
		}
		return $this->value;
	}

	/**
	 * Set the name of this ship.
	 *
	 * @param string $shipname the name to set for this Ship
	 */
	function setName($shipname)
	{
		$this->shipname = $shipname;
	}

	/**
	 * Set the class of this ship.
	 *
	 * @param ShipClass $shipclass the class object to set for this Ship
	 */
	function setClass($shipclass)
	{
		$this->shipclass = $shipclass;
	}

	function execQuery()
	{
		if (!$this->executed) {
			if ($this->id && $this->isCached()) {
				$cache = $this->getCache();
				$this->shipname = $cache->shipname;
				$this->shipclass = $cache->shipclass;
				$this->shiptechlevel = $cache->shiptechlevel;
				$this->shipisfaction = $cache->shipisfaction;
				$this->id = $cache->id;
				$this->value = $cache->value;
				$this->executed = true;
				return;
			}

			$qry = DBFactory::getDBQuery();

			$sql = "SELECT typeName, shp_id, shp_class, basePrice, price FROM kb3_ships
						   INNER JOIN kb3_invtypes ON typeID=shp_id";
			$sql .= " NATURAL LEFT JOIN kb3_item_price";
			$sql .= " WHERE shp_id = ".$this->id;

			$qry->execute($sql);
			$row = $qry->getRow();
			$this->shipname = $row['shp_id'] ? $row['typeName'] : "Unknown";
			$this->shipclass = Cacheable::factory('ShipClass', $row['shp_class']);
			$this->id = (int) $row['shp_id'];

			if (!$this->value = (float) $row['price']) {
				$this->value = (float) $row['basePrice'];
			}

			if ($this->id) {
				$this->putCache();
			}
		}
		$this->executed = true;
	}

	/**
	 * Look up a Ship by name.
	 *
	 * @param string $name a string containing a ship name.
	 */
	static function lookup($name)
	{
		static $cache_name = array();
		static $pqry = null;
		static $id = 0;
		static $shp_name = "";
		static $scl_id = 0;
		static $typeName = "";

		if (isset($cache_name[$name])) {
			return $cache_name[$name];
		}
		if ($name == "Unknown") {
			$cache_name[$name] = Ship::getByID(0);
			return $cache_name[$name];
			
		}
		if ($pqry === null) {
			$pqry = new DBPreparedQuery();
			$pqry->prepare("SELECT typeID, typeName, shp_class"
					." FROM kb3_ships RIGHT JOIN kb3_invtypes ON shp_id=typeID"
					." WHERE typeName = ?");
		}

		$shp_name = $name = trim(stripslashes($name));
		$pqry->bind_param('s', $shp_name);
		$pqry->bind_result($id, $typeName, $scl_id);

		if (!$pqry->execute() || !$pqry->recordCount()) {
			return false;
		} else {
			$pqry->fetch();
		}
		if ($scl_id == null && $id) {
			$qry = DBFactory::getDBQuery();
			$qry->execute("INSERT INTO kb3_ships (shp_id, shp_class) values($id, 18)");
			$scl_id = 18; // "Unknown"
		}
		$shipclass = ShipClass::getByID($scl_id);
		$cache_name[$name] = new Ship($id, null, $typeName, $shipclass);
		return $cache_name[$name];
	}

	/**
	 * Return a new object by ID. Will fetch from cache if enabled.
	 *
	 * @param mixed $id ID to fetch
	 * @return Ship
	 */
	static function getByID($id)
	{
		return Cacheable::factory(get_class(), $id);
	}
}
