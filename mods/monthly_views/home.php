<?php
require_once('common/includes/class.killsummarytable.public.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.contract.php');
require_once('common/includes/class.toplist.php');
if(config::get('show_clock')) require_once('common/includes/class.clock.php');

$week = kbdate('W');
$year = kbdate('Y');
$month = kbdate('m');

$page = new Page( kbdate("F") );

$kslist = new KillList();
involved::load($kslist,'kill');
$kslist->setStartDate(date('Y-m').'-1 00:00:00');

if (config::get('summarytable')){
if (config::get('public_summarytable')){
	$summarytable = new KillSummaryTablePublic($kslist);
}
else
{
	$llist = new KillList();
	involved::load($llist,'loss');
	$llist->setStartDate(date('Y-m').'-1 00:00:00');
	
	$summarytable = new KillSummaryTable($kslist, $llist);
}
$summarytable->setBreak(config::get('summarytable_rowcount'));
$html .= $summarytable->generate();
}

if ($week == 1)
{
    $pyear = kbdate("Y") - 1;
    $pweek = 53;
}
else
{
    $pyear = kbdate("Y");
    $pweek = $week - 1;
}

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
}

// bad hax0ring, we really need mod callback stuff
if (strpos(config::get('mods_active'), 'rss_feed') !== false)
{
    $html .= "<div class=kb-kills-header style=\"margin-top: 30px\"><a href=\"?a=rss\"><img src=\"mods/rss_feed/rss_icon.png\" alt=\"RSS-Feed\" border=\"0\"></a>&nbsp;".config::get('killcount')." most recent kills</div>";
}
else
{
    $html .= "<div class=kb-kills-header style=\"margin-top: 30px\">Most recent kills</div>";
}


$klist = new KillList();
$klist->setOrdered(true);
    if(config::get('show_comb_home'))
    {
        if(ALLIANCE_ID >0) $klist->addCombinedAlliance(ALLIANCE_ID);
        if(CORP_ID >0) $klist->addCombinedCorp(CORP_ID);
        if(PILOT_ID>0) $klist->addCombinedPilot(PILOT_ID);
    }
    else involved::load($klist,'kill');

$klist->setLimit(config::get('killcount'));

if ($_GET['scl_id'])
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

$table = new KillListTable($klist);
if(config::get('show_comb_home')) $table->setCombined(true);
$table->setLimit(config::get('killcount'));
$html .= $table->generate();

$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Navigation");
$menubox->addOption("link","Kills ". date('F'), "?a=killsmonthly&amp;m=" . $month . "&amp;y=" . $year);
$menubox->addOption("link","Losses ". date('F'), "?a=lossesmonthly&amp;m=" . $month . "&amp;y=" . $year);
$page->addContext($menubox->generate());

// Show the Eve time.
if(config::get('show_clock'))
{
	$clock = new Clock();
	$page->addContext($clock->generate());
}
$tklist = new TopKillsList();
$tklist->setStartDate(date('Y-m').'-1 00:00:00');
involved::load($tklist,'kill');

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills in " . date('F'), "kills", "eagle");
$page->addContext($tkbox->generate());

if (config::get('kill_points'))
{
    $tklist = new TopScoreList();
    $tklist->setStartDate(date('Y-m').'-1 00:00:00');
    involved::load($tklist,'kill');

    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points in " . date('F'), "points", "redcross");
    $page->addContext($tkbox->generate());
}

$page->generate();
?>