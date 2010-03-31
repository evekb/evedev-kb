<?php

// Fetches a information for each item in a list. The list is based on dropped
// items for given kill ids, dropped items for given kill ids or a list of
// itemIDs
class ItemList
{
	private $itemarray = array();
	private $destroyedIDarray = array();
	private $droppedIDarray = array();
	public $price = false;
	public $executed = false;
	private $qry = null;

	function ItemList($itemarray = null, $price = false)
	{
		$this->itemarray = $itemarray;
		$this->price = $price;
		$this->qry = DBFactory::getDBQuery();
	}

	// Add an itemID to the list of items to check.
	function addItem($itemID)
	{
		if($this->executed) return false;
		$this->itemarray[] = $itemID;
	}

	function addKillDestroyed($killID)
	{
		if($this->executed) return false;
		$this->destroyedIDarray[] = $killID;
	}

	function addKillDropped($killID)
	{
		if($this->executed) return false;
		$this->droppedIDarray[] = $killID;
	}

	function execute()
	{
		if ($this->executed || (!count($this->itemarray)&& !count($this->destroyedIDarray) && !count($this->droppedIDarray))) return;
		$sql = "select inv.icon as itm_icon, inv.typeID as itm_externalid, ".
			"itp.price as itm_value, kb3_item_types.*, dga.value as itm_techlevel, ".
			"dc.value as usedcharge, dl.value as usedlauncher, ".
			"inv.groupID, inv.typeName, inv.capacity, inv.raceID, inv.basePrice, inv.marketGroupID";
		if(count($this->destroyedIDarray)) $sql .= ", if(dl.attributeID IS NULL,sum(itd.itd_quantity),truncate(sum(itd.itd_quantity)/count(dl.attributeID),0)) as itd_quantity, ".
				"itd_itm_id, itd_itl_id, itl_location ";
		elseif(count($this->droppedIDarray)) $sql .= ", if(dl.attributeID IS NULL,sum(itd.itd_quantity),truncate(sum(itd.itd_quantity)/count(dl.attributeID),0)) as itd_quantity, ".
				"itd_itm_id, itd_itl_id, itl_location ";

		$sql .= "from kb3_invtypes inv ".
			"left join kb3_dgmtypeattributes dga on dga.typeID=inv.typeID and dga.attributeID=633 ".
			"left join kb3_item_price itp on itp.typeID=inv.typeID ".
			"left join kb3_item_types on inv.groupID=itt_id ".
			"left join kb3_dgmtypeattributes dc on dc.typeID = inv.typeID AND dc.attributeID IN (128) ".
			"left join kb3_dgmtypeattributes dl on dl.typeID = inv.typeID AND dl.attributeID IN (137,602) ";

		if(count($this->destroyedIDarray)) $sql .= "join kb3_items_destroyed itd on inv.typeID = itd_itm_id ".
				"and itd_kll_id in (".implode(',',$this->destroyedIDarray).") ".
				"left join kb3_item_locations itl on (itd.itd_itl_id = itl.itl_id or (itd.itd_itl_id = 0 and itl.itl_id = 1))";
		elseif(count($this->droppedIDarray)) $sql .= "join kb3_items_dropped itd on inv.typeID = itd_itm_id ".
				"and itd_kll_id in (".implode(',',$this->droppedIDarray).") ".
				"left join kb3_item_locations itl on (itd.itd_itl_id = itl.itl_id or (itd.itd_itl_id = 0 and itl.itl_id = 1)) ";
		else $sql .= "where inv.typeID in (".implode(',',$this->itemarray).") ";

		if(count($this->destroyedIDarray) || count($this->droppedIDarray))
		{
			$sql .= "group by itd.itd_itm_id, itd.itd_itl_id order by itd.itd_itl_id ";
		}

		$this->qry->execute($sql);
		$this->executed = true;
	}

	// Iterate through the list of items returned, returning one for each call
	function getItem()
	{
		if (!$this->executed) $this->execute();
		if($row = $this->qry->getRow())
		{
			// Set up a new Item and return it.
			$item = new Item($row['itm_externalid']);
			$item->executed_ = true;
			$item->row_ = $row;
			return $item;
		}
		return null;
	}

	// Rewind the list of items to the start.
	function rewind()
	{
		$this->qry->rewind();
	}
}

