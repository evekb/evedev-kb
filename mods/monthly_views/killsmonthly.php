<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.public.php');
require_once('common/includes/class.toplist.php');

$week = $_GET['w'];
$year = $_GET['y'];
$month = $_GET['m'];

if ($week == "") {
    $week = kbdate("W");
}

if ($year == "") {
    $year = kbdate("Y");
}

if ($month == "") {
    $month = kbdate("n");
}

if ($month == 1)
{
    $pyear = $year - 1;
    $pmonth = 12;
}
else
{
    $pyear = $year;
    $pmonth = $month - 1;
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

$page = new Page("Kills - ".date('F Y', mktime(0,0,0,$month, 1, $year)));

$kslist = new KillList();
$kslist->setStartDate($year.'-'.$month.'-1 00:00:00');
$kslist->setEndDate($year.'-'.$month.'-31 23:59:59');
involved::load($kslist,'kill');

if (config::get('summarytable')){
if (config::get('public_summarytable')){
	$summarytable = new KillSummaryTablePublic($kslist);
}
else
{
	$llist = new KillList();
	$llist->setStartDate($year.'-'.$month.'-1 00:00:00');
	$llist->setEndDate($year.'-'.$month.'-31 23:59:59');
	involved::load($llist,'loss');
	$summarytable = new KillSummaryTable($kslist, $llist);
}
$summarytable->setBreak(config::get('summarytable_rowcount'));
$html .= $summarytable->generate();
}

$klist = new KillList();
$klist->setOrdered(true);
$klist->setStartDate($year.'-'.$month.'-1 00:00:00');
$klist->setEndDate($year.'-'.$month.'-31 23:59:59');
involved::load($klist,'kill');
if ($_GET['scl_id'])
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

$table = new KillListTable($klist);
$html .= $table->generate();

$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link","Previous month", "?a=killsmonthly&m=" . $pmonth . "&y=" . $pyear);
if ($month != date("n"))
{
    $menubox->addOption('link', "Next month", "?a=killsmonthly&m=".$nmonth."&y=".$nyear);
}
$menubox->addOption('link', "Losses this month", "?a=lossesmonthly&m=".$month."&y=".$year);
$page->addContext($menubox->generate());

$tklist = new TopKillsList();
$tklist->setStartDate($year.'-'.$month.'-1 00:00:00');
$tklist->setEndDate($year.'-'.$month.'-31 23:59:59');
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills ". date('F Y', mktime(0,0,0,$month, 1, $year)), "kills", "eagle");
$page->addContext($tkbox->generate());

if (config::get('kill_points'))
{
    $tklist = new TopScoreList();
    $tklist->setStartDate($year.'-'.$month.'-1 00:00:00');
    $tklist->setEndDate($year.'-'.$month.'-31 23:59:59');
    involved::load($tklist,'kill');

    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points ".  date('F Y', mktime(0,0,0,$month, 1, $year)), "points", "redcross");
    $page->addContext($tkbox->generate());
}

$page->generate();
?>