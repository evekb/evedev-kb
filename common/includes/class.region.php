<?php
/**
 * $Date: 2010-05-29 20:59:20 +1000 (Sat, 29 May 2010) $
 * $Revision: 705 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.solarsystem.php $
 * @package EDK
 */

/**
 * @package EDK
 */
class Region
{
	private $id = 0;
	private $name = '';
	private $coords = array();

    function Region($id = 0)
    {
        $this->id = $id;
    }

    function getID()
    {
        return $this->id;
    }

    function getName()
    {
        if(!$this->name) $this->execQuery();
        return $this->name;
    }

    function getCoords()
    {
        if(!$this->coords) $this->execQuery();
        return $this->coords;
    }

    function execQuery()
    {
		$qry = DBFactory::getDBQuery();
		$qry->execute("select * from kb3_regions where reg_id = ".$this->id);
		$row = $qry->getRow();
		$this->name = $row['reg_name'];
		$this->coords = array($row['reg_x'], $row['reg_y'], $row['reg_z']);
    }
}