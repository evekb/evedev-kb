<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


//! Contains the attributes of a Ship and standard methods to manipulate Ships.
class Ship
{
	private $executed = false;
	private $id = 0;
	private $externalid = null;
	private $shipname = null;
	private $shipclass = null;
	private $shiptechlevel = null;
	private $shipisofficer = null;
	private $shipisfaction = null;
	private $value = 0;

	//! Construct the Ship object.

	/*!
	 * A Ship object can be constructed from an ID and further details fetched
	 * from the db. It can also be constructed by passing more details to the
	 * constructor.
	 *
	 * \param $id The Ship ID.
	 * \param $externalID The Ship external ID.
	 * \param $name The Ship name.
	 * \param $class The ShipClass for this Ship.
	 */
	function Ship($id = 0, $externalID = null, $name = null, $class = null)
	{
		if($id)
			$this->id = intval($id);
		if(isset($externalID))
			$this->externalid = intval($externalID);
		if(isset($name))
			$this->shipname = $name;
		if(isset($class))
			$this->shipclass = $class;
	}
	//! Return the id for this Ship.

	/*!
	 * \return integer id for this Ship.
	*/
	function getID()
	{
		if($this->id) return $this->id;
		elseif($this->externalid)
		{
			$this->execQuery();
			return $this->id;
		}
		return 0;
	}
	//! Return the external id for this Ship.

	/*!
	 * \return integer external id for this Ship.
	*/
	function getExternalID()
	{
		if(!$this->externalid) $this->execQuery();
		return $this->externalid;
	}
	//! Return the name of this Ship.

	/*!
	 * \return string name of this Ship.
	*/
	function getName()
	{
		if (is_null($this->shipname)) $this->execQuery();
		return $this->shipname;
	}
	//! Return the ShipClass for this Ship.

	/*!
	 * \return ShipClass object for this Ship.
	*/
	function getClass()
	{
		if (is_null($this->shipclass)) $this->execQuery();
		return $this->shipclass;
	}
	//! Return the tech level of this Ship.

	/*!
	 * \return integer tech level for this Ship.
	*/
	function getTechLevel()
	{
		if (is_null($this->shiptechlevel)) $this->execQuery();
		return $this->shiptechlevel;
	}
	//! Return if this Ship is faction.

	/*!
	 * \return boolean factionality for this Ship.
	*/
	function isFaction()
	{
		if (is_null($this->shipisfaction)) $this->execQuery();
		return $this->shipisfaction;
	}
	//! Return the URL for a portrait of this Ship.

	/*!
	 * \param $size the size of the image to return.
	 * \return string containing valid URL for a portrait of this Ship.
	*/
	function getImage($size)
	{
		if (is_null($this->externalid)) $this->execQuery();

		return IMG_URL."/ships/".$size."_".$size."/".$this->externalid.".png";
	}
	//! Return the base price of this Ship.

	/*!
	 * \return a number representing the baseprice of this Ship.
	*/
	function getPrice()
	{
		if(!$this->value) $this->execQuery();
		return $this->value;
	}

	//! Set the name of this ship.

	//! \param $shipname the name to set for this Ship
	function setName($shipname)
	{
		$this->shipname = $shipname;
	}
	//! Set the class of this ship.

	//! \param $shipclass the class object to set for this Ship
	function setClass($shipclass)
	{
		$this->shipclass = $shipclass;
	}

	function execQuery()
	{
		if (!$this->executed)
		{
			$qry = DBFactory::getDBQuery();

			$sql = "select * from kb3_ships shp
						   inner join kb3_ship_classes scl on shp.shp_class = scl.scl_id";
			$sql .= ' left join kb3_item_price itm on (shp.shp_externalid = itm.typeID) ';
			if(isset($this->externalid)) $sql .= " where shp.shp_externalid = ".$this->externalid;
			else $sql .= " where shp.shp_id = ".$this->id;

			$qry->execute($sql);
			$row = $qry->getRow();
			$this->shipname = $row['shp_name'];
			$this->shipclass = new ShipClass($row['scl_id']);
			$this->shiptechlevel = $row['shp_techlevel'];
			$this->shipisfaction = $row['shp_isfaction'];
			$this->externalid = $row['shp_externalid'];
			$this->id = $row['shp_id'];

			if (!$this->value = $row['price'])
			{
				$this->value = $row['shp_baseprice'];
			}
		}
		$this->executed = true;
	}
	//! Look up a Ship by name.

	/*!
	 * \param $name a string containing a ship name.
	*/
	function lookup($name)
	{
		$pqry = new DBPreparedQuery();
		$pqry->prepare("select shp_id, shp_name, shp_techlevel, shp_externalid, price, shp_baseprice, shp_class, shp_isfaction from kb3_ships left join kb3_item_price on (shp_externalid = typeID) where shp_name = ?");
		$pqry->bind_param('s', $name);
		$baseprice=0;
		$price = 0;
		$scl_id = 0;
		$pqry->bind_result($this->id, $this->shipname, $this->shiptechlevel,
			$this->externalid, $price, $baseprice, $scl_id, $this->shipisfaction);
		if(!$pqry->execute() || !$pqry->recordCount()) return false;
		else $pqry->fetch();

		$this->shipclass = new ShipClass($scl_id);
		if (!$this->value = $price)
		{
			$this->value = $baseprice;
		}
	}
}

