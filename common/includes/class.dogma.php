<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class dogma
{
	private $valid = false;
	private $id = 0;
	public $item = array();
	public $attrib = array();
	public $effects = array();

	public function dogma($itemID)
	{
		$itemID = intval($itemID);
		if(!$itemID)
		{
			$this->valid = false;
			return;
		}
		$qry = DBFactory::getDBQuery();
		$query = 'select * from kb3_invtypes
                    left join kb3_item_types on itt_id = groupID
                    where typeID = '.$itemID;
		$qry->execute($query);

		if(!$row = $qry->getRow())
		{
			$this->valid = false;
			return;
		}
		$this->valid = true;
		$this->id = $row['typeID'];
		$this->item = $row;

		$this->attrib = array();
		$query = 'select kb3_dgmtypeattributes.*,kb3_dgmattributetypes.*,kb3_eveunits.displayName as unit
          from kb3_dgmtypeattributes
          inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
          left join kb3_eveunits on kb3_dgmattributetypes.unitID = kb3_eveunits.unitID
          where typeID = '.$itemID;

		$qry->execute($query);
		while($row = $qry->getRow())
		{
			if($row['displayName'] == '')
			{
				$row['displayName'] = $row['attributeName'];
			}
			$this->attrib[$row['attributeName']] = $row;
		}

		$this->effects = array();
		$query = 'select * from kb3_dgmtypeeffects
          inner join kb3_dgmeffects on kb3_dgmtypeeffects.effectID = kb3_dgmeffects.effectID
          where typeID = '.$itemID;

		$qry->execute($query);
		while($row = $qry->getRow())
		{
			if(!$row['effectName'])
			{
				var_export($row);
				continue;
			}
			if($row['displayName'] == '')
			{
				$row['displayName'] = $row['effectName'];
			}
			$this->effects[$row['effectName']] = $row;
		}
	}

	public function get($string)
	{
		if(isset($this->item[$string]))
		{
			return $this->item[$string];
		}

		return false;
	}

	public function isValid()
	{
		return $this->valid;
	}

	public static function resolveTypeID($id)
	{
		$id = intval($id);
		$qry = DBFactory::getDBQuery();
		$qry->execute('select typeName from kb3_invtypes where typeID='.$id);
		$row = $qry->getRow();
		return $row['typeName'];
	}

	public static function resolveGroupID($id)
	{
		$id = intval($id);
		$qry = DBFactory::getDBQuery();
		$qry->execute('select itt_name from kb3_item_types where itt_id='.$id);
		$row = $qry->getRow();
		return $row['itt_name'];
	}

	public static function resolveAttributeID($id)
	{
		$id = intval($id);
		$qry = DBFactory::getDBQuery();
		$qry->execute('select displayName from kb3_dgmattributetypes where attributeID='.$id);
		$row = $qry->getRow();
		return $row['displayName'];
	}
}
