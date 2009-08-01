<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');

if (config::get('public_losses')){
	die('Forbidden');
}

$week = $_GET['w'];
$year = $_GET['y'];

if ($week == '')
    $week = kbdate('W');

if ($year == '')
    $year = kbdate('Y');

if ($week == 52)
{
    $nweek = 1;
    $nyear = $year + 1;
}
else
{
    $nweek = $week + 1;
    $nyear = $year;
}
if ($week == "1")
{
    $pweek = 52;
    $pyear = $year - 1;
}
else
{
    $pweek = $week - 1;
    $pyear = $year;
}

$page = new Page("Losses - Week ".$week);

$klist = new KillList();
$klist->setWeek($week);
$klist->setYear($year);
involved::load($klist,'kill');

$lslist = new KillList();
$lslist->setWeek($week);
$lslist->setYear($year);
involved::load($lslist,'loss');

if (config::get('summarytable')){
	$summarytable = new KillSummaryTable($klist, $lslist);
	$summarytable->setBreak(config::get('summarytable_rowcount'));
	$html .= $summarytable->generate();
}
// $html .= "<table width=\"99%\" align=center><tr><td class=weeknav align=left>";
// if ( $week != kbdate( "W" ) )
// $html .= "[<a href=\"?a=losses&w=".$nweek."&y=".$nyear."\"><<</a>]";
// $html .= "</td><td class=weeknav align=right>[<a href=\"?a=losses&w=".$pweek."&y=".$pyear."\">>></a>]</td></tr></table>";
$llist = new KillList();
$llist->setOrdered(true);
$llist->setWeek($week);
$llist->setYear($year);
involved::load($llist,'loss');
if ($_GET['scl_id'])
    $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $llist->setPodsNoobShips(false);

$pagesplitter = new PageSplitter($llist->getCount(), 30);    
$llist->setPageSplitter($pagesplitter);
$table = new KillListTable($llist);
$html .= $table->generate();
$html .= $pagesplitter->generate();

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link", "Previous week", "?a=losses&w=".$pweek."&y=".$pyear);
if ($week != kbdate("W"))
{
    $menubox->addOption("link", "Next week", "?a=losses&w=".$nweek."&y=".$nyear);
}
$page->addContext($menubox->generate());

$tllist = new TopLossesList();
$tllist->setWeek($week);
$tllist->setYear($year);
involved::load($tllist,'loss');

$tllist->generate();
$tlbox = new AwardBox($tllist, "Top losers", "losses in week ".$week, "losses", "moon");
$page->addContext($tlbox->generate());

$page->setContent($html);
$page->generate();
?>