<?php
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
	static public function update($kill, $difference, $differenceloot)
	{
		allianceSummary::update($kill, $difference, $differenceloot);
		corpSummary::update($kill, $difference, $differenceloot);
		pilotSummary::update($kill, $difference, $differenceloot);
	}
}