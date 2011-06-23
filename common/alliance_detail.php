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
class pAllianceDetail extends pageAssembly
{
	public $page = null;
	public $scl_id = 0;
	public $all_id = 0;
	public $all_external_id = 0;
	public $alliance = null;
	private $view = null;
	private $viewList = array();
	private $menuOptions = array();
	private $allianceCorps = array();
	private $month = '';
	private $year = '';
	private $nmonth = '';
	private $nyear = '';
	private $pmonth = '';
	private $pyear = '';
	private $kill_summary = null;

	/**
	 * Construct the Alliance Details object.
	 *
	 * Set up the basic variables of the class and add the functions to the
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
	/**
	 * Start constructing the page.
	 * Prepare all the shared variables such as dates and check alliance ID.
	 */
	function start()
	{
		$this->page = new Page();

		$this->scl_id = intval($_GET['scl_id']);
		$this->all_id = intval($_GET['all_id']);
		if(isset($_GET['all_external_id'])) $this->all_external_id = intval($_GET['all_external_id']);
		elseif(isset($_GET['all_ext_id'])) $this->all_external_id = intval($_GET['all_ext_id']);
		else $this->all_external_id = 0;
		$this->view = $_GET['view'];
		if($this->view) $this->page->addHeader('<meta name="robots" content="noindex, nofollow" />');

		if (!$this->all_id && !$this->all_external_id)
		{
			$html = 'No valid alliance id specified.';
			$this->page->setContent($html);
			$this->page->generate();
			exit;
		}

		if(!$this->all_id && $this->all_external_id)
		{
			$this->alliance = new Alliance($this->all_external_id, true);
			$this->all_id = $this->alliance->getID();
			if(!$this->all_id)
			{
				echo 'No valid alliance id specified.';
				exit;
			}

		}
		else
		{
			$this->alliance = new Alliance($this->all_id);
			$this->all_external_id = $this->alliance->getExternalID();
		}

		if($this->all_external_id) $this->page->addHeader("<link rel='canonical' href='".KB_HOST."/?a=alliance_detail&amp;all_ext_id=". $this->all_external_id."' />");
		else $this->page->addHeader("<link rel='canonical' href='".KB_HOST."/?a=alliance_detail&amp;all_id=".$this->all_id."' />");

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
		global $smarty;
		$smarty->assign('monthname', $this->monthname);
		$smarty->assign('year', $this->year);
		$smarty->assign('pmonth', $this->pmonth);
		$smarty->assign('pyear', $this->pyear);
		$smarty->assign('nmonth', $this->nmonth);
		$smarty->assign('nyear', $this->nyear);
		if($this->alliance->isFaction()) $this->page->setTitle('Faction details - '.$this->alliance->getName());
		else $this->page->setTitle('Alliance details - '.$this->alliance->getName());

		$smarty->assign('all_name', $this->alliance->getName());
		$smarty->assign('all_id', $this->alliance->getID());
	}
	/**
	 *  Set up the stats needed for stats and summaryTable functions
	 *
	 * @return string
	 */
	function statSetup()
	{
		$this->kill_summary = new KillSummaryTable();
		$this->kill_summary->addInvolvedAlliance($this->all_id);
		$this->kill_summary->generate();
		return "";
	}

	/**
	 *  Show the overall statistics for this alliance.
	 *
	 * @global Smarty $smarty
	 * @return string
	 */
	function stats()
	{
		global $smarty;
		$tempMyCorp = new Corporation();

		$myAlliAPI = new API_Alliance();
		$myAlliAPI->fetchalliances();

		// Use alliance ID if we have it
		if($this->alliance->getExternalID()) $myAlliance = $myAlliAPI->LocateAllianceID( $this->alliance->getExternalID() );
		else $myAlliance = $myAlliAPI->LocateAlliance( $this->alliance->getName() );

		if($this->alliance->isFaction()) $this->page->setTitle('Faction details - '.$this->alliance->getName() . " [" . $myAlliance["shortName"] . "]");
		else $this->page->setTitle('Alliance details - '.$this->alliance->getName() . " [" . $myAlliance["shortName"] . "]");

		$myCorpAPI = new API_CorporationSheet();

		if ($myAlliance)
		{
			foreach ( (array)$myAlliance["memberCorps"] as $tempcorp)
			{
				$myCorpAPI->setCorpID($tempcorp["corporationID"]);
				$result .= $myCorpAPI->fetchXML();

				if ($tempcorp["corporationID"] == $myAlliance["executorCorpID"])
				{
					$myAlliance["executorCorpName"] = $myCorpAPI->getCorporationName();
					$ExecutorCorp = $myCorpAPI->getCorporationName();
					$ExecutorCorpID = $myCorpAPI->getCorporationID();
				}
				// Build Data array
				$membercorp["corpExternalID"] = $myCorpAPI->getCorporationID();
				$membercorp["corpName"] = $myCorpAPI->getCorporationName();
				$membercorp["ticker"] = $myCorpAPI->getTicker();
				$membercorp["members"] = $myCorpAPI->getMemberCount();
				$membercorp["joinDate"] = $tempcorp["startDate"];
				$membercorp["taxRate"] = $myCorpAPI->getTaxRate() . "%";
				$membercorp["url"] = $myCorpAPI->getUrl();

				$this->allianceCorps[] = $membercorp;

				// Check if corp is known to EDK DB, if not, add it.
				$tempMyCorp->Corporation();
				$tempMyCorp->lookup($myCorpAPI->getCorporationName());
				if ($tempMyCorp->getID() == 0)
				{
					$tempMyCorp->add($myCorpAPI->getCorporationName(), $this->alliance , substr($tempcorp["startDate"], 0, 16),$myCorpAPI->getCorporationID());
				}

				$membercorp = array();
				unset($membercorp);
			}

			if(!isset($this->kill_summary))
			{
				$this->kill_summary = new KillSummaryTable();
				$this->kill_summary->addInvolvedAlliance($this->alliance);
				$this->kill_summary->generate();
			}
			$smarty->assign('myAlliance', $myAlliance);
			$smarty->assign('memberCorpCount', count($myAlliance["memberCorps"]));

			if ($this->kill_summary->getTotalKillISK())
			{
				 $efficiency = round($this->kill_summary->getTotalKillISK() / ($this->kill_summary->getTotalKillISK() + $this->kill_summary->getTotalLossISK()) * 100, 2);
			}
			else
			{
				$efficiency = 0;
			}

		}
		// The summary table is also used by the stats. Whichever is called
		// first generates the table.
		$smarty->assign('all_img', $this->alliance->getPortraitURL());
		$smarty->assign('totalkills', $this->kill_summary->getTotalKills());
		$smarty->assign('totallosses', $this->kill_summary->getTotalLosses());
		$smarty->assign('totalkisk', round($this->kill_summary->getTotalKillISK()/1000000000, 2));
		$smarty->assign('totallisk', round($this->kill_summary->getTotalLossISK()/1000000000, 2));
		if ($this->kill_summary->getTotalKillISK())
			$smarty->assign('efficiency', round($this->kill_summary->getTotalKillISK() / ($this->kill_summary->getTotalKillISK() + $this->kill_summary->getTotalLossISK()) * 100, 2));
		else
			$smarty->assign('efficiency', '0');
		return $smarty->fetch(get_tpl('alliance_detail_stats'));
	}

	/**
	 * Show the list of corps.
	 *
	 * @global Smarty $smarty
	 * @return string
	 */
	function corpList()
	{
		global $smarty;
		foreach ( $this->allianceCorps as &$tempcorp )
		{
			$tempcorp['url'] = htmlspecialchars(html_entity_decode(urldecode($tempcorp['url'])));
			if($tempcorp['url'] == 'http://') $tempcorp['url'] = '';
			$tempcorp['corpName'] = preg_replace('/(\w{30})\w+/', '$1...', $tempcorp['corpName']);
		}
		$smarty->assignByRef('corps', $this->allianceCorps);
		return $smarty->fetch(get_tpl('alliance_detail_corps'));
	}

	/**
	 *  Display the summary table showing all kills and losses for this alliance.
	 *
	 * @return string
	 */
	function summaryTable()
	{
		if($this->view != '' && $this->view != 'recent_activity'
			&& $this->view != 'kills' && $this->view != 'losses') return '';
		// The summary table is also used by the stats. Whichever is called
		// first generates the table.
		return $this->kill_summary->generate();
	}

	/**
	 *  Build the killlists that are needed for the options selected.
	 *
	 * @global Smarty $smarty
	 * @return string
	 */
	function killList()
	{
		global $smarty;
		if($this->view == '')
			$smarty->assign('view', 'recent_activity');
		else
			$smarty->assign('view', $this->view);

		if(isset($this->viewList[$this->view])) return call_user_func_array($this->viewList[$this->view], array(&$this));

		switch ($this->view)
		{
			case "":
				$list = new KillList();
				$list->setOrdered(true);
				if (config::get('comments_count')) $list->setCountComments(true);
				if (config::get('killlist_involved')) $list->setCountInvolved(true);
				$list->setLimit(10);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->addInvolvedAlliance($this->alliance);
				if ($this->scl_id)
					$list->addVictimShipClass($this->scl_id);
				//$list->setStartDate(date('Y-m-d H:i',strtotime('- 30 days')));
				$ktab = new KillListTable($list);
				$ktab->setLimit(10);
				$ktab->setDayBreak(false);
				$smarty->assign('kills', $ktab->generate());

				$list = new KillList();
				$list->setOrdered(true);
				if (config::get('comments_count')) $list->setCountComments(true);
				if (config::get('killlist_involved')) $list->setCountInvolved(true);
				$list->setLimit(10);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->addVictimAlliance($this->alliance);
				if ($this->scl_id)
					$list->addVictimShipClass($this->scl_id);
				//$list->setStartDate(date('Y-m-d H:i',strtotime('- 30 days')));

				$ltab = new KillListTable($list);
				$ltab->setLimit(10);
				$ltab->setDayBreak(false);
				$smarty->assign('losses', $ltab->generate());

				return $smarty->fetch(get_tpl('detail_kl_default'));

				break;
			case "kills":
				$list = new KillList();
				$list->setOrdered(true);
				$list->addInvolvedAlliance($this->alliance);
				if ($this->scl_id)
					$list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$smarty->assign('kills', $table->generate());
				$smarty->assign('splitter', $pagesplitter->generate());

				return $smarty->fetch(get_tpl('detail_kl_kills'));

				break;
			case "losses":
				$list = new KillList();
				$list->setOrdered(true);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->addVictimAlliance($this->alliance);
				if ($this->scl_id)
					$list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$smarty->assign('losses', $table->generate());
				$smarty->assign('splitter', $pagesplitter->generate());

				return $smarty->fetch(get_tpl('detail_kl_losses'));

				break;
			case "corp_kills":
				$smarty->assign('title', 'Top Killers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('all_id', $this->all_id);
				$smarty->assign('url_previous', "?a=alliance_detail&amp;view=corp_kills&amp;all_id=$this->all_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=alliance_detail&amp;view=corp_kills&amp;all_id=$this->all_id&amp;m=$this->nmonth&amp;y=$this->nyear");

				$list = new TopList_CorpKills();
				$list->addInvolvedAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Corp($list, "Kills");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_CorpKills();
				$list->addInvolvedAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Corp($list, "Kills");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case "corp_kills_class":
				$smarty->assign('title', 'Destroyed Ships');

				// Get all ShipClasses
				$sql = "select scl_id, scl_class from kb3_ship_classes
					where scl_class not in ('Drone','Unknown') order by scl_class";

				$qry = DBFactory::getDBQuery();
				$qry->execute($sql);
				while ($row = $qry->getRow())
				{
					$shipclass[] = new Shipclass($row['scl_id']);
				}
				$newrow = true;
				$ships = array();

				foreach ($shipclass as $shp)
				{
					$list = new TopList_CorpKills();
					$list->addInvolvedAlliance($this->alliance);
					$list->addVictimShipClass($shp);
					$table = new TopTable_Corp($list, "Kills");
					$content = $table->generate();
					$ships[] = array('name'=>$shp->getName(), 'table'=>$content);
				}

				$smarty->assignByRef('ships', $ships);
				return $smarty->fetch(get_tpl('detail_kl_ships'));

				break;
			case "kills_class":
				$smarty->assign('title', 'Destroyed Ships');

				// Get all ShipClasses
				$sql = "select scl_id, scl_class from kb3_ship_classes
					where scl_class not in ('Drone','Unknown') order by scl_class";

				$qry = DBFactory::getDBQuery();
				$qry->execute($sql);
				while ($row = $qry->getRow())
				{
					$shipclass[] = new Shipclass($row['scl_id']);
				}
				foreach ($shipclass as $shp)
				{
					$list = new TopList_Kills();
					$list->addInvolvedAlliance($this->alliance);
					$list->addVictimShipClass($shp);
					$table = new TopTable_Pilot($list, "Kills");
					$content = $table->generate();
					$ships[] = array('name'=>$shp->getName(), 'table'=>$content);
				}
				$smarty->assignByRef('ships', $ships);
				return $smarty->fetch(get_tpl('detail_kl_ships'));

				break;
			case "corp_losses_class":
				$smarty->assign('title', 'Lost Ships');

				// Get all ShipClasses
				$sql = "select scl_id, scl_class from kb3_ship_classes
					where scl_class not in ('Drone','Unknown') order by scl_class";

				$qry = DBFactory::getDBQuery();
				$qry->execute($sql);
				while ($row = $qry->getRow())
				{
					$shipclass[] = new Shipclass($row['scl_id']);
				}
				foreach ($shipclass as $shp)
				{
					$list = new TopList_CorpLosses();
						$list->addVictimAlliance($this->alliance);
					$list->addVictimShipClass($shp);
					$table = new TopTable_Corp($list, "Losses");
					$content = $table->generate();
					$ships[] = array('name'=>$shp->getName(), 'table'=>$content);
				}
				$smarty->assignByRef('ships', $ships);
				return $smarty->fetch(get_tpl('detail_kl_ships'));

				break;
			case "losses_class":
				$smarty->assign('title', 'Lost Ships');


					// Get all ShipClasses
				$sql = "select scl_id, scl_class from kb3_ship_classes
					where scl_class not in ('Drone','Unknown') order by scl_class";

				$qry = DBFactory::getDBQuery();
				$qry->execute($sql);
				while ($row = $qry->getRow())
				{
					$shipclass[] = new Shipclass($row['scl_id']);
				}
				foreach ($shipclass as $shp)
				{
					$list = new TopList_Losses();
					$list->addVictimAlliance($this->alliance);
					$list->addVictimShipClass($shp);
					$table = new TopTable_Pilot($list, "Losses");
					$content = $table->generate();
					$ships[] = array('name'=>$shp->getName(), 'table'=>$content);
				}
				$smarty->assignByRef('ships', $ships);
				return $smarty->fetch(get_tpl('detail_kl_ships'));

				break;
			case "corp_losses":
				$smarty->assign('title', 'Top Losers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('all_id', $this->all_id);
				$smarty->assign('url_previous', "?a=alliance_detail&amp;view=corp_kills&amp;all_id=$this->all_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=alliance_detail&amp;view=corp_kills&amp;all_id=$this->all_id&amp;m=$this->nmonth&amp;y=$this->nyear");

				$list = new TopList_CorpLosses();
				$list->addVictimAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Corp($list, "Losses");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_CorpLosses();
				$list->addVictimAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Corp($list, "Losses");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case "pilot_kills":
				$smarty->assign('title', 'Top Killers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('all_id', $this->all_id);
				$smarty->assign('url_previous', "?a=alliance_detail&amp;view=pilot_kills&amp;all_id=$this->all_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=alliance_detail&amp;view=pilot_kills&amp;all_id=$this->all_id&amp;m=$this->nmonth&amp;y=$this->nyear");

				$list = new TopList_Kills();
				$list->addInvolvedAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Kills");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_Kills();
				$list->addInvolvedAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Pilot($list, "Kills");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case "pilot_scores":
				$smarty->assign('title', 'Top Scorers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('all_id', $this->all_id);
				$smarty->assign('url_previous', "?a=alliance_detail&amp;view=pilot_scores&amp;all_id=$this->all_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=alliance_detail&amp;view=pilot_scores&amp;all_id=$this->all_id&amp;m=$this->nmonth&amp;y=$this->nyear");

				$list = new TopList_Score();
				$list->addInvolvedAlliance($this->alliance);
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Points");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_Score();
				$list->addInvolvedAlliance($this->alliance);
				$table = new TopTable_Pilot($list, "Points");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case "pilot_losses":
				$smarty->assign('title', 'Top Losers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('all_id', $this->all_id);
				$smarty->assign('url_previous', "?a=alliance_detail&amp;view=pilot_losses&amp;all_id=$this->all_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=alliance_detail&amp;view=pilot_losses&amp;all_id=$this->all_id&amp;m=$this->nmonth&amp;y=$this->nyear");

				$list = new TopList_Losses();
				$list->addVictimAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Losses");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_Losses();
				$list->addVictimAlliance($this->alliance);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Pilot($list, "Losses");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case "ships_weapons":
				$view = "ships_weapons";
				$shiplist = new TopList_Ship();
				$shiplist->addInvolvedAlliance($this->alliance);
				$shiplisttable = new TopTable_Ship($shiplist);
				$smarty->assign('ships', $shiplisttable->generate());

				$weaponlist = new TopList_Weapon();
				$weaponlist->addInvolvedAlliance($this->alliance);
				$weaponlisttable = new TopTable_Weapon($weaponlist);
				$smarty->assign('weapons', $weaponlisttable->generate());
				return $smarty->fetch(get_tpl('detail_kl_ships_weapons'));

				break;
			case 'violent_systems':
				$smarty->assign('title', 'Most violent systems');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('all_id', $this->all_id);
				$smarty->assign('url_previous', "?a=alliance_detail&amp;view=violent_systems&amp;all_id=$this->all_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=alliance_detail&amp;view=violent_systems&amp;all_id=$this->all_id&amp;m=$this->nmonth&amp;y=$this->nyear");

				$startdate = gmdate('Y-m-d H:i', makeStartDate(0, $this->year, $this->month));
				$enddate = gmdate('Y-m-d H:i', makeEndDate(0, $this->year, $this->month));
				$sql = "select sys.sys_name, sys.sys_sec, sys.sys_id, count(kll.kll_id) as kills
							from kb3_systems sys, kb3_kills kll, kb3_inv_all ina
							where kll.kll_system_id = sys.sys_id
							and ina.ina_kll_id = kll.kll_id
							and ina.ina_all_id = ".$this->all_id;

				$sql .= "   and kll.kll_timestamp > '$startdate'
							and kll.kll_timestamp < '$enddate'
							and ina.ina_timestamp > '$startdate'
							and ina.ina_timestamp < '$enddate'
							group by sys.sys_id
							order by kills desc, sys.sys_name asc
							limit 25";

				$qry = DBFactory::getDBQuery();
				$qry->execute($sql);
				$odd = false;
				$counter = 1;
				$syslist = array();

				while ($row = $qry->getRow())
				{
					if (!$odd)
					{
						$odd = true;
						$rowclass = 'kb-table-row-odd';
					}
					else
					{
						$odd = false;
						$rowclass = 'kb-table-row-even';
					}

					$syslist[] = array(
						"counter"=>$counter,
						"url"=>"?a=system_detail&amp;sys_id=".$row['sys_id'],
						"name"=>$row['sys_name'],
						"sec"=>roundsec($row['sys_sec']),
						"kills"=>$row['kills']);
					$counter++;
				}
				$smarty->assignByRef('syslist', $syslist);
				$smarty->assign('monthly_stats', $smarty->fetch(get_tpl(violent_systems)));

				$sql = "select sys.sys_name, sys.sys_id, sys.sys_sec, count(kll.kll_id) as kills
							from kb3_systems sys, kb3_kills kll, kb3_inv_all ina
							where kll.kll_system_id = sys.sys_id
							and ina.ina_kll_id = kll.kll_id
							and ina.ina_all_id = ".$this->all_id;

				$sql .= " group by sys.sys_id
							order by kills desc, sys.sys_name asc
							limit 25";

				$qry = DBFactory::getDBQuery();
				$qry->execute($sql);
				$odd = false;
				$counter = 1;
				$syslist = array();

				while ($row = $qry->getRow())
				{
					if (!$odd)
					{
						$odd = true;
						$rowclass = 'kb-table-row-odd';
					}
					else
					{
						$odd = false;
						$rowclass = 'kb-table-row-even';
					}

					$syslist[] = array(
						"counter"=>$counter,
						"url"=>"?a=system_detail&amp;sys_id=".$row['sys_id'],
						"name"=>$row['sys_name'],
						"sec"=>roundsec($row['sys_sec']),
						"kills"=>$row['kills']);
					$counter++;
				}
				$smarty->assignByRef('syslist', $syslist);
				$smarty->assign('total_stats', $smarty->fetch(get_tpl(violent_systems)));
				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case 'corp_list':
				return $this->corpList();
				break;
		}
		return $smarty->fetch(get_tpl('alliance_detail'));
	}

	/**
	 *  Reset the assembly object to prepare for creating the context.
	 */
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
	}

	/**
	 * Build the menu.
	 *
	 *  Additional options that have been set are added to the menu.
	 */
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
	/**
	 * Set up the menu.
	 *
	 *  Additional options that have been set are added to the menu.
	 */
	function menuSetup()
	{
		$menubox = new Box("Menu");
		$menubox->setIcon("menu-item.gif");
		$this->addMenuItem("caption","Kills &amp; losses");
		$this->addMenuItem("link","Recent activity", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID());
		$this->addMenuItem("link","Kills", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=kills");
		$this->addMenuItem("link","Losses", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=losses");
		$this->addMenuItem("caption","Corp statistics");
		$this->addMenuItem("link","Corp List", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=corp_list");
		$this->addMenuItem("link","Top killers", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=corp_kills");
		$this->addMenuItem("link","Top losers", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=corp_losses");
		$this->addMenuItem("link","Destroyed ships", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=corp_kills_class");
		$this->addMenuItem("link","Lost ships", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=corp_losses_class");
		$this->addMenuItem("caption","Pilot statistics");
		$this->addMenuItem("link","Top killers", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=pilot_kills");
		if (config::get('kill_points'))
		{
			$this->addMenuItem('link', "Top scorers", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=pilot_scores");
		}
		$this->addMenuItem("link","Top losers", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=pilot_losses");
		$this->addMenuItem("link","Destroyed ships", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=kills_class");
		$this->addMenuItem("link","Lost ships", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=losses_class");
		$this->addMenuItem("caption","Global statistics");
		$this->addMenuItem("link","Ships &amp; weapons", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=ships_weapons");
		$this->addMenuItem("link","Most violent systems", "?a=alliance_detail&amp;all_id=" . $this->alliance->getID() . "&amp;view=violent_systems");
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
}

$allianceDetail = new pAllianceDetail();
event::call("allianceDetail_assembling", $allianceDetail);
$html = $allianceDetail->assemble();
$allianceDetail->page->setContent($html);

$allianceDetail->context();
event::call("allianceDetail_context_assembling", $allianceDetail);
$context = $allianceDetail->assemble();
$allianceDetail->page->addContext($context);

$allianceDetail->page->generate();
