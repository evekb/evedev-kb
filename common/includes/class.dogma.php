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
class dogma extends Cacheable
{
	private $valid = false;
	private $id = 0;
	public $item = array();
	public $attrib = array();
	public $effects = array();

	/**
	 * @param integer $itemID
	 */
	public function dogma($itemID)
	{
		$itemID = intval($itemID);
		if(!$itemID)
		{
			$this->valid = false;
			return;
		}
		if ($this->isCached()) {
			$cache = $this->getCache();
			$this->item = $cache->item;
			$this->attrib = $cache->attrib;
			$this->effects = $cache->effects;
			$this->valid = true;
		} else {
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
			$this->putCache();
		}
	}

	/**
	 *
	 * @param string $string
	 * @return boolean|string
	 */
	public function get($string)
	{
		if(isset($this->item[$string]))
		{
			return $this->item[$string];
		}

		return false;
	}

	/**
	 * @return boolean
	 */
	public function isValid()
	{
		return $this->valid;
	}

	/**
	 * Return the typeName for the given typeID
	 * 
	 * @param integer $id
	 * @return string
	 */
	public static function resolveTypeID($id)
	{
		$id = intval($id);
		$qry = DBFactory::getDBQuery();
		$qry->execute('select typeName from kb3_invtypes where typeID='.$id);
		$row = $qry->getRow();
		return $row['typeName'];
	}

	/**
	 * Return group name for the given ID
	 *
	 * @param integer $id
	 * @return string
	 */
	public static function resolveGroupID($id)
	{
		$id = intval($id);
		$qry = DBFactory::getDBQuery();
		$qry->execute('select itt_name from kb3_item_types where itt_id='.$id);
		$row = $qry->getRow();
		return $row['itt_name'];
	}

	/**
	 * Return Attribute name for the given attributeID
	 *
	 * @param integer $id
	 * @return string
	 */
	public static function resolveAttributeID($id)
	{
		$id = intval($id);
		$qry = DBFactory::getDBQuery();
		$qry->execute('select displayName from kb3_dgmattributetypes where attributeID='.$id);
		$row = $qry->getRow();
		return $row['displayName'];
	}

	public function getID()
	{
		return $this->id;
	}

	/**
	 * Return a new object by ID. Will fetch from cache if enabled.
	 *
	 * @param mixed $id ID to fetch
	 * @return dogma
	 */
	static function getByID($id)
	{
		return Cacheable::factory(get_class(), $id);
	}
}
