<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/*
 * Create a syndication feed of kills stored on this board.
 *
 * Flags
 * week = week
 * year = year
 * lastkllid = return all kills lastkllid on (ordered by kll_id)
 * range = return all kills with lastkllid <= id <= lastkllid + range
 * APIkills = restrict results to kills with an external id set
 * pilot = pilot to retrieve kills for
 * corp = corp_name = corp to retrieve kills for
 * alli = alli_name = alliance to retrieve kills for
 * master = retrieve all kills
 * friend = set pilot/corp/alli as involved killer (default is victim)
 * combined = return both kills and losses
 *
 */
@set_time_limit(120);
// include feed_fetcher to get version number
require_once('class.fetcher.php');
header("Content-Type: text/xml");
// maximum amount of kills to return.
$maxreturned = 200;
$html = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
  	<channel>
	<title>'.config::get('cfg_kbtitle').'</title>
	<link>'.KB_HOST.'</link>
	<description>Kill Feed '.$feedversion.'</description>
	<copyright>'.config::get('cfg_kbtitle')."</copyright>\n";
if($_GET['combined']) $html .= "<combined>true</combined>\n";
if($_GET['APIkills']) $html .= "<apikills>true</apikills>\n";
$klist = new KillList();
$klist->setPodsNoobShips(true);

$w = intval($_GET['week']);
if ($w)
{
	$klist->setWeek($w);
}
elseif (!isset($_GET['lastkllid']))
{
	$klist->setWeek(kbdate("W"));
}

$y = intval($_GET['year']);
if ($y)
{
	$klist->setYear($y);
}
elseif (!isset($_GET['lastkllid']))
{
	$klist->setYear(kbdate("o"));
}

$kid = intval($_GET['lastkllid']);
if (isset($_GET['lastkllid']))
{
	$klist->setMinKllID($kid);
	$klist->setOrderBy(' kll.kll_id ASC');
	$klist->setOrdered(true);
	$klist->setLimit($maxreturned);
	if(intval($_GET['range'])) $klist->setMaxKllID(intval($_GET['range'])+$kid);
}
// If asked, set feed to only retrieve kills with an external id set.
if (intval($_GET['APIkills'])) $klist->setAPIKill();
if ($_GET['pilot'] || $_GET['pilot_name'])
{
	if ($_GET['pilot'])
	{
		$p = $_GET['pilot'];
	}
	if ($_GET['pilot_name'])
	{
		$p = $_GET['pilot_name'];
	}
	$pilot = Pilot::lookup(urldecode($p));
}

if ($_GET['corp'] || $_GET['corp_name'])
{
	if ($_GET['corp'])
	{
		$c = $_GET['corp'];
	}
	if ($_GET['corp_name'])
	{
		$c = $_GET['corp_name'];
	}
	$corp = Corporation::lookup(urldecode($c));
}

if ($_GET['alli'] || $_GET['alliance_name'])
{
	if ($_GET['alli'])
	{
		$a = $_GET['alli'];
	}
	if ($_GET['alliance_name'])
	{
		$a = $_GET['alliance_name'];
	}
	$alli = Alliance::add(urldecode($a));
}

if ($_GET['master'] == 1 && config::get('feed_allowmaster') == 1)
{
	$master = true;
}

if (!$master && $_GET['losses'])
{
	if (config::get('cfg_pilotid')  && !$pilot && !corp && !$alli) // local
	{
		$klist->addVictimPilot(config::get('cfg_pilotid'));
	}
	if (config::get('cfg_corpid')  && !$pilot && !$corp && !$alli) // local
	{
		$klist->addVictimCorp(config::get('cfg_corpid'));
	}
	if (config::get('cfg_allianceid')  && !$pilot && !$corp && !$alli) // local
	{
		$klist->addVictimAlliance(config::get('cfg_allianceid'));
	}
	if ($pilot && $_GET['friend']) // remote friend
	{
		$klist->addVictimPilot($pilot);
	}
	if ($corp && $_GET['friend']) // remote friend
	{
		$klist->addVictimCorp($corp);
	}
	if ($alli && $_GET['friend']) // remote friend
	{
		$klist->addVictimAlliance($alli);
	}
	if ($pilot && !$_GET['friend']) // remote
	{
		$klist->addInvolvedPilot($pilot);
	}
	if ($corp && !$_GET['friend']) // remote
	{
		$klist->addInvolvedCorp($corp);
	}
	if ($alli && !$_GET['friend']) // remote
	{
		$klist->addInvolvedAlliance($alli);
	}
}
else if(!$master && $_GET['combined'])
{
	if($pilot) $klist->addCombinedPilot($pilot);
	if($corp) $klist->addCombinedCorp($corp);
	if($alli) $klist->addCombinedAlliance($alli);
}
else if (!$master)
{
	if (config::get('cfg_pilotid')  && !$pilot && !corp && !$alli) // local
	{
		$klist->addInvolvedPilot(config::get('cfg_pilotid'));
	}
	if (config::get('cfg_corpid')  && !$pilot && !$corp && !$alli) // local
	{
		$klist->addInvolvedCorp(config::get('cfg_corpid'));
	}
	if (config::get('cfg_allianceid')  && !$pilot && !$corp && !$alli) // local
	{
		$klist->addInvolvedAlliance(config::get('cfg_allianceid'));
	}
	if ($pilot && $_GET['friend']) // remote friend
	{
		$klist->addInvolvedPilot($pilot);
	}
	if ($corp && $_GET['friend']) // remote friend
	{
		$klist->addInvolvedCorp($corp);
	}
	if ($alli && $_GET['friend']) // remote friend
	{
		$klist->addInvolvedAlliance($alli);
	}
	if ($pilot && !$_GET['friend']) // remote
	{
		$klist->addVictimPilot($pilot);
	}
	if ($corp && !$_GET['friend']) // remote
	{
		$klist->addVictimCorp($corp);
	}
	if ($alli && !$_GET['friend']) // remote
	{
		$klist->addVictimAlliance($alli);
	}
}

$kills = array();
$finalkill = 0;
while ($kill = $klist->getKill())
{
	if ($kill->isClassified())
	{
		continue;
	}
	if($finalkill < $kill->getID())$finalkill = $kill->getID();
	$kills[$kill->getID()] = $kill->getTimestamp();
}
if (!$kid)
{
	asort($kills);
}
$qry = DBFactory::getDBQuery();
// If kills returned = $maxreturned assume that it was limited and set
// last kill as the lower of highest kill id returned or highest non-classified
// kill
if($klist->getCount() != $maxreturned)
{
	$qry = DBFactory::getDBQuery();
	if(config::get('kill_classified'))
	{
		$qry->execute('SELECT max(kll_id) as finalkill FROM kb3_kills WHERE kll_timestamp < "'.(date('Y-m-d H:i:s',time()-config::get('kill_classified')*60*60)).'"');
	}
	else $qry->execute('SELECT max(kll_id) as finalkill FROM kb3_kills');
	$row=$qry->getRow();
	$finalkill = intval($row['finalkill']);
}
elseif(config::get('kill_classified'))
{
	// Check if there are classified kills with lower kill ids still to come.
	$qry->execute('SELECT max(kll_id) as finalkill FROM kb3_kills WHERE kll_timestamp < "'.(date('Y-m-d H:i:s',time()-config::get('kill_classified')*60*60)).'"');
	$row=$qry->getRow();
	if($finalkill > intval($row['finalkill'])) $finalkill = intval($row['finalkill']);
}

$html .= '<finalkill>'.$finalkill."</finalkill>\n";
foreach ($kills as $id => $timestamp)
{
	$kill = new Kill($id);
	$html .= '<item>
				<title>'.$id.'</title>
				<time>'.$timestamp.'</time>';
	if($kill->getHash() !== false) $html .= '<hash>'.bin2hex($kill->getHash()).'</hash>';
	$html .= '<trust>'.$kill->getTrust().'</trust>';
	$html .= '			<description><![CDATA[ '.$kill->getRawMail().' ]]></description>
				<guid>?a=kill_detail&amp;kll_id='.$id."</guid>\n";
	if($kill->getExternalID()) $html .= "<apiID>".$kill->getExternalID()."</apiID>\n";
	$html .= "</item>\n";
}
$html .= '</channel></rss>';

if ($_GET['gz'])
{
	echo gzdeflate($html,6);
}
else
{
echo $html;
}
