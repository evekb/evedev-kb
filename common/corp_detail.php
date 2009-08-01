<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');
require_once("common/includes/evelogo.php");
require_once("common/includes/class.eveapi.php");

$crp_id = intval($_GET['crp_id']);
$crp_external_id = intval($_GET['crp_external_id']);
if (!$crp_id && !$crp_external_id)
{
    if (CORP_ID)
    {
        $crp_id = CORP_ID;
    }
    else
    {
        echo 'no valid corp id specified<br/>';
        return;
    }
}

if(!$crp_id && $crp_external_id)
{
	$qry = new DBQuery();
	$qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id = ".$crp_external_id);
	if($qry->recordCount())
	{
		$row = $qry->getRow();
		$crp_id = $row['crp_id'];
	}
}

$month = $_GET['m'];
$year = $_GET['y'];

if ($month == '')
    $month = kbdate('m');

if ($year == '')
    $year = kbdate('Y');

if ($month == 12)
{
    $nmonth = 1;
    $nyear = $year + 1;
}
else
{
    $nmonth = $month + 1;
    $nyear = $year;
}
if ($month == 1)
{
    $pmonth = 12;
    $pyear = $year - 1;
}
else
{
    $pmonth = $month - 1;
    $pyear = $year;
}
$monthname = kbdate("F", strtotime("2000-".$month."-2"));

$all_id = $_GET['all_id'];
$corp = new Corporation($crp_id);
$alliance = $corp->getAlliance();

$kill_summary = new KillSummaryTable();
$kill_summary->addInvolvedCorp($corp);
$kill_summary->setBreak(config::get('summarytable_rowcount'));
$summary_html = $kill_summary->generate();

$corpname = str_replace(" ", "%20", $corp->getName() );
$myID = new API_NametoID();
$myID->setNames($corpname);
$html .= $myID->fetchXML();
$myNames = $myID->getNameData();
		
$myAPI = new API_CorporationSheet();
$myAPI->setCorpID($myNames[0]['characterID']);

$result .= $myAPI->fetchXML();

if ($result == "Corporation is not part of alliance.")
{
	$page = new Page('Corporation details - '.$corp->getName());
} else {
	$page = new Page('Corporation details - '.$corp->getName() . " [" . $myAPI->getTicker() . "]");
}	
$html .= "<table class=kb-table width=\"100%\" border=\"0\" cellspacing=1><tr class=kb-table-row-even><td rowspan=8 width=128 align=center bgcolor=black>";

if (file_exists("img/corps/".$corp->getID().".jpg") || file_exists("img/corps/".$corp->getUnique().".jpg"))
{
    $html .= "<img src=\"".$corp->getPortraitURL(128)."\" border=\"0\" alt=\"\"></td>";
}
else
{
	if ($alliance != 0)
	{	
		$mylogo = $myAPI->getLogo();
		
		if ($result == "Corporation is not part of alliance.")
		{
			$html .= "<img src=\"".IMG_URL."/campaign-big.gif\" border=\"0\" alt=\"\"></td>";
		} elseif ($result == "") {
			// create two sized logo's in 2 places - this allows checks already in place not to keep requesting corp logos each time page is viewed
			// class.thumb.php cannot work with png (although saved as jpg these are actually pngs) therefore we have to create the 128 size for it
			// doing this prevents the images being rendered each time the function is called and allows it to use one in the cache instead.
			CorporationLogo( $mylogo, 64, $corp->getID() );
			CorporationLogo( $mylogo, 128, $corp->getID() );
			
			$html .= "<img src=\"".$corp->getPortraitURL(128)."\" border=\"0\" alt=\"\"></td>";
		} else {
			// some kind of error getting details from CCP so abort writing file(s) allowing us to try again later - in the meantime, lets print trusty default
			// error text will also appear where the picture is, which is nice
			$html .= "<img src=\"".IMG_URL."/campaign-big.gif\" border=\"0\" alt=\"\"></td>";
		}	
	} else {
		$html .= "<img src=\"".IMG_URL."/campaign-big.gif\" border=\"0\" alt=\"\"></td>";
	}
}

if ($result == "Corporation is not part of alliance.")
{
	$html .= "<td class=kb-table-cell width=180><b>Alliance:</b></td><td class=kb-table-cell>";
	if ($alliance->getName() == "Unknown" || $alliance->getName() == "None")
	{
   		$html .= "<b>".$alliance->getName()."</b>";
	}
	else
	{
    	$html .= "<a href=\"?a=alliance_detail&amp;all_id=".$alliance->getID()."\">".$alliance->getName()."</a>";
	}
	$html .= "</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>".$kill_summary->getTotalKills()."</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$kill_summary->getTotalLosses()."</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".round($kill_summary->getTotalKillISK()/1000000000, 2)."B</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".round($kill_summary->getTotalLossISK()/1000000000, 2)."B</td></tr>";
	if ($kill_summary->getTotalKillISK())
	{
    	$efficiency = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
	}
	else
	{
   		$efficiency = 0;
	}

	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>" . $efficiency . "%</b></td></tr>";
	$html .= "</table>";
	$html .= "<br/>";
} else {
	$html .= "<td class=kb-table-cell width=150><b>Alliance:</b></td><td class=kb-table-cell>";
	if ($alliance->getName() == "Unknown" || $alliance->getName() == "None")
	{
   		$html .= "<b>".$alliance->getName()."</b>";
	}
	else
	{
    	$html .= "<a href=\"?a=alliance_detail&amp;all_id=".$alliance->getID()."\">".$alliance->getName()."</a>";
	}
	$html .= "</td><td class=kb-table-cell width=65><b>CEO:</b></td><td class=kb-table-cell><a href=\"?a=search&amp;searchtype=pilot&amp;searchphrase=" . urlencode($myAPI->getCeoName()) . "\">" . $myAPI->getCeoName() . "</a></td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>".$kill_summary->getTotalKills()."</td>";
	$html .= "<td class=kb-table-cell><b>HQ:</b></td><td class=kb-table-cell>" . $myAPI->getStationName() . "</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$kill_summary->getTotalLosses()."</td>";
	$html .= "<td class=kb-table-cell><b>Members:</b></td><td class=kb-table-cell>" . $myAPI->getMemberCount() . "</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".round($kill_summary->getTotalKillISK()/1000000000, 2)."B</td>";
	$html .= "<td class=kb-table-cell><b>Shares:</b></td><td class=kb-table-cell>" . $myAPI->getShares() . "</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".round($kill_summary->getTotalLossISK()/1000000000, 2)."B</td>";
	$html .= "<td class=kb-table-cell><b>Tax Rate:</b></td><td class=kb-table-cell>" . $myAPI->getTaxRate() . "%</td></tr>";
	if ($kill_summary->getTotalKillISK())
	{
    	$efficiency = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
	}
	else
	{
   		$efficiency = 0;
	}

	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>" . $efficiency . "%</b></td>";
	$html .= "<td class=kb-table-cell><b>Website:</b></td><td class=kb-table-cell><a href=\"" . $myAPI->getUrl() . "\">" . $myAPI->getUrl() . "</a></td></tr>";
	$html .= "</table>";
	//$html .= "Corporation Description:";
	$html .= "<div class=kb-table-row-even style='width:100%;height:100px;overflow:auto'>";
	$html .= $myAPI->getDescription();
	$html .= "</div>";
	$html .= "<br/>";
}

if ($_GET['view'] == "" || $_GET['view'] == "kills" || $_GET['view'] == "losses")
{
    $html .= $summary_html;
}

switch ($_GET['view'])
{
    case "":
        $html .= "<div class=kb-kills-header>10 Most recent kills in the last 30 days</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addInvolvedCorp($corp);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
		$list->setStartDate(date('Y-m-d H:i',strtotime('- 30 days')));

        $ktab = new KillListTable($list);
        $ktab->setLimit(10);
        $ktab->setDayBreak(false);
        $html .= $ktab->generate();

        $html .= "<div class=kb-losses-header>10 Most recent losses in the last 30 days</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addVictimCorp($corp);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
		$list->setStartDate(date('Y-m-d H:i',strtotime('- 30 days')));

        $ltab = new KillListTable($list);
        $ltab->setLimit(10);
        $ltab->setDayBreak(false);
        $html .= $ltab->generate();

        break;
    case "kills":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->addInvolvedCorp($corp);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
		$list->setPageSplitter(config::get('killcount'));
		$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        break;
    case "losses":
        $html .= "<div class=kb-losses-header>All losses</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setPodsNoobships(true);
        $list->addVictimCorp($corp);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
		$list->setPageSplitter(config::get('killcount'));
		$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        break;
    case "pilot_kills":
        $html .= "<div class=block-header2>Top killers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopKillsList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "<table width=300 cellspacing=1><tr><td><a href='?a=corp_detail&amp;view=pilot_kills&amp;m=$pmonth&amp;crp_id=$crp_id&amp;y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_kills&amp;crp_id=$crp_id&amp;m=$nmonth&amp;y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopKillsList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_scores":
        $html .= "<div class=block-header2>Top scorers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopScoreList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(true);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "<table width=300 cellspacing=1><tr><td><a href='?a=corp_detail&amp;view=pilot_scores&amp;m=$pmonth&amp;crp_id=$crp_id&amp;y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_scores&amp;crp_id=$crp_id&amp;m=$nmonth&amp;y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopScoreList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_solo":
        $html .= "<div class=block-header2>Top solokillers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopSoloKillerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Solokills");
        $html .= $table->generate();

        $html .= "<table width=300 cellspacing=1><tr><td><a href='?a=corp_detail&amp;view=pilot_solo&amp;m=$pmonth&amp;crp_id=$crp_id&amp;y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_solo&amp;crp_id=$crp_id&amp;m=$nmonth&amp;y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopSoloKillerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Solokills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;

    case "pilot_damage":
        $html .= "<div class=block-header2>Top damagedealers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopDamageDealerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "<table width=300 cellspacing=1><tr><td><a href='?a=corp_detail&amp;view=pilot_damage&amp;m=$pmonth&amp;crp_id=$crp_id&amp;y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_damage&amp;crp_id=$crp_id&amp;m=$nmonth&amp;y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopDamageDealerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;

    case "pilot_griefer":
        $html .= "<div class=block-header2>Top griefers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopGrieferList();
        $list->addInvolvedCorp($corp);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "<table width=300 cellspacing=1><tr><td><a href='?a=corp_detail&amp;view=pilot_griefer&amp;m=$pmonth&amp;crp_id=$crp_id&amp;y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_griefer&amp;crp_id=$crp_id&amp;m=$nmonth&amp;y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopGrieferList();
        $list->addInvolvedCorp($corp);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;

    case "pilot_losses":
        $html .= "<div class=block-header2>Top losers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopLossesList();
        $list->addVictimCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

        $html .= "<table width=300 cellspacing=1><tr><td><a href='?a=corp_detail&amp;view=pilot_losses&amp;m=$pmonth&amp;crp_id=$crp_id&amp;y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_losses&amp;crp_id=$crp_id&amp;m=$nmonth&amp;y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopLossesList();
        $list->addVictimCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "ships_weapons":
        $html .= "<div class=block-header2>Ships &amp; weapons used</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=400>";
        $shiplist = new TopShipList();
        $shiplist->addInvolvedCorp($corp);
        $shiplisttable = new TopShipListTable($shiplist);
        $html .= $shiplisttable->generate();
        $html .= "</td><td valign=top align=right width=400>";

        $weaponlist = new TopWeaponList();
        $weaponlist->addInvolvedCorp($corp);
        $weaponlisttable = new TopWeaponListTable($weaponlist);
        $html .= $weaponlisttable->generate();
        $html .= "</td></tr></table>";

        break;
    case 'violent_systems':
        $html .= "<div class=block-header2>Most violent systems</div>";
        $html .= "<table width=\"99%\"><tr><td align=center valign=top>";

        $html .= "<div class=block-header>This month</div>";
        $html .= "<table class=kb-table>";
        $html .= "<tr class=kb-table-header><td>#</td><td width=180>System</td><td width=40 align=center >Kills</td></tr>";

        $sql = "select sys.sys_name, sys.sys_sec, sys.sys_id, count(distinct kll.kll_id) as kills
                    from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
                    where kll.kll_system_id = sys.sys_id
                    and inv.ind_kll_id = kll.kll_id";

        if ($crp_id)
            $sql .= " and inv.ind_crp_id in (".$crp_id.")";
        if ($all_id)
            $sql .= " and inv.ind_all_id = ".$all_id;

        $sql .= "   and date_format( kll.kll_timestamp, \"%c\" ) = ".kbdate("m")."
                    and date_format( kll.kll_timestamp, \"%Y\" ) = ".kbdate("Y")."
                    group by sys.sys_name
                    order by kills desc
                    limit 25";

        $qry = new DBQuery();
        $qry->execute($sql);
        $odd = false;
        $counter = 1;
        while ($row = $qry->getRow())
        {
            if (!$odd)
            {
                $odd = true;
                $rowclass = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $rowclass = 'kb-table-row-even';
            }

            $html .= "<tr class=".$rowclass."><td><b>".$counter.".</b></td><td class=kb-table-cell width=180><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=center>".$row['kills']."</td></tr>";
            $counter++;
        }

        $html .= "</table>";

        $html .= "</td><td align=center valign=top>";
        $html .= "<div class=block-header>All-Time</div>";
        $html .= "<table class=kb-table>";
        $html .= "<tr class=kb-table-header><td>#</td><td width=180>System</td><td width=40 align=center>Kills</td></tr>";

        $sql = "select sys.sys_name, sys.sys_id, sys.sys_sec, count(distinct kll.kll_id) as kills
                    from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
                    where kll.kll_system_id = sys.sys_id
                    and inv.ind_kll_id = kll.kll_id";

        if ($crp_id)
            $sql .= " and inv.ind_crp_id in (".$crp_id.")";
        if ($all_id)
            $sql .= " and inv.ind_all_id = ".$all_id;

        $sql .= " group by sys.sys_name
                    order by kills desc
                    limit 25";

        $qry = new DBQuery();
        $qry->execute($sql);
        $odd = false;
        $counter = 1;
        while ($row = $qry->getRow())
        {
            if (!$odd)
            {
                $odd = true;
                $rowclass = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $rowclass = 'kb-table-row-even';
            }

            $html .= "<tr class=".$rowclass."><td><b>".$counter.".</b></td><td class=kb-table-cell><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=center>".$row['kills']."</td></tr>";
            $counter++;
        }
        $html .= "</table>";
        $html .= "</td></tr></table>";
        break;
}

$html .= "<hr><b>Extended Corp Detail " . EVELOGOVERSION . " by " . FindThunk() . ". Logo generation by Entity. Thanks to Arkady and Exi.</b><br/>";

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Kills &amp; losses");
$menubox->addOption("link","Recent activity", "?a=corp_detail&amp;crp_id=" . $corp->getID());
$menubox->addOption("link","Kills", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=kills");
$menubox->addOption("link","Losses", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=losses");
$menubox->addOption("caption","Pilot statistics");
$menubox->addOption("link","Top killers", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=pilot_kills");

if (config::get('kill_points'))
    $menubox->addOption("link","Top scorers", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=pilot_scores");
$menubox->addOption("link","Top solokillers", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=pilot_solo");
$menubox->addOption("link","Top damagedealers", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=pilot_damage");
$menubox->addOption("link","Top griefers", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=pilot_griefer");
$menubox->addOption("link","Top losers", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=pilot_losses");
$menubox->addOption("caption","Global statistics");
$menubox->addOption("link","Ships &amp; weapons", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=ships_weapons");
$menubox->addOption("link","Most violent systems", "?a=corp_detail&amp;crp_id=" . $corp->getID() . "&amp;view=violent_systems");
$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>
