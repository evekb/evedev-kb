<?php
/*
 * $Date: 2011-04-22 17:06:57 +1000 (Fri, 22 Apr 2011) $
 * $Revision: 1274 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/branches/3.2/common/includes/class.toplist.php $
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
