<?php
require_once('common/includes/class.pilot.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.kill.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');
require_once('mods/rank_mod/rank.php');


$pilot = new Pilot($_GET['plt_id']);
$corp = $pilot->getCorp();
$alliance = $corp->getAlliance();

if (!$pilot->exists())
{
    $html = 'That pilot doesn\'t exist.';
    $page->generate($html);
    exit;
}

if (    (CORP_ID == 0 && ALLIANCE_ID == 0) // Public Board
	|| (CORP_ID && CORP_ID == $corp->getID()) // Corporate Board
	|| (ALLIANCE_ID && ALLIANCE_ID == $alliance->getID()) // Allied Board
)
{  $allow_rank= TRUE; } else {  $allow_rank= FALSE; }

$klist = new KillList();
$tklist = new KillList();
$llist = new KillList();
$tllist = new KillList();
$klist->addInvolvedPilot($pilot);
$tklist->addInvolvedPilot($pilot);
$llist->addVictimPilot($pilot);
$tllist->addVictimPilot($pilot);
$klist->getAllKills();
$llist->getAllKills();
$tklist->setPodsNoobShips(false);
$tllist->setPodsNoobShips(false);

$medals=array();
$shipbadges=array();
$weaponbadges=array();
$rps=0;
$bonus_rps=0;
$base_rps=0;

$show_options = config::get('rankmod_show');
$titles = config::getnumerical('rankmod_titles');

$rank = GetPilotRank($_GET['plt_id'], $points, $medals, $shipbadges, $weaponbadges, $base_rps, $bonus_rps, $rps);

if ( $allow_rank ) {
  $page = new Page('Pilot details - '.$titles[$rank]['abbr'].' '.$pilot->getName());
} else {
  $page = new Page('Pilot details - '.$pilot->getName());
}

$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";

$html .= "<tr class=kb-table-row-even>";
$html .= "<td rowspan=7 width=128><div id=\"portrait\" style=\"position:relative; height:128px; width:128px; background-image:url(".$pilot->getPortraitURL(128).")\" name=\"portrait\">";
if (strpos($show_options, 'rank') && $allow_rank) // portrait rank insignia
{
   $html .= "<div id=\"insignia\" style=\"position:absolute; left:0px; top:0px; width:32px; height:32px; z-index:0;\">".$titles[$rank]['img']."</div>";
}
if (strpos($show_options, 'medal') && !strpos($show_options, 'nomeds') && $allow_rank && !strpos($show_options,'case')) // portrait show medals
{
  $i=0;
  $j=0;
  foreach ($medals as $med)
  {
    if ($med['cnt']) {
	  if ($i>1) { $i=0; $j++;}
	  $x=88+($i*20);
	  $y=0+($j*20);
	  $html .= "<div id=\"".$med['type']."\" style=\"position:absolute; left:".$x."px; top:".$y."px; width:20px; height:20px; z-index:1;\">";
          $html .= "<img src=\"".IMG_URL."/ranks/awards/".$med['type']."_port.gif\" border=\"0\" alt=\"".$med['mname']."\" title=\"".$med['mname']." (".$med['cnt'].")\"></div>";
	  $i++;
    }
  }
}
if (strpos($show_options, 'badge') && $allow_rank && !strpos($show_options,'case')) // portrait show ribbons
{
  $i=0;
  $j=0;
  foreach ($shipbadges as $ship)
  {
    if ($ship['icon']) {
	  if ($i>5) { $i=0; $j++;}
	  $x=1+($i*21);
	  $y=99+($j*7);
	  $html .= "<div id=\"".$ship['type']."\" style=\"position:absolute; left:".$x."px; top:".$y."px; width:21px; height:6px; z-index:1;\">";
	  $html .= "<img width=\"21\" height=\"6\" src=\"".IMG_URL."/ranks/ribbons/".$ship['icon'].".gif\" border=\"0\" alt=\"".$ship['badge']."\" title=\"".$ship['badge']."\"></div>";
	  $i++;
    }
  }
  foreach ($weaponbadges as $weap)
  {
    if ($weap['icon']) {
	  if ($i>5) { $i=0; $j++;}
	  $x=1+($i*21);
	  $y=99+($j*7);
	  $html .= "<div id=\"".$weap['type']."\" style=\"position:absolute; left:".$x."px; top:".$y."px; width:21px; height:6px; z-index:1;\">";
	  $html .= "<img width=\"21\" height=\"6\" src=\"".IMG_URL."/ranks/ribbons/".$weap['icon'].".gif\" border=\"0\" alt=\"".$weap['badge']."\" title=\"".$weap['badge']."\"></div>";
	  $i++;
    }
  }
  if (strpos($show_options, 'medal') && strpos($show_options, 'nomeds') && $allow_rank && !strpos($show_options,'case')) {
    foreach($medals as $med)
    {
	if ($med['cnt']) {
	  if ($i>5) { $i=0; $j++;}
	  $x=1+($i*21);
	  $y=99+($j*7);
	  $html .= "<div id=\"".$med['type']."\" style=\"position:absolute; left:".$x."px; top:".$y."px; width:21px; height:6px; z-index:1;\">";
	  $html .= "<img width=\"21\" height=\"6\"src=\"".IMG_URL."/ranks/ribbons/".$med['type'].".gif\" border=\"0\" alt=\"".$med['mname']."\" title=\"".$med['mname']." (".$med['cnt'].")\"></div>";

	  $i++;
	}
    }	
  }
}

$html .= "</div></td>";

$k_cost = $klist->getISK();
$l_cost = $llist->getISK();
$k_count = $klist->getCount();
$l_count = $llist->getCount();
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
if ($allow_rank) {
  $html .= "<td class=kb-table-cell width=160><b>Rank:</b></td><td class=kb-table-cell colspan=3><b>".$titles[$rank]['title']."</b></td></tr><tr class=kb-table-row-even>";
}
$html .= "<td class=kb-table-cell width=160><b>Corporation:</b></td><td class=kb-table-cell colspan=3><a href=\"?a=corp_detail&crp_id=".$corp->getID()."\">".$corp->getName()."</a></td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Alliance:</b></td><td class=kb-table-cell colspan=3>";
if ($alliance->getName() == "Unknown" || $alliance->getName() == "None")
    $html .= "<b>".$alliance->getName()."</b>";
else
    $html .= "<a href=\"?a=alliance_detail&all_id=".$alliance->getID()."\">".$alliance->getName()."</a>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>".$klist->getCount()."</td>";
$html .= "<td class=kb-table-cell width=160><b>Real kills:</b></td><td class=kl-kill>".$tklist->getCount()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$llist->getCount()."</td>";
$html .= "<td class=kb-table-cell><b>Real losses:</b></td><td class=kl-loss>".$tllist->getCount()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done:</b></td><td class=kl-kill>".$k_cost."</td>";
$html .= "<td class=kb-table-cell><b>Damage received:</b></td><td class=kl-loss>".$l_cost."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kill Ratio:</b></td><td class=kb-table-cell><b>".$k_ratio."</b></td>";
$html .= "<td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>".$efficiency."</b></td></tr>";

$html .= "</td></tr>";
$html .= "</table>";

if ( $allow_rank && strpos($show_options,'case')) {
  $html .= "<br /><table class=kb-table cellspacing=1 width=\"100%\">";
  $html .= "<tr class=kb-table-header><td colspan=2 align=center>Awards</td></tr>";
  $html .= "<tr height=24 class=kb-table-row-even><td width=375><div id=\"medalcase\" style=\"position:relative; width:375px; height:24px;\">";
  $i=0;
  $j=0;
  $temp=0;
  foreach ($medals as $med)
  {
    if ($med['cnt']) { $temp++; }
  }
  $sx = (int) (187 - (( $temp / 2) * 24));
  if ($temp != 0)
  {
    foreach ($medals as $med)
    {
      if ($med['cnt']) {
	    $x=$sx+($i*24);
	    $y=2;
	    $html .= "<div id=\"".$med['type']."\" style=\"position:absolute; left:".$x."px; top:".$y."px; width:20px; height:20px; z-index:1;\">";
            $html .= "<img src=\"".IMG_URL."/ranks/awards/".$med['type']."_port.gif\" border=\"0\" alt=\"".$med['mname']."\" title=\"".$med['mname']." (".$med['cnt'].")\"></div>";
	    $i++;
      }
    }
  }
  $html .= "</div></td><td width=375><div id=\"ribboncase\" style=\"position:relative; width:375px; height:24px;\">";
  $i=0;
  $j=0;
  foreach ($shipbadges as $ship)
  {
    if ($ship['icon']) {
	  if ($i>9) { $i=0; $j++;}
	  $x=2+($i*37);
	  $y=1+($j*12);
	  $html .= "<div id=\"".$ship['type']."\" style=\"position:absolute; left:".$x."px; top:".$y."px; width:35px; height:10px; z-index:1;\">";
	  $html .= "<img width=\"35\" height=\"10\" src=\"".IMG_URL."/ranks/ribbons/".$ship['icon'].".gif\" border=\"0\" alt=\"".$ship['badge']."\" title=\"".$ship['badge']."\"></div>";
	  $i++;
    }
  }
  foreach ($weaponbadges as $weap)
  {
    if ($weap['icon']) {
	  if ($i>9) { $i=0; $j++;}
	  $x=2+($i*37);
	  $y=1+($j*12);
	  $html .= "<div id=\"".$weap['type']."\" style=\"position:absolute; left:".$x."px; top:".$y."px; width:35px; height:10px; z-index:1;\">";
	  $html .= "<img width=\"35\" height=\"10\" src=\"".IMG_URL."/ranks/ribbons/".$weap['icon'].".gif\" border=\"0\" alt=\"".$weap['badge']."\" title=\"".$weap['badge']."\"></div>";
	  $i++;
    }
  }
  $html .= "</div></td></tr></table>";	
}

$html .= "<br/>";

$lpoints = $llist->getPoints();
$summary = new KillSummaryTable($klist, $llist);
$summary->setBreak(config::get('summarytable_rowcount'));
if ($_GET['view'] == "ships_weapons")
{
    $summary->setFilter(false);
}
$html .= $summary->generate();

switch ($_GET['view'])
{
    case "kills":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->addInvolvedPilot($pilot);
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
        $list->addVictimPilot($pilot);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();
        break;
    case "p_awards":
        $html .= "<div class=kb-kills-header>Personal Awards</div>";

	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=\"50%\">Award</td><td>Times</td></tr>";
	$class='odd';
	foreach ($medals as $med)
	{
	  if ($class=='odd') {$class='even';} else {$class='odd';}
	  $html .= "<tr class=kb-table-row-".$class."><td>".$med['name'].":</td><td>".$med['cnt']."</td></tr>";
	}
	$html .= "</table>";

        $html .= "<div class=kb-kills-header>Ships Used</div>";

	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=\"50%\">Class</td><td>Kills</td></tr>";
	$enables = config::get('rankmod_enables');
	$class='odd';
	foreach ($shipbadges as $ship)
	{
	  if (isset($ship['parent'])) {
		if (strpos($enables, $ship['name'])) {
		    if ($class=='odd') {$class='even';} else {$class='odd';}
		    $html .= "<tr class=kb-table-row-".$class."><td><i>".$ship['name'].":</i></td><td><i>".$ship['cnt']."</i></td></tr>";
	        }
	  } else {	
	    if ($class=='odd') {$class='even';} else {$class='odd';}
	    $html .= "<tr class=kb-table-row-".$class."><td>".$ship['name'].":</td><td>".$ship['cnt']."</td></tr>";
          }
	}
	$html .= "</table>";

        $html .= "<div class=kb-kills-header>Weapons Used</div>";

	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=\"50%\">Weapon Class</td><td>Kills</td></tr>";
	$class='odd';
	foreach ($weaponbadges as $weap)
	{
	  if ($class=='odd') {$class='even';} else {$class='odd';}
	  $html .= "<tr class=kb-table-row-".$class."><td>".$weap['name'].":</td><td>".$weap['cnt']."</td></tr>";
	}
	$html .= "</table>";

        break;
    case "ribbons":
// Rank stuff
	$r_type = config::get('rankmod_rtype');
	switch ($r_type) {
		case "Enlisted": $limit = 9; break;
		case "Officer": $limit = 10; break;
		case "Enlisted + Officer": $limit = 19; break;
	}
        $html .= "<div class=block-header2>Rank</div>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr height=34><td width=34 alingn=left valign=top>".$titles[$rank]['img']."</td><td align=left valign=center><b><i>".$titles[$rank]['title']." ".$pilot->getName()."</b><br>Abbreviation: ".$titles[$rank]['abbr']."</i></td></tr>";
	$html .= "</table>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=\"33%\">Base Rank points</td><td width=\"33%\">Bonus Rank points</td><td width=\"33%\">Total Rank points</td></tr>";
	$html .= "<tr class=kb-table-row-even><td>".$base_rps."</td><td>".$bonus_rps."</td><td>".$rps."</td></tr>";
	$html .= "</table>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	if ($rank == $limit) {
	  $next = 'Max';
	  $current = $titles[$rank]['reqrp'];
	  $width = 600;
	  $now = 'Max';
	} else {
	  $next = $titles[$rank+1]['reqrp'];
	  $current = $titles[$rank]['reqrp'];
	  $width = ($rps - $current) * 600 / ($next - $current);
	  $now = $titles[$rank+1]['abbr'];
	}
	$html .= "<tr class=kb-table-header><td width=80>".$titles[$rank]['abbr']."</td><td width=600 align=center>Progression</td><td width=80>".$now."</td></tr>";
	$html .= "<tr class=kb-table-row-odd><td align=right valign=center><b>".$current."</b></td><td align=left valign=center><div class=bar style=\"position:relative; height: 8px; width: ".$width."px;\"><b><i>&nbsp;</i></b></div></td><td align=left valign=center><b>".$next."</b></td></tr>";
	$html .= "</table>";
// Awarded medals
	$html .= "<div class=block-header2>Awarded Medals</div>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=108>Medal</td><td width=50>Times</td><td width=250>Name</td><td>Class</td></tr>";
	$class='odd';
	foreach ($medals as $med)
	{
	  if ($med['cnt'])
	  {
	  	if ($class=='odd') {$class='even';} else {$class='odd';}
	  	$html .= "<tr class=kb-table-row-".$class." height=64><td align=\"center\"><img src=\"".IMG_URL."/ranks/awards/".$med['type'].".gif\" border=\"0\"></td><td>".$med['cnt']."</td><td>".$med['mname']."</td><td>Top ".$med['name']." Award</td></tr>";
	  }
	}
	$html .= "</table>";
        $html .= "<div class=block-header2>Ship Combat Ribbons</div>";
// Ship Combat ribbons
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=108>Ribbon</td><td width=300>Name</td><td>Class</td></tr>";
	$class='odd';
	foreach ($shipbadges as $ship)
	{
	  if ($ship['icon'])
	  {
	  	if ($class=='odd') {$class='even';} else {$class='odd';}
	  	$html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$ship['icon'].".gif\" border=\"0\"></td><td>".$ship['badge']."</td><td>".$ship['class']."</td></tr>";
	  }
	}
	$html .= "</table>";
// Weapon Master ribbons
        $html .= "<div class=block-header2>Weapon Master Ribbons</div>";
	$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";
	$html .= "<tr class=kb-table-header><td width=108>Ribbon</td><td width=300>Name</td><td>Class</td></tr>";
	$class='odd';
	foreach ($weaponbadges as $weap)
	{
	  if ($weap['icon'])
	  {
	  	if ($class=='odd') {$class='even';} else {$class='odd';}
	  	$html .= "<tr class=kb-table-row-".$class." height=32><td><img src=\"".IMG_URL."/ranks/ribbons/".$weap['icon'].".gif\" border=\"0\"></td><td>".$weap['badge']."</td><td>".$weap['class']."</td></tr>";
	  }
	}
	$html .= "</table>";

        break;
    case "ships_weapons":
        $html .= "<div class=block-header2>Ships & weapons used</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=400>";
        $shiplist = new TopShipList();
        $shiplist->addInvolvedPilot($pilot);
        $shiplisttable = new TopShipListTable($shiplist);
        $html .= $shiplisttable->generate();
        $html .= "</td><td valign=top align=right width=400>";

        $weaponlist = new TopWeaponList();
        $weaponlist->addInvolvedPilot($pilot);
        $weaponlisttable = new TopWeaponListTable($weaponlist);
        $html .= $weaponlisttable->generate();
        $html .= "</td></tr></table>";

        break;
    default:
        $html .= "<div class=kb-kills-header>10 Most recent kills</div>";
        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addInvolvedPilot($pilot);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();

        $html .= "<div class=kb-losses-header>10 Most recent losses</div>";
        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addVictimPilot($pilot);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $table->setDayBreak(false);
        $html .= $table->generate();
        break;
}

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Kills & losses");
$menubox->addOption("link","Recent activity", "?a=pilot_detail&plt_id=".$pilot->getID()."&view=recent");
$menubox->addOption("link","Kills", "?a=pilot_detail&plt_id=".$pilot->getID()."&view=kills");
$menubox->addOption("link","Losses", "?a=pilot_detail&plt_id=".$pilot->getID()."&view=losses");
$menubox->addOption("caption","Statistics");
$menubox->addOption("link","Ships & weapons", "?a=pilot_detail&plt_id=".$pilot->getID()."&view=ships_weapons");
if ($allow_rank) {
  $menubox->addOption("link","Personal Awards", "?a=pilot_detail&plt_id=".$pilot->getID()."&view=p_awards");
  $menubox->addOption("caption","Rank");
  $menubox->addOption("link","Rank & Decorations", "?a=pilot_detail&plt_id=".$pilot->getID()."&view=ribbons");
}
if (strstr(config::get("mods_active"), 'signature_generator'))
{
    $menubox->addOption("caption","Signature");
    $menubox->addOption("link","Link", "?a=sig_list&i=".$pilot->getID());
}
$page->addContext($menubox->generate());

if (config::get('kill_points'))
{
    $scorebox = new Box("Kill points");
    $scorebox->addOption("points", $points);
    $page->addContext($scorebox->generate());
}
if (config::get('loss_points'))
{
    $scorebox = new Box("Loss points");
    $scorebox->addOption("points", $lpoints);
    $page->addContext($scorebox->generate());
}
if (config::get('total_points'))
{
    $scorebox = new Box("Total points");
    $scorebox->addOption("points", $points-$lpoints);
    $page->addContext($scorebox->generate());
}

// Rank Points Score boxes

if (strpos($show_options, 'base') && $allow_rank)
{
    $scorebox = new Box("Base Rank points");
    $scorebox->addOption("points", $base_rps);
    $page->addContext($scorebox->generate());
}
if (strpos($show_options, 'bonus') && $allow_rank)
{
    $scorebox = new Box("Bonus Rank points");
    $scorebox->addOption("points", $bonus_rps);
    $page->addContext($scorebox->generate());
}
if (strpos($show_options, 'total') && $allow_rank)
{
    $scorebox = new Box("Total Rank points");
    $scorebox->addOption("points", $rps);
    $page->addContext($scorebox->generate());
}

$page->setContent($html);
$page->generate();
?>