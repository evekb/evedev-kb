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

if ($contract->getCampaign())
    $smarty->assign('campaign', true);
else
    $smarty->assign('campaign', false);
if ($contract->getEndDate() == "")
    $smarty->assign('contract_enddate', "Active");
else
    $smarty->assign('contract_enddate', substr($contract->getEndDate(), 0, 10));
$smarty->assign('contract_startdate', substr($contract->getStartDate(), 0, 10));
$smarty->assign('kill_count', $contract->getKills());
$smarty->assign('loss_count', $contract->getLosses());
$smarty->assign('kill_isk', round($contract->getKillISK()/1000000000, 2));
$smarty->assign('loss_isk', round($contract->getLossISK()/1000000000, 2));
$smarty->assign('contract_runtime', $contract->getRunTime());
$smarty->assign('contract_efficiency', $contract->getEfficiency());

$klist = $contract->getKillList();
$llist = $contract->getLossList();
$killsummary = new KillSummaryTable($klist, $llist);
$killsummary->setBreak(config::get('summarytable_rowcount'));
if ($_GET['view'] == "") $killsummary->setFilter(false);

$smarty->assign('contract_summary', $killsummary->generate());
$smarty->assign('view',$_GET['view']);
switch ($_GET['view'])
{
    case "":
		$qrylength=new DBQuery();
		// set break at half of the number of valid classes - excludes noob ships, drones and unknown
		$qrylength->execute("SELECT count(*) - 3 AS cnt FROM kb3_ship_classes");
		if($qrylength->recordCount())
		{
			$res = $qrylength->getRow();
			$breaklen =$res['cnt']/2;
		}
		else $breaklen = 15;
		unset($qrylength);
		$targets = array();
		$curtarget = array();
        while ($target = &$contract->getContractTarget())
        {
            $kl = &$target->getKillList();
            $ll = &$target->getLossList();
            $summary = new KillSummaryTable($kl, $ll);
            $summary->setVerbose(true);
			$summary->setBreak($breaklen);
            $summary->setView('combined');

			$curtargets['type'] = $target->getType();
			$curtargets['id'] = $target->getID();
			$curtargets['name'] = $target->getName();
			$curtargets['summary'] = $summary->generate();

            if ($summary->getTotalKillISK())
                $curtargets['efficiency'] = round($summary->getTotalKillISK() / ($summary->getTotalKillISK() + $summary->getTotalLossISK()) * 100, 2);
            else
                $curtargets['efficiency'] = 0;
			$curtargets['total_kills'] = $summary->getTotalKills();
			$curtargets['total_losses'] = $summary->getTotalLosses();
			$curtargets['total_kill_isk'] = round($summary->getTotalKillISK()/1000000000, 2);
			$curtargets['total_loss_isk'] = round($summary->getTotalLossISK()/1000000000, 2);
            $bar = new BarGraph($curtargets['efficiency'], 100, 120);
			$curtargets['bar'] = $bar->generate();
			$targets[] = $curtargets;
        }
		$smarty->assign_by_ref('targets', $targets);
		$html .= $smarty->fetch(get_tpl('cc_detail'));
        break;
    case "recent_activity":
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
        $smarty->assign('killtable', $table->generate());

        $llist = $contract->getLossList();
        $llist->setOrdered(true);
        if ($_GET['scl_id'])
            $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));
        else
            $llist->setPodsNoobShips(false);

        $table = new KillListTable($llist);
        $table->setLimit(10);
        $table->setDayBreak(false);
        $smarty->assign('losstable', $table->generate());
		$html .= $smarty->fetch(get_tpl('cc_detail'));
        break;
    case "kills":
        $contract = new Contract($ctr_id);
        $list = $contract->getKillList();
        $list->setOrdered(true);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

		$list->setPageSplitter(config::get('killcount'));
		$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $smarty->assign('killtable', $table->generate());
        $smarty->assign('splitter', $pagesplitter->generate());
		$html .= $smarty->fetch(get_tpl('cc_detail'));
        break;
    case "losses":
        $contract = new Contract($ctr_id);
        $llist = $contract->getLossList();
        $llist->setOrdered(true);
        if ($_GET['scl_id'])
            $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));

		$list->setPageSplitter(config::get('killcount'));
		$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
        $table = new KillListTable($llist);
        $table->setDayBreak(false);
        $smarty->assign('losstable', $table->generate());
        $smarty->assign('splitter', $pagesplitter->generate());
		$html .= $smarty->fetch(get_tpl('cc_detail'));
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