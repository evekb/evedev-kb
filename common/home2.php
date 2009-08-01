<?php
require_once('common/includes/class.killsummarytable.public.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.contract.php');
require_once('common/includes/class.toplist.php');
require_once('common/includes/class.pageAssembly.php');

class pHome extends pageAssembly
{
    function __construct()
    {
        parent::__construct();
        $this->queue('start');
        $this->queue('summarytable');
        $this->queue('campaigns');
        $this->queue('contracts');
        $this->queue('kills');
        $this->queue('context');
    }

    function start()
    {
        $this->week = kbdate('W');
        $this->year = kbdate('o');

        $this->page = new Page('Week ' . $this->week);
    }

    function summarytable()
    {
        if (config::get('summarytable'))
        {
            $kslist = new KillList();
            involved::load($kslist, 'kill');
            $kslist->setWeek($this->week);
            $kslist->setYear($this->year);

            if (config::get('public_summarytable'))
            {
                $summarytable = new KillSummaryTablePublic($kslist);
            }
            else
            {
                $llist = new KillList();
                involved::load($llist, 'loss');
                $llist->setWeek($this->week);
                $llist->setYear($this->year);
                $summarytable = new KillSummaryTable($kslist, $llist);
            }
            $summarytable->setBreak(config::get('summarytable_rowcount'));
            return $summarytable->generate();
        }
    }

    function campaigns()
    {
        if ($this->page->killboard_->hasCampaigns(true))
        {
            $html .= '<div class=kb-campaigns-header>Active campaigns</div>';
            $list = new ContractList();
            $list->setActive('yes');
            $list->setCampaigns(true);
            $table = new ContractListTable($list);
            $html .= $table->generate();
            return $html;
        }
    }

    function contracts()
    {
        if ($this->page->killboard_->hasContracts(true))
        {
            $html .= '<div class=kb-campaigns-header>Active contracts</div>';
            $list = new ContractList();
            $list->setActive('yes');
            $list->setCampaigns(false);
            $table = new ContractListTable($list);
            $html .= $table->generate();
            return $html;
        }
    }

    function kills()
    {
        // bad hax0ring, we really need mod callback stuff
        if (strpos(config::get('mods_active'), 'rss_feed') !== false)
        {
            $html .= "<div class=kb-kills-header><a href=\"?a=rss\"><img src=\"mods/rss_feed/rss_icon.png\" alt=\"RSS-Feed\" border=\"0\"></a>&nbsp;20 most recent kills</div>";
        }
        else
        {
            $html .= '<div class=kb-kills-header>20 most recent kills</div>';
        }

        $klist = new KillList();
        $klist->setOrdered(true);
        involved::load($klist, 'kill');

        // boards with low killcount could not display 20 kills with those limits
        // $klist->setStartWeek($week - 1);
        // $klist->setYear($year);
        $klist->setLimit(config::get('killcount'));

        if ($_GET['scl_id'])
            $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
        else
            $klist->setPodsNoobShips(false);

        $table = new KillListTable($klist);
        $table->setLimit(config::get('killcount'));
        $html .= $table->generate();

        return $html;
    }

    function context()
    {
        if ($this->week == 1)
        {
            $pyear = kbdate("o") - 1;
            $pweek = 52;
        }
        else
        {
            $pyear = kbdate("o");
            $pweek = $week - 1;
        }

        $menubox = new box("Menu");
        $menubox->setIcon("menu-item.gif");
        $menubox->addOption("caption", "Navigation");
        $menubox->addOption("link", "Previous week", "?a=kills&w=" . $pweek . "&y=" . $pyear);
        $this->page->addContext($menubox->generate());

        $tklist = new TopKillsList();
        $tklist->setWeek($week);
        $tklist->setYear($year);
        involved::load($tklist, 'kill');

        $tklist->generate();
        $tkbox = new AwardBox($tklist, "Top killers", "kills in week " . $this->week, "kills", "eagle");
        $this->page->addContext($tkbox->generate());

        if (config::get('kill_points'))
        {
            $tklist = new TopScoreList();
            $tklist->setWeek($week);
            $tklist->setYear($year);
            involved::load($tklist, 'kill');

            $tklist->generate();
            $tkbox = new AwardBox($tklist, "Top scorers", "points in week " . $this->week, "points", "redcross");
            $this->page->addContext($tkbox->generate());
        }
    }
}

$pageAssembly = new pHome();
$html = $pageAssembly->assemble();

$pageAssembly->page->setContent($html);
$pageAssembly->page->generate();