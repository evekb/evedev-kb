<?php
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
    $year = kbdate('Y');

if ($week == 52)
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
    $pweek = 52;
    $pyear = $year - 1;
}
else
{
    $pweek = $week - 1;
    $pyear = $year;
}

$page = new Page("Kills - Week ".$week);

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
	
$klist = new KillList();
$klist->setOrdered(true);
$klist->setWeek($week);
$klist->setYear($year);
involved::load($klist,'kill');
if ($_GET['scl_id'])
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

$pagesplitter = new PageSplitter($klist->getCount(), 30);    
$klist->setPageSplitter($pagesplitter);
$table = new KillListTable($klist);
$html .= $table->generate();
$html .= $pagesplitter->generate();

$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link", "Previous week ", "?a=kills&w=".$pweek."&y=".$pyear);
if ($week != kbdate("W"))
{
    $menubox->addOption('link', "Next week", "?a=kills&w=".$nweek."&y=".$nyear);
}
$page->addContext($menubox->generate());

$tklist = new TopKillsList();
$tklist->setWeek($week);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills in week ".$week, "kills", "eagle");
$page->addContext($tkbox->generate());

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