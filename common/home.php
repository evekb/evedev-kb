<?php
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.contract.php');
require_once('common/includes/class.toplist.php');
if(config::get('show_clock')) require_once('common/includes/class.clock.php');

class Home
{
	function Home()
	{
		$this->killboard = new Killboard();
		$this->killcount = config::get('killcount');
		$this->hourlimit = config::get('limit_hours');
		if(!$this->hourlimit) $this->hourlimit = 1;
		$this->klreturnmax = 3;
		$this->showcombined = config::get('show_comb_home')
			&& !isset($_REQUEST['kills'])
			&& !isset($_REQUEST['losses'])
			&& (ALLIANCE_ID || CORP_ID || PILOT_ID);
		$this->prevweek = false;
		$this->week = kbdate('W');
		$this->month = kbdate('m');
		$this->year = getYear();

		if ($this->week == 1)
		{
			$this->pyear = $this->year - 1;
			$this->pweek = 53;
		}
		else
		{
			$this->pyear = $this->year;
			$this->pweek = $this->week - 1;
		}
	}

	function setWeek($week, $year)
	{
		// If a valid week and year are given then show that week.
		if(((int)$week) < 1 || ((int)$week) > 53 || ((int)$year) < 2000) return false;

		$this->prevweek = true;
		$this->week = (int)$week;
		if($this->week < 10) $this->week = '0'.$this->week;
		$this->year = (int)$year;

		if ($this->week == 1)
		{
			$this->pyear = $this->year - 1;
			$this->pweek = 53;
		}
		else
		{
			$this->pyear = $this->year;
			$this->pweek = $this->week - 1;
		}
		return true;
	}

	function setMonth($month, $year)
	{
		$month = (int)$month;
		$year = (int)$year;
		if($month < 1 || $month > 12 || $year < 2000) return false;
		$this->month = $month;
		$this->year = $year;
		if($month == 1)
		{
			$this->pyear = $year - 1;
			$this->pmonth = 12;
		}
		else
		{
			$this->pyear = $year;
			$this->pmonth = $month - 1;
		}
		return true;
	}

	function SummaryTable()
	{
		// Display the summary table.
		if (config::get('summarytable'))
		{
			require_once('common/includes/class.killsummarytable.php');
			if (config::get('public_summarytable'))
			{
			$kslist = new KillList();
			involved::load($kslist,'kill');
			$kslist->setWeek($this->week);
			$kslist->setYear($this->year);
				require_once('common/includes/class.killsummarytable.public.php');
				$summarytable = new KillSummaryTablePublic($kslist);
			}
			else
			{
				$summarytable = new KillSummaryTable();
				$summarytable->setWeek($this->week);
				$summarytable->setYear($this->year);
				involved::load($summarytable, 'kill');
			}
			$summarytable->setBreak(config::get('summarytable_rowcount'));
			$html .= $summarytable->generate();
		}
		return $html;
	}

	function campaigns()
	{
		// Display campaigns, if any.
		if ($this->killboard->hasCampaigns(true))
		{
			$html .= "<div class=\"kb-campaigns-header\">Active campaigns</div>";
			$list = new ContractList();
			$list->setActive("yes");
			$list->setCampaigns(true);
			$table = new ContractListTable($list);
			$html .= $table->generate();
			return $html;
		}
	}

	function contracts()
	{
		// Display contracts, if any.
		if ($this->killboard->hasContracts(true))
		{
			$html .= "<div class=\"kb-campaigns-header\">Active contracts</div>";
			$list = new ContractList();
			$list->setActive("yes");
			$list->setCampaigns(false);
			$table = new ContractListTable($list);
			$html .= $table->generate();
			return $html;
		}
	}

	function kills()
	{
		global $smarty;
		$smarty->assign('kill_count', $this->killcount);
		// bad hax0ring, we really need mod callback stuff
		if (strpos(config::get('mods_active'), 'rss_feed') !== false)
		{
			$smarty->assign('rss_feed', true);
		}
		else
		{
			$smarty->assign('rss_feed', false);
		}

			// Retrieve kills to be displayed limited by the date. If too few are returned
			// extend the date range. If too many are returned reduce the date range.
			$klist = new KillList();
			$klist->setOrdered(true);
			// We'll be needing comment counts so set the killlist to retrieve them
			if (config::get('comments_count')) $klist->setCountComments(true);
			// We'll be needing involved counts so set the killlist to retrieve them
			if (config::get('killlist_involved')) $klist->setCountInvolved(true);

			if (!$this->prevweek) $klist->setLimit($this->killcount);
			else
			{
					$klist->setWeek($this->week);
					$klist->setYear($this->year);
			}
			// Select between combined kills and losses or just kills.
			if($this->showcombined) involved::load($klist,'combined');
			elseif(isset($_REQUEST['losses'])) involved::load($klist,'loss');
			else involved::load($klist,'kill');

			if ($_GET['scl_id'])
				$klist->addVictimShipClass(intval($_GET['scl_id']));
			else
				$klist->setPodsNoobShips(false);

		// If this is the current week then show the most recent kills. If a previous
		// week show all kills for the week using the page splitter.
		if($this->prevweek)
		{
				$klist->setPageSplit($this->killcount);
				$pagesplitter = new PageSplitter($klist->getCount(), $this->killcount);
				$table = new KillListTable($klist);
				if($this->showcombined)
					$table->setCombined(true);
				$html .= $table->generate();
				$html .= $pagesplitter->generate();
		}
		else
		{
				$table = new KillListTable($klist);
				if($this->showcombined) $table->setCombined(true);
				$table->setLimit($this->killcount);
				$html .= $table->generate();
		}
		return $html;
	}

	function menu()
	{
		// Display the menu for previous and next weeks.
		$menubox = new box("Menu");
		$menubox->setIcon("menu-item.gif");
		$menubox->addOption("caption","Navigation");

		if(isset($_REQUEST['kills'])) $suffix = '&amp;kills';
		elseif(isset($_REQUEST['losses'])) $suffix .= '&amp;losses';
		if($_REQUEST['scl_id']) $suffixscl = '&amp;scl_id='.intval($_REQUEST['scl_id']);
		if($this->prevweek)
		{
			$menubox->addOption("link","Previous week",
				"?a=home&amp;w=" . $this->pweek . "&amp;y=" . $this->pyear . $suffix.$suffixscl);

			if(kbdate('W') != $this->week || getYear() != $this->year)
			{
				if ($this->week == 53)
				{
					$nweek = 1;
					$nyear = $this->year + 1;
					$this->pyear = $this->year - 1;
				}
				else
				{
					$nweek = $this->week + 1;
					$nyear = $this->year;
				}
				$menubox->addOption("link","Next week",
					"?a=home&amp;w=" . $nweek . "&amp;y=" . $nyear . $suffix.$suffixscl);
			}
		}
		else
		{
			$menubox->addOption("link","Previous week",
				"?a=home&amp;w=" . $this->pweek . "&amp;y=" . $this->pyear . $suffix.$suffixscl);
		}
		if(kbdate('W') != $this->week || getYear() != $this->year) $weektext = $this->week . ", " . $this->year;
		else $weektext = "This Week's";
		if(!isset($_REQUEST['kills'])) $menubox->addOption("link", $weektext." Kills",
				"?a=home&amp;w=" . $this->week . "&amp;y=" . $this->year . '&amp;kills'.$suffixscl);
		if(!isset($_REQUEST['losses'])) $menubox->addOption("link", $weektext." Losses",
				"?a=home&amp;w=" . $this->week . "&amp;y=" . $this->year . '&amp;losses'.$suffixscl);
		if((isset($_REQUEST['losses']) || isset($_REQUEST['kills']) || !$this->prevweek) && config::get('show_comb_home')) $menubox->addOption("link",
			 $weektext." All Kills",
				"?a=home&amp;w=" . $this->week . "&amp;y=" . $this->year.$suffixscl);
		return $menubox->generate();
	}

	function clock()
	{
		// Show the Eve time.
		if(config::get('show_clock'))
		{
			$clock = new Clock();
			return $clock->generate();
		}
	}

	function topLists()
	{
		// Display the top pilot lists.
		if(!isset($_REQUEST['losses']))
		{
			$tklist = new TopKillsList();
			$tklist->setWeek($this->week);
			$tklist->setYear($this->year);
			involved::load($tklist,'kill');

			$tklist->generate();
			$tkbox = new AwardBox($tklist, "Top killers", "kills in week " . $this->week, "kills", "eagle");
			$html .= $tkbox->generate();
		}
		if(isset($_REQUEST['losses']))
		{
			$tllist = new TopLossesList();
			$tllist->setWeek($this->week);
			$tllist->setYear($this->year);
			involved::load($tllist,'loss');

			$tllist->generate();
			$tlbox = new AwardBox($tllist, "Top losers", "losses in week ".$this->week, "losses", "moon");
			$html .= $tlbox->generate();
		}
		if (!isset($_REQUEST['kills']) && !isset($_REQUEST['losses']))
		{
			$tklist = new TopScoreList();
			$tklist->setWeek($this->week);
			$tklist->setYear($this->year);
			involved::load($tklist,'kill');

			$tklist->generate();
			$tkbox = new AwardBox($tklist, "Top scorers", "points in week " . $this->week, "points", "redcross");
			$html .= $tkbox->generate();
		}
		return $html;
	}

	function generateContent()
	{
		global $smarty;
		$smarty->assign('summary', $this->SummaryTable());
		// Only show campaigns/contracts if on home page and current week.
		if(!isset($_REQUEST['losses']) && !isset($_REQUEST['kills']) &&
			(kbdate('W') == $this->week && getYear() == $this->year))
		{
			$smarty->assign('campaigns', $this->campaigns());
			$smarty->assign('contracts', $this->contracts());
		}
		$smarty->assign('kills', $this->kills());
		$smarty->assign('prevweek', $this->prevweek);
		return $smarty->fetch(get_tpl('home'));
	}

	function generateContext()
	{
		$html = '';
		$html .= $this->menu();
		$html .= $this->clock();
		$html .= $this->topLists();
		return $html;
	}

	function getWeek()
	{
		return $this->week;
	}

	function getMonth()
	{
		return $this->month;
	}

	function getYear()
	{
		return $this->year;
	}
}

$home = new Home();

if($_GET['w'] && $_GET['y']) $home->setWeek($_GET['w'], $_GET['y']);
// Show complete week for kills and losses pages.
if(isset($_REQUEST['losses']) || isset($_REQUEST['kills'])) $home->prevweek=true;;
if(isset($_REQUEST['kills'])) $page = new Page('Kills - Week '.$home->getWeek().', '.$home->getYear());
elseif(isset($_REQUEST['losses'])) $page = new Page('Losses - Week '.$home->getWeek().', '.$home->getYear());
else $page = new Page('Week '.$home->getWeek().', '.$home->getYear());
$page->setContent($home->generateContent());
$page->addContext($home->generateContext());
$page->generate();
?>