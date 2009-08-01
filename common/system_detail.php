<?php
require_once('common/includes/class.system.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');

$sys_id = intval($_GET['sys_id']);

if (!$sys_id)
{
    echo 'no valid id supplied<br/>';
    exit;
}
$system = new SolarSystem($sys_id);

$page = new Page('System details - '.$system->getName());

$smarty->assign('sys_id',$sys_id);

$kslist = new KillList();
involved::load($kslist,'kill');
$kslist->addSystem($system);
if(config::get('kill_classified')) $kslist->setEndDate(gmdate('Y-m-d H:i',strtotime('now - '.(config::get('kill_classified')*3600).' hours')));

$lslist = new KillList();
involved::load($lslist,'loss');
$lslist->addSystem($system);
if(config::get('kill_classified')) $lslist->setEndDate(gmdate('Y-m-d H:i',strtotime('now - '.(config::get('kill_classified')*3600).' hours')));

$summarytable = new KillSummaryTable($kslist, $lslist);
$summarytable->setBreak(config::get('summarytable_rowcount'));
$html .= $summarytable->generate();

$klist = new KillList();
$klist->setOrdered(true);
if ($_GET['view'] == 'losses') involved::load($klist,'loss');
else involved::load($klist,'kill');
$klist->addSystem($system);
if ($_GET['scl_id']) $klist->addVictimShipClass(intval($_GET['scl_id']));
else $klist->setPodsNoobShips(false);

if ($_GET['view'] == 'recent' || !isset($_GET['view']))
{
	$smarty->assign('sys_view', 'recentkills');
//    $html .= "<div class=kb-kills-header>20 most recent kills</div>";
    $klist->setLimit(20);
}
else
{
    if ($_GET['view'] == 'losses')
    {
		$smarty->assign('sys_view', 'losses');
        //$html .= "<div class=kb-kills-header>All losses</div>";
    }
    else
    {
		$smarty->assign('sys_view', 'kills');
        //$html .= "<div class=kb-kills-header>All kills</div>";
    }
	$klist->setPageSplitter(config::get('killcount'));
	$pagesplitter = new PageSplitter($klist->getCount(), config::get('killcount'));
}

$table = new KillListTable($klist);
$smarty->assign('sys_killlist', $table->generate());
if (is_object($pagesplitter))
{
    $smarty->assign('sys_splitter', $pagesplitter->generate());
}

$page->setContent($smarty->fetch(get_tpl(system_detail)));
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Navigation");
$menubox->addOption("link","All kills", "?a=system_detail&amp;sys_id=".$sys_id."&amp;view=kills");
$menubox->addOption("link","All losses", "?a=system_detail&amp;sys_id=".$sys_id."&amp;view=losses");
$menubox->addOption("link","Recent Activity", "?a=system_detail&amp;sys_id=".$sys_id."&amp;view=recent");
$page->addContext($menubox->generate());

$page->generate();
?>