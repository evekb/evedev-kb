<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');
require_once("common/includes/class.eveapi.php");

class APIAllianceMod
{
	public static function addCorpList($home)
	{
		$home->addBefore("summaryTable", "APIAllianceMod::corpList");
	}

	public static function replaceStats($home)
	{
		$home->replace("stats", "APIAllianceMod::stats");
	}

	public static function stats($home)
	{
		$tempMyCorp = new Corporation();

		$myAlliAPI = new AllianceAPI();
		$myAlliAPI->fetchalliances();

		// Use alliance ID if we have it
		if($home->alliance->getExternalID()) $myAlliance = $myAlliAPI->LocateAllianceID( $home->alliance->getExternalID() );
		else $myAlliance = $myAlliAPI->LocateAlliance( $home->alliance->getName() );

		if($home->alliance->isFaction()) $home->page->setTitle('Faction details - '.$home->alliance->getName() . " [" . $myAlliance["shortName"] . "]");
		else $home->page->setTitle('Alliance details - '.$home->alliance->getName() . " [" . $myAlliance["shortName"] . "]");

		$myCorpAPI = new API_CorporationSheet();

		if ($myAlliance)
		{
			foreach ( (array)$myAlliance["memberCorps"] as $tempcorp)
			{
				$myCorpAPI->setCorpID($tempcorp["corporationID"]);
				$result .= $myCorpAPI->fetchXML();

				if ($tempcorp["corporationID"] == $myAlliance["executorCorpID"])
				{
					$ExecutorCorp = $myCorpAPI->getCorporationName();
				}
				// Build Data array
				$membercorp["corpExternalID"] = $myCorpAPI->getCorporationID();
				$membercorp["corpName"] = $myCorpAPI->getCorporationName();
				$membercorp["ticker"] = $myCorpAPI->getTicker();
				$membercorp["members"] = $myCorpAPI->getMemberCount();
				$membercorp["joinDate"] = $tempcorp["startDate"];
				$membercorp["taxRate"] = $myCorpAPI->getTaxRate() . "%";
				$membercorp["url"] = $myCorpAPI->getUrl();

				$home->allianceCorps[] = $membercorp;

				// Check if corp is known to EDK DB, if not, add it.
				$tempMyCorp->Corporation();
				$tempMyCorp->lookup($myCorpAPI->getCorporationName());
				if ($tempMyCorp->getID() == 0)
				{
					$tempMyCorp->add($myCorpAPI->getCorporationName(), $home->alliance , substr($tempcorp["startDate"], 0, 16),$myCorpAPI->getCorporationID());
				}

				$membercorp = array();
				unset($membercorp);
			}

			$html .= "<table class='kb-table' width=\"100%\" border=\"0\" cellspacing='1'><tr class='kb-table-row-even'><td rowspan='8' width='128' align='center' bgcolor='black'>";

			if (file_exists("img/alliances/".$home->alliance->getUnique().".png"))
			{
				$html .= "<img src=\"".IMG_URL."/alliances/".$home->alliance->getUnique().".png\" border=\"0\" /></td>";
			}
			else
			{
				$html .= "<img src=\"".IMG_URL."/alliances/default.gif\" border=\"0\" /></td>";
			}
			if(!isset($home->kill_summary))
			{
				$home->kill_summary = new KillSummaryTable();
				$home->kill_summary->addInvolvedAlliance($home->alliance);
				$home->kill_summary->generate();
			}

			$html .= "<td class='kb-table-cell' width='150'><b>Kills:</b></td><td class='kl-kill'>".$home->kill_summary->getTotalKills()."</td>";
			$html .= "<td class='kb-table-cell' width='65'><b>Executor:</b></td><td class='kb-table-cell'><a href=\"?a=search&amp;searchtype=corp&amp;searchphrase=" . urlencode($ExecutorCorp) . "\">" . $ExecutorCorp . "</a></td></tr>";
			$html .= "<tr class='kb-table-row-even'><td class='kb-table-cell'><b>Losses:</b></td><td class='kl-loss'>".$home->kill_summary->getTotalLosses()."</td>";
			$html .= "<td class='kb-table-cell'><b>Members:</b></td><td class='kb-table-cell'>" . $myAlliance["memberCount"] . "</td></tr>";
			$html .= "<tr class='kb-table-row-even'><td class='kb-table-cell'><b>Damage done (ISK):</b></td><td class='kl-kill'>".round($home->kill_summary->getTotalKillISK()/1000000000, 2)."B</td>";
			$html .= "<td class='kb-table-cell'><b>Start Date:</b></td><td class='kb-table-cell'>" . $myAlliance["startDate"] . "</td></tr>";
			$html .= "<tr class='kb-table-row-even'><td class='kb-table-cell'><b>Damage received (ISK):</b></td><td class='kl-loss'>".round($home->kill_summary->getTotalLossISK()/1000000000, 2)."B</td>";
			$html .= "<td class='kb-table-cell'><b>Number of Corps:</b></td><td class='kb-table-cell'>" . count($myAlliance["memberCorps"]) . "</td></tr>";
			if ($home->kill_summary->getTotalKillISK())
			{
				 $efficiency = round($home->kill_summary->getTotalKillISK() / ($home->kill_summary->getTotalKillISK() + $home->kill_summary->getTotalLossISK()) * 100, 2);
			}
			else
			{
				$efficiency = 0;
			}

			$html .= "<tr class='kb-table-row-even'><td class='kb-table-cell'><b>Efficiency:</b></td><td class='kb-table-cell'><b>" . $efficiency . "%</b></td>";
			$html .= "<td class='kb-table-cell'></td><td class='kb-table-cell'></td></tr>";

			$html .= "</table>";
//			$html .= "<br />";
			return $html;
		}
		else
		{
			return $home->stats;
		}
	}

	public static function corpList($home)
	{
		if($home->view != '' && $home->view != 'recent_activity'
			&& $home->view != 'kills' && $home->view != 'losses') return '';
		$html = "<br /><table class='kb-table' width=\"100%\" border=\"0\" cellspacing='1'><tr class='kb-table-header'>";
		$html .= "<td class='kb-table-cell'><b>Corporation Name</b></td><td class='kb-table-cell' align='center'><b>Ticker</b></td><td class='kb-table-cell' align='center'><b>Members</b></td><td class='kb-table-cell' align='center'><b>Join Date</b></td><td class='kb-table-cell' align='center'><b>Tax Rate</b></td><td class='kb-table-cell'><b>Website</b></td></tr>";
		foreach ( (array)$home->allianceCorps as $tempcorp )
		{
			$html .= "<tr class='kb-table-row-even'>";
			$html .= "<td class='kb-table-cell'><a href=\"?a=corp_detail&crp_ext_id=" . $tempcorp["corpExternalID"] . "\">" . $tempcorp["corpName"] . "</a></td>";
			$html .= "<td class='kb-table-cell' align='center'>" . $tempcorp["ticker"] . "</td>";
			$html .= "<td class='kb-table-cell' align='center'>" . $tempcorp["members"] . "</td>";
			$html .= "<td class='kb-table-cell' align='center'>" . $tempcorp["joinDate"] . "</td>";
			$html .= "<td class='kb-table-cell' align='center'>" . $tempcorp["taxRate"] . "</td>";
			if($tempcorp["url"]) $html .= "<td class='kb-table-cell'><a href=\"" . $tempcorp["url"] . "\">" . $tempcorp["url"] . "</a></td>";
			else $html .= "<td class='kb-table-cell'></td>";
			$html .= "</tr>";
		}
		$html .= "</table><br />";
		return $html;
	}
}