<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top locations by kills in a solarsystem

class TopList_Locations extends TopList_Base
{
	function generate()
	{
		$sql = "select count(kll.kll_location) as cnt, kll.kll_location as itemID, mdn.itemName as itemName
                from kb3_kills kll
	      INNER JOIN kb3_mapdenormalize mdn
		      on ( mdn.itemID = kll.kll_location )";

		$this->setSQLTop($sql);

		$this->setSQLBottom("group by kll.kll_location order by 1 desc limit ".$this->limit);
	}
}
