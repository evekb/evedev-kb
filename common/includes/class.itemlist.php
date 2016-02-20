<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Fetches a information for each item in a list.
 *
 * The list is based on dropped items for given kill ids, dropped items for
 * given kill ids or a list of itemIDs
 * @package EDK
 */
class ItemList
{
	private $itemarray = array();
	private $destroyedIDarray = array();
	private $droppedIDarray = array();
	/** @var float */
	public $price = 0;
	/** @var boolean */
	public $executed = false;
	/** @var DBBaseQuery */
	private $qry = null;

	/**
	 *
	 * @param array $itemarray
	 * @param float $price
	 */
	function __construct($itemarray = null, $price = 0)
	{
		if (isset($itemarray)) {
			$this->itemarray = $itemarray;
		}
		$this->price = $price;
		$this->qry = DBFactory::getDBQuery();
	}

	/**
	 * Add an itemID to the list of items.
	 * @param integer $itemID
	 */
	function addItem($itemID)
	{
		if ($this->executed) {
			return false;
		}
		$this->itemarray[] = $itemID;
	}

	/**
	 * Add an killID to the list of kills to check for destroyed items.
	 * @param integer $killID
	 */
	function addKillDestroyed($killID)
	{
		if ($this->executed) {
			return false;
		}
		$this->destroyedIDarray[] = $killID;
	}

	/**
	 * Add an killID to the list of kills to check for dropped items.
	 * @param integer $killID
	 */
	function addKillDropped($killID)
	{
		if ($this->executed) {
			return false;
		}
		$this->droppedIDarray[] = $killID;
	}

	function execute()
	{
		if ($this->executed
				|| (!count($this->itemarray)
				&& !count($this->destroyedIDarray)
				&& !count($this->droppedIDarray))) {
			return;
		}
		$sql = "select inv.icon, inv.typeID,
                            itp.price, kb3_item_types.*, dga.value as metalevel, dgb.value as techlevel,
                            dc.value as usedcharge, dl.value as usedlauncher,
                            inv.groupID, inv.typeName, inv.capacity, inv.raceID, inv.mass, inv.volume,
                            inv.basePrice, inv.marketGroupID, te.effectID as slotIndicator";
		if (count($this->destroyedIDarray)) {
			$sql .= ", if(dl.attributeID IS NULL,sum(itd.itd_quantity),"
					."truncate(sum(itd.itd_quantity)/count(dl.attributeID),0)) "
					."as itd_quantity, itd_itm_id, itd_itl_id, itl_flagText, itd_singleton ";
		} else if (count($this->droppedIDarray)) {
			$sql .= ", if(dl.attributeID IS NULL,sum(itd.itd_quantity),"
					."truncate(sum(itd.itd_quantity)/count(dl.attributeID),0)) "
					."as itd_quantity, itd_itm_id, itd_itl_id, itl_flagText, itd_singleton ";
		}

		$sql .= "from kb3_invtypes inv
                            left join kb3_dgmtypeattributes dga on dga.typeID=inv.typeID and dga.attributeID=633
                            left join kb3_dgmtypeattributes dgb on dgb.typeID=inv.typeID and dgb.attributeID=422
                            left join kb3_item_price itp on itp.typeID=inv.typeID
                            left join kb3_item_types on inv.groupID=itt_id
                            left join kb3_dgmtypeattributes dc on dc.typeID = inv.typeID AND dc.attributeID IN (128)
                            left join kb3_dgmtypeattributes dl on dl.typeID = inv.typeID AND dl.attributeID IN (137,602)
                            left join kb3_dgmtypeeffects te on te.typeID = inv.typeID AND te.effectID IN ("
                                        .implode(", ", array_keys(InventoryFlag::$EFFECT_ID_SLOT_MAPPING))
                                   .") ";

		if (count($this->destroyedIDarray)) {
			$sql .= "join kb3_items_destroyed itd on inv.typeID = itd_itm_id "
					."and itd_kll_id in ("
					.implode(',', $this->destroyedIDarray).") "
					."left join kb3_item_locations itl "
					."on (itd.itd_itl_id = itl.itl_flagID )";
		} else if (count($this->droppedIDarray)) {
			$sql .= "join kb3_items_dropped itd "
					."on inv.typeID = itd_itm_id and itd_kll_id in ("
					.implode(',', $this->droppedIDarray).") "
					."left join kb3_item_locations itl "
					."on (itd.itd_itl_id = itl.itl_flagID ) ";
		} else {
			$sql .= "where inv.typeID in (".implode(',', $this->itemarray).") ";
		}

		if (count($this->destroyedIDarray) || count($this->droppedIDarray)) {
			$sql .= "group by itd.itd_itm_id, itd.itd_itl_id, itd.itd_singleton "
					."order by itd.itd_itl_id ";
		}

		$this->qry->execute($sql);
		$this->executed = true;
	}

	/**
	 * Iterate through the list of items returned, returning one for each call
	 *
	 * @return Item
	 */
	function getItem()
	{
		if (!$this->executed) {
			$this->execute();
		}
		if ($row = $this->qry->getRow()) {
			// Set up a new Item and return it.
			$item = new Item($row['typeID'], $row);
			return $item;
		}
		return null;
	}

	/**
	 * Rewind the list of items to the start.
	 */
	function rewind()
	{
		$this->qry->rewind();
	}
}

