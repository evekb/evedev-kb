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
class DroppedItem extends DestroyedItem
{
	function DroppedItem($item, $quantity, $location, $locationID = null)
	{
		$this->item_ = $item;
		$this->quantity_ = $quantity;
		$this->location_ = $location;
		$this->locationID_ = $locationID;
	}
}