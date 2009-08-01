<?php
require_once('common/includes/class.killsummarytable.public.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.contract.php');
require_once('common/includes/class.toplist.php');
if(config::get('show_clock')) require_once('common/includes/class.clock.php');

// If a valid week and year are given then show that week.
if(((int)$_GET['w']) >0 && ((int)$_GET['w']) < 54 && ((int)$_GET['y']) > 2000) $prevweek = true;
else $prevweek = false;
if($prevweek)
{
	$week = (int)$_GET['w'];
	if($week<10) $week='0'.$week;
	$year = (int)$_GET['y'];
}
else
{
	$week = kbdate('W');
	$year = getYear();
}
if ($week == 1)
{
    $pyear = $year - 1;
    $pweek = 53;
}
else
{
    $pyear = $year;
    $pweek = $week - 1;
}

$killcount = config::get('killcount');
$hourlimit = config::get('limit_hours');
if(!$hourlimit) $hourlimit = 1;
$klreturnmax = 3;

$page = new Page('Week '.$week);

// Display the summary table.
$kslist = new KillList();
involved::load($kslist,'kill');
$kslist->setWeek($week);
$kslist->setYear($year);

if (config::get('summarytable')){
if (config::get('public_summarytable')){
	$summarytable = new KillSummaryTablePublic($kslist);
}
else
{
	$llist = new KillList();
	involved::load($llist,'loss');
	$llist->setWeek($week);
	$llist->setYear($year);
	$summarytable = new KillSummaryTable($kslist, $llist);
}
$summarytable->setBreak(config::get('summarytable_rowcount'));
$html .= $summarytable->generate();
}

// Display campaigns, if any.
if ($page->killboard_->hasCampaigns(true))
{
    $html .= "<div class=kb-campaigns-header>Active campaigns</div>";
    $list = new ContractList();
    $list->setActive("yes");
    $list->setCampaigns(true);
    $table = new ContractListTable($list);
    $html .= $table->generate();
}

// Display contracts, if any.
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
    $html .= "<div class=kb-kills-header><a href=\"?a=rss\"><img src=\"mods/rss_feed/rss_icon.png\" alt=\"RSS-Feed\" border=\"0\"></a>&nbsp;".$killcount." most recent kills</div>";
}
else
{
    $html .= "<div class=kb-kills-header>".$killcount." most recent kills</div>";
}

// Retrieve kills to be displayed limited by the date. If too few are returned
// extend the date range. If too many are returned reduce the date range.
while(true)
{
    $klist = new KillList();
    $klist->setOrdered(true);
    // We'll be needing comment counts so set the killlist to retrieve them
    if (config::get('comments_count')) $klist->setCountComments(true);
    // We'll be needing involved counts so set the killlist to retrieve them
    if (config::get('killlist_involved')) $klist->setCountInvolved(true);
    // limiting doesn't work well with grouping and ordering sql but in case
    // it improves a limit one higher than the size test is used
    if (!$prevweek) $klist->setLimit($killcount * $klreturnmax + 1);
    else
    {
            $klist->setWeek($week);
            $klist->setYear($year);
    }
//    $klist->setLimit($killcount);
	// Select between combined kills and losses or just kills.
    if(config::get('show_comb_home'))
    {
        if(ALLIANCE_ID >0) $klist->addCombinedAlliance(ALLIANCE_ID);
        if(CORP_ID >0) $klist->addCombinedCorp(CORP_ID);
        if(PILOT_ID>0) $klist->addCombinedPilot(PILOT_ID);
    }
    else involved::load($klist,'kill');


    if ($_GET['scl_id'])
        $klist->addVictimShipClass(intval($_GET['scl_id']));
    else
        $klist->setPodsNoobShips(false);

	if($prevweek ) break; // If showing a previous week then don't limit results
    $klist->setStartDate(gmdate("Y-m-d H:i", time()-$hourlimit*3600));

    if($klist->getRealCount() < $killcount)
    {
        // Find oldest kill with board owner as victim or involved
        $qry = new DBQuery();
        $sql = "SELECT kll_timestamp AS oldest FROM kb3_kills ";
		if($_GET['scl_id']) $sql .= "JOIN kb3_ships ON shp_id = kll_ship_id ";
		if(ALLIANCE_ID) $sql .= "JOIN kb3_inv_detail ON kll_id = ind_kll_id WHERE kll_all_id != ".ALLIANCE_ID." AND ind_all_id = ".ALLIANCE_ID." ";
        else if(CORP_ID) $sql .= "JOIN kb3_inv_detail ON kll_id = ind_kll_id WHERE kll_crp_id != ".CORP_ID." AND ind_crp_id = ".CORP_ID." ";
        else if(PILOT_ID) $sql .= "JOIN kb3_inv_detail ON kll_id = ind_plt_id WHERE ind_plt_id = ".PILOT_ID." ";
        if($_GET['scl_id']) $sql .="and shp_class = ".intval($_GET['scl_id'])." ";
        $sql .="ORDER BY kll_timestamp LIMIT 1";
        $qry->execute($sql);
        // If there are no kills there's no point changing the date range
        if($qry->recordCount() == 0) break;
        // If the date range already includes the oldest kill then no kills are
        // relevant so there's no point changing the date range
        $row = $qry->getRow();
        if($hourlimit > abs(strtotime($row['oldest'])-time())/3600 ) break;
        if( !($hourlimit > 1) ) $hourlimit = 1;
        $hourlimit = $hourlimit * 2;
        if(!$_GET['scl_id']) config::set('limit_hours', $hourlimit);
        $limitDate=date('Y-m-d H:i',strtotime('-'.$hourlimit.'hours') );
        continue;
    }
    // If more than the needed kills are retrieved
    // reduce the hour count by 1 with a minimum of 4
    else if($klist->getRealCount() > $killcount * $klreturnmax)
    {
        if($hourlimit > 4) config::set('limit_hours', intval($hourlimit * 0.8) );
    }
    break;
}

// If this is the current week then show the most recent kills. If a previous
// week show all kills for the week using the page splitter.
if($prevweek)
{
        $pagesplitter = new PageSplitter($klist->getCount(), $killcount);
        $klist->setPageSplitter($pagesplitter);
        $table = new KillListTable($klist);
        if(config::get('show_comb_home')) $table->setCombined(true);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();
}
else
{
        $table = new KillListTable($klist);
        if(config::get('show_comb_home')) $table->setCombined(true);
        $table->setLimit($killcount);
        $html .= $table->generate();
}

// Display the menu for previous and next weeks.
$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Navigation");
if($prevweek)
{
	$menubox->addOption("link","Previous week", "?a=home&amp;w=" . $pweek . "&amp;y=" . $pyear);
	if(kbdate('W') != $week)
	{
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
		$menubox->addOption("link","Next week", "?a=home&amp;w=" . $nweek . "&amp;y=" . $nyear);
	}
}
else
{
	$menubox->addOption("link","Whole week", "?a=home&amp;w=" . $week . "&amp;y=" . $year);
	$menubox->addOption("link","Previous week", "?a=home&amp;w=" . $pweek . "&amp;y=" . $pyear);
}
$page->addContext($menubox->generate());

// Show the Eve time.
if(config::get('show_clock'))
{
	$clock = new Clock();
	$page->addContext($clock->generate());
}
// Display the top pilot lists.
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