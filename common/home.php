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
class pHome extends pageAssembly
{
	/** @var array */
	private $pargs = array();
	/** @var array */
	private $nargs = array();
	/** @var array */
	private $cargs = array();
	/** @var array */
	protected $menuOptions = array();
	/** @var array */
	protected $viewList = array();
	/** @var integer */
	protected $day;
	/** @var integer */
	protected $week;
	/** @var integer */
	protected $month;
	/** @var integer */
	protected $year;

	/** @var string */
	protected $view;
	/** @var integer */
	protected $scl_id;
	/** @var boolean */
	protected $currentTime;
	/** @var Page */
	public $page;
	/** @var boolean */
	protected $showcombined;
	/** @var boolean */
	private $dateSet = false;

	function __construct()
	{
		parent::__construct();
		$this->queue('start');
		$this->queue('summaryTable');
		$this->queue('campaigns');
		// Legacy support for mods placing themselves after it.
		$this->queue('contracts');
		$this->queue('killList');
	}

	function start()
	{
		$this->page = new Page();
		$this->page->addHeader(
				"<link rel='canonical' href='".edkURI::page()."' />");
		$this->view = preg_replace('/[^a-zA-Z0-9_-]/', '',
				edkURI::getArg('view', 1));
		$period = edkURI::getArg('period');

		$day = $week = $month = $year = 0;
		// First argument is either the view or the year
		if (is_numeric($this->view) || !$this->view 
				|| $this->view == 'day'
				|| $this->view == 'week'
				|| $this->view == 'month') {
			$this->view = '';
			$datestart = 1;
		} else {
			$datestart = 2;
		}

		$year = edkURI::getArg('y', $datestart);

		if((int)$year && !$period) {
			$year = (int)$year;
			$this->dateSet = true;
			if (config::get('show_monthly')) {
				$month = (int) edkURI::getArg('m', $datestart + 1);
			} else {
				$week = (int) edkURI::getArg('w', $datestart + 1);
			}
		} else if ($year || $period) {
			if (!$period) {
				$period = $year;
			}
			$datestart++;
			switch($period) {
				case "month":
					$year = (int) edkURI::getArg('y', $datestart);
					if((int)$year) {
						$month = (int) edkURI::getArg('m', $datestart + 1);
						$this->dateSet = true;
					}
					break;
				case "week":
					$year = (int) edkURI::getArg('y', $datestart);
					if((int)$year) {
						$week = (int) edkURI::getArg('w', $datestart + 1);
						$this->dateSet = true;
					}
					break;
				case "day":
					$year = (int) edkURI::getArg('y', $datestart);
					if((int)$year) {
						$month = (int) edkURI::getArg('m', $datestart + 1);
						if((int)$month) {
							$day = (int) edkURI::getArg('d', $datestart + 2);
							$this->dateSet = true;
						}
					}
					break;
			}
		}

		$this->setTime($week, $year, $month, $day);

		if (edkURI::getArg('scl_id') === false
				|| edkURI::getArg('y', 1) === false) {
			$this->page->addHeader(
					'<meta name="robots" content="index, follow" />');
		}

		$this->scl_id = (int) edkURI::getArg('scl_id');

		$this->showcombined = config::get('show_comb_home')
				&& (count(config::get('cfg_allianceid'))
						|| count(config::get('cfg_corpid'))
						|| count(config::get('cfg_pilotid')));

		if ($this->view == 'kills') {
			$this->page->setTitle('Kills - '.$this->getCurrentPeriod());
		} else if ($this->view == 'losses') {
			$this->page->setTitle('Losses - '.$this->getCurrentPeriod());
		} else {
			$this->page->setTitle($this->getCurrentPeriod());
		}
	}

	/**
	 * Check if summary tables are enabled and if so return a table for this
	 * week.
	 *
	 * @return string HTML string for a summary table.
	 */
	function summaryTable()
	{
		// Display the summary table.
		if (config::get('summarytable')) {
			if (config::get('public_summarytable')) {
				//$kslist = new KillList();
				$summarytable = new KillSummaryTablePublic();
				$this->loadTime($summarytable);
				involved::load($summarytable, 'kill');
				//$summarytable = new KillSummaryTablePublic($kslist);
			} else {
				$summarytable = new KillSummaryTable();
				$this->loadTime($summarytable);
				involved::load($summarytable, 'kill');
			}
			return $summarytable->generate();
		}
	}

	/**
	 * Returns HTML string for campaigns, if any.
	 * @return string HTML string for campaigns, if any
	 */
	function campaigns()
	{
		// Display campaigns, if any.
		if (Killboard::hasCampaigns(true) &&
				$this->isCurrentPeriod()) {
			$html = "<div class='kb-campaigns-header'>Active campaigns</div>";
			$list = new ContractList();
			$list->setActive("yes");
			$list->setCampaigns(true);
			$table = new ContractListTable($list);
			$html .= $table->generate();
			return $html;
		}
	}

	/**
	 * Return the main killlists
	 * @global Smarty $smarty
	 * @return string HTML string for killlist tables
	 */
	function killList()
	{
		if (isset($this->viewList[$this->view])) {
			return call_user_func_array($this->viewList[$this->view],
					array(&$this));
		}

		global $smarty;

		$klist = new KillList();
		$klist->setOrdered(true);
		// We'll be needing comment counts so set the killlist to retrieve them
		if (config::get('comments_count')) {
			$klist->setCountComments(true);
		}
		// We'll be needing involved counts so set the killlist to retrieve them
		if (config::get('killlist_involved')) {
			$klist->setCountInvolved(true);
		}

		// Select between kills, losses or both.
		if ($this->view == 'combined'
				|| ($this->view == '' && $this->showcombined)) {
			involved::load($klist, 'combined');
		} else if ($this->view == 'losses') {
			involved::load($klist, 'loss');
		} else {
			involved::load($klist, 'kill');
		}

		if ($this->scl_id) {
			$klist->addVictimShipClass($this->scl_id);
		} else {
			$klist->setPodsNoobShips(config::get('podnoobs'));
		}

		// If no week is set then show the most recent kills. Otherwise
		// show all kills for the week using the page splitter.
		if (config::get("cfg_fillhome") && !$this->dateSet) {
			$klist->setLimit(config::get('killcount'));
			$table = new KillListTable($klist);
			if ($this->showcombined) $table->setCombined(true);
			$table->setLimit(config::get('killcount'));
			$html = $table->generate();
		} else {
			$this->loadTime($klist);
			//$klist->setWeek($this->week);
			//$klist->setYear($this->year);
			$klist->setPageSplit(config::get('killcount'));
			$pagesplitter = new PageSplitter($klist->getCount(),
					config::get('killcount'));
			$table = new KillListTable($klist);
			if ($this->showcombined) $table->setCombined(true);
			$pagesplit = $pagesplitter->generate();
			$html = $pagesplit.$table->generate().$pagesplit;
		}
		return $html;
	}

	/**
	 * Set up the menu.
	 *
	 * Prepare all the base menu options.
	 */
	function menuSetup()
	{
		// Display the menu for previous and next weeks.
		$this->addMenuItem("caption", "Navigation");

		$view = $sclarg = null;
		if ($this->view) {
			$view = array('view', $this->view, true);
		}
		if ($this->scl_id) {
			$sclarg = array('scl_id', $this->scl_id, false);
		}
		$previous = $this->pargs;
		$next = $this->nargs;
		if ($view) {
			array_unshift($previous, $view);
			array_unshift($next, $view);
		}

		$killLink = $this->cargs;
		array_unshift($killLink, array('view', 'kills', true));
		$lossLink = $this->cargs;
		array_unshift($lossLink, array('view', 'losses', true));
		$combinedLink = $this->cargs;
		if ($sclarg) {
			$previous[] = $sclarg;
			$next[] = $sclarg;
			$killLink[] = $sclarg;
			$lossLink[] = $sclarg;
			$combinedLink[] = $sclarg;
		}
		$this->addMenuItem("link", "Previous ".$this->getPeriodName(),
				edkURI::build($previous));
		if (!$this->isCurrentPeriod()) {
			$this->addMenuItem("link", "Next ".$this->getPeriodName(),
					edkURI::build($next));
		}
		$this->addMenuItem("link", "Kills", edkURI::build($killLink));
		$this->addMenuItem("link", "Losses", edkURI::build($lossLink));
		if (config::get('show_comb_home')) {
			$this->addMenuItem("link", $weektext."All Kills",
					edkURI::build($combinedLink));
		}
		return "";
	}

	/**
	 * Build the menu.
	 *
	 * Add all preset options to the menu.
	 *
	 * @return string
	 */
	function menu()
	{
		$menubox = new box("Menu");
		$menubox->setIcon("menu-item.gif");
		foreach ($this->menuOptions as $options) {
			if (isset($options[2])) {
				$menubox->addOption($options[0], $options[1], $options[2]);
			} else {
				$menubox->addOption($options[0], $options[1]);
			}
		}
		return $menubox->generate();
	}

	/**
	 *
	 * @return string HTML string for a clock
	 */
	function clock()
	{
		// Show the Eve time.
		if (config::get('show_clock')) {
			$this->page->addOnLoad("setInterval('updateClock()', 60000 )");
			$clock = new Clock();
			return $clock->generate();
		}
	}

	/**
	 *
	 * @return string HTML string for toplists
	 */
	function topLists()
	{
		// Display the top pilot lists.
		if ($this->view != 'losses') {
			$tklist = new TopList_Kills();
			$this->loadTime($tklist);
			involved::load($tklist, 'kill');

			$tklist->generate();
			$tkbox = new AwardBox($tklist, "Top killers", "kills in "
					.$this->getCurrentPeriod(), "kills", "eagle");
			$html = $tkbox->generate();

			$tklist = new TopList_Score();
			$this->loadTime($tklist);
			involved::load($tklist, 'kill');

			$tklist->generate();
			$tkbox = new AwardBox($tklist, "Top scorers", "points in "
					.$this->getCurrentPeriod(), "points", "redcross");
			$html .= $tkbox->generate();
		} else {
			$tllist = new TopList_Losses();
			$this->loadTime($tllist);
			involved::load($tllist, 'loss');

			$tllist->generate();
			$tlbox = new AwardBox($tllist, "Top losers", "losses in "
					.$this->getCurrentPeriod(), "losses", "moon");
			$html = $tlbox->generate();
		}
		return $html;
	}

	/**
	 * Set up to generate the context.
	 */
	function context()
	{
		parent::__construct();
		$this->queue('menuSetup');
		$this->queue('menu');
		$this->queue('clock');
		$this->queue('topLists');
	}

	/**
	 * Return the set day.
	 * @return integer
	 */
	function getDay()
	{
		return $this->day;
	}

	/**
	 * Return the set week.
	 * @return integer
	 */
	function getWeek()
	{
		return $this->week;
	}

	/**
	 * Return the set month.
	 * @return integer
	 */
	function getMonth()
	{
		return $this->month;
	}

	/**
	 * Return the set year.
	 * @return integer
	 */
	function getYear()
	{
		return $this->year;
	}

	/**
	 * Return the requested view.
	 * @return string
	 */
	function getView()
	{
		return $this->view;
	}

	/**
	 *
	 * @param integer $day
	 * @param integer $month
	 * @param integer $year
	 */
	function setDay($day, $month, $year)
	{
		if ($month < 1 || $month > 12 || $year < 2000) {
			$month = kbdate('m');
			$year = getYear();
		}
		$this->month = $month;
		if ($this->month < 10) {
			$this->month = '0'.$this->month;
		}
		$this->year = $year;
		$this->day = $day;

		$cdate = strtotime("{$this->year}-{$this->month}-{$this->day} 00:01"
				." UTC");
		$pdate = $cdate - 24 * 60 * 60;
		$ndate = $cdate + 24 * 60 * 60;

		$pyear = gmdate("Y", $pdate);
		$pmonth = gmdate("m", $pdate);
		$pday = gmdate("d", $pdate);

		$nyear = gmdate("Y", $ndate);
		$nmonth = gmdate("m", $ndate);
		$nday = gmdate("d", $ndate);

		$cyear = gmdate("Y", $cdate);
		$cmonth = gmdate("m", $cdate);
		$cday = gmdate("d", $cdate);


		$this->periodName = 'Day';
		$this->period = date('d F, Y', mktime(0, 0, 0,
				$this->month, $this->day, $this->year));
		$this->currentTime =
				($this->day == kbdate('d')
				&& $this->month == kbdate('m')
				&& $this->year == kbdate('Y'));

		$this->pargs[] = array('period', 'day', true);
		$this->pargs[] = array('y', $pyear, true);
		$this->pargs[] = array('m', $pmonth, true);
		$this->pargs[] = array('d', $pday, true);

		$this->nargs[] = array('period', 'day', true);
		$this->nargs[] = array('y', $nyear, true);
		$this->nargs[] = array('m', $nmonth, true);
		$this->nargs[] = array('d', $nday, true);

		$this->cargs[] = array('period', 'day', true);
		$this->cargs[] = array('y', $this->year, true);
		$this->cargs[] = array('m', $this->month, true);
		$this->cargs[] = array('d', $cday, true);
	}
	/**
	 *
	 * @param integer $week
	 * @param integer $year
	 */
	function setWeek($week, $year)
	{
		$week = (int)$week;
		$year = (int)$year;
		// If a valid week and year are given then show that week.
		if (($year) < 2000 || ($week) < 1 || ($week) > getWeeks($year)) {
			$week = kbdate('W');
			$year = kbdate('o');
		}

		$this->week = $week;
		if ($this->week < 10) $this->week = '0'.$this->week;
		$this->year = $year;

		if ($this->week == 1) {
			$pyear = $this->year - 1;
			$pweek = getWeeks($pyear);
		} else {
			$pyear = $this->year;
			$pweek = $this->week - 1;
		}
		if ($this->week == getWeeks($this->year)) {
			$nweek = 1;
			$nyear = $this->year + 1;
		} else {
			$nweek = $this->week + 1;
			$nyear = $this->year;
		}
		$this->periodName = 'Week';
		$this->period = 'Week '.$this->week.', '.$this->year;
		$this->currentTime =
				($this->week == kbdate('W') && $this->year == kbdate('o'));

		$this->pargs = $this->nargs = $this->cargs = array();
		if (config::get('show_monthly')) {
			$this->pargs[] = array('period', 'week', true);
			$this->nargs[] = array('period', 'week', true);
			$this->cargs[] = array('period', 'week', true);
		}
		$this->pargs[] = array('y', $pyear, true);
		$this->pargs[] = array('w', $pweek, true);

		$this->nargs[] = array('y', $nyear, true);
		$this->nargs[] = array('w', $nweek, true);

		$this->cargs[] = array('y', $this->year, true);
		$this->cargs[] = array('w', $this->week, true);
	}

	/**
	 *
	 * @param integer $month
	 * @param integer $year
	 */
	function setMonth($month, $year)
	{
		$month = (int) $month;
		$year = (int) $year;
		if ($month < 1 || $month > 12 || $year < 2000) {
			$month = kbdate('m');
			$year = getYear();
		}
		$this->month = $month;
		if ($this->month < 10) {
			$this->month = '0'.$this->month;
		}
		$this->year = $year;
		if ($month == 1) {
			$pyear = $year - 1;
			$pmonth = 12;
		} else {
			$pyear = $year;
			$pmonth = $month - 1;
		}
		if ($this->month == 12) {
			$nmonth = 1;
			$nyear = $this->year + 1;
		} else {
			$nmonth = $this->month + 1;
			$nyear = $this->year;
		}
		$this->periodName = 'Month';
		$this->period = date('F, Y', mktime(0, 0, 0,
				$this->month, 1, $this->year));
		$this->currentTime =
				($this->month == kbdate('m') && $this->year == kbdate('Y'));

		if (!config::get('show_monthly')) {
			$this->pargs[] = array('period', 'month', true);
			$this->nargs[] = array('period', 'month', true);
			$this->cargs[] = array('period', 'month', true);
		}
		$this->pargs[] = array('y', $pyear, true);
		$this->pargs[] = array('m', $pmonth, true);

		$this->nargs[] = array('y', $nyear, true);
		$this->nargs[] = array('m', $nmonth, true);

		$this->cargs[] = array('y', $this->year, true);
		$this->cargs[] = array('m', $this->month, true);
	}

	/**
	 * Returns true if the board is showing the current time period.
	 * @return boolean true if the board is showing the current time period,
	 * false otherwise.
	 */
	function isCurrentPeriod()
	{
		return $this->currentTime;
	}

	/**
	 * Return the text name of the current time period type.
	 *
	 * @return string
	 */
	function getPeriodName()
	{
		return $this->periodName;
	}

	/**
	 * Return the text name of the current time period.
	 *
	 * @return string
	 */
	function getCurrentPeriod()
	{
		return $this->period;
	}

	/**
	 * Return an array of arguments to generate current period links.
	 *
	 * @return string
	 */
	function getCurrentPeriodArgs()
	{
		return $this->cargs;
	}

	/**
	 * Return an array of arguments to generate next period links.
	 *
	 * @return string
	 */
	function getNextPeriodArgs()
	{
		return $this->nargs;
	}

	/**
	 * Return an array of arguments to generate previous period links.
	 *
	 * @return string
	 */
	function getPreviousPeriodArgs()
	{
		return $this->pargs;
	}

	/**
	 * Set start and end dates on any object passed in.
	 * @param mixed $object Accepts any object with setStartDate & setEndDate
	 */
	function loadTime(&$object)
	{
		if ($this->day) {
			$object->setStartDate("{$this->year}-{$this->month}-{$this->day}"
					." 00:00");
			$object->setEndDate("{$this->year}-{$this->month}-{$this->day}"
					." 23:59");
		} else if ($this->week) {
			$object->setWeek($this->week);
			$object->setYear($this->year);
		} elseif ($this->month) {
			$start = makeStartDate($this->week, $this->year, $this->month);
			$end = makeEndDate($this->week, $this->year, $this->month);
			$object->setStartDate(gmdate('Y-m-d H:i', $start));
			$object->setEndDate(gmdate('Y-m-d H:i', $end));
		}
	}

	/**
	 * Set the current time to use for this page.
	 *
	 * @param integer $week
	 * @param integer $year
	 * @param integer $month
	 */
	function setTime($week = 0, $year = 0, $month = 0, $day = 0)
	{
		if ($day && $month && $year) {
			$this->setDay($day, $month, $year);
			$this->week = 0;
		} else if ($week && $year) {
			$this->setWeek($week, $year);
			$this->month = 0;
		} elseif ($month && $year) {
			$this->setMonth($month, $year);
			$this->week = 0;
		} else {
			if (config::get('show_monthly')) {
				$this->setMonth(kbdate('m'), kbdate('Y'));
			} else {
				$this->setWeek(kbdate('W'), kbdate('o'));
			}
		}
	}

	/**
	 * Add an item to the menu in standard box format.
	 *
	 * Only links need all 3 attributes
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
}

$pageAssembly = new pHome();
event::call("home_assembling", $pageAssembly);
$html = $pageAssembly->assemble();
$pageAssembly->page->setContent($html);

$pageAssembly->context(); //This resets the queue and queues context items.
event::call("home_context_assembling", $pageAssembly);
$contextHTML = $pageAssembly->assemble();
$pageAssembly->page->addContext($contextHTML);


$pageAssembly->page->generate();
