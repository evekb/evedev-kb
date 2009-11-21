<?php
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.contract.php');
require_once('common/includes/class.toplist.php');

$ctr_id = $_GET['ctr_id'];

$contract = new Contract($ctr_id);

if ($contract->getCampaign())
    $title = 'Campaign details';
else
    $title = 'Contract details';

$page = new Page($title.' - '.$contract->getName());

$html .= "<table align=center class=kb-table width=\"100%\" height=\"80\" border=\"0\" cellspacing=1><tr class=kb-table-row-even><td rowspan=5 align=center width=80 height=80>";
// table class=kb-subtable cellspacing=0><tr class=kb-table-row-even><td width=80 height=80 align=center>";

$custom_img = preg_replace('/[^a-zA-Z0-9-\s]/', '',$contract->getName());
$custom_img = str_replace(' ', '_', $custom_img);
$custom_img = IMG_URL.'/'.$custom_img.'.gif';
if(file_exists($custom_img))
{
$html .= "<img src=\"".$custom_img."\" align=center>";

}
else
{
	if ($contract->getCampaign())
    	$html .= "<img src=\"".IMG_URL."/campaign-big.gif\" align=center>";
	else
    	$html .= "<img src=\"".IMG_URL."/contract-big.gif\" align=center>";
}

$html .= "</td>";
// $html .= "<td valign=top align=left height=80>";
// $html .= "<table class=kb-subtable width=\"100%\" height=\"100%\" cellspacing=1 border=\"0\">";
if ($contract->getEndDate() == "")
    $ended = "Active";
else
    $ended = substr($contract->getEndDate(), 0, 10);
$html .= "<td class=kb-table-cell><b>Start date:</b></td><td class=kb-table-cell width=120><b>".substr($contract->getStartDate(), 0, 10)."</b></td><td class=kb-table-cell><b>End date:</b></td><td class=kb-table-cell width=120><b>".$ended."</b></td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>".$contract->getKills()."</td><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$contract->getLosses()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".round($contract->getKillISK()/1000000, 2)."M</td><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".round($contract->getLossISK()/1000000, 2)."M</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Runtime:</b></td><td class=kb-table-cell><b>".$contract->getRunTime()." days</b></td><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>".$contract->getEfficiency()."%</b></td></tr>";
$html .= "</table>";
// $html .= "</td></tr></table>";
$klist = $contract->getKillList();
$llist = $contract->getLossList();
$killsummary = new KillSummaryTable($klist, $llist);
if ($_GET['view'] == "")
    $killsummary->setFilter(false);

$html .= "<br>";
$html .= $killsummary->generate();

switch ($_GET['view'])
{
    case "":
        while ($target = &$contract->getContractTarget())
        {
            $kl = &$target->getKillList();
            $ll = &$target->getLossList();
            $summary = new KillSummaryTable($kl, $ll);
            $summary->setVerbose(true);
            $summary->setView('combined');

            $html .= "<br><div class=kb-contract-target-header>Target ".$target->getType()." - ";
            switch ($target->getType()) {
                case "corp":
                    $html .= "<a class=kb-contract href=\"?a=".$target->getType()."_detail&crp_id=".$target->getID()."\">".$target->getName()."</a>";
                    break;
                case "alliance":
                    $html .= "<a class=kb-contract href=\"?a=".$target->getType()."_detail&all_id=".$target->getID()."\">".$target->getName()."</a>";
                    break;
                case "system":
                    $html .= "<a class=kb-contract href=\"?a=" .$target->getType()."_detail&sys_id=".$target->getID()."\">".$target->getName()."</a>";
                    break;
                case "region":
                    $html .= $target->getName();
                    break;
            }
            $html .= "</div>";
            $html .= $summary->generate();

            $html .= "<br><table class=kb-subtable border=\"0\" cellspacing=0 width=\"100%\"><tr><td>";

            if ($summary->getTotalKillISK())
                $efficiency = round($summary->getTotalKillISK() / ($summary->getTotalKillISK() + $summary->getTotalLossISK()) * 100, 2);
            else
                $efficiency = 0;

            $bar = new BarGraph($efficiency, 100, 120);
            $html .= "<table class=kb-table cellspacing=1 border=\"0\" width=\"100%\"><tr class=kb-table-row-even>";
            $html .= "<td class=kb-table-cell width=108><b>Totals:</b></td><td class=kl-kill-bg width=60 align=center>".$summary->getTotalKills()."</td><td class=kl-kill-bg width=60 align=center>".round($summary->getTotalKillISK()/1000000, 2)."M</td>";
            $html .= "<td class=kl-loss-bg width=64 align=center>".$summary->getTotalLosses()."</td><td class=kl-loss-bg width=60 align=center>".round($summary->getTotalLossISK()/1000000, 2)."M</td></tr></table>";

            $html .= "</td><td align=left>";

            $html .= "<table class=kb-table cellspacing=1 border=\"0\"><tr class=kb-table-row-even>";
            $html .= "<td class=kb-table-cell width=108><b>Efficiency:</b></td><td class=kb-table-cell align=center colspan=2 width=120><b>".$efficiency."%</b></td>";
            $html .= "<td class=kb-table-cell colspan=2 width=120>".$bar->generate()."</td></tr>";
            $html .= "</tr></table>";

            $html .= "</td></tr></table>";
        }

        break;
    case "recent_activity":
        $html .= "<div class=kb-kills-header>10 Most recent kills</div>";

        $contract = new Contract($ctr_id);
        $klist = $contract->getKillList();
        $klist->setOrdered(true);
        if ($_GET['scl_id'])
            $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
        else
            $klist->setPodsNoobShips(false);

        $table = new KillListTable($klist);
        $table->setLimit(10);
        $table->setDayBreak(false);
        $html .= $table->generate();

        $html .= "<div class=kb-losses-header>10 Most recent losses</div>";
        $llist = $contract->getLossList();
        $llist->setOrdered(true);
        if ($_GET['scl_id'])
            $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));
        else
            $llist->setPodsNoobShips(false);

        $table = new KillListTable($llist);
        $table->setLimit(10);
        $table->setDayBreak(false);
        $html .= $table->generate();
        break;
    case "kills":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $contract = new Contract($ctr_id);
        $list = $contract->getKillList();
        $list->setOrdered(true);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);
        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();
        break;
    case "losses":
        $html .= "<div class=kb-losses-header>All losses</div>";

        $contract = new Contract($ctr_id);
        $llist = $contract->getLossList();
        $llist->setOrdered(true);
        if ($_GET['scl_id'])
            $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $pagesplitter = new PageSplitter($llist->getCount(), 30);
        $llist->setPageSplitter($pagesplitter);
        $table = new KillListTable($llist);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();
        break;
    case "combined":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $contract = new Contract($ctr_id);
        $list = $contract->getKillList();
        $list->setOrdered(true);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $pagesplitter = new PageSplitter($list->getCount(), 20);
        $list->setPageSplitter($pagesplitter);
        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        $html .= "<div class=kb-losses-header>All losses</div>";

        $contract = new Contract($ctr_id);
        $llist = $contract->getLossList();
        $llist->setOrdered(true);
        if ($_GET['scl_id'])
            $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $pagesplitter = new PageSplitter($llist->getCount(), 20);
        $llist->setPageSplitter($pagesplitter);
        $table = new KillListTable($llist);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();
        break;
}

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Overview");
$menubox->addOption("link","Target overview", "?a=cc_detail&ctr_id=".$_GET['ctr_id']);
$menubox->addOption("caption","Kills & losses");
$menubox->addOption("link","Recent activity", "?a=cc_detail&ctr_id=".$_GET['ctr_id']."&view=recent_activity");
$menubox->addOption("link","All kills", "?a=cc_detail&ctr_id=".$_GET['ctr_id']."&view=kills");
$menubox->addOption("link","All losses", "?a=cc_detail&ctr_id=".$_GET['ctr_id']."&view=losses");
if($page->isAdmin()){
$menubox->addOption("caption","Admin");
$menubox->addOption("link", "Forum Summary", "javascript:sndReq('index.php?a=forum_post&ctr_id=".$_GET['ctr_id']."');ReverseContentDisplay('popup')");
}

$page->addContext($menubox->generate());

$tklist = new TopContractKillsList();
$tklist->setContract(new Contract($ctr_id));
involved::load($tklist,'kill');

$tklist->generate();
if ($contract->getCampaign())
    $campaign = "campaign";
else
    $campaign = "contract";
$tkbox = new AwardBox($tklist, "Top killers", "kills in this ".$campaign, "kills", "eagle");

$page->addContext($tkbox->generate());

if (config::get('kill_points'))
{
    $tklist = new TopContractScoreList();
    $tklist->setContract(new Contract($ctr_id));
    involved::load($tklist,'kill');

    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points in this ".$campaign, "points", "redcross");
    $page->addContext($tkbox->generate());
}

$page->setContent($html);
$page->generate();
?>