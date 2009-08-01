<?php
require_once('common/includes/class.toplist.php');

$page = new Page('Awards');

$month = $_GET['m'];
$year = $_GET['y'];

if ($month == '')
    $month = kbdate('m') - 1;

if ($year == '')
    $year = kbdate('Y');

if ($month == 0)
{
    $month = 12;
    $year = $year - 1;
}

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

$html .= "<div class=block-header2>Awards for ".$monthname." ".$year."</div>";
// main table
$html .= "<table height=600 width=\"100%\"><tr>";
// top killers
$tklist = new TopKillsList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills", "kills", "eagle");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

// top scorers
if (config::get('kill_points'))
{
    $tklist = new TopScoreList();
    $tklist->setMonth($month);
    $tklist->setYear($year);
    involved::load($tklist,'kill');
    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points", "points", "redcross");
    $html .= "<td valign=top align=center>".$tkbox->generate()."</td>";
}

// top solo killers
$tklist = new TopSoloKillerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top solokillers", "solo kills", "kills", "cross");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

$html .= "</tr><tr>";

// top damage dealers
$tklist = new TopDamageDealerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top damagedealers", "kills w/ most damage", "kills", "wing1");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

// top final blows
$tklist = new TopFinalBlowList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top finalblows", "final blows", "kills", "skull");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

// top podkillers
$tklist = new TopPodKillerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top podkillers", "podkills", "kills", "globe");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

$html .= "</tr><tr>";

// top griefers
$tklist = new TopGrieferList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top griefers", "carebear kills", "kills", "star");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

// top capital killers
$tklist = new TopCapitalShipKillerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top ISK killers", "capital shipkills", "kills", "wing2");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

// top losers
$tklist = new TopLossesList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'loss');
$tklist->generate();
$tkbox = new AwardBox($tklist, "Top Losers", "ship lost", "kills", "moon");
$html .= "<td valign=top align=center>".$tkbox->generate()."</td>";

$html .= "</td></tr></table>";

$menubox = new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link", "Previous month ", "?a=awards&m=".$pmonth."&y=".$pyear);
if (! ($month == kbdate("m") - 1 && $year == kbdate("Y")))
    $menubox->addOption("link", "Next month", "?a=awards&m=".$nmonth."&y=".$nyear);
$page->addContext($menubox->generate());

$page->setContent($html);
$page->generate();
?>
