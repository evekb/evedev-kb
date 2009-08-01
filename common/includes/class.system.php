<?php
require_once("db.php");

class SolarSystem
{
    function SolarSystem($id = 0)
    {
        $this->id_ = $id;
        $this->qry_ = new DBQuery();
    }

    function getID()
    {
        return $this->id_;
    }

    function getName()
    {
        $this->execQuery();
        return $this->row_['sys_name'];
    }

    function getSecurity($rounded = false)
    {
        $this->execQuery();
        $sec = $this->row_['sys_sec'];

        if ($rounded)
        {
            if ($sec <= 0)
                return number_format(0.0, 1);
            else
                return number_format(round($sec, 1), 1);
        }
        else return $sec;
    }

    function getConstellationName()
    {
        $this->execQuery();
        return $this->row_['con_name'];
    }

    function getRegionName()
    {
        $this->execQuery();
        return $this->row_['reg_name'];
    }

    function execQuery()
    {
        if (!$this->executed_)
        {
        $sql = "select *
                       from kb3_systems sys, kb3_constellations con,
		               kb3_regions reg
          		       where sys.sys_id = ".$this->id_."
        		       and con.con_id = sys.sys_con_id
        			   and reg.reg_id = con.con_reg_id";
            $this->qry_->execute($sql);
            $this->row_ = $this->qry_->getRow();
            $this->executed_ = true;
        }
    }

    function lookup($name)
    {
        $qry = new DBQuery();
        $qry->execute("select *
                       from kb3_systems
                       where sys_name = '".slashfix($name)."'");

        $row = $qry->getRow();
        if (!$row['sys_id'])
        {
            return null;
        }
        $this->id_ = $row['sys_id'];
        $this->executed_ = false;
    }
}

class Region
{
    function Region($id = 0)
    {
        $this->id_ = $id;
    }

    function getID()
    {
        return $this->id_;
    }

    function getName()
    {
        $this->execQuery();
        return $this->row_['reg_name'];
    }

    function execQuery()
    {
        if (!$this->qry_)
        {
            $this->qry_ = new DBQuery();
            $this->qry_->execute("select * from kb3_regions
	                        where reg_id = ".$this->id_);
            $this->row_ = $this->qry_->getRow();
        }
    }
}
?>