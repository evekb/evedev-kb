<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/*
 * @package EDK
 */
class pContractDetail extends pageAssembly
{
	/** @var Page The Page object used to display this page. */
	public $page;
	/** @var integer The ID for this page's Contract. */
	public $ctr_id;
	
	/** @var string The selected view. */
	protected $view = null;
	/** @var Contract The Contract this page is built from. */
	protected $contract;

	/** @var array The list of views and their callbacks. */
	protected $viewList = array();
	/** @var array The list of menu options to display. */
	protected $menuOptions = array();

	/**
	 * Construct the Contract Details object.
	 * Set up the basic variables of the class and add the functions to the
	 *  build queue.
	 */
	function __construct()
	{
		parent::__construct();
		$this->queue("start");
		$this->queue("stats");
		$this->queue("comment");
		$this->queue("summaryTable");
		$this->queue("killList");

	}
	/**
	 *  Reset the assembly object to prepare for creating the context.
	 */
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
		$this->queue("topLists");
	}
	/**
	 * Start constructing the page.
	 * Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{
		$this->page = new Page();
		$this->ctr_id = (int)edkURI::getArg('ctr_id', 1);
		$this->view = preg_replace('/[^a-zA-Z0-9_-]/','', edkURI::getArg('view', 2));

		$this->viewList = array();

		$this->menuOptions = array();

		$this->contract = new Contract($this->ctr_id);
		if(!$this->contract->validate())
		{
			$this->page = new Page('Campaign details');
			$this->page->generate( 'No valid campaign ID specified');
			exit;
		}

		$title = 'Campaign details';

		// SetTitle will escape the characters so unescape first.
		$this->page->setTitle($title.' - '.html_entity_decode(
				$this->contract->getName(), ENT_QUOTES, 'UTF-8'));
		$this->page->addHeader('<meta name="robots" content="index, nofollow" />');

	}

	/**
	 *  Build the toplists to highlight efforts.
	 */
	function topLists()
	{
		$tklist = new TopList_ContractKills();
		$tklist->setContract(new Contract($this->ctr_id));
		involved::load($tklist,'kill');

		$tklist->generate();
		$campaign = "campaign";
		$tkbox = new AwardBox($tklist, "Top killers", "kills in this ".$campaign, "kills", "eagle");

		$html = $tkbox->generate();

		if (config::get('kill_points'))
		{
			$tklist = new TopList_ContractScore();
			$tklist->setContract(new Contract($this->ctr_id));
			involved::load($tklist,'kill');

			$tklist->generate();
			$tkbox = new AwardBox($tklist, "Top scorers", "points in this ".$campaign, "points", "redcross");
			$html .= $tkbox->generate();
		}
		return $html;
	}
	/**
	 *  Build the summary table showing all kills and losses for the contract.
	 */
	function summaryTable()
	{
		$klist = $this->contract->getKillList();
		$llist = $this->contract->getLossList();
		$killsummary = new KillSummaryTable($klist, $llist);
		if ($view == "") $killsummary->setFilter(false);

		return $killsummary->generate();
	}
	/**
	 *  Show the overall statistics of the contract.
	 */
	function stats()
	{
		global $smarty;

		if ($this->contract->getEndDate() == "")
			$smarty->assign('contract_enddate', "Active");
		else
			$smarty->assign('contract_enddate', substr($this->contract->getEndDate(), 0, 10));
		$smarty->assign('contract_startdate', substr($this->contract->getStartDate(), 0, 10));
		$smarty->assign('kill_count', $this->contract->getKills());
		$smarty->assign('loss_count', $this->contract->getLosses());
		$smarty->assign('kill_isk', round($this->contract->getKillISK()/1000000000, 2));
		$smarty->assign('loss_isk', round($this->contract->getLossISK()/1000000000, 2));
		$smarty->assign('contract_runtime', $this->contract->getRunTime());
		$smarty->assign('contract_efficiency', $this->contract->getEfficiency());

		return $smarty->fetch(get_tpl('cc_detail_stats'));
	}

	/**
	 *  Show the comment for this campaign, if there is one.
	 */
	function comment()
	{
		global $smarty;

		$html = "";

		if ($this->contract->getComment())
		{
			$smarty->assign("contract_comment",htmlentities(
					$this->contract->getComment(), ENT_QUOTES, 'UTF-8'));
			$html = $smarty->fetch(get_tpl("cc_detail_comment"));
		}

		return $html;
	}
	/**
	 *  Build the killlists that are needed for the options selected.
	 */
	function killList()
	{
		if(isset($this->viewList[$this->view])) {
			return call_user_func_array($this->viewList[$this->view],
					array(&$this));
		}

		$scl_id = (int)edkURI::getArg('scl_id');
		
		global $smarty;

		$html = '';
		$smarty->assign('view',$this->view);
		switch ($this->view)
		{
			case "":
				$qrylength=DBFactory::getDBQuery();
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
				while ($target = &$this->contract->getContractTarget())
				{
					$kl = &$target->getKillList();
					$ll = &$target->getLossList();
					$summary = new KillSummaryTable($kl, $ll);
					$summary->setVerbose(true);
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
				$smarty->assignByRef('targets', $targets);
				$html .= $smarty->fetch(get_tpl('cc_detail_lists'));
				break;
			case "recent_activity":
				$this->contract = new Contract($this->ctr_id);
				$klist = $this->contract->getKillList();
				$klist->setOrdered(true);
				if ($scl_id)
					$klist->addVictimShipClass($scl_id);
				else
					$klist->setPodsNoobShips(config::get('podnoobs'));

				$table = new KillListTable($klist);
				$table->setLimit(10);
				$table->setDayBreak(false);
				$smarty->assign('killtable', $table->generate());

				$llist = $this->contract->getLossList();
				$llist->setOrdered(true);
				if ($scl_id)
					$llist->addVictimShipClass($scl_id);
				else
					$llist->setPodsNoobShips(config::get('podnoobs'));

				$table = new KillListTable($llist);
				$table->setLimit(10);
				$table->setDayBreak(false);
				$smarty->assign('losstable', $table->generate());
				$html .= $smarty->fetch(get_tpl('cc_detail_lists'));
				break;
			case "kills":
				$this->contract = new Contract($this->ctr_id);
				$list = $this->contract->getKillList();
				$list->setOrdered(true);
				if ($scl_id)
					$list->addVictimShipClass($scl_id);
				else
					$list->setPodsNoobShips(config::get('podnoobs'));

				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$smarty->assign('killtable', $table->generate());
				$smarty->assign('splitter', $pagesplitter->generate());
				$html .= $smarty->fetch(get_tpl('cc_detail_lists'));
				break;
			case "losses":
				$this->contract = new Contract($this->ctr_id);
				$llist = $this->contract->getLossList();
				$llist->setOrdered(true);
				if ($scl_id)
					$llist->addVictimShipClass($scl_id);
				else
					$llist->setPodsNoobShips(config::get('podnoobs'));

				$llist->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($llist->getCount(), config::get('killcount'));
				$table = new KillListTable($llist);
				$table->setDayBreak(false);
				$smarty->assign('losstable', $table->generate());
				$smarty->assign('splitter', $pagesplitter->generate());
				$html .= $smarty->fetch(get_tpl('cc_detail_lists'));
				break;
		}
		return $html;
	}
	/**
	 * Set up the menu.
	 *
	 *  Prepare all the base menu options.
	 */
	function menuSetup()
	{
		$this->addMenuItem("caption","Overview");
		$this->addMenuItem("link","Target overview", KB_HOST."/?a=cc_detail&amp;ctr_id=".$this->ctr_id);
		$this->addMenuItem("caption","Kills &amp; losses");
		$this->addMenuItem("link","Recent activity", KB_HOST."/?a=cc_detail&amp;ctr_id=".$this->ctr_id."&amp;view=recent_activity");
		$this->addMenuItem("link","All kills", KB_HOST."/?a=cc_detail&amp;ctr_id=".$this->ctr_id."&amp;view=kills");
		$this->addMenuItem("link","All losses", KB_HOST."/?a=cc_detail&amp;ctr_id=".$this->ctr_id."&amp;view=losses");
		return "";
	}
	/**
	 * Build the menu.
	 *
	 *  Add all preset options to the menu.
	 */
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
	/**
	 * Add an item to the menu in standard box format.
	 *
	 *  Only links need all 3 attributes
	 * @param string $type Types can be caption, img, link, points.
	 * @param string $name The name to display.
	 * @param string $url Only needed for URLs.
	 */
	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = array($type, $name, $url);
	}

	/**
	 * Add a type of view to the options.
	 *
	 * @param string $view The name of the view to recognise.
	 * @param mixed $callback The method to call when this view is used.
	 */
	function addView($view, $callback)
	{
		$this->viewList[$view] = $callback;
	}

	/**
	 * Return the set view.
	 * @return string
	 */
	function getView()
	{
		return $this->view;
	}
}

$contractDetail = new pContractDetail();
event::call("contractDetail_assembling", $contractDetail);
$html = $contractDetail->assemble();
$contractDetail->page->setContent($html);

$contractDetail->context();
event::call("contractDetail_context_assembling", $contractDetail);
$context = $contractDetail->assemble();
$contractDetail->page->addContext($context);

$contractDetail->page->generate();
