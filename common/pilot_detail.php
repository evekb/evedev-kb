<?php
require_once('common/includes/class.pilot.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.kill.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');
require_once('common/includes/class.pageAssembly.php');

class pPilotDetail extends pageAssembly
{
	//! Construct the Pilot Details object.

	/** Set up the basic variables of the class and add the functions to the
	 *  build queue.
	 */
	function __construct()
	{
		parent::__construct();
		$this->scl_id = intval($_GET['scl_id']);
		$this->plt_id = intval($_GET['plt_id']);
		$this->plt_external_id = intval($_GET['plt_external_id']);
		$this->view = $_GET['view'];
		$this->klist = null;
		$this->llist = null;

		$this->menuOptions = array();

		$this->queue("start");
		$this->queue("stats");
		$this->queue("summaryTable");
		$this->queue("killList");

	}

	function context()
	{
		parent::__construct();
		$this->queue("menu");
		$this->queue("points");
	}

	//! Start constructing the page.

	/*! Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{

		if(!$this->plt_id)
		{
			if($this->plt_external_id)
			{
				$qry = new DBQuery();
				$qry->execute('SELECT plt_id FROM kb3_pilots WHERE plt_externalid = '.$this->plt_external_id);
				if($qry->recordCount())
				{
					$row = $qry->getRow();
					$this->plt_id = $row['plt_id'];
				}
			}
			elseif(PILOT_ID) $this->plt_id = PILOT_ID;
			else
			{
				$html = 'That pilot doesn\'t exist.';
				$this->page->generate($html);
				exit;
			}

		}
		$this->pilot = new Pilot($this->plt_id);
		$this->page = new Page('Pilot details - '.$this->pilot->getName());

		if (!$this->pilot->exists())
		{
			$html = 'That pilot doesn\'t exist.';
			$this->page->generate($html);
			exit;
		}
		$this->corp = $this->pilot->getCorp();
		$this->alliance = $this->corp->getAlliance();
	}
	function summaryTable()
	{
		if(is_null($this->klist) || is_null($this->llist))
		{
			$this->klist = new KillList();
			$this->llist = new KillList();
			$this->klist->addInvolvedPilot($this->pilot);
			$this->llist->addVictimPilot($this->pilot);
			$this->klist->getAllKills();
			$this->llist->getAllKills();
			$this->points = $this->klist->getPoints();
			$this->lpoints = $this->llist->getPoints();
			if(!isset($this->kill_summary))
			{
				$this->summary = new KillSummaryTable($this->klist, $this->llist);
				if ($_GET['view'] == "ships_weapons") $this->summary->setFilter(false);
			}
		}
		$html .= $this->summary->generate();
		return $html;
	}

	function stats()
	{
		if(is_null($this->klist) || is_null($this->llist))
		{
			$this->klist = new KillList();
			$this->llist = new KillList();
			$this->klist->addInvolvedPilot($this->pilot);
			$this->llist->addVictimPilot($this->pilot);
			$this->klist->getAllKills();
			$this->llist->getAllKills();
			$this->points = $this->klist->getPoints();
			$this->lpoints = $this->llist->getPoints();
			if(!isset($this->kill_summary))
			{
				$this->summary = new KillSummaryTable($this->klist, $this->llist);
				if ($_GET['view'] == "ships_weapons") $this->summary->setFilter(false);
			}
		}
		global $smarty;
		$smarty->assign('portrait_URL',$this->pilot->getPortraitURL(128));
		$smarty->assign('corp_id',$this->corp->getID());
		$smarty->assign('corp_name',$this->corp->getName());
		$smarty->assign('all_name',$this->alliance->getName());
		$smarty->assign('all_id',$this->alliance->getID());
		$smarty->assign('klist_count',$this->klist->getCount());
		$smarty->assign('klist_real_count',$this->klist->getRealCount());
		$smarty->assign('llist_count',$this->llist->getCount());
		$smarty->assign('klist_isk_B',round($this->klist->getISK()/1000000000,2));
		$smarty->assign('llist_isk_B',round($this->llist->getISK()/1000000000,2));

		//Pilot Efficiency Mod Begin (K Austin)

		if ($this->klist->getRealCount() == 0)
		{
			$pilot_survival = 100;
			$pilot_efficiency = 0;
		}
		else
		{
			if($this->klist->getRealCount() + $this->llist->getCount()) $pilot_survival = round($this->llist->getCount() / ($this->klist->getRealCount() + $this->llist->getCount()) * 100,2);
			else $pilot_survival = 0;
			if($this->klist->getISK() + $this->llist->getISK()) $pilot_efficiency = round(($this->klist->getISK() / ($this->klist->getISK() + $this->llist->getISK())) * 100,2);
			else $pilot_efficiency = 0;
		}

		$smarty->assign('pilot_survival',$pilot_survival);
		$smarty->assign('pilot_efficiency',$pilot_efficiency);

		return $smarty->fetch(get_tpl('pilot_detail_stats'));
	}

	function killList()
	{
		switch ($_GET['view'])
		{
			case "kills":
				$html .= "<div class='kb-kills-header'>All kills</div>";

				$list = new KillList();
				$list->setOrdered(true);
				$list->addInvolvedPilot($this->pilot);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$html .= $table->generate();
				$html .= $pagesplitter->generate();

				break;
			case "losses":
				$html .= "<div class='kb-losses-header'>All losses</div>";

				$list = new KillList();
				$list->setOrdered(true);
				$list->setPodsNoobships(config::get('podnoobs'));
				$list->addVictimPilot($this->pilot);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$html .= $table->generate();
				$html .= $pagesplitter->generate();
				break;
			case "ships_weapons":
				$html .= "<div class='block-header2'>Ships & weapons used</div>";

				$html .= "<table class='kb-subtable'><tr><td valign=top width=400>";
				$shiplist = new TopShipList();
				$shiplist->addInvolvedPilot($this->pilot);
				$shiplisttable = new TopShipListTable($shiplist);
				$html .= $shiplisttable->generate();
				$html .= "</td><td valign=top align=right width=400>";

				$weaponlist = new TopWeaponList();
				$weaponlist->addInvolvedPilot($this->pilot);
				$weaponlisttable = new TopWeaponListTable($weaponlist);
				$html .= $weaponlisttable->generate();
				$html .= "</td></tr></table>";

				break;
			default:
				$html .= "<div class='kb-kills-header'>10 Most recent kills</div>";
				$list = new KillList();
				$list->setOrdered(true);
				if (config::get('comments_count')) $list->setCountComments(true);
				if (config::get('killlist_involved')) $list->setCountInvolved(true);
				$list->setLimit(10);
				$list->setPodsNoobships(config::get('podnoobs'));
				$list->addInvolvedPilot($this->pilot);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);

				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$html .= $table->generate();

				$html .= "<div class='kb-losses-header'>10 Most recent losses</div>";
				$list = new KillList();
				$list->setOrdered(true);
				if (config::get('comments_count')) $list->setCountComments(true);
				if (config::get('killlist_involved')) $list->setCountInvolved(true);
				$list->setLimit(10);
				$list->setPodsNoobships(config::get('podnoobs'));
				$list->addVictimPilot($this->pilot);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);

				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$table->setDayBreak(false);
				$html .= $table->generate();
				break;
		}
		return $html;
	}
	function menu()
	{
		$menubox = new box("Menu");
		$menubox->setIcon("menu-item.gif");
		$menubox->addOption("caption","Kills &amp; losses");
		$menubox->addOption("link","Recent activity", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=recent");
		$menubox->addOption("link","Kills", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=kills");
		$menubox->addOption("link","Losses", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=losses");
		$menubox->addOption("caption","Statistics");
		$menubox->addOption("link","Ships &amp; weapons", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=ships_weapons");
		foreach($this->menuOptions as $options)
		{
			if(isset($options[2]))
				$menubox->addOption($options[0],$options[1], $options[2]);
			else
				$menubox->addOption($options[0],$options[1]);
		}
		return $menubox->generate();
	}

	function points()
	{
		$html = '';
		if (config::get('kill_points') && !empty($this->points))
		{
			$scorebox = new Box("Kill points");
			$scorebox->addOption("points", $this->points);
			$html .= $scorebox->generate();
		}
		if (config::get('loss_points') && !empty($this->lpoints))
		{
			$scorebox = new Box("Loss points");
			$scorebox->addOption("points", $this->lpoints);
			$html .= $scorebox->generate();
		}
		if (config::get('total_points') && !empty($this->lpoints))
		{
			$scorebox = new Box("Total points");
			$scorebox->addOption("points", $this->points-$this->lpoints);
			$html .= $scorebox->generate();
		}
		return $html;
	}

	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = array($type, $name, $url);
	}
}



$pilotDetail = new pPilotDetail();
event::call("pilotDetail_assembling", $pilotDetail);
$html = $pilotDetail->assemble();
$pilotDetail->page->setContent($html);

$pilotDetail->context();
event::call("pilotDetail_context_assembling", $pilotDetail);
$context = $pilotDetail->assemble();
$pilotDetail->page->addContext($context);

$pilotDetail->page->generate();