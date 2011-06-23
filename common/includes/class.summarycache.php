<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * Convenience class to call summary caches for alliance, corp and pilots.
 * @package EDK
 */
class summaryCache
{
	static public function addKill($kill)
	{
		allianceSummary::addKill($kill);
		corpSummary::addKill($kill);
		pilotSummary::addKill($kill);
	}
	static public function delKill($kill)
	{
		allianceSummary::delKill($kill);
		corpSummary::delKill($kill);
		pilotSummary::delKill($kill);
	}
	static public function update($kill, $difference)
	{
		allianceSummary::update($kill, $difference);
		corpSummary::update($kill, $difference);
		pilotSummary::update($kill, $difference);
	}
}