<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_PodKiller extends TopList_Kills
{
	function TopList_PodKiller()
	{
		trigger_error("Using ".get_class($this)." is deprecated. Use TopList_Kills and set ship classes as needed.", E_USER_NOTICE);
		$this->TopList_Kills();
		$this->addVictimShipClass(2); // capsule
	}
}
