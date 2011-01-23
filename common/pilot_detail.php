<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class pPilotDetail extends pageAssembly
{
	private $viewList = array();
	private $klist = null;
	private $llist = null;
	private $menuOptions = array();

	// TODO: Make these private and add access functions for mods to use.
	public $scl_id = false;
	public $plt_id = false;
	public $page = null;

	//! Construct the Pilot Details object.

	/** Set up the basic variables of the class and add the functions to the
	 *  build queue.
	 */
	function __construct()
	{
		parent::__construct();

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
		$this->page = new Page();

		$this->scl_id = intval($_GET['scl_id']);
		$this->plt_id = intval($_GET['plt_id']);
		if(isset($_GET['plt_external_id'])) $this->plt_external_id = intval($_GET['plt_external_id']);
		elseif(isset($_GET['plt_ext_id'])) $this->plt_external_id = intval($_GET['plt_ext_id']);
		else $this->plt_external_id = 0;
		$this->view =  preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['view']);
		if($this->view) $this->page->addHeader('<meta name="robots" content="noindex, nofollow" />');

		if(!$this->plt_id)
		{
			if($this->plt_external_id)
			{
				$this->pilot = new Pilot(0, $this->plt_external_id);
				$this->plt_id = $this->pilot->getID();
			}
			elseif(PILOT_ID)
			{
				$this->plt_id = PILOT_ID;
				$this->pilot = new Pilot(PILOT_ID);
			}
			else
			{
				$html = 'That pilot doesn\'t exist.';
				$this->page->generate($html);
				exit;
			}

		}
		else $this->pilot = new Pilot($this->plt_id);
		$this->page->setTitle('Pilot details - '.$this->pilot->getName());

		if (!$this->pilot->exists())
		{
			$html = 'That pilot doesn\'t exist.';
			$this->page->generate($html);
			exit;
		}
		$this->corp = $this->pilot->getCorp();
		$this->alliance = $this->corp->getAlliance();
		
		$this->month = intval($_GET['m']);
		$this->year = intval($_GET['y']);

		if ($this->month == '')
			$this->month = kbdate('m');

		if ($this->year == '')
			$this->year = kbdate('Y');

		if ($this->month == 12)
		{
			$this->nmonth = 1;
			$this->nyear = $this->year + 1;
		}
		else
		{
			$this->nmonth = $this->month + 1;
			$this->nyear = $this->year;
		}
		if ($this->month == 1)
		{
			$this->pmonth = 12;
			$this->pyear = $this->year - 1;
		}
		else
		{
			$this->pmonth = $this->month - 1;
			$this->pyear = $this->year;
		}
		$this->monthname = kbdate("F", strtotime("2000-".$this->month."-2"));
	}
	//! Set up the stats used by stats and summaryTable functions.
	function statSetup()
	{
			if(!isset($this->kill_summary))
			{
				$this->summary = new KillSummaryTable();
				$this->summary->addInvolvedPilot($this->plt_id);
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
		$this->summary->generate();
		if($this->pilot->getExternalID())
		{
			$apiInfo = new API_CharacterInfo();
			$apiInfo->setID($this->pilot->getExternalID());
			$result .= $apiInfo->fetchXML();
			// Update the name if it has changed.
			if($result == "")
			{
				$data = $apiInfo->getData();
				$this->alliance->add($data['alliance'],
					$data['allianceID']);
				$this->corp->add($data['corporation'],
					$this->alliance, $apiInfo->getCurrentTime(),
					$data['corporationID']);
				$this->pilot->add($data['characterName'], $this->corp, $apiInfo->getCurrentTime(), $data['characterID']);
			}
		}
		global $smarty;
		$smarty->assign('portrait_URL',$this->pilot->getPortraitURL(128));
		$smarty->assign('corp_id',$this->corp->getID());
		$smarty->assign('corp_name',$this->corp->getName());
		$smarty->assign('all_name',$this->alliance->getName());
		$smarty->assign('all_id',$this->alliance->getID());
		$smarty->assign('klist_count',$this->summary->getTotalKills());
		$smarty->assign('klist_real_count',$this->summary->getTotalRealKills());//$this->klist->getRealCount());
		$smarty->assign('llist_count',$this->summary->getTotalLosses());
		$smarty->assign('klist_isk_B',round($this->summary->getTotalKillISK()/1000000000,2));
		$smarty->assign('llist_isk_B',round($this->summary->getTotalLossISK()/1000000000,2));

		//Pilot Efficiency Mod Begin (K Austin)
		if ($this->summary->getTotalKills() == 0)
		{
			$pilot_survival = 100;
			$pilot_efficiency = 0;
		}
		else
		{
			if($this->summary->getTotalKills() + $this->summary->getTotalLosses()) $pilot_survival = round($this->summary->getTotalLosses() / ($this->summary->getTotalKills() + $this->summary->getTotalLosses()) * 100,2);
			else $pilot_survival = 0;
			if($this->summary->getTotalKillISK() + $this->summary->getTotalLossISK()) $pilot_efficiency = round(($this->summary->getTotalKillISK() / ($this->summary->getTotalKillISK() + $this->summary->getTotalLossISK())) * 100,2);
			else $pilot_efficiency = 0;
		}

		$smarty->assign('pilot_survival',$pilot_survival);
		$smarty->assign('pilot_efficiency',$pilot_efficiency);

		return $smarty->fetch(get_tpl('pilot_detail_stats'));
	}

	//! Build the killlists that are needed for the options selected.
	function killList()
	{
		global $smarty;
		if(isset($this->viewList[$this->view])) return call_user_func_array($this->viewList[$this->view], array(&$this));

		switch ($this->view)
		{
			case "kills":
				$list = new KillList();
				$list->setOrdered(true);
				$list->addInvolvedPilot($this->pilot);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
				$table = new KillListTable($list);
				$table->setDayBreak(false);

				$smarty->assign('splitter',$pagesplitter->generate());
				$smarty->assign('kills', $table->generate());
				return $smarty->fetch(get_tpl('detail_kl_kills'));

				break;
			case "losses":
				$list = new KillList();
				$list->setOrdered(true);
				$list->setPodsNoobships(config::get('podnoobs'));
				$list->addVictimPilot($this->pilot);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

				$table = new KillListTable($list);
				$table->setDayBreak(false);
				
				$smarty->assign('splitter',$pagesplitter->generate());
				$smarty->assign('losses', $table->generate());
				return $smarty->fetch(get_tpl('detail_kl_losses'));
				
				break;
			case "ships_weapons":
				$shiplist = new TopShipList();
				$shiplist->addInvolvedPilot($this->pilot);
				$shiplisttable = new TopShipListTable($shiplist);
				$smarty->assign('ships', $shiplisttable->generate());

				$weaponlist = new TopWeaponList();
				$weaponlist->addInvolvedPilot($this->pilot);
				$weaponlisttable = new TopWeaponListTable($weaponlist);

				$smarty->assign('weapons', $weaponlisttable->generate());
				return $smarty->fetch(get_tpl('detail_kl_ships_weapons'));

				break;
			default:
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
				$smarty->assign('kills', $table->generate());

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
				$smarty->assign('losses', $table->generate());
				return $smarty->fetch(get_tpl('detail_kl_default'));
				
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