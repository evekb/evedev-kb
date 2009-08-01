<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');
require_once('mods/rank_mod/rank.php');

if (!$all_id = intval($_GET['all_id']))
{
    if (ALLIANCE_ID)
    {
        $all_id = ALLIANCE_ID;
    }
    else
    {
        echo 'no valid alliance id specified<br/>';
        return;
    }
}

$rank_known = config::get('rankmod_known');

$medals=array(
array( 'type' => 'eagle', 'name' => 'Killer', 'cnt' => 0, 'mname' =>'Silver Eagle'),		// 0
array( 'type' => 'redcross', 'name' => 'Scorer', 'cnt' => 0,  'mname' =>'Iron Cross'),		// 1
array( 'type' => 'cross', 'name' => 'Solo Killer', 'cnt' => 0, 'mname' =>'Winged Cross'),	// 2
array( 'type' => 'wing1', 'name' => 'Damagedealer', 'cnt' => 0, 'mname' =>'Diamond Wing'),	// 3	
array( 'type' => 'skull', 'name' => 'Final Blows', 'cnt' => 0, 'mname' =>'Red Skull'),		// 4
array( 'type' => 'globe', 'name' => 'Podkiller', 'cnt' => 0, 'mname' =>'Silver Globe'),		// 5
array( 'type' => 'star', 'name' => 'Griefer', 'cnt' => 0, 'mname' =>'Golden Star'),		// 6
array( 'type' => 'wing2', 'name' => 'ISK Killer', 'cnt' => 0, 'mname' =>'Gold Wing'),		// 7
array( 'type' => 'moon', 'name' => 'Loser', 'cnt' => 0, 'mname' =>'Purple Moon')		// 8
);

$alliance = new Alliance($all_id);
$page = new Page('Alliance details - '.$alliance->getName());

$html .= "<table class=kb-table width=\"100%\" border=\"0\" cellspacing=1><tr class=kb-table-row-even><td rowspan=8 width=128 align=center bgcolor=black>";

if (file_exists("img/alliances/".$alliance->getUnique().".png"))
{
    $html .= "<img src=\"".IMG_URL."/alliances/".$alliance->getUnique().".png\" border=\"0\"></td>";
}
else
{
    $html .= "<img src=\"".IMG_URL."/alliances/default.gif\" border=\"0\"></td>";
}
$kill_summary = new KillSummaryTable();
$kill_summary->addInvolvedAlliance($alliance);
$kill_summary->setBreak(config::get('summarytable_rowcount'));
$summary_html = $kill_summary->generate();
$k_cost=$kill_summary->getTotalKillISK();
$l_cost=$kill_summary->getTotalLossISK();
$k_count=$kill_summary->getTotalKills();
$l_count=$kill_summary->getTotalLosses();
	  if (($k_cost == 0) && ($l_cost == 0)) {
	    $efficiency = 'N/A';
	  } elseif ($k_cost == 0) {
	    $efficiency = '0%';
	  } elseif ($l_cost == 0) {
	    $efficiency = '100%';
	  } else {
	    $efficiency = round($k_cost / ($k_cost + $l_cost) * 100, 2).'%';
	  }
	  if ($k_cost >= 1000000000) {
	    $k_cost = round($k_cost / 1000000000, 2).'B';
	  } else { 
	    $k_cost = round($k_cost / 1000000, 2).'M';
	  }
	  if ($l_cost >= 1000000000) {
	    $l_cost = round($l_cost / 1000000000, 2).'B';
	  } else { 
	    $l_cost = round($l_cost / 1000000, 2).'M';
	  }
	  if ($k_count == 0) {
	    $k_ratio = 'N/A';
	  } elseif ($l_count == 0) {
	    $k_ratio = $k_count.' : 0';
	  } else {
	    $k_ratio = round($k_count / $l_count, 2).' : 1';
	  }

$html .= "<td class=kb-table-cell width=180><b>Kills:</b></td><td class=kl-kill>".$k_count."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$l_count."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".$k_cost."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".$l_cost."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>" . $efficiency . "</b></td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kill Ratio:</b></td><td class=kb-table-cell><b>" . $k_ratio . "</b></td></tr>";
$html .= "</table>";
$html .= "<br/>";

if ($_GET['view'] == "" || $_GET['view'] == "kills" || $_GET['view'] == "losses")
{
    $html .= $summary_html;
}

switch ($_GET['view'])
{
    case "":
        $html .= "<div class=kb-kills-header>10 Most recent kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addInvolvedAlliance($alliance);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $ktab = new KillListTable($list);
        $ktab->setLimit(10);
        $ktab->setDayBreak(false);
        $html .= $ktab->generate();

        $html .= "<div class=kb-losses-header>10 Most recent losses</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addVictimAlliance($alliance);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $ltab = new KillListTable($list);
        $ltab->setLimit(10);
        $ltab->setDayBreak(false);
        $html .= $ltab->generate();

        break;
    case "kills":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->addInvolvedAlliance($alliance);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);
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
        $list->addVictimAlliance($alliance);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        break;
    case "corp_kills":
        $html .= "<div class=block-header2>Top killers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopCorpKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopCorpTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopCorpKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopCorpTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "corp_kills_class":
        $html .= "<div class=block-header2>Destroyed ships</div>";

        // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopCorpKillsList();
            $list->addInvolvedAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopCorpTable($list, "Kills");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center>#</td><td class=kb-table-cell align=center>Corporation</td><td class=kb-table-cell align=center width=60>Kills</td></tr></table>'){
            $html .= "<td valign=top width=440>";
            $html .= "<div class=block-header>".$shp->getName()."</div>";
            $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }

        }
        $html .= "</tr></table>";

        break;
    case "kills_class":
        $html .= "<div class=block-header2>Destroyed ships</div>";

        // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopKillsList();
            $list->addInvolvedAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopPilotTable($list, "Kills");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center colspan=2>Pilot</td><td class=kb-table-cell align=center width=60>Kills</td></tr></table>'){
            $html .= "<td valign=top width=440>";
            $html .= "<div class=block-header>".$shp->getName()."</div>";
            $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }

        }
        $html .= "</tr></table>";

        break;
    case "corp_losses_class":
        $html .= "<div class=block-header2>Lost ships</div>";

            // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopCorpLossesList();
                $list->addVictimAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopCorpTable($list, "Losses");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center>#</td><td class=kb-table-cell align=center>Corporation</td><td class=kb-table-cell align=center width=60>Losses</td></tr></table>'){
            $html .= "<td valign=top width=440>";
                $html .= "<div class=block-header>".$shp->getName()."</div>";
                $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }
        }
        $html .= "</tr></table>";

        break;
    case "losses_class":
        $html .= "<div class=block-header2>Lost ships</div>";

            // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopLossesList();
                $list->addVictimAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopPilotTable($list, "Losses");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center colspan=2>Pilot</td><td class=kb-table-cell align=center width=60>Losses</td></tr></table>'){
            $html .= "<td valign=top width=440>";
                $html .= "<div class=block-header>".$shp->getName()."</div>";
                $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }
        }
        $html .= "</tr></table>";

        break;
    case "corp_losses":
        $html .= "<div class=block-header2>Top losers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopCorpLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopCorpTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopCorpLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopCorpTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_kills":
        $html .= "<div class=block-header2>Top killers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_scores":
        $html .= "<div class=block-header2>Top scorers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopScoreList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopScoreList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_losses":
        $html .= "<div class=block-header2>Top losers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_solo":
        $html .= "<div class=block-header2>Top solokillers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopSoloKillerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopSoloKillerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_damage":
        $html .= "<div class=block-header2>Top damagedealers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopDamageDealerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopDamageDealerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_blow":
        $html .= "<div class=block-header2>Top finalblows</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopFinalBlowList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopFinalBlowList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

	break;
    case "pilot_pod":
        $html .= "<div class=block-header2>Top podkillers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopPodKillerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopPodKillerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_griefer":
        $html .= "<div class=block-header2>Top griefers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopGrieferList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopGrieferList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_isk":
        $html .= "<div class=block-header2>Top ISK killers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopCapitalShipKillerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth(kbdate("m"));
        $list->setYear(kbdate("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopCapitalShipKillerList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "ships_weapons":
        $html .= "<div class=block-header2>Ships & weapons used</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=400>";
        $shiplist = new TopShipList();
        $shiplist->addInvolvedAlliance($alliance);
        $shiplisttable = new TopShipListTable($shiplist);
        $html .= $shiplisttable->generate();
        $html .= "</td><td valign=top align=right width=400>";

        $weaponlist = new TopWeaponList();
        $weaponlist->addInvolvedAlliance($alliance);
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
    case "pilot_ranks":
	break;
    case "pilot_medals":
	break;
   case "evo_ranks":
	$rank_imageset = config::get('rankmod_imageset');
	$rank_titleset = config::get('rankmod_titleset');
	$keep_title = config::get('rankmod_keep');
	$rank_type = config::get('rankmod_rtype');
	$rank_ttl = config::getnumerical('rankmod_titles');
        if ($keep_title) { $words = 'Custom Rank Set'; } else { $words = $rank_titleset." Rank Set"; }
	$html .= "<div class=block-header2>Rank Evolution Table - ".$words." with ".$rank_imageset." Insignia Set - ".$rank_type."</div>";
	$html .= "<table class=kb-table width=\"750\" border=\"0\" cellspacing=\"1\">";
	$html .= "<tr><td width=34><b>Icon</b></td><td width=266><b>Rank Name</b></td><td width=150><b>Abbreviation</b></td><td width=150><b>Req. Rank Points</b></td><td width=150><b>Req. Kill Points</b></td></tr>";
	foreach($rank_ttl as $level) {
	  $html .= "<tr height=36><td class=\"item-icon\" valign=\"top\" width=\"34\" height=\"36\">".$level['img']."</td>";	
	  $html .= "<td>".$level['title']."</td>";
	  $html .= "<td>".$level['abbr']."</td>";
	  $html .= "<td align=right>".$level['reqrp']."</td>";
	  $html .= "<td align=right>".$level['reqkp']."</td></tr>";
	}
	$html .= "</table><br>";
	break;
   case "rank_ribbons":
	$rank_badges = config::getnumerical('rankmod_badreqs');
	$rank_sub_badges = config::getnumerical('rankmod_sub_badreqs');
	GetEnabledClasses($shipbadges);	
	$html .= "<div class=block-header2>Grantable Ship Combat Ribbons</div>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=108>Ribbon</td><td width=300>Name / Class</td><td>Description</td></tr>";
	$class='odd';
	foreach ($shipbadges as $i => $ship)
	{
	  if ($class=='odd') {$class='even';} else {$class='odd';}
	  if ($ship['type'] == 'kamikaze') { $conj = 'like a'; } else { $conj = 'in a'; }
	  if (isset($ship['parent'])) {
		$expert = $rank_sub_badges[$ship['cnt']][2];
		$veteran = $rank_sub_badges[$ship['cnt']][1];
		$elite = $rank_sub_badges[$ship['cnt']][0];
	  } else {
		$expert = $rank_badges[$ship['cnt']][2];
		$veteran = $rank_badges[$ship['cnt']][1];
		$elite = $rank_badges[$ship['cnt']][0];
	  }
  	  $html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$ship['type']."_expert.gif\" border=\"0\"></td><td><b>Expert ".$ship['name']." Pilot</b><br />".$ship['name']." Combat 3rd Class</td><td>Awarded when a pilot does <b><i>".$expert."</i></b> kills ".$conj." ".$ship['type'].".</td></tr>";
  	  $html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$ship['type']."_veteran.gif\" border=\"0\"></td><td><b>Veteran ".$ship['name']." Pilot</b><br />".$ship['name']." Combat 2nd Class</td><td>Awarded when a pilot does <b><i>".$veteran."</i></b> kills ".$conj." ".$ship['type'].".</td></tr>";
  	  $html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$ship['type']."_elite.gif\" border=\"0\"></td><td><b>Elite ".$ship['name']." Pilot</b><br />".$ship['name']." Combat 1st Class</td><td>Awarded when a pilot does <b><i>".$elite."</i></b> kills ".$conj." ".$ship['type'].".</td></tr>";

	}
	$html .= "</table>";
	$weaponbadges=array(
		array( 'type' => 'hybrid', 'name' => 'Hybrid Turret', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 0
		array( 'type' => 'laser', 'name' => 'Laser Turret', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 1
		array( 'type' => 'projectile', 'name' => 'Projectile Turret', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),	// 2
		array( 'type' => 'missile', 'name' => 'Missile Launcher', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 3
		array( 'type' => 'ew', 'name' => 'Electronic Warfare', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 4
		array( 'type' => 'drone', 'name' => 'Drone', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0)			// 5
		);
	$html .= "<div class=block-header2>Grantable Weapon Master Ribbons</div>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=108>Ribbon</td><td width=300>Name / Class</td><td>Description</td></tr>";
	$class='odd';
	foreach ($weaponbadges as $weap)
	{
	  if ($class=='odd') {$class='even';} else {$class='odd';}
	  switch ($weap['type']) {
	  case 'ew':
		$conj = 'with an';
		$bottom = 'Operator';
		$bottom2 = ' device';
		break;
	  case 'missile':
		$conj = 'with a';
		$bottom = 'Operator';
		$bottom2 = ' launcher';
		break;
	  case 'drone':
		$conj = 'using';
		$bottom = 'Operator';
		$bottom2 = 's';
		break;
	  default:
		$conj = 'with a';
		$bottom = 'Gunner';
		$bottom2 = ' turret';
		break;    
	  }
  	  $html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$weap['type']."_expert.gif\" border=\"0\"></td><td><b>Expert ".$weap['name']." ".$bottom."</b><br />".$weap['name']." Master 3rd Class</td><td>Awarded when a pilot does <b><i>".$rank_badges[$weap['cnt']][2]."</i></b> kills ".$conj." ".$weap['type'].$bottom2.".</td></tr>";
  	  $html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$weap['type']."_veteran.gif\" border=\"0\"></td><td><b>Veteran ".$weap['name']." ".$bottom."</b><br />".$weap['name']." Master 2nd Class</td><td>Awarded when a pilot does <b><i>".$rank_badges[$weap['cnt']][1]."</i></b> kills ".$conj." ".$weap['type'].$bottom2.".</td></tr>";
  	  $html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$weap['type']."_elite.gif\" border=\"0\"></td><td><b>Elite ".$weap['name']." ".$bottom."</b><br />".$weap['name']." Master 1st Class</td><td>Awarded when a pilot does <b><i>".$rank_badges[$weap['cnt']][0]."</i></b> kills ".$conj." ".$weap['type'].$bottom2.".</td></tr>";

	}
	$html .= "</table>";
	$html .= "<div class=block-header2>Awarded Medal Ribbons</div>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=108>Ribbon</td><td width=300>Name / Class</td><td>Description</td></tr>";
	$class='odd';
	foreach ($medals as $med)
	{
	  if ($class=='odd') {$class='even';} else {$class='odd';}
  	  $html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$med['type'].".gif\" border=\"0\"></td><td><b>".$med['mname']."</b><br />Top ".$med['name']." Award</td><td>Awarded monthly to the Top ".$med['name']." pilot.</td></tr>";
	}
	$html .= "</table>";
	break;
   case "known_members":
	if (isset($_GET['page'])) { 
		$limit = ($_GET['page'] - 1)*30;
		$last_page = $_GET['page'] - 1;
		$next_page = $_GET['page'] + 1;  
	} else {
		$limit = 0;
		$last_page = 0;
		$next_page = 2;  
	}
  	$query = "SELECT * FROM kb3_pilots plt
		INNER JOIN kb3_corps crp ON ( plt.plt_crp_id = crp.crp_id )
		WHERE crp.crp_all_id =" . $alliance->getID() . "
                AND plt.plt_name NOT LIKE '%Warp Disruptor%'
                AND plt.plt_name NOT LIKE '%Control Tower%'
                AND plt.plt_name NOT LIKE '%Sentry Gun%'
                AND plt.plt_name NOT LIKE '%Battery%'
		ORDER BY plt.plt_name ASC
		LIMIT ".$limit." , 30";
	$qry = new DBQuery();
	$qry->execute($query);
	if ($qry->recordCount() < 30) { $next = FALSE; } else { $next = TRUE; }
	$html .= "<table class=kb-table align=center width=\"100%\">";
	$html .= "<tr><td width =\"33%\" align=left>";
	if ($last_page > 0) {
		$html .= "<a href=\"?a=alliance_detail&all_id=" . $alliance->getID() . "&view=known_members&page=".$last_page."\">Previous Page</a>";
	}
	$html .= "</td><td width =\"33%\" align=center>";
	if ($next_page > 3) {
		$html .= "<a href=\"?a=alliance_detail&all_id=" . $alliance->getID() . "&view=known_members&page=1\">Back to First Page</a>";
	}
	$html .= "</td><td width =\"33%\" align=right>";
	if ($next) {
		$html .= "<a href=\"?a=alliance_detail&all_id=" . $alliance->getID() . "&view=known_members&page=".$next_page."\">Next Page</a>";
	}
	$html .= "</td></tr></table>";
        $html .= "<div class=block-header2>".$alliance->getName()." Known Members</div>";
	$html .= "<table class=kb-table align=center>";
	$html .= '<tr class=kb-table-header>';
	if (strpos($rank_known, 'portrait'))
	{
	  $html .= '<td width=34></td>';
	}
	$html .= '<td width=150>Name</td>';
	
	if (strpos($rank_known, 'score'))
	{
	  $html .= '<td width=80 align=center>Kill<br>Points</td>';
	}
	if (strpos($rank_known, 'done'))
	{		
	  $html .= '<td align=center>Damage<br>Done</td>';
	}
	if (strpos($rank_known, 'received'))
	{
	  $html .= '<td width=80 align=center>Damage<br>Received</td>';
	}
	if (strpos($rank_known, 'efficiency'))
	{
	  $html .= '<td width=80 align=center>Efficiency</td>';
	}
	if (strpos($rank_known, 'ratio'))
	{
	  $html .= '<td width=80 align=center>Kill<br>Ratio</td>';
	}
	if ($page->isAdmin())
	{
	  $html .= '<td width=80 align=center>Admin<br>Move</td>';
	}
	$html .= '</tr>';
	$class='odd';
	while ($row = $qry->getRow()) {
	  if ($class=='odd') {$class='even';} else {$class='odd';}
	  $pilot = new Pilot($row['plt_id']);
	  $kill_list = new KillList();
	  $kill_list->addInvolvedPilot($pilot);
	  $kill_list->getAllKills();
	  $k_score = $kill_list->getPoints();
	  if (!$k_score) { $k_score = 0; }
	  $k_count = $kill_list->getCount();
	  $k_cost = $kill_list->getISK();	
	  $loss_list = new KillList();
	  $loss_list->addVictimPilot($pilot);
	  $loss_list->getAllKills();
	  $l_count = $loss_list->getCount();
	  $l_cost = $loss_list->getISK();
	  if (($k_cost == 0) && ($l_cost == 0)) {
	    $efficiency = 'N/A';
	  } elseif ($k_cost == 0) {
	    $efficiency = '0%';
	  } elseif ($l_cost == 0) {
	    $efficiency = '100%';
	  } else {
	    $efficiency = round($k_cost / ($k_cost + $l_cost) * 100, 2).'%';
	  }
	  if ($k_cost >= 1000000000) {
	    $k_cost = round($k_cost / 1000000000, 2).'B';
	  } else { 
	    $k_cost = round($k_cost / 1000000, 2).'M';
	  }
	  if ($l_cost >= 1000000000) {
	    $l_cost = round($l_cost / 1000000000, 2).'B';
	  } else { 
	    $l_cost = round($l_cost / 1000000, 2).'M';
	  }
	  if ($k_count == 0) {
	    $k_ratio = 'N/A';
	  } elseif ($l_count == 0) {
	    $k_ratio = $k_count.' : 0';
	  } else {
	    $k_ratio = round($k_count / $l_count, 2).' : 1';
	  }
	  $html .= "<tr height=34 class=kb-table-row-".$class.">";
 	  if (strpos($rank_known, 'portrait'))
	  {
	    $html .= "<td><img src=".$pilot->getPortraitURL( 32 )."></td>";
	  }
	  $html .= "<td class=kb-table-cell><a class=kb-shipclass href=?a=pilot_detail&plt_id=".$pilot->getID().">".$pilot->getName()."</a></td>";
	  if (strpos($rank_known, 'score'))
	  {
	    $html .= '<td align=right>'.$k_score.'</td>';
	  }
	  if (strpos($rank_known, 'done'))
	  {
	    $html .= '<td align=right>'.$k_cost.'</td>';
	  }
	  if (strpos($rank_known, 'received'))
	  {
	    $html .= '<td align=right>'.$l_cost.'</td>';
	  }
	  if (strpos($rank_known, 'efficiency'))
	  {
	    $html .= '<td align=right>'.$efficiency.'</td>';
	  }
	  if (strpos($rank_known, 'ratio'))
	  {
	    $html .= '<td align=right>'.$k_ratio.'</td>';
	  }
	  if ($page->isAdmin())
	  {
	    $html .= "<td align=center><a href=\"javascript:openWindow('?a=admin_move_pilot&plt_id=".$plt['plt_id']."', null, 500, 500, '' )\">Move</a></td>";
	  }
	  $html .= "</tr>";
	}
	$html .= "</table>";
	break;

}

$menubox = new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Kills & losses");
$menubox->addOption("link","Recent activity", "?a=alliance_detail&all_id=" . $alliance->getID());
$menubox->addOption("link","Kills", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=kills");
$menubox->addOption("link","Losses", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=losses");
$menubox->addOption("caption","Corp statistics");
$menubox->addOption("link","Top killers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_kills");
$menubox->addOption("link","Top losers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_losses");
$menubox->addOption("link","Destroyed ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_kills_class");
$menubox->addOption("link","Lost ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_losses_class");
$menubox->addOption("caption","Pilot statistics");
$menubox->addOption("link","Top killers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_kills");
if (config::get('kill_points'))
{
    $menubox->addOption('link', "Top scorers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_scores");
}
$menubox->addOption("link","Top solokillers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_solo");
$menubox->addOption("link","Top damagedealers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_damage");
$menubox->addOption("link","Top final blows", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_blow");
$menubox->addOption("link","Top podkillers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_pod");
$menubox->addOption("link","Top griefers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_griefer");
$menubox->addOption("link","Top ISK killers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_isk");
$menubox->addOption("link","Top losers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_losses");
$menubox->addOption("caption","Pilot ship statistics");
$menubox->addOption("link","Destroyed ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=kills_class");
$menubox->addOption("link","Lost ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=losses_class");

$menubox->addOption("caption","Global statistics");
$menubox->addOption("link","Ships & weapons", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=ships_weapons");
$menubox->addOption("link","Most violent systems", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=violent_systems");

if (    (CORP_ID == 0 && ALLIANCE_ID == 0) // Public Board
	|| (ALLIANCE_ID && ALLIANCE_ID == $alliance->getID()) // Allied Board
)
{
  $menubox->addOption("caption","Ranks & Medals");
  $menubox->addOption("link","Pilot Ranks", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_ranks");
  $menubox->addOption("link","Pilot Medals", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_medals");
  $menubox->addOption("caption","Rank Showroom");
  $menubox->addOption("link","Evolution Table", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=evo_ranks");
  $menubox->addOption("link","Ribbons", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=rank_ribbons");
} elseif (strpos($rank_known, 'enabled')) {
  $menubox->addOption("link","Known Members", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=known_members&page=1");
}


$page->addContext($menubox->generate());

$page->setContent($html);
$page->generate();
?>