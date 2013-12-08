<?php
/**
 * Convenience class to call summary caches for alliance, corp and pilots.
 * @package EDK
 */
class summaryCache
{
	static public function addKill($kill)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("BEGIN");
		allianceSummary::addKill($kill);
		corpSummary::addKill($kill);
		pilotSummary::addKill($kill);
		config::set('last_summary_id', $lastkillid);
		$qry->execute("COMMIT");
	}
	static public function delKill($kill)
	{
		$qry = DBFactory::getDBQuery();
		$qry->execute("BEGIN");
		allianceSummary::delKill($kill);
		corpSummary::delKill($kill);
		pilotSummary::delKill($kill);
		$qry->execute("COMMIT");
	}
	static public function update($kill, $difference, $differenceloot)
	{
		allianceSummary::update($kill, $difference, $differenceloot);
		corpSummary::update($kill, $difference, $differenceloot);
		pilotSummary::update($kill, $difference, $differenceloot);
	}
}