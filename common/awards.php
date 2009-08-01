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

$awardboxes = array();
// top killers
$tklist = new TopKillsList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills", "kills", "eagle");
$awardboxes[] = $tkbox->generate();
// top scorers
if (config::get('kill_points'))
{
    $tklist = new TopScoreList();
    $tklist->setMonth($month);
    $tklist->setYear($year);
    involved::load($tklist,'kill');

    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points", "points", "redcross");
	$awardboxes[] = $tkbox->generate();
}
// top solo killers
$tklist = new TopSoloKillerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top solokillers", "solo kills", "kills", "cross");
$awardboxes[] = $tkbox->generate();
// top damage dealers
$tklist = new TopDamageDealerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top damagedealers", "kills w/ most damage", "kills", "wing1");
$awardboxes[] = $tkbox->generate();

$html .= "</tr><tr>";
// top final blows
$tklist = new TopFinalBlowList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top finalblows", "final blows", "kills", "skull");
$awardboxes[] = $tkbox->generate();
// top podkillers
$tklist = new TopPodKillerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top podkillers", "podkills", "kills", "globe");
$awardboxes[] = $tkbox->generate();
// top griefers
$tklist = new TopGrieferList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top griefers", "carebear kills", "kills", "star");
$awardboxes[] = $tkbox->generate();
// top capital killers
$tklist = new TopCapitalShipKillerList();
$tklist->setMonth($month);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top ISK killers", "capital shipkills", "kills", "wing2");
$awardboxes[] = $tkbox->generate();

$smarty->assign_by_ref('awardboxes', $awardboxes);
$smarty->assign('month', $monthname);
$smarty->assign('year', $year);
$smarty->assign('boxcount', count($awardboxes));

$menubox = new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link", "Previous month ", "?a=awards&m=".$pmonth."&y=".$pyear);
if (! ($month == kbdate("m") - 1 && $year == kbdate("Y")))
    $menubox->addOption("link", "Next month", "?a=awards&m=".$nmonth."&y=".$nyear);
$page->addContext($menubox->generate());

$page->setContent($smarty->fetch(get_tpl('awards')));
$page->generate();
?>
