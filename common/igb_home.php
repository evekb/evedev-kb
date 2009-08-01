<?php
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.contract.php');
require_once('common/includes/class.toplist.php');

$week = kbdate('W');
$year = kbdate('Y');

$page = new Page('Week '.$week);

$kslist = new KillList();
involved::load($kslist,'kill');
$kslist->setWeek($week);
$kslist->setYear($year);

$llist = new KillList();
involved::load($llist,'loss');
$llist->setWeek($week);
$llist->setYear($year);

$summarytable = new KillSummaryTable($kslist, $llist);
$summarytable->setBreak(config::get('summarytable_rowcount'));

if ($week == 1)
{
    $pyear = kbdate("Y") - 1;
    $pweek = 52;
}
else
{
    $pyear = kbdate("Y");
    $pweek = $week - 1;
}
/*
if ($page->killboard_->hasCampaigns(true))
{
    $html .= "<div class=kb-campaigns-header>Active campaigns</div>";
    $list = new ContractList();
    $list->setActive("yes");
    $list->setCampaigns(true);
    $table = new ContractListTable($list);
    $html .= $table->generate();
}

if ($page->killboard_->hasContracts(true))
{
    $html .= "<div class=kb-campaigns-header>Active contracts</div>";
    $list = new ContractList();
    $list->setActive("yes");
    $list->setCampaigns(false);
    $table = new ContractListTable($list);
    $html .= $table->generate();
}*/

$html .= "<div class=kb-kills-header>20 most recent kills</div>";


$klist = new KillList();
$klist->setOrdered(true);
involved::load($klist,'kill');

// boards with low killcount could not display 20 kills with those limits
//$klist->setStartWeek($week - 1);
//$klist->setYear($year);
$klist->setLimit(20);

if ($_GET['scl_id'])
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

$table = new KillListTable($klist);
$table->setLimit(20);
$html .= $table->generate();

$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Navigation");
$menubox->addOption("link","Previous week", "?a=kills&w=" . $pweek . "&y=" . $pyear);
$page->addContext($menubox->generate());

$tklist = new TopKillsList();
$tklist->setWeek($week);
$tklist->setYear($year);
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills in week " . $week, "kills", "eagle");
$page->addContext($tkbox->generate());

if (config::get('kill_points'))
{
    $tklist = new TopScoreList();
    $tklist->setWeek($week);
    $tklist->setYear($year);
    involved::load($tklist,'kill');

    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points in week " . $week, "points", "redcross");
    $page->addContext($tkbox->generate());
}

$page->generate();
?>