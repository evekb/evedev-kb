<?php
require_once('class.dbprepared.php');

class Ship
{
    function Ship($id = 0, $externalFlag = false)
    {
		if($externalFlag)
		{
			$this->externalid_ = intval($id);
			$this->id_ = 0;
		}
        else
		{
			$this->id_ = intval($id);
			$this->externalid_ = 0;
		}
    }
	//! Return the id for this Ship.

	/*!
	 * \return integer id for this Ship.
	 */
    function getID()
    {
        if($this->id_) return $this->id_;
		elseif($this->externalid_)
		{
			$this->execQuery();
			return $this->id_;
		}
		return 0;
    }
	//! Return the external id for this Ship.

	/*!
	 * \return integer external id for this Ship.
	 */
    function getExternalID()
    {
		if(!$this->externalid_) $this->execQuery();
        return $this->externalid_;
    }
	//! Return the name of this Ship.

	/*!
	 * \return string name of this Ship.
	 */
    function getName()
    {
        if (empty($this->shipname_)) $this->execQuery();
        return $this->shipname_;
    }
	//! Return the ShipClass for this Ship.

	/*!
	 * \return ShipClass object for this Ship.
	 */
    function getClass()
    {
        if (!$this->shipclass_) $this->execQuery();
        return $this->shipclass_;
    }
	//! Return the tech level of this Ship.

	/*!
	 * \return integer tech level for this Ship.
	 */
    function getTechLevel()
    {
        if (!$this->shiptechlevel_) $this->execQuery();
        return $this->shiptechlevel_;
    }
	//! Return the URL for a portrait of this Ship.

	/*!
	 * \return string containing valid URL for a portrait of this Ship.
	 */
    function getImage($size)
    {
        if (!$this->externalid_)
        {
            $this->execQuery();
        }
        return IMG_URL."/ships/".$size."_".$size."/".$this->externalid_.".png";
    }
	//! Return the base price of this Ship.

	/*!
	 * \return a number representing the baseprice of this Ship.
	 */
    function getPrice()
    {
		if(!$this->value_) $this->execQuery();
        return $this->value_;
    }

	//! Set the name of this ship.

	//! \param $shipname the name to set for this Ship
    function setName($shipname)
    {
        $this->shipname_ = $shipname;
    }
	//! Set the class of this ship.

	//! \param $shipclass the class object to set for this Ship
    function setClass($shipclass)
    {
        $this->shipclass_ = $shipclass;
    }

    function execQuery()
    {
        if (!$this->qry_)
        {
			$this->qry_ = DBFactory::getDBQuery();;

			$this->sql_ = "select * from kb3_ships shp
						   inner join kb3_ship_classes scl on shp.shp_class = scl.scl_id";
			$this->sql_ .= ' left join kb3_item_price itm on (shp.shp_externalid = itm.typeID) ';
			if($this->externalid_) $this->sql_ .= " where shp.shp_externalid = ".$this->externalid_;
			else $this->sql_ .= " where shp.shp_id = ".$this->id_;

			$this->qry_->execute($this->sql_);
			$row = $this->qry_->getRow();
			$this->shipname_ = $row['shp_name'];
			$this->shipclass_ = new ShipClass($row['scl_id']);
			$this->shiptechlevel_ = $row['shp_techlevel'];
			$this->externalid_ = $row['shp_externalid'];

			if (!$this->value_ = $row['price'])
			{
				$this->value_ = $row['shp_baseprice'];
			}
        }
    }
	//! Look up a Ship by name.

	/*!
	 * \param $name a string containing a ship name.
	 */
    function lookup($name)
    {
		$pqry = new DBPreparedQuery();
		$pqry->prepare("select shp_id, shp_name, shp_techlevel, shp_externalid, price, shp_baseprice, shp_class from kb3_ships left join kb3_item_price on (shp_externalid = typeID) where shp_name = ?");
		$pqry->bind_param('s', $name);
		$baseprice=0;
		$price = 0;
		$scl_id = 0;
		$pqry->bind_result($this->id_, $this->shipname_, $this->shiptechlevel_,
			$this->externalid_, $price, $baseprice, $scl_id);
		if(!$pqry->execute() || !$pqry->recordCount()) return false;
		else $pqry->fetch_prepared();

		$this->shipclass_ = new ShipClass($scl_id);
		if (!$this->value_ = $price)
		{
			$this->value_ = $baseprice;
		}
    }
}

class ShipClass
{
    function ShipClass($id = 0)
    {
        if (!$id) $id = 0;
        $this->id_ = intval($id);

        $this->qry_ = DBFactory::getDBQuery();;
    }

    function getID()
    {
        return $this->id_;
    }

    function getName()
    {
        if (!$this->name_) $this->execQuery();
        return $this->name_;
    }

    // Why would you round here this is fucking retarded!
    function getValue()
    {
        if (!$this->value_) $this->execQuery();
        return round($this->value_ / 1000000, 2);
    }

    function getActualValue()
    {
        if (!$this->value_) $this->execQuery();
        return $this->value_;
    }

    function getPoints()
    {
        if (!$this->points_) $this->execQuery();
        return $this->points_;
    }

    function setName($name)
    {
        $this->name_ = $name;
    }

    function setValue($value)
    {
        $this->value_ = $value;
    }

    function getValueIndicator()
    {
        $value = $this->getValue();

        if ($value >= 0 && $value <= 1)
            $color = "gray";
        elseif ($value > 1 && $value <= 15)
            $color = "blue";
        elseif ($value > 15 && $value <= 25)
            $color = "green";
        elseif ($value > 25 && $value <= 40)
            $color = "yellow";
        elseif ($value > 40 && $value <= 80)
            $color = "red";
        elseif ($value > 80 && $value <= 250)
            $color = "orange";
        elseif ($value > 250)
            $color = "purple";

        return IMG_URL."/ships/ship-".$color.".gif";
    }

    function execQuery()
    {
        if (!$this->qry_->executed_)
        {
            $sql = "select *
                  from kb3_ship_classes
  	         where scl_id = ".$this->id_;

            $this->qry_->execute($sql);
            $row = $this->qry_->getRow();

            $this->name_ = $row['scl_class'];
            $this->value_ = $row['scl_value'];
            $this->points_ = $row['scl_points'];
        }
    }
}