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
		$this->view =  preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['view']);
		$this->viewList = array();
		$this->klist = null;
		$this->llist = null;

		$this->menuOptions = array();

		$this->queue("start");
		$this->queue("statSetup");
		$this->queue("stats");
		$this->queue("summaryTable");
		$this->queue("killList");

	}

	//! Reset the assembly object to prepare for creating the context.
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
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
	//! Set up the stats used by stats and summaryTable functions.
	function statSetup()
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
				if ($this->view == "ships_weapons") $this->summary->setFilter(false);
			}
	}
	//! Build the summary table showing all kills and losses for this pilot.
	function summaryTable()
	{
		return $this->summary->generate();
	}

	//! Show the overall statistics for this alliance.
	function stats()
	{
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

	//! Build the killlists that are needed for the options selected.
	function killList()
	{

		if(isset($this->viewList[$this->view])) return call_user_func_array($this->viewList[$this->view], array(&$this));

		switch ($this->view)
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
				$pagesplit = $pagesplitter->generate();
				$html .= $pagesplit."<br /><br />".$table->generate().$pagesplit;

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
				$pagesplit = $pagesplitter->generate();
				$html .= $pagesplit."<br /><br />".$table->generate().$pagesplit;
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
	//! Set up the menu.

	//! Prepare all the base menu options.
	function menuSetup()
	{
		$this->addMenuItem("caption","Kills &amp; losses");
		$this->addMenuItem("link","Recent activity", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=recent");
		$this->addMenuItem("link","Kills", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=kills");
		$this->addMenuItem("link","Losses", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=losses");
		$this->addMenuItem("caption","Statistics");
		$this->addMenuItem("link","Ships &amp; weapons", "?a=pilot_detail&amp;plt_id=".$this->pilot->getID()."&amp;view=ships_weapons");
		return "";
	}
	//! Build the menu.

	//! Add all preset options to the menu.
	function menu()
	{
		$menubox = new box("Menu");
		$menubox->setIcon("menu-item.gif");
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

	//! Add an item to the menu in standard box format.

	/*!
	 *  Only links need all 3 attributes
	 * \param type Types can be caption, img, link, points.
	 * \param name The name to display.
	 * \param url Only needed for URLs.
	 */
	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = array($type, $name, $url);
	}

	//! Add a type of view to the options.

	/*!
	 * \param view The name of the view to recognise.
	 * \param callback The method to call when this view is used.
	 */
	function addView($view, $callback)
	{
		$this->viewList[$view] = $callback;
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