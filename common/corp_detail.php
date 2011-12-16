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
class pCorpDetail extends pageAssembly
{
	/** @var Page */
	public $page = null;
	/** @var integer */
	public $crp_id = 0;
	/** @var integer */
	public $crp_external_id = 0;
	/** @var Corporation */
	public $corp = null;
	/** @var Alliance */
	public $alliance = null;

	/** @var string The selected view. */
	protected $view = null;
	/** @var array The list of views and their callbacks. */
	protected $viewList = array();
	/** @var array The list of menu options to display. */
	protected $menuOptions = array();
	/** @var integer */
	protected $month;
	/** @var integer */
	protected $year;

	/** @var integer */
	private $nmonth;
	/** @var integer */
	private $nyear;
	/** @var integer */
	private $pmonth;
	/** @var integer */
	private $pyear;
	/** @var KillSummaryTable */
	private $kill_summary = null;
	
	/**
	 * Construct the Pilot Details object.
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
	 *  Reset the assembly object to prepare for creating the context.
	 */
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
	}

	/**

	 * Start constructing the page.

	 * Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{
		$this->page = new Page('Corporation details');

		$this->scl_id = (int)edkURI::getArg('scl_id');
		$this->crp_id = (int)edkURI::getArg('crp_id');
		if (!$this->crp_id) {
			$this->crp_external_id = (int)edkURI::getArg('crp_ext_id');
			if (!$this->crp_external_id) {
				$id = (int)edkURI::getArg('id', 1);
				// True for NPC corps too, but NPC alliances recorded as corps
				// fail here. Use Jedi mind tricks?
				if ($id > 1000000) {
					$this->crp_external_id = $id;
				} else {
					$this->crp_id = $id;
				}
			}
		}

		$this->view = preg_replace('/[^a-zA-Z0-9_-]/','', edkURI::getArg('view', 2));
		if($this->view) {
			$this->page->addHeader('<meta name="robots" content="noindex, nofollow" />');
		}

		if(!$this->crp_id) {
			if($this->crp_external_id) {
				$this->corp = new Corporation($this->crp_external_id, true);
				$this->crp_id = $this->corp->getID();
			} else {
				$html = 'That corporation does not exist.';
				$this->page->setContent($html);
				$this->page->generate();
				exit;
			}
		} else {
			$this->corp = Cacheable::factory('Corporation', $this->crp_id);
			$this->crp_external_id = $this->corp->getExternalID();
		}

		if($this->crp_external_id) {
			$this->page->addHeader("<link rel='canonical' href='"
					.edkURI::build(array('crp_ext_id', $this->crp_external_id,
						true))."' />");
		} else {
			$this->page->addHeader("<link rel='canonical' href='"
					.edkURI::build(array('crp_id', $this->crp_id,
						true))."' />");
		}

		$this->alliance = $this->corp->getAlliance();

		if ($this->view) {
			$this->year = (int)edkURI::getArg('y', 3);
			$this->month = (int)edkURI::getArg('m', 4);
		} else {
			$this->year = (int)edkURI::getArg('y', 2);
			$this->month = (int)edkURI::getArg('m', 3);
		}

		if (!$this->month) {
			$this->month = kbdate('m');
		}
		if (!$this->year) {
			$this->year = kbdate('Y');
		}

		if ($this->month == 12) {
			$this->nmonth = 1;
			$this->nyear = $this->year + 1;
		} else {
			$this->nmonth = $this->month + 1;
			$this->nyear = $this->year;
		}
		if ($this->month == 1) {
			$this->pmonth = 12;
			$this->pyear = $this->year - 1;
		} else {
			$this->pmonth = $this->month - 1;
			$this->pyear = $this->year;
		}
		$this->monthname = kbdate("F", strtotime("2000-".$this->month."-2"));
	}
	/**
	 *  Set up the stats used by the stats and summary table functions
	 */
	function statSetup()
	{
		$this->kill_summary = new KillSummaryTable();
		$this->kill_summary->addInvolvedCorp($this->crp_id);
		$this->kill_summary->generate();
	}
	/**
	 *  Build the summary table showing all kills and losses for this corporation.
	 */
	function summaryTable()
	{
		if($this->view != '' && $this->view != 'kills'
			&& $this->view != 'losses') return '';
		// The summary table is also used by the stats. Whichever is called
		// first generates the table.
		return $this->kill_summary->generate();
	}
	/**
	 *  Show the overall statistics for this corporation.
	 */
	function stats()
	{
		global $smarty;
		// The summary table is also used by the stats. Whichever is called
		// first generates the table.

		$myAPI = new API_CorporationSheet();
		$myAPI->setCorpID($this->corp->getExternalID());
		$result .= $myAPI->fetchXML();
		// Update the name if it has changed.
		if($result == "" && $myAPI->getCorporationName())
		{
			$this->alliance = Alliance::add($myAPI->getAllianceName(),
				$myAPI->getAllianceID());
			$this->corp = Corporation::add($myAPI->getCorporationName(),
				$this->alliance, $myAPI->getCurrentTime(),
				$externalid = $this->corp->getExternalID());
		}
		$this->page->setTitle('Corporation details - '.$this->corp->getName() . " [" . $myAPI->getTicker() . "]");

		$smarty->assign('portrait_url', $this->corp->getPortraitURL(128));

		if($this->alliance->getName() == "None") {
			$smarty->assign('alliance_url', false);
		} else if($this->alliance->getExternalID()) {
			$smarty->assign('alliance_url', edkURI::build(
					array('a', 'alliance_detail', true),
					array('all_ext_id', $this->alliance->getExternalID(), true)));
		} else {
			$smarty->assign('alliance_url', edkURI::build(
					array('a', 'alliance_detail', true),
					array('all_id', $this->alliance->getID(), true)));
		}
		$smarty->assign('alliance_name', $this->alliance->getName());

		$smarty->assign('kill_count', $this->kill_summary->getTotalKills());
		$smarty->assign('loss_count', $this->kill_summary->getTotalLosses());
		$smarty->assign('damage_done', number_format($this->kill_summary->getTotalKillISK()/1000000000, 2));
		$smarty->assign('damage_received', number_format($this->kill_summary->getTotalLossISK()/1000000000, 2));
		if ($this->kill_summary->getTotalKillISK()) {
			$smarty->assign('efficiency',
					number_format(100 * $this->kill_summary->getTotalKillISK() /
							($this->kill_summary->getTotalKillISK()
							+ $this->kill_summary->getTotalLossISK()), 2));
		} else {
			$smarty->assign('efficiency', 0);
		}

		if ($result != "Corporation is not part of alliance.") {
			$smarty->assign('ceo_url', edkURI::build(
					array('a', 'pilot_detail', true),
					array('plt_ext_id', $myAPI->getCeoID(), true)));
			$smarty->assign('ceo_name', $myAPI->getCeoName());
			$smarty->assign('HQ_location', $myAPI->getStationName());
			$smarty->assign('member_count', $myAPI->getMemberCount());
			$smarty->assign('share_count', $myAPI->getShares());
			$smarty->assign('tax_rate', $myAPI->getTaxRate());
			$smarty->assign('external_url', $myAPI->getUrl());
			$smarty->assign('corp_description', str_replace( "<br>", "<br />",
					$myAPI->getDescription()));
		}
		return $smarty->fetch(get_tpl('corp_detail_stats'));
	}

	/**
	 *  Build the killlists that are needed for the options selected.
	 */
	function killList()
	{
		global $smarty;
		if(isset($this->viewList[$this->view])) {
			return call_user_func_array($this->viewList[$this->view], array(&$this));
		}
		$args = array();
		if ($this->crp_external_id) {
			$args[] = array('crp_ext_id', $this->crp_external_id, true);
		} else {
			$args[] = array('crp_id', $this->crp_id, true);
		}

		$pyear = array('y', $this->pyear, true);
		$nyear = array('y', $this->nyear, true);
		$pmonth = array('m', $this->pmonth, true);
		$nmonth = array('m', $this->nmonth, true);
		switch ($this->view)
		{
			case "":
				$list = new KillList();
				$list->setOrdered(true);
				$list->setLimit(10);
				$list->addInvolvedCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				else $list->setPodsNoobShips(config::get('podnoobs'));
				if (config::get('comments_count')) $list->setCountComments(true);
				if (config::get('killlist_involved')) $list->setCountInvolved(true);

				$ktab = new KillListTable($list);
				$ktab->setLimit(10);
				$ktab->setDayBreak(false);
				$smarty->assign('kills', $ktab->generate());

				$list = new KillList();
				$list->setOrdered(true);
				$list->setLimit(10);
				$list->addVictimCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				else $list->setPodsNoobShips(config::get('podnoobs'));
				if (config::get('comments_count')) $list->setCountComments(true);
				if (config::get('killlist_involved')) $list->setCountInvolved(true);

				$ltab = new KillListTable($list);
				$ltab->setLimit(10);
				$ltab->setDayBreak(false);
				$smarty->assign('losses', $ltab->generate());
				return $smarty->fetch(get_tpl('detail_kl_default'));

				break;
			case "kills":
				$list = new KillList();
				$list->setOrdered(true);
				$list->addInvolvedCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				else $list->setPodsNoobShips(config::get('podnoobs'));
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
				$list->addVictimCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				else $list->setPodsNoobShips(config::get('podnoobs'));
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$smarty->assign('splitter',$pagesplitter->generate());
				$smarty->assign('losses', $table->generate());
				return $smarty->fetch(get_tpl('detail_kl_losses'));

				break;
			case "pilot_kills":
				$smarty->assign('title', 'Top Killers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('crp_id', $this->crp_id);
				$smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_kills', true), $pyear, $pmonth));
				$smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_kills', true), $nyear, $nmonth));

				$list = new TopList_Kills();
				$list->addInvolvedCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Kills");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_Kills();
				$list->addInvolvedCorp($this->crp_id);
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
				$smarty->assign('crp_id', $this->crp_id);
				$smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_scores', true), $pyear, $pmonth));
				$smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_scores', true), $nyear, $nmonth));

				$list = new TopList_Score();
				$list->addInvolvedCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Points");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_Score();
				$list->addInvolvedCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Pilot($list, "Points");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case "pilot_solo":
				$smarty->assign('title', 'Top Solokillers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('crp_id', $this->crp_id);
				$smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_solo', true), $pyear, $pmonth));
				$smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_solo', true), $nyear, $nmonth));

				$list = new TopList_SoloKiller();
				$list->addInvolvedCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Solokills");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_SoloKiller();
				$list->addInvolvedCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Pilot($list, "Solokills");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;

			case "pilot_damage":
				$smarty->assign('title', 'Top Damagedealers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('crp_id', $this->crp_id);
				$smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_damage', true), $pyear, $pmonth));
				$smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_damage', true), $nyear, $nmonth));

				$list = new TopList_DamageDealer();
				$list->addInvolvedCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Kills");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_DamageDealer();
				$list->addInvolvedCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Pilot($list, "Kills");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;

			case "pilot_griefer":
				$smarty->assign('title', 'Top Griefers');
				$smarty->assign('month', $this->monthname);
				$smarty->assign('year', $this->year);
				$smarty->assign('pmonth', $this->pmonth);
				$smarty->assign('pyear', $this->pyear);
				$smarty->assign('nmonth', $this->nmonth);
				$smarty->assign('nyear', $this->nyear);
				$smarty->assign('crp_id', $this->crp_id);
				$smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_griefer', true), $pyear, $pmonth));
				$smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_griefer', true), $nyear, $nmonth));

				$list = new TopList_Kills();
				$list->addVictimShipClass(20); // freighter
				$list->addVictimShipClass(22); // exhumer
				$list->addVictimShipClass(7); // industrial
				$list->addVictimShipClass(12); // barge
				$list->addVictimShipClass(14); // transport

				$list->addInvolvedCorp($this->crp_id);
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Kills");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_Kills();
				$list->addVictimShipClass(20); // freighter
				$list->addVictimShipClass(22); // exhumer
				$list->addVictimShipClass(7); // industrial
				$list->addVictimShipClass(12); // barge
				$list->addVictimShipClass(14); // transport
				$list->addInvolvedCorp($this->crp_id);
				$table = new TopTable_Pilot($list, "Kills");
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
				$smarty->assign('crp_id', $this->crp_id);
				$smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_losses', true), $pyear, $pmonth));
				$smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_losses', true), $nyear, $nmonth));

				$list = new TopList_Losses();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopTable_Pilot($list, "Losses");
				$smarty->assign('monthly_stats', $table->generate());

				$list = new TopList_Losses();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopTable_Pilot($list, "Losses");
				$smarty->assign('total_stats', $table->generate());

				return $smarty->fetch(get_tpl('detail_kl_monthly'));

				break;
			case "ships_weapons":
				$shiplist = new TopList_Ship();
				$shiplist->addInvolvedCorp($this->crp_id);
				$shiplisttable = new TopTable_Ship($shiplist);
				$smarty->assign('ships', $shiplisttable->generate());

				$weaponlist = new TopList_Weapon();
				$weaponlist->addInvolvedCorp($this->crp_id);
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
				$smarty->assign('crp_id', $this->crp_id);
				$smarty->assign('url_previous', edkURI::build($args, array('view', 'violent_systems', true), $pyear, $pmonth));
				$smarty->assign('url_next', edkURI::build($args, array('view', 'violent_systems', true), $nyear, $nmonth));

				$startdate = gmdate('Y-m-d H:i', makeStartDate(0, $this->year, $this->month));
				$enddate = gmdate('Y-m-d H:i', makeEndDate(0, $this->year, $this->month));
				$sql = "select sys.sys_name, sys.sys_sec, sys.sys_id, count(kll.kll_id) as kills
							from kb3_systems sys, kb3_kills kll, kb3_inv_crp inc
							where kll.kll_system_id = sys.sys_id
							and inc.inc_kll_id = kll.kll_id
							and inc.inc_crp_id = ".$this->crp_id;

				$sql .= "   and kll.kll_timestamp > '$startdate'
							and kll.kll_timestamp < '$enddate'
							and inc.inc_timestamp > '$startdate'
							and inc.inc_timestamp < '$enddate'
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
							from kb3_systems sys, kb3_kills kll, kb3_inv_crp inc
							where kll.kll_system_id = sys.sys_id
							and inc.inc_kll_id = kll.kll_id
							and inc.inc_crp_id = ".$this->crp_id;

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
		$args = array();
		if ($this->crp_external_id) {
			$args[] = array('crp_ext_id', $this->crp_external_id, true);
		} else {
			$args[] = array('crp_id', $this->crp_id, true);
		}

		$this->addMenuItem("caption","Kills &amp; losses");
		$this->addMenuItem("link","Recent activity", edkURI::build($args));
		$this->addMenuItem("link","Kills", edkURI::build($args, array('view', 'kills', true)));
		$this->addMenuItem("link","Losses", edkURI::build($args, array('view', 'losses', true)));
		$this->addMenuItem("caption","Pilot statistics");
		$this->addMenuItem("link","Top killers", edkURI::build($args, array('view', 'pilot_kills', true)));

		if (config::get('kill_points'))
			$this->addMenuItem("link","Top scorers", edkURI::build($args, array('view', 'pilot_scores', true)));
		$this->addMenuItem("link","Top solokillers", edkURI::build($args, array('view', 'pilot_solo', true)));
		$this->addMenuItem("link","Top damagedealers", edkURI::build($args, array('view', 'pilot_damage', true)));
		$this->addMenuItem("link","Top griefers", edkURI::build($args, array('view', 'pilot_griefer', true)));
		$this->addMenuItem("link","Top losers", edkURI::build($args, array('view', 'pilot_losses', true)));
		$this->addMenuItem("caption","Global statistics");
		$this->addMenuItem("link","Ships &amp; weapons", edkURI::build($args, array('view', 'ships_weapons', true)));
		$this->addMenuItem("link","Most violent systems", edkURI::build($args, array('view', 'violent_systems', true)));
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
}

$corpDetail = new pCorpDetail();
event::call("corpDetail_assembling", $corpDetail);
$html = $corpDetail->assemble();
$corpDetail->page->setContent($html);

$corpDetail->context();
event::call("corpDetail_context_assembling", $corpDetail);
$context = $corpDetail->assemble();
$corpDetail->page->addContext($context);

$corpDetail->page->generate();
