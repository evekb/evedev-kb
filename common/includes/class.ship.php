<?php

/**
 *  Changes
 *  11/23/06 - function getActualValue()
 *             Returns actual value of the ship unrounded, rounding is retarded but
 *             I dont want to fuck anything else up - Coni
 *
*/

class Ship
{
    function Ship($id = 0)
    {
        $this->id_ = $id;
    }

    function getID()
    {
        return $this->id_;
    }

    function getExternalID()
    {
		if(!$this->externalid_) $this->execQuery();
        return $this->externalid_;
    }

    function getName()
    {
        if ($this->shipname_ == "") $this->execQuery();
        return $this->shipname_;
    }

    function getClass()
    {
        if (!$this->shipclass_) $this->execQuery();
        return $this->shipclass_;
    }

    function getTechLevel()
    {
        if (!$this->shiptechlevel_) $this->execQuery();
        return $this->shiptechlevel_;
    }

    function getImage($size)
    {
        if (!$this->externalid_)
        {
            $this->execQuery();
        }
        return IMG_URL."/ships/".$size."_".$size."/".$this->externalid_.".png";
    }

    function setName($shipname)
    {
        $this->shipname_ = $shipname;
    }

    function setClass($shipclass)
    {
        $this->shipclass_ = $shipclass;
    }

    function getPrice()
    {
		if(!$this->value_) $this->execQuery();
        return $this->value_;
    }

    function execQuery()
    {
        if (!$this->qry_)
        {
			$this->qry_ = new DBQuery();

			$this->sql_ = "select * from kb3_ships shp
						   inner join kb3_ship_classes scl on shp.shp_class = scl.scl_id";
			$this->sql_ .= ' left join kb3_item_price itm on (shp.shp_externalid = itm.typeID) ';
			$this->sql_ .= " where shp.shp_id = ".$this->id_;

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

    function lookup($name)
    {
        $qry = new DBQuery();
        $qry->execute("select *
                        from kb3_ships
                       where shp_name = '".slashfix($name)."'");

        $row = $qry->getRow();
        if ($row['shp_id']) $this->id_ = $row['shp_id'];
    }
}

class ShipClass
{
    function ShipClass($id = 0)
    {
        if (!$id) $id = 0;
        $this->id_ = intval($id);

        $this->qry_ = new DBQuery();
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
?>