<?php
require_once('common/includes/class.pilot.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.kill.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');

$plt_id = intval($_GET['plt_id']);
$plt_external_id = intval($_GET['plt_external_id']);
if(!$plt_id)
{
	if($plt_external_id)
	{
		$qry = new DBQuery();
		$qry->execute('SELECT plt_id FROM kb3_pilots WHERE plt_externalid = '.$plt_external_id);
		if($qry->recordCount())
		{
			$row = $qry->getRow();
			$plt_id = $row['plt_id'];
		}
	}
	else
	{
		$html = 'That pilot doesn\'t exist.';
		$page->generate($html);
		exit;
	}

}
$pilot = new Pilot($plt_id);
$corp = $pilot->getCorp();
$alliance = $corp->getAlliance();
$page = new Page('Pilot details - '.$pilot->getName());

if (!$pilot->exists())
{
	$html = 'That pilot doesn\'t exist.';
	$page->generate($html);
	exit;
}

$klist = new KillList();
$tklist = new KillList();
$llist = new KillList();
$klist->addInvolvedPilot($pilot);
$tklist->addInvolvedPilot($pilot);
$llist->addVictimPilot($pilot);
$klist->getAllKills();
$llist->getAllKills();
$tklist->setPodsNoobShips(false);

$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";

$html .= "<tr class=kb-table-row-even>";
$html .= "<td rowspan=8 width=128><img src=\"".$pilot->getPortraitURL(128)."\" border=\"0\" width=\"128\" height=\"128\" alt=\"portrait\"></td>";

$html .= "<td class=kb-table-cell width=160><b>Corporation:</b></td><td class=kb-table-cell><a href=\"?a=corp_detail&amp;crp_id=".$corp->getID()."\">".$corp->getName()."</a></td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Alliance:</b></td><td class=kb-table-cell>";
if ($alliance->getName() == "Unknown" || $alliance->getName() == "None")
	$html .= "<b>".$alliance->getName()."</b>";
else
	$html .= "<a href=\"?a=alliance_detail&amp;all_id=".$alliance->getID()."\">".$alliance->getName()."</a>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>".$klist->getCount()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Real kills:</b></td><td class=kl-kill>".$tklist->getCount()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$llist->getCount()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".round($klist->getISK()/1000000000,2)."B</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".round($llist->getISK()/1000000000,2)."B</td></tr>";

//Pilot Efficiency Mod Begin (K Austin)

if ($tklist->getCount() == 0)
{
	$pilot_survival = 100;
	$pilot_efficiency = 0;
}
else
{
	if($tklist->getCount() + $llist->getCount()) $pilot_survival = round($llist->getCount() / ($tklist->getCount() + $llist->getCount()) * 100,2);
	else $pilot_survival = 0;
	if($klist->getISK() + $llist->getISK()) $pilot_efficiency = round(($klist->getISK() / ($klist->getISK() + $llist->getISK())) * 100,2);
	else $pilot_efficiency = 0;
}

//PE MOD addon (C Berry)
$half = 50.0;
if ($pilot_survival >= $half) $ps_color = "#00AA00";
else $ps_color = "#AA0000";

if ($pilot_efficiency < $half) $pe_color = "#AA0000";
else $pe_color = "#00AA00";

$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Chance of enemy survival:</b></td><td class=kb-table-cell><b><span style=\"color:" .$ps_color .";\">".$pilot_survival ."%</span></b></td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell></td><td class=kb-table-cell><b>Pilot Efficiency (ISK):</b></td><td class=kb-table-cell><b><span style=\"color:" .$pe_color .";\">".$pilot_efficiency ."%</span></b></td></tr>";

//Pilot Efficiency Mod End

//$html .= "</td></tr>";
$html .= "</table>";

$html .= "<br/>";

$points = $klist->getPoints();
$lpoints = $llist->getPoints();
$summary = new KillSummaryTable($klist, $llist);
//$summary = new KillSummaryTable();
//$summary->addInvolvedPilot($pilot);

$summary->setBreak(config::get('summarytable_rowcount'));
if ($_GET['view'] == "ships_weapons")
{
	$summary->setFilter(false);
}
$html .= $summary->generate();

switch ($_GET['view'])
{
	case "kills":
		$html .= "<div class=kb-kills-header>All kills</div>";

		$list = new KillList();
		$list->setOrdered(true);
		$list->addInvolvedPilot($pilot);
		if ($_GET['scl_id'])
			$list->addVictimShipClass(new ShipClass($_GET['scl_id']));
		$list->setPageSplitter(config::get('killcount'));
		$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
		$table = new KillListTable($list);
		$table->setDayBreak(false);
		$html .= $table->generate();
		$html .= $pagesplitter->generate();

		break;
	case "losses":
		$html .= "<div class=kb-losses-header>All losses</div>";

		$list = new KillList();
		$list->setOrdered(true);
		$list->setPodsNoobships(true);
		$list->addVictimPilot($pilot);
		if ($_GET['scl_id'])
			$list->addVictimShipClass(new ShipClass($_GET['scl_id']));
		$list->setPageSplitter(config::get('killcount'));
		$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

		$table = new KillListTable($list);
		$table->setDayBreak(false);
		$html .= $table->generate();
		$html .= $pagesplitter->generate();
		break;
	case "ships_weapons":
		$html .= "<div class=block-header2>Ships & weapons used</div>";

		$html .= "<table class=kb-subtable><tr><td valign=top width=400>";
		$shiplist = new TopShipList();
		$shiplist->addInvolvedPilot($pilot);
		$shiplisttable = new TopShipListTable($shiplist);
		$html .= $shiplisttable->generate();
		$html .= "</td><td valign=top align=right width=400>";

		$weaponlist = new TopWeaponList();
		$weaponlist->addInvolvedPilot($pilot);
		$weaponlisttable = new TopWeaponListTable($weaponlist);
		$html .= $weaponlisttable->generate();
		$html .= "</td></tr></table>";

		break;
	default:
		$html .= "<div class=kb-kills-header>10 Most recent kills</div>";
		$list = new KillList();
		$list->setOrdered(true);
		if (config::get('comments_count')) $list->setCountComments(true);
		if (config::get('killlist_involved')) $list->setCountInvolved(true);
		$list->setLimit(10);
		$list->setPodsNoobships(true);
		$list->addInvolvedPilot($pilot);
		if ($_GET['scl_id'])
			$list->addVictimShipClass(new ShipClass($_GET['scl_id']));

		$table = new KillListTable($list);
		$table->setDayBreak(false);
		$html .= $table->generate();

		$html .= "<div class=kb-losses-header>10 Most recent losses</div>";
		$list = new KillList();
		$list->setOrdered(true);
		if (config::get('comments_count')) $list->setCountComments(true);
		if (config::get('killlist_involved')) $list->setCountInvolved(true);
		$list->setLimit(10);
		$list->setPodsNoobships(true);
		$list->addVictimPilot($pilot);
		if ($_GET['scl_id'])
			$list->addVictimShipClass(new ShipClass($_GET['scl_id']));

		$table = new KillListTable($list);
		$table->setDayBreak(false);
		$table->setDayBreak(false);
		$html .= $table->generate();
		break;
}

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Kills &amp; losses");
$menubox->addOption("link","Recent activity", "?a=pilot_detail&amp;plt_id=".$pilot->getID()."&amp;view=recent");
$menubox->addOption("link","Kills", "?a=pilot_detail&amp;plt_id=".$pilot->getID()."&amp;view=kills");
$menubox->addOption("link","Losses", "?a=pilot_detail&amp;plt_id=".$pilot->getID()."&amp;view=losses");
$menubox->addOption("caption","Statistics");
$menubox->addOption("link","Ships &amp; weapons", "?a=pilot_detail&amp;plt_id=".$pilot->getID()."&amp;view=ships_weapons");
if (strstr(config::get("mods_active"), 'signature_generator'))
{
	$menubox->addOption("caption","Signature");
	$menubox->addOption("link","Link", "?a=sig_list&amp;i=".$pilot->getID());
}
$page->addContext($menubox->generate());

if (config::get('kill_points'))
{
	$scorebox = new Box("Kill points");
	$scorebox->addOption("points", $points);
	$page->addContext($scorebox->generate());
}
if (config::get('loss_points'))
{
	$scorebox = new Box("Loss points");
	$scorebox->addOption("points", $lpoints);
	$page->addContext($scorebox->generate());
}
if (config::get('total_points'))
{
	$scorebox = new Box("Total points");
	$scorebox->addOption("points", $points-$lpoints);
	$page->addContext($scorebox->generate());
}

$page->setContent($html);
$page->generate();
?>