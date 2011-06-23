<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_CapitalShipKiller extends TopList_Base
{
	function TopList_CapitalShipKiller()
	{
		trigger_error("Using ".get_class($this)." is deprecated. Use TopList_Kills and set ship classes as needed.", E_USER_NOTICE);
		$this->addVictimShipClass(20); // freighter
		$this->addVictimShipClass(19); // dread
		$this->addVictimShipClass(27); // carrier
		$this->addVictimShipClass(28); // mothership
		$this->addVictimShipClass(26); // titan
		$this->addVictimShipClass(29); // cap. industrial
	}
}
