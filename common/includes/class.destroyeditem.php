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
class DestroyedItem
{
	/**
	 * @param Item $item
	 * @param integer $quantity
	 * @param string|integer $location
	 * @param integer $locationID
	 */
	function DestroyedItem($item, $quantity, $location, $locationID = null)
	{
		$this->item_ = $item;
		$this->quantity_ = $quantity;
		$this->location_ = $location;
		$this->locationID_ = $locationID;
	}

	/**
	 * @return Item
	 */
	function getItem()
	{
		return $this->item_;
	}

	/**
	 * @return integer
	 */
	function getQuantity()
	{
		if ($this->quantity_ == "") $this->quantity = 1;
		return $this->quantity_;
	}
	/**
	 * Return value formatted into millions or thousands.
	 *
	 * @return string
	 */
	function getFormattedValue()
	{
		if (!isset($this->value))
		{
			$this->getValue();
		}
		if ($this->value > 0)
		{
			$value = $this->value * $this->getQuantity();
			// Value Manipulation for prettyness.
			if (strlen($value) > 6) // Is this value in the millions?
			{
				$formatted = round($value / 1000000, 2);
				$formatted = number_format($formatted, 2);
				$formatted = $formatted." M";
			}
			elseif (strlen($value) > 3) // 1000's ?
			{
				$formatted = round($value / 1000, 2);

				$formatted = number_format($formatted, 2);
				$formatted = $formatted." K";
			}
			else
			{
				$formatted = number_format($value, 2);
				$formatted = $formatted." isk";
			}
		}
		else
		{
			$formatted = "0 isk";
		}
		return $formatted;
	}

	/**
	 * @return integer
	 */
	function getValue()
	{
		if (isset($this->value))
		{
			return $this->value;
		}
		if ($this->item_->row_['itm_value'])
		{
			$this->value = $this->item_->row_['itm_value'];
			return $this->item_->row_['itm_value'];
		}
		elseif ($this->item_->row_['baseprice'])
		{
			$this->value = $this->item_->row_['baseprice'];
			return $this->item_->row_['baseprice'];
		}
		$this->value = 0;
		$qry = DBFactory::getDBQuery();
		$qry->execute("select basePrice, price
					from kb3_invtypes
					left join kb3_item_price on kb3_invtypes.typeID=kb3_item_price.typeID
					where kb3_invtypes.typeID='".$this->item_->getID()."'");
		if ($row = $qry->getRow())
		{
			if ($row['price'])
			{
				$this->value = $row['price'];
			}
			else
			{
				$this->value = $row['basePrice'];
			}
		}
		return $this->value;

		//returns the value of an item
		$value = 0; 				// Set 0 value incase nothing comes back
		$id = $this->item_->getID(); // get Item ID
		$qry = DBFactory::getDBQuery();
		$qry->execute("select itm_value from kb3_items where itm_id= '".$id."'");
		$row = $qry->getRow();
		$value = $row['itm_value'];
		if ($value == '')
		{
			$value = 0;
		}
		return $value;
	}

	/**
	 * @return integer
	 */
	function getLocationID()
	{
		if(!is_null($this->locationID_)) return $this->locationID_;
		$id = false;
		if (strlen($this->location_) < 2)
		{
			$id = $this->item_->getSlot();
		}
		else
		{
			$qry = DBFactory::getDBQuery();
			$qry->execute("select itl_id from kb3_item_locations where itl_location = '".$this->location_."'");
			$row = $qry->getRow();
			$id = $row['itl_id'];
		}
		return $id;
	}
}