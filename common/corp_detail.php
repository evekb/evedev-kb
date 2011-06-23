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
		private $viewList = array();
		private $menuOptions = array();
		public $corp = null;
		public $crp_id = 0;
		public $crp_external_id = 0;
		public $scl_id = 0;
		public $alliance = 0;
		public $kill_summary = null;

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

		if(isset($_GET['scl_id'])) $this->scl_id = intval($_GET['scl_id']);
		else $this->scl_id = false;
		if(isset($_GET['crp_id'])) $this->crp_id = intval($_GET['crp_id']);
		if(isset($_GET['crp_external_id'])) $this->crp_external_id = intval($_GET['crp_external_id']);
		elseif(isset($_GET['crp_ext_id'])) $this->crp_external_id = intval($_GET['crp_ext_id']);
		$this->view = $_GET['view'];
		if($this->view) $this->page->addHeader('<meta name="robots" content="noindex, nofollow" />');

		if(!$this->crp_id)
		{
			if($this->crp_external_id)
			{
				$this->corp = new Corporation($this->crp_external_id, true);
				$this->crp_id = $this->corp->getID();
			}
			else
			{
				$html = 'That corporation does not exist.';
				$this->page->setContent($html);
				$this->page->generate();
				exit;
			}
		}
		else
		{
			$this->corp = new Corporation($this->crp_id);
			$this->crp_external_id = $this->corp->getExternalID();
		}

		if($this->crp_external_id) $this->page->addHeader("<link rel='canonical' href='".KB_HOST."/?a=corp_detail&amp;crp_ext_id=". $this->crp_external_id."' />");
		else $this->page->addHeader("<link rel='canonical' href='".KB_HOST."/?a=corp_detail&amp;crp_id=".$this->crp_id."' />");

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
		if($result == "")
		{
			$this->alliance->add($myAPI->getAllianceName(),
				$myAPI->getAllianceID());
			$this->corp->add($myAPI->getCorporationName(),
				$this->alliance, $myAPI->getCurrentTime(),
				$externalid = $this->corp->getExternalID());
		}
		$this->page->setTitle('Corporation details - '.$this->corp->getName() . " [" . $myAPI->getTicker() . "]");

		$smarty->assign('portrait_url', $this->corp->getPortraitURL(128));

		if($this->alliance->getName() == "None")
			$smarty->assign('alliance_url', false);
		else if($this->alliance->getExternalID())
			$smarty->assign('alliance_url', "?a=alliance_detail&amp;all_ext_id=".$this->alliance->getExternalID());
		else
			$smarty->assign('alliance_url', "?a=alliance_detail&amp;all_id=".$this->alliance->getID());
		$smarty->assign('alliance_name', $this->alliance->getName());

		$smarty->assign('kill_count', $this->kill_summary->getTotalKills());
		$smarty->assign('loss_count', $this->kill_summary->getTotalLosses());
		$smarty->assign('damage_done', number_format($this->kill_summary->getTotalKillISK()/1000000000, 2));
		$smarty->assign('damage_received', number_format($this->kill_summary->getTotalLossISK()/1000000000, 2));
		if ($this->kill_summary->getTotalKillISK())
		{
			$smarty->assign('efficiency', number_format(100 * $this->kill_summary->getTotalKillISK() / ($this->kill_summary->getTotalKillISK() + $this->kill_summary->getTotalLossISK()), 2));
		}
		else
		{
			$smarty->assign('efficiency', 0);
		}

		if ($result != "Corporation is not part of alliance.")
		{
			$smarty->assign('ceo_url', "?a=pilot_detail&amp;plt_ext_id=".$myAPI->getCeoID());
			$smarty->assign('ceo_name', $myAPI->getCeoName());
			$smarty->assign('HQ_location', $myAPI->getStationName());
			$smarty->assign('member_count', $myAPI->getMemberCount());
			$smarty->assign('share_count', $myAPI->getShares());
			$smarty->assign('tax_rate', $myAPI->getTaxRate());
			$smarty->assign('external_url', $myAPI->getUrl());
			$smarty->assign('corp_description', str_replace( "<br>", "<br />", $myAPI->getDescription()));
		}
		return $smarty->fetch(get_tpl('corp_detail_stats'));
	}

	/**
	 *  Build the killlists that are needed for the options selected.
	 */
	function killList()
	{
		global $smarty;
		if(isset($this->viewList[$this->view])) return call_user_func_array($this->viewList[$this->view], array(&$this));

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
				$smarty->assign('url_previous', "?a=corp_detail&amp;view=pilot_kills&amp;crp_id=$this->crp_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=corp_detail&amp;view=pilot_kills&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear");

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
				$smarty->assign('url_previous', "?a=corp_detail&amp;view=pilot_scores&amp;crp_id=$this->crp_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=corp_detail&amp;view=pilot_scores&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear");

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
				$smarty->assign('url_previous', "?a=corp_detail&amp;view=pilot_solo&amp;crp_id=$this->crp_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=corp_detail&amp;view=pilot_solo&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear");

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
				$smarty->assign('url_previous', "?a=corp_detail&amp;view=pilot_damage&amp;crp_id=$this->crp_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=corp_detail&amp;view=pilot_damage&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear");

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
				$smarty->assign('url_previous', "?a=corp_detail&amp;view=pilot_griefer&amp;crp_id=$this->crp_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=corp_detail&amp;view=pilot_griefer&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear");

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
				$smarty->assign('url_previous', "?a=corp_detail&amp;view=pilot_losses&amp;crp_id=$this->crp_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=corp_detail&amp;view=pilot_losses&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear");

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
				$smarty->assign('url_previous', "?a=corp_detail&amp;view=violent_systems&amp;crp_id=$this->crp_id&amp;m=$this->pmonth&amp;y=$this->pyear");
				$smarty->assign('url_next', "?a=corp_detail&amp;view=violent_systems&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear");

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
		$this->addMenuItem("caption","Kills &amp; losses");
		$this->addMenuItem("link","Recent activity", "?a=corp_detail&amp;crp_id=" . $this->corp->getID());
		$this->addMenuItem("link","Kills", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=kills");
		$this->addMenuItem("link","Losses", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=losses");
		$this->addMenuItem("caption","Pilot statistics");
		$this->addMenuItem("link","Top killers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_kills");

		if (config::get('kill_points'))
			$this->addMenuItem("link","Top scorers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_scores");
		$this->addMenuItem("link","Top solokillers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_solo");
		$this->addMenuItem("link","Top damagedealers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_damage");
		$this->addMenuItem("link","Top griefers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_griefer");
		$this->addMenuItem("link","Top losers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_losses");
		$this->addMenuItem("caption","Global statistics");
		$this->addMenuItem("link","Ships &amp; weapons", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=ships_weapons");
		$this->addMenuItem("link","Most violent systems", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=violent_systems");
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
