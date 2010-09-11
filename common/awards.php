<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class pAwards extends pageAssembly
{
	//! Construct the Alliance Details object.

	/** Set up the basic variables of the class and add the functions to the
	 *  build queue.
	 */
	function __construct()
	{
		parent::__construct();

		$this->queue("start");
		$this->queue("awards");
	}
	//! Start constructing the page.

	/*! Prepare all the shared variables.
	 *
	 */
	function start()
	{
		$this->page = new Page("Awards");
		$this->page->addHeader('<meta name="robots" content="index, follow" />');


		if(isset($_GET['m'])) $this->month = intval($_GET['m']);
		else $this->month = kbdate('m') - 1;
		if(isset($_GET['y'])) $this->year = intval($_GET['y']);
		else $this->year = kbdate('Y');

		// Make sure month and year are set.
		if(!$this->year)
		{
			$this->year = kbdate('Y');
			$this->month = kbdate('m') - 1;
		}

		if ($this->month == 0)
		{
			$this->month = 12;
			$this->year = $this->year - 1;
		}

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

		if(isset($_GET['view'])) $this->view = $_GET['view'];
		else $this->view = false;

		$this->viewList = array();

		$this->menuOptions = array();

	}
	function awards()
	{
		global $smarty;
		$awardboxes = array();
		// top killers
		$tklist = new TopKillsList();
		$tklist->setMonth($this->month);
		$tklist->setYear($this->year);
		involved::load($tklist,'kill');

		$tklist->generate();
		$tkbox = new AwardBox($tklist, "Top killers", "kills", "kills", "eagle");
		$awardboxes[] = $tkbox->generate();
		// top scorers
		if (config::get('kill_points'))
		{
			$tklist = new TopScoreList();
			$tklist->setMonth($this->month);
			$tklist->setYear($this->year);
			involved::load($tklist,'kill');

			$tklist->generate();
			$tkbox = new AwardBox($tklist, "Top scorers", "points", "points", "redcross");
			$awardboxes[] = $tkbox->generate();
		}
		// top solo killers
		$tklist = new TopSoloKillerList();
		$tklist->setMonth($this->month);
		$tklist->setYear($this->year);
		involved::load($tklist,'kill');

		$tklist->generate();
		$tkbox = new AwardBox($tklist, "Top solokillers", "solo kills", "kills", "cross");
		$awardboxes[] = $tkbox->generate();
		// top damage dealers
		$tklist = new TopDamageDealerList();
		$tklist->setMonth($this->month);
		$tklist->setYear($this->year);
		involved::load($tklist,'kill');

		$tklist->generate();
		$tkbox = new AwardBox($tklist, "Top damagedealers", "kills w/ most damage", "kills", "wing1");
		$awardboxes[] = $tkbox->generate();

		// top final blows
		$tklist = new TopFinalBlowList();
		$tklist->setMonth($this->month);
		$tklist->setYear($this->year);
		involved::load($tklist,'kill');

		$tklist->generate();
		$tkbox = new AwardBox($tklist, "Top finalblows", "final blows", "kills", "skull");
		$awardboxes[] = $tkbox->generate();
		// top podkillers
		$tklist = new TopPodKillerList();
		$tklist->setMonth($this->month);
		$tklist->setYear($this->year);
		involved::load($tklist,'kill');

		$tklist->generate();
		$tkbox = new AwardBox($tklist, "Top podkillers", "podkills", "kills", "globe");
		$awardboxes[] = $tkbox->generate();
		// top griefers
		$tklist = new TopGrieferList();
		$tklist->setMonth($this->month);
		$tklist->setYear($this->year);
		involved::load($tklist,'kill');

		$tklist->generate();
		$tkbox = new AwardBox($tklist, "Top griefers", "carebear kills", "kills", "star");
		$awardboxes[] = $tkbox->generate();
		// top capital killers
		$tklist = new TopCapitalShipKillerList();
		$tklist->setMonth($this->month);
		$tklist->setYear($this->year);
		involved::load($tklist,'kill');

		$tklist->generate();
		$tkbox = new AwardBox($tklist, "Top ISK killers", "capital shipkills", "kills", "wing2");
		$awardboxes[] = $tkbox->generate();

		$smarty->assignByRef('awardboxes', $awardboxes);
		$smarty->assign('month', $this->monthname);
		$smarty->assign('year', $this->year);
		$smarty->assign('boxcount', count($awardboxes));

		return $smarty->fetch(get_tpl('awards'));
	}
	//! Reset the assembly object to prepare for creating the context.
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
	}

	//! Build the menu.

	//! Additional options that have been set are added to the menu.
	function menu()
	{
		$menubox = new Box("Menu");
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
	//! Set up the menu.

	//! Additional options that have been set are added to the menu.
	function menuSetup()
	{
		$this->addMenuItem("caption", "Navigation");
		$this->addMenuItem("link", "Previous month ", "?a=awards&amp;m=".$this->pmonth."&amp;y=".$this->pyear);
		if (! ($this->month == kbdate("m") - 1 && $this->year == kbdate("Y")))
			$this->addMenuItem("link", "Next month", "?a=awards&amp;m=".$this->nmonth."&amp;y=".$this->nyear);
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

$award = new pAwards();
event::call("award_assembling", $award);
$html = $award->assemble();
$award->page->setContent($html);

$award->context();
event::call("award_context_assembling", $award);
$context = $award->assemble();
$award->page->addContext($context);

$award->page->generate();






