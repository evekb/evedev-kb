<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_Griefer extends TopList_Kills
{
	function TopList_Griefer()
	{
		trigger_error("Using ".get_class($this)." is deprecated. Use TopList_Kills and set ship classes as needed.", E_USER_NOTICE);
		$this->addVictimShipClass(20); // freighter
		$this->addVictimShipClass(22); // exhumer
		$this->addVictimShipClass(7); // industrial
		$this->addVictimShipClass(12); // barge
		$this->addVictimShipClass(14); // transport
	}
}
