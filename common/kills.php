<?php
// Kills page. Display a list of most recent kills and the top killers for 
// the week
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.ship.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.public.php');
require_once('common/includes/class.toplist.php');

$week = $_GET['w'];
$year = $_GET['y'];

if ($week == '')
    $week = kbdate('W');

if ($year == '')
    $year = getYear();

if ($week == 53)
{
    $nweek = 1;
    $nyear = $year + 1;
    $pyear = $year - 1;
}
else
{
    $nweek = $week + 1;
    $nyear = $year;
}
if ($week == 1)
{
    $pweek = 53;
    $pyear = $year - 1;
}
else
{
    $pweek = $week - 1;
    $pyear = $year;
}

$page = new Page("Kills - Week ".$week);

// Build summary table
$kslist = new KillList();
$kslist->setWeek($week);
$kslist->setYear($year);
involved::load($kslist,'kill');

if (config::get('summarytable')){
if (config::get('public_summarytable')){
	$summarytable = new KillSummaryTablePublic($kslist);
}
else
{
	$llist = new KillList();
	$llist->setWeek($week);
	$llist->setYear($year);
	involved::load($llist,'loss');
	$summarytable = new KillSummaryTable($kslist, $llist);
}
$summarytable->setBreak(config::get('summarytable_rowcount'));
$html .= $summarytable->generate();
}

// Build table of recent kills
$klist = new KillList();
$klist->setOrdered(true);
if (config::get('comments_count')) $klist->setCountComments(true);
if (config::get('killlist_involved')) $klist->setCountInvolved(true);
$klist->setWeek($week);
$klist->setYear($year);
involved::load($klist,'kill');
if ($_GET['scl_id'])
    $klist->addVictimShipClass(intval($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

$pagesplitter = new PageSplitter($klist->getCount(), 30);
$klist->setPageSplitter($pagesplitter);
$table = new KillListTable($klist);
$html .= $table->generate();
$html .= $pagesplitter->generate();

$page->setContent($html);

// Create side menu
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link", "Previous week ", "?a=kills&amp;w=".$pweek."&amp;y=".$pyear);
if ($week != kbdate("W"))
{
    $menubox->addOption('link', "Next week", "?a=kills&amp;w=".$nweek."&amp;y=".$nyear);
}
$page->addContext($menubox->generate());

// Create top kills list
$tklist = new TopKillsList();
$tklist->setWeek($week);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills in week ".$week, "kills", "eagle");
$page->addContext($tkbox->generate());

// If 'kill_points' is set, create top scores list
if (config::get('kill_points'))
{
    $tklist = new TopScoreList();
    $tklist->setWeek($week);
    $tklist->setYear($year);
    involved::load($tklist,'kill');

    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points in week ".$week, "points", "redcross");
    $page->addContext($tkbox->generate());
}

$page->generate();
?>