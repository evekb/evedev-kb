<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');
require_once("common/includes/evelogo.php");
require_once("common/includes/class.eveapi.php");
require_once('common/includes/class.pageAssembly.php');

class pCorpDetail extends pageAssembly
{
	//! Construct the Pilot Details object.

	/** Set up the basic variables of the class and add the functions to the
	 *  build queue.
	 */
	function __construct()
	{
		parent::__construct();
		$this->scl_id = intval($_GET['scl_id']);
		$this->crp_id = intval($_GET['crp_id']);
		$this->crp_external_id = intval($_GET['crp_external_id']);
		$this->view = $_GET['view'];
		$this->viewList = array();

		$this->menuOptions = array();

		$this->queue("start");
		$this->queue("stats");
		$this->queue("summaryTable");
		$this->queue("killList");

	}

	//! Reset the assembly object to prepare for creating the context.
	function context()
	{
		parent::__construct();
		$this->queue("menu");
	}

	//! Start constructing the page.

	/*! Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{
		$this->page = new Page('Corporation details');

		if(!$this->crp_id)
		{
			if($this->crp_external_id)
			{
				$qry = new DBQuery();
				$qry->execute('SELECT crp_id FROM kb3_corps WHERE crp_externalid = '.$this->crp_external_id);
				if($qry->recordCount())
				{
					$row = $qry->getRow();
					$this->crp_id = $row['crp_id'];
				}
			}
			elseif(CORP_ID) $this->crp_id = CORP_ID;
			else
			{
				$html = 'That corporation does not exist.';
				$this->page->generate($html);
				exit;
			}

		}
		$this->all_id = intval($_GET['all_id']);
		$this->corp = new Corporation($this->crp_id);
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

	//! Build the summary table showing all kills and losses for this corporation.
	function summaryTable()
	{
		if($this->view != '' && $this->view != 'kills'
			&& $this->view != 'losses') return '';
		// The summary table is also used by the stats. Whichever is called
		// first generates the table.
		if(isset($this->kill_summary)) return $this->kill_summary->generate();

		$this->kill_summary = new KillSummaryTable();
		$this->kill_summary->addInvolvedCorp($this->crp_id);
		return $this->kill_summary->generate();
	}

	//! Show the overall statistics for this corporation.
	function stats()
	{
		global $smarty;
		// The summary table is also used by the stats. Whichever is called
		// first generates the table.
		if(!isset($this->kill_summary))
		{
			$this->kill_summary = new KillSummaryTable();
			$this->kill_summary->addInvolvedCorp($this->crp_id);
			$this->kill_summary->generate();
		}
		$corpname = str_replace(" ", "%20", $this->corp->getName() );
		$myID = new API_NametoID();
		$myID->setNames($corpname);
		$html .= $myID->fetchXML();
		$myNames = $myID->getNameData();

		$myAPI = new API_CorporationSheet();
		$myAPI->setCorpID($myNames[0]['characterID']);

		$result .= $myAPI->fetchXML();

		if ($result == "Corporation is not part of alliance.")
		{
			$this->page->setTitle('Corporation details - '.$this->corp->getName());
		} else {
			$this->page->setTitle('Corporation details - '.$this->corp->getName() . " [" . $myAPI->getTicker() . "]");
		}
		$html .= '<table class="kb-table" width="100%" border="0" cellspacing="1"><tr class="kb-table-row-even"><td rowspan="8" width="128" align="center" bgcolor="black">';

		$html .= "<img src=\"".$this->corp->getPortraitURL(128)."\" border=\"0\" alt=\"\" /></td>";

		if ($result == "Corporation is not part of alliance.")
		{
			$html .= '<td class="kb-table-cell" width="180"><b>Alliance:</b></td><td class="kb-table-cell">';
			if ($this->alliance->getName() == "Unknown" || $this->alliance->getName() == "None")
			{
				$html .= "<b>".$this->alliance->getName()."</b>";
			}
			else
			{
				$html .= "<a href=\"?a=alliance_detail&amp;all_id=".$this->alliance->getID()."\">".$this->alliance->getName()."</a>";
			}
			$html .= "</td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Kills:</b></td><td class=\"kl-kill\">".$this->kill_summary->getTotalKills()."</td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Losses:</b></td><td class=\"kl-loss\">".$this->kill_summary->getTotalLosses()."</td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Damage done (ISK):</b></td><td class=\"kl-kill\">".round($this->kill_summary->getTotalKillISK()/1000000000, 2)."B</td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Damage received (ISK):</b></td><td class=\"kl-loss\">".round($this->kill_summary->getTotalLossISK()/1000000000, 2)."B</td></tr>";
			if ($this->kill_summary->getTotalKillISK())
			{
				$efficiency = round($this->kill_summary->getTotalKillISK() / ($this->kill_summary->getTotalKillISK() + $this->kill_summary->getTotalLossISK()) * 100, 2);
			}
			else
			{
				$efficiency = 0;
			}

			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Efficiency:</b></td><td class=\"kb-table-cell\"><b>" . $efficiency . "%</b></td></tr>";
			$html .= "</table>";
			$html .= "<br/>";
		} else {
			$html .= "<td class=\"kb-table-cell\" width=\"150\"><b>Alliance:</b></td><td class=\"kb-table-cell\">";
			if ($this->alliance->getName() == "Unknown" || $this->alliance->getName() == "None")
			{
				$html .= "<b>".$this->alliance->getName()."</b>";
			}
			else
			{
				$html .= "<a href=\"?a=alliance_detail&amp;all_id=".$this->alliance->getID()."\">".$this->alliance->getName()."</a>";
			}
			$html .= "</td><td class=\"kb-table-cell\" width=\"65\"><b>CEO:</b></td><td class=\"kb-table-cell\"><a href=\"?a=search&amp;searchtype=pilot&amp;searchphrase=" . urlencode($myAPI->getCeoName()) . "\">" . $myAPI->getCeoName() . "</a></td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Kills:</b></td><td class=\"kl-kill\">".$this->kill_summary->getTotalKills()."</td>";
			$html .= "<td class=\"kb-table-cell\"><b>HQ:</b></td><td class=\"kb-table-cell\">" . $myAPI->getStationName() . "</td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Losses:</b></td><td class=\"kl-loss\">".$this->kill_summary->getTotalLosses()."</td>";
			$html .= "<td class=\"kb-table-cell\"><b>Members:</b></td><td class=\"kb-table-cell\">" . $myAPI->getMemberCount() . "</td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Damage done (ISK):</b></td><td class=\"kl-kill\">".round($this->kill_summary->getTotalKillISK()/1000000000, 2)."B</td>";
			$html .= "<td class=\"kb-table-cell\"><b>Shares:</b></td><td class=\"kb-table-cell\">" . $myAPI->getShares() . "</td></tr>";
			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Damage received (ISK):</b></td><td class=\"kl-loss\">".round($this->kill_summary->getTotalLossISK()/1000000000, 2)."B</td>";
			$html .= "<td class=\"kb-table-cell\"><b>Tax Rate:</b></td><td class=\"kb-table-cell\">" . $myAPI->getTaxRate() . "%</td></tr>";
			if ($this->kill_summary->getTotalKillISK())
			{
				$efficiency = round($this->kill_summary->getTotalKillISK() / ($this->kill_summary->getTotalKillISK() + $this->kill_summary->getTotalLossISK()) * 100, 2);
			}
			else
			{
				$efficiency = 0;
			}

			$html .= "<tr class=\"kb-table-row-even\"><td class=\"kb-table-cell\"><b>Efficiency:</b></td><td class=\"kb-table-cell\"><b>" . $efficiency . "%</b></td>";
			$html .= "<td class=\"kb-table-cell\"><b>Website:</b></td><td class=\"kb-table-cell\"><a href=\"" . $myAPI->getUrl() . "\">" . $myAPI->getUrl() . "</a></td></tr>";
			$html .= "</table>";
			//$html .= "Corporation Description:";
			$html .= "<div class=\"kb-table-row-even\" style='width:100%;height:100px;overflow:auto'>";
			$html .= str_replace( "<br>", "<br />", $myAPI->getDescription() );
			$html .= "</div>";
			$html .= "<br/>";
		}
		return $html;
		//return $smarty->fetch(get_tpl('corp_detail_stats'));
	}

	//! Build the killlists that are needed for the options selected.
	function killList()
	{
		if(isset($this->viewList[$this->view])) return call_user_func_array($this->viewList[$this->view], array(&$this));

		switch ($this->view)
		{
			case "":
				$html .= "<div class=\"kb-kills-header\">10 Most recent kills</div>";

				$list = new KillList();
				$list->setOrdered(true);
				$list->setLimit(10);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->addVictimCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				//$list->setStartDate(date('Y-m-d H:i',strtotime('- 30 days')));

				$ktab = new KillListTable($list);
				$ktab->setLimit(10);
				$ktab->setDayBreak(false);
				$html .= $ktab->generate();

				$html .= "<div class=\"kb-losses-header\">10 Most recent losses</div>";

				$list = new KillList();
				$list->setOrdered(true);
				$list->setLimit(10);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->addVictimCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				//$list->setStartDate(date('Y-m-d H:i',strtotime('- 30 days')));

				$ltab = new KillListTable($list);
				$ltab->setLimit(10);
				$ltab->setDayBreak(false);
				$html .= $ltab->generate();

				break;
			case "kills":
				$html .= "<div class=\"kb-kills-header\">All kills</div>";

				$list = new KillList();
				$list->setOrdered(true);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->addVictimCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$html .= $table->generate();
				$html .= $pagesplitter->generate();

				break;
			case "losses":
				$html .= "<div class=\"kb-losses-header\">All losses</div>";

				$list = new KillList();
				$list->setOrdered(true);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->addVictimCorp($this->crp_id);
				if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
				$list->setPageSplit(config::get('killcount'));
				$pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

				$table = new KillListTable($list);
				$table->setDayBreak(false);
				$html .= $table->generate();
				$html .= $pagesplitter->generate();

				break;
			case "pilot_kills":
				$html .= "<div class=\"block-header2\">Top killers</div>";

				$html .= "<table class=\"kb-subtable\"><tr><td valign=\"top\" width=\"440\">";
				$html .= "<div class=\"block-header\">$this->monthname $this->year</div>";

				$list = new TopKillsList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopPilotTable($list, "Kills");
				$html .= $table->generate();

				$html .= "<table width=\"300\" cellspacing=\"1\"><tr><td><a href='?a=corp_detail&amp;view=pilot_kills&amp;m=$this->pmonth&amp;crp_id=$this->crp_id&amp;y=$this->pyear'>previous</a></td>";
				$html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_kills&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear'>next</a></p></td></tr></table>";

				$html .= "</td><td valign=\"top\" width=\"400\">";
				$html .= "<div class=\"block-header\">All time</div>";

				$list = new TopKillsList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopPilotTable($list, "Kills");
				$html .= $table->generate();

				$html .= "</td></tr></table>";

				break;
			case "pilot_scores":
				$html .= "<div class=\"block-header2\">Top scorers</div>";

				$html .= "<table class=\"kb-subtable\"><tr><td valign=\"top\" width=\"440\">";
				$html .= "<div class=\"block-header\">$this->monthname $this->year</div>";

				$list = new TopScoreList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopPilotTable($list, "Points");
				$html .= $table->generate();

				$html .= "<table width=\"300\" cellspacing=\"1\"><tr><td><a href='?a=corp_detail&amp;view=pilot_scores&amp;m=$this->pmonth&amp;crp_id=$this->crp_id&amp;y=$this->pyear'>previous</a></td>";
				$html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_scores&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear'>next</a></p></td></tr></table>";

				$html .= "</td><td valign=\"top\" width=\"400\">";
				$html .= "<div class=\"block-header\">All time</div>";

				$list = new TopScoreList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopPilotTable($list, "Points");
				$html .= $table->generate();

				$html .= "</td></tr></table>";

				break;
			case "pilot_solo":
				$html .= "<div class=\"block-header2\">Top solokillers</div>";

				$html .= "<table class=\"kb-subtable\"><tr><td valign=\"top\" width=\"440\">";
				$html .= "<div class=\"block-header\">$this->monthname $this->year</div>";

				$list = new TopSoloKillerList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopPilotTable($list, "Solokills");
				$html .= $table->generate();

				$html .= "<table width=\"300\" cellspacing=\"1\"><tr><td><a href='?a=corp_detail&amp;view=pilot_solo&amp;m=$this->pmonth&amp;crp_id=$this->crp_id&amp;y=$this->pyear'>previous</a></td>";
				$html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_solo&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear'>next</a></p></td></tr></table>";

				$html .= "</td><td valign=\"top\" width=\"400\">";
				$html .= "<div class=\"block-header\">All time</div>";

				$list = new TopSoloKillerList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopPilotTable($list, "Solokills");
				$html .= $table->generate();

				$html .= "</td></tr></table>";

				break;

			case "pilot_damage":
				$html .= "<div class=\"block-header2\">Top damagedealers</div>";

				$html .= "<table class=\"kb-subtable\"><tr><td valign=\"top\" width=\"440\">";
				$html .= "<div class=\"block-header\">$this->monthname $this->year</div>";

				$list = new TopDamageDealerList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopPilotTable($list, "Kills");
				$html .= $table->generate();

				$html .= "<table width=\"300\" cellspacing=\"1\"><tr><td><a href='?a=corp_detail&amp;view=pilot_damage&amp;m=$this->pmonth&amp;crp_id=$this->crp_id&amp;y=$this->pyear'>previous</a></td>";
				$html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_damage&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear'>next</a></p></td></tr></table>";

				$html .= "</td><td valign=\"top\" width=\"400\">";
				$html .= "<div class=\"block-header\">All time</div>";

				$list = new TopDamageDealerList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopPilotTable($list, "Kills");
				$html .= $table->generate();

				$html .= "</td></tr></table>";

				break;

			case "pilot_griefer":
				$html .= "<div class=\"block-header2\">Top griefers</div>";

				$html .= "<table class=\"kb-subtable\"><tr><td valign=\"top\" width=\"440\">";
				$html .= "<div class=\"block-header\">$this->monthname $this->year</div>";

				$list = new TopGrieferList();
				$list->addVictimCorp($this->crp_id);
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopPilotTable($list, "Kills");
				$html .= $table->generate();

				$html .= "<table width=\"300\" cellspacing=\"1\"><tr><td><a href='?a=corp_detail&amp;view=pilot_griefer&amp;m=$this->pmonth&amp;crp_id=$this->crp_id&amp;y=$this->pyear'>previous</a></td>";
				$html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_griefer&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear'>next</a></p></td></tr></table>";

				$html .= "</td><td valign=\"top\" width=\"400\">";
				$html .= "<div class=\"block-header\">All time</div>";

				$list = new TopGrieferList();
				$list->addVictimCorp($this->crp_id);
				$table = new TopPilotTable($list, "Kills");
				$html .= $table->generate();

				$html .= "</td></tr></table>";

				break;

			case "pilot_losses":
				$html .= "<div class=\"block-header2\">Top losers</div>";

				$html .= "<table class=\"kb-subtable\"><tr><td valign=\"top\" width=\"440\">";
				$html .= "<div class=\"block-header\">$this->monthname $this->year</div>";

				$list = new TopLossesList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$list->setMonth($this->month);
				$list->setYear($this->year);
				$table = new TopPilotTable($list, "Losses");
				$html .= $table->generate();

				$html .= "<table width=\"300\" cellspacing=\"1\"><tr><td><a href='?a=corp_detail&amp;view=pilot_losses&amp;m=$this->pmonth&amp;crp_id=$this->crp_id&amp;y=$this->pyear'>previous</a></td>";
				$html .= "<td align='right'><a href='?a=corp_detail&amp;view=pilot_losses&amp;crp_id=$this->crp_id&amp;m=$this->nmonth&amp;y=$this->nyear'>next</a></p></td></tr></table>";

				$html .= "</td><td valign=\"top\" width=\"400\">";
				$html .= "<div class=\"block-header\">All time</div>";

				$list = new TopLossesList();
				$list->addVictimCorp($this->crp_id);
				$list->setPodsNoobShips(config::get('podnoobs'));
				$table = new TopPilotTable($list, "Losses");
				$html .= $table->generate();

				$html .= "</td></tr></table>";

				break;
			case "ships_weapons":
				$html .= "<div class=\"block-header2\">Ships &amp; weapons used</div>";

				$html .= "<table class=\"kb-subtable\"><tr><td valign=\"top\" width=\"400\">";
				$shiplist = new TopShipList();
				$shiplist->addVictimCorp($this->crp_id);
				$shiplisttable = new TopShipListTable($shiplist);
				$html .= $shiplisttable->generate();
				$html .= "</td><td valign=\"top\" align=\"right\" width=\"400\">";

				$weaponlist = new TopWeaponList();
				$weaponlist->addVictimCorp($this->crp_id);
				$weaponlisttable = new TopWeaponListTable($weaponlist);
				$html .= $weaponlisttable->generate();
				$html .= "</td></tr></table>";

				break;
			case 'violent_systems':
				$html .= "<div class=\"block-header2\">Most violent systems</div>";
				$html .= "<table width=\"99%\"><tr><td align=\"center\" valign=\"top\">";

				$html .= "<div class=\"block-header\">This month</div>";
				$html .= "<table class=\"kb-table\">";
				$html .= "<tr class=\"kb-table-header\"><td>#</td><td width=\"180\">System</td><td width=\"40\" align=\"center\">Kills</td></tr>";

				$sql = "select sys.sys_name, sys.sys_sec, sys.sys_id, count(distinct kll.kll_id) as kills
							from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
							where kll.kll_system_id = sys.sys_id
							and inv.ind_kll_id = kll.kll_id";

				if ($this->crp_id)
					$sql .= " and inv.ind_crp_id in (".$this->crp_id.")";
				if ($this->all_id)
					$sql .= " and inv.ind_all_id = ".$this->all_id;

				$sql .= "   and date_format( kll.kll_timestamp, \"%c\" ) = ".kbdate("m")."
							and date_format( kll.kll_timestamp, \"%Y\" ) = ".kbdate("Y")."
							group by sys.sys_name
							order by kills desc
							limit 25";

				$qry = new DBQuery();
				$qry->execute($sql);
				$odd = false;
				$counter = 1;
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

					$html .= "<tr class=\"".$rowclass."\"><td><b>".$counter.".</b></td><td class=\"kb-table-cell\" width=\"180\"><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=\"center\">".$row['kills']."</td></tr>";
					$counter++;
				}

				$html .= "</table>";

				$html .= "</td><td align=\"center\" valign=\"top\">";
				$html .= "<div class=\"block-header\">All-Time</div>";
				$html .= "<table class=\"kb-table\">";
				$html .= "<tr class=\"kb-table-header\"><td>#</td><td width=\"180\">System</td><td width=\"40\" align=\"center\">Kills</td></tr>";

				$sql = "select sys.sys_name, sys.sys_id, sys.sys_sec, count(distinct kll.kll_id) as kills
							from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
							where kll.kll_system_id = sys.sys_id
							and inv.ind_kll_id = kll.kll_id";

				if ($this->crp_id)
					$sql .= " and inv.ind_crp_id in (".$this->crp_id.")";
				if ($this->all_id)
					$sql .= " and inv.ind_all_id = ".$this->all_id;

				$sql .= " group by sys.sys_name
							order by kills desc
							limit 25";

				$qry = new DBQuery();
				$qry->execute($sql);
				$odd = false;
				$counter = 1;
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

					$html .= "<tr class=\"".$rowclass."\"><td><b>".$counter.".</b></td><td class=\"kb-table-cell\"><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=\"center\">".$row['kills']."</td></tr>";
					$counter++;
				}
				$html .= "</table>";
				$html .= "</td></tr></table>";
				break;
		}
		return $html;
	}
	//! Build the menu.

	//! Additional options that have been set are added to the menu.
	function menu()
	{
		$menubox = new box("Menu");
		$menubox->setIcon("menu-item.gif");
		$menubox->addOption("caption","Kills &amp; losses");
		$menubox->addOption("link","Recent activity", "?a=corp_detail&amp;crp_id=" . $this->corp->getID());
		$menubox->addOption("link","Kills", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=kills");
		$menubox->addOption("link","Losses", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=losses");
		$menubox->addOption("caption","Pilot statistics");
		$menubox->addOption("link","Top killers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_kills");

		if (config::get('kill_points'))
			$menubox->addOption("link","Top scorers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_scores");
		$menubox->addOption("link","Top solokillers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_solo");
		$menubox->addOption("link","Top damagedealers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_damage");
		$menubox->addOption("link","Top griefers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_griefer");
		$menubox->addOption("link","Top losers", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=pilot_losses");
		$menubox->addOption("caption","Global statistics");
		$menubox->addOption("link","Ships &amp; weapons", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=ships_weapons");
		$menubox->addOption("link","Most violent systems", "?a=corp_detail&amp;crp_id=" . $this->corp->getID() . "&amp;view=violent_systems");
		foreach($this->menuOptions as $options)
		{
			if(isset($options[2]))
				$menubox->addOption($options[0],$options[1], $options[2]);
			else
				$menubox->addOption($options[0],$options[1]);
		}
		return $menubox->generate();
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

$corpDetail = new pCorpDetail();
event::call("corpDetail_assembling", $corpDetail);
$html = $corpDetail->assemble();
$corpDetail->page->setContent($html);

$corpDetail->context();
event::call("corpDetail_context_assembling", $corpDetail);
$context = $corpDetail->assemble();
$corpDetail->page->addContext($context);

$corpDetail->page->generate();