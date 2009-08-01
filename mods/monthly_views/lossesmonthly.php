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

$page = new Page("Losses - ".date('F Y', mktime(0,0,0,$month, 1, $year)));

$klist = new KillList();
$klist->setStartDate($year.'-'.$month.'-1 00:00:00');
$klist->setEndDate($year.'-'.$month.'-31 23:59:59');
involved::load($klist,'kill');

$lslist = new KillList();
$lslist->setStartDate($year.'-'.$month.'-1 00:00:00');
$lslist->setEndDate($year.'-'.$month.'-31 23:59:59');
involved::load($lslist,'loss');

if (config::get('summarytable')){
$summarytable = new KillSummaryTable($klist, $lslist);
$summarytable->setBreak(config::get('summarytable_rowcount'));
$html .= $summarytable->generate();
}

$llist = new KillList();
$llist->setOrdered(true);
$llist->setStartDate($year.'-'.$month.'-1 00:00:00');
$llist->setEndDate($year.'-'.$month.'-31 23:59:59');
involved::load($llist,'loss');
if ($_GET['scl_id'])
    $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $llist->setPodsNoobShips(false);

$table = new KillListTable($llist);
$html .= $table->generate();

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link","Previous month", "?a=lossesmonthly&m=" . $pmonth . "&y=" . $pyear);
if ($month != date("m"))
{
    $menubox->addOption('link', "Next month", "?a=lossesmonthly&m=".$nmonth."&y=".$nyear);
}
$menubox->addOption('link', "Kills this month", "?a=killsmonthly&m=".$month."&y=".$year);
$page->addContext($menubox->generate());

$tllist = new TopLossesList();
$tllist->setStartDate($year.'-'.$month.'-1 00:00:00');
$tllist->setEndDate($year.'-'.$month.'-31 23:59:59');
involved::load($tllist,'loss');

$tllist->generate();
$tlbox = new AwardBox($tllist, "Top losers", "losses ". date('F Y', mktime(0,0,0,$month, 1, $year)), "losses", "moon");
$page->addContext($tlbox->generate());

$page->setContent($html);
$page->generate();
?>