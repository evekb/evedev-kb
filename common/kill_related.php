<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');

$page = new Page('Related kills & losses');
$page->addHeader('<meta name="robots" content="index, nofollow" />');

$scl_id = intval($_GET['scl_id']);
if (!$kll_id = intval($_GET['kll_id']))
{
    echo 'No valid kill id specified';
    exit;
}
$kill = new Kill($kll_id);
$kill->setDetailedInvolved();
if(!$kill->exists())
{
    echo 'No valid kill id specified';
    exit;
}
if($kill->isClassified())
{
	Header("Location: ".KB_HOST."/?a=kill_detail&kll_id=".$kll_id);
	die();
}
$victimAll = array();
$invAll = array();
$victimCorp = array();
$invCorp = array();
// Find all involved parties not in the same corp/alliance as the victim. If
// the board has an owner swap sides if necessary so board owner is the killer
foreach ($kill->involvedparties_ as $inv)
{
	if($inv->getAlliance()->getName() != 'None'
            && $inv->getAllianceID() != $kill->getVictimAllianceID())
                $invAll[$inv->getAllianceID()] = $inv->getAllianceID();
	elseif($inv->getCorpID() != $kill->getVictimCorpID())
            $invCorp[$inv->getCorpID()] = $inv->getCorpID();
}
if($kill->getVictimAllianceName() != 'None' )
	$victimAll[$kill->getVictimAllianceID()] = $kill->getVictimAllianceID();
else $victimCorp[$kill->getVictimCorpID()] = $kill->getVictimCorpID();

if(CORP_ID == $kill->getVictimCorpID() || ALLIANCE_ID == $kill->getVictimAllianceID())
{
	$tmp = $victimAll;
	$victimAll = $invAll;
	$invAll = $tmp;
	$tmp = $victimCorp;
	$victimCorp = $invCorp;
	$invCorp = $tmp;
}

// Check which side board owner is on and make that the kill side. The other
// side is the loss side. If board owner is on neither then victim is the loss
// side.
// Check if killlist works like this.
//
// Profit


// this is a fast query to get the system and timestamp
$rqry = new DBQuery();
if(isset($_GET['adjacent']))
	$rsql = 'SELECT kll_timestamp, b.sys_id from kb3_kills
	join kb3_systems a ON (a.sys_id = kll_system_id)
	join kb3_system_jumps on (sjp_from = a.sys_eve_id)
	join kb3_systems b ON (b.sys_eve_id = sjp_to)
	where kll_id = '.$kll_id.' UNION
	SELECT kll_timestamp, kll_system_id as sys_id from kb3_kills
	where kll_id = '.$kll_id;
else
	$rsql = 'SELECT kll_timestamp, kll_system_id as sys_id from kb3_kills
	where kll_id = '.$kll_id;
$rqry->execute($rsql);
while($rrow = $rqry->getRow())
{
	$systems[] = $rrow['sys_id'];
	$basetime = $rrow['kll_timestamp'];
}

        // now we get all kills in that system for +-4 hours
$query = 'SELECT kll.kll_timestamp AS ts FROM kb3_kills kll WHERE kll.kll_system_id IN ('.implode(',', $systems).
            ') AND kll.kll_timestamp <= "'.(date('Y-m-d H:i:s',strtotime($basetime) +  4 * 60 * 60)).'"'.
            ' AND kll.kll_timestamp >= "'.(date('Y-m-d H:i:s',strtotime($basetime) -  4 * 60 * 60)).'"'.
            ' ORDER BY kll.kll_timestamp ASC';
$qry = new DBQuery();
$qry->execute($query);
$ts = array();
while ($row = $qry->getRow())
{
    $time = strtotime($row['ts']);
    $ts[intval(date('H', $time))][] = $row['ts'];
}

// this tricky thing looks for gaps of more than 1 hour and creates an intersection
$baseh = date('H', strtotime($basetime));
$maxc = count($ts);
$times = array();
for ($i = 0; $i < $maxc; $i++)
{
    $h = ($baseh+$i) % 24;
    if (!isset($ts[$h]))
    {
        break;
    }
    foreach ($ts[$h] as $timestamp)
    {
        $times[] = $timestamp;
    }
}
for ($i = 0; $i < $maxc; $i++)
{
    $h = ($baseh-$i) % 24;
    if ($h < 0)
    {
        $h += 24;
    }
    if (!isset($ts[$h]))
    {
        break;
    }
    foreach ($ts[$h] as $timestamp)
    {
        $times[] = $timestamp;
    }
}
unset($ts);
asort($times);

// we got 2 resulting timestamps
$firstts = array_shift($times);
$lastts = array_pop($times);

$kslist = new KillList();
$kslist->setOrdered(true);
foreach($systems as $system) $kslist->addSystem($system);
$kslist->setStartDate($firstts);
$kslist->setEndDate($lastts);
//involved::load($kslist,'kill');
foreach($invCorp as $ic) $kslist->addInvolvedCorp($ic);
foreach($invAll as $ia) $kslist->addInvolvedAlliance($ia);

$lslist = new KillList();
$lslist->setOrdered(true);
foreach($systems as $system) $lslist->addSystem($system);
$lslist->setStartDate($firstts);
$lslist->setEndDate($lastts);
//involved::load($lslist,'loss');
foreach($invCorp as $ic) $lslist->addVictimCorp($ic);
foreach($invAll as $ia) $lslist->addVictimAlliance($ia);

$summarytable = new KillSummaryTable($kslist, $lslist);
$smarty->assign('summarytable', $summarytable->generate());

$klist = new KillList();
$klist->setOrdered(true);
$klist->setCountComments(true);
$klist->setCountInvolved(true);
foreach($systems as $system) $klist->addSystem($system);
$klist->setStartDate($firstts);
$klist->setEndDate($lastts);
//involved::load($klist,'kill');
foreach($invCorp as $ic) $klist->addInvolvedCorp($ic);
foreach($invAll as $ia) $klist->addInvolvedAlliance($ia);

$llist = new KillList();
$llist->setOrdered(true);
$llist->setCountComments(true);
$llist->setCountInvolved(true);
foreach($systems as $system) $llist->addSystem($system);
$llist->setStartDate($firstts);
$llist->setEndDate($lastts);
//involved::load($llist,'loss');
foreach($invCorp as $ic) $llist->addVictimCorp($ic);
foreach($invAll as $ia) $llist->addVictimAlliance($ia);

if ($scl_id)
{
    $klist->addVictimShipClass($scl_id);
    $llist->addVictimShipClass($scl_id);
}

function handle_involved($kill, $side)
{
    global $pilots;

    // we need to get all involved pilots, killlists dont supply them
    $qry = new DBQuery();
    $sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status, ind_shp_id, ind_wep_id,
            typeName, plt_name, crp_name, all_name, shp_name, scl_points, scl_id, shp_externalid
            from kb3_inv_detail
            left join kb3_invtypes on ind_wep_id=typeID
            left join kb3_pilots on ind_plt_id=plt_id
            left join kb3_corps on ind_crp_id=crp_id
            left join kb3_alliances on ind_all_id=all_id
            left join kb3_ships on ind_shp_id=shp_id
            left join kb3_ship_classes on shp_class=scl_id
            where ind_kll_id = ".$kill->getID()."
            order by ind_order";

    $qry->execute($sql);
    while ($row = $qry->getRow())
    {
        //$ship = new Ship($row['ind_shp_id']);
        //$shipc = $ship->getClass();

        // check for npc names (copied from pilot class)
        $pos = strpos($row['plt_name'], "#");
        if ($pos !== false)
        {
            $name = explode("#", $row['plt_name']);
            $item = new Item($name[2]);
            $row['plt_name'] = $item->getName();
        }


        // dont set pods as ships for pilots we already have
        if (isset($pilots[$side][$row['ind_plt_id']]))
        {
            if ($row['scl_id'] == 18 || $row['scl_id'] == 2)
            {
                continue;
            }
        }

        // search for ships with the same id
        if (isset($pilots[$side][$row['ind_plt_id']]))
        {
            foreach ($pilots[$side][$row['ind_plt_id']] as $id => $_ship)
            {
                if ($row['ind_shp_id'] == $_ship['sid'])
                {
                    // we already got that pilot in this ship, continue
                    continue 2;
                }
            }
        }
/*
		// Replace pods and unknowns
		if(isset($pilots[$side][$row['ind_plt_id']]))
		{
			foreach ($pilots[$side][$row['ind_plt_id']] as $id => &$_ship)
            {
                if ($_ship['shpclass'] == 18 || $_ship['shpclass'] == 2)
				{
					$shipimage = IMG_URL.'/ships/32_32/'.$row['shp_externalid'].'.png';
					$_ship['sid'] = $row['ind_shp_id'];
					$_ship['spic'] = $shipimage;
					$_ship['ts'] = strtotime($kill->getTimeStamp());
					$_ship['scl'] = $row['scl_points'];
					$_ship['ship'] = $row['shp_name'];
					$_ship['weapon'] = $row['itm_name'];
					$_ship['shpclass'] = $row['scl_id'];
					continue 2;
				}
			}
		}
 */
        $shipimage = IMG_URL.'/ships/32_32/'.$row['shp_externalid'].'.png';
        $pilots[$side][$row['ind_plt_id']][] = array('name' => $row['plt_name'], 'sid' => $row['ind_shp_id'],
               'spic' => $shipimage, 'aid' => $row['ind_all_id'], 'ts' => strtotime($kill->getTimeStamp()),
               'corp' =>$row['crp_name'], 'alliance' => $row['all_name'], 'scl' => $row['scl_points'],
               'ship' => $row['shp_name'], 'weapon' => $row['itm_name'], 'cid' => $row['ind_crp_id'],
				'shpclass' => $row['scl_id']);
    }
}

function handle_destroyed($kill, $side)
{
    global $destroyed, $pilots;

    $destroyed[$kill->getID()] = $kill->getVictimID();

    $ship = new Ship();
    $ship->lookup($kill->getVictimShipName());
    $shipc = $ship->getClass();

    $ts = strtotime($kill->getTimeStamp());

    // mark the pilot as podded
    if ($shipc->getID() == 18 || $shipc->getID() == 2)
    {
        // increase the timestamp of a podkill by 1 so its after the shipkill
        $ts++;
        global $pods;
        $pods[$kill->getID()] = $kill->getVictimID();

        // return when we've added him already
        if (isset($pilots[$side][$kill->getVictimId()]))
        {
            #return;
        }
    }

    // search for ships with the same id
    if (isset($pilots[$side][$kill->getVictimId()]))
    {
        foreach ($pilots[$side][$kill->getVictimId()] as $id => $_ship)
        {
            if ($ship->getID() == $_ship['sid'])
            {
                $pilots[$side][$kill->getVictimId()][$id]['destroyed'] = true;

                if (!isset($pilots[$side][$kill->getVictimId()][$id]['kll_id']))
                {
                    $pilots[$side][$kill->getVictimId()][$id]['kll_id'] = $kill->getID();
                }
                return;
            }
        }
    }

    $pilots[$side][$kill->getVictimId()][] = array('name' => $kill->getVictimName(), 'kll_id' => $kill->getID(),
           'spic' => $ship->getImage(32), 'scl' => $shipc->getPoints(), 'destroyed' => true,
           'corp' => $kill->getVictimCorpName(), 'alliance' => $kill->getVictimAllianceName(), 'aid' => $kill->getVictimAllianceID(),
           'ship' => $kill->getVictimShipname(), 'sid' => $ship->getID(), 'cid' => $kill->getVictimCorpID(), 'ts' => $ts);
}

$destroyed = $pods = array();
$pilots = array('a' => array(), 'e' => array());
$kslist->rewind();
$classified = false;
while ($kill = $kslist->getKill())
{
    handle_involved($kill, 'a');
    handle_destroyed($kill, 'e');
    if ($kill->isClassified())
    {
        $classified = true;
    }
}
$lslist->rewind();
while ($kill = $lslist->getKill())
{
    handle_involved($kill, 'e');
    handle_destroyed($kill, 'a');
    if ($kill->isClassified())
    {
        $classified = true;
    }
}
function cmp_func($a, $b)
{
    // select the biggest fish of that pilot
    $t_scl = 0;
    foreach ($a as $i => $ai)
    {
        if ($ai['scl'] > $t_scl)
        {
            $t_scl = $ai['scl'];
            $cur_i = $i;
        }
    }
    $a = $a[$cur_i];

    $t_scl = 0;
    foreach ($b as $i => $bi)
    {
        if ($bi['scl'] > $t_scl)
        {
            $t_scl = $bi['scl'];
            $cur_i = $i;
        }
    }
    $b = $b[$cur_i];

    if ($a['scl'] > $b['scl'])
    {
        return -1;
    }
    // sort after points, shipname, pilotname
    elseif ($a['scl'] == $b['scl'])
    {
        if ($a['ship'] == $b['ship'])
        {
            if ($a['name'] > $b['name'])
            {
                return 1;
            }
            return -1;
        }
        elseif ($a['ship'] > $b['ship'])
        {
            return 1;
        }
        return -1;
    }
    return 1;
}

function is_destroyed($pilot)
{
    global $destroyed;

    if ($result = array_search((string)$pilot, $destroyed))
    {
        global $smarty;

        $smarty->assign('kll_id', $result);
        return true;
    }
    return false;
}

function podded($pilot)
{
    global $pods;

    if ($result = array_search((string)$pilot, $pods))
    {
        global $smarty;

        $smarty->assign('pod_kll_id', $result);
        return true;
    }
    return false;
}

function cmp_ts_func($a, $b)
{
    if ($a['ts'] < $b['ts'])
    {
        return -1;
    }
    return 1;
}

// sort pilot ships, order pods after ships
foreach ($pilots as $side => $pilot)
{
    foreach ($pilot as $id => $kll)
    {
        usort($pilots[$side][$id], 'cmp_ts_func');
    }
}

// sort arrays, ships with high points first
uasort($pilots['a'], 'cmp_func');
uasort($pilots['e'], 'cmp_func');

// now get the pods out and mark the ships the've flown as podded
foreach ($pilots as $side => $pilot)
{
    foreach ($pilot as $id => $kll)
    {
        $max = count($kll);
        for ($i = 0; $i < $max; $i++)
        {
            if ($kll[$i]['ship'] == 'Capsule')
            {
                if (isset($kll[$i-1]['sid']) && isset($kll[$i]['destroyed']))
                {
                    $pilots[$side][$id][$i-1]['podded'] = true;
                    $pilots[$side][$id][$i-1]['podid'] = $kll[$i]['kll_id'];
                    unset($pilots[$side][$id][$i]);
                }
                else
                {
                    // now sort out all pods from pilots who previously flown a real ship
                    $valid_ship = false;
                    foreach ($kll as $ship)
                    {
                        if ($ship['ship'] != 'Capsule')
                        {
                            $valid_ship = true;
                            break;
                        }
                    }
                    if ($valid_ship)
                    {
                        unset($pilots[$side][$id][$i]);
                    }
                }
            }
        }
    }
}

$smarty->assign_by_ref('pilots_a', $pilots['a']);
$smarty->assign_by_ref('pilots_e', $pilots['e']);

$pod = new Ship(6);
$smarty->assign('podpic', $pod->getImage(32));
$smarty->assign('friendlycnt', count($pilots['a']));
$smarty->assign('hostilecnt', count($pilots['e']));
if ($classified)
{
    $smarty->assign('system', 'Classified System');
}
else
{
	$kill = new Kill($kll_id);
	if(!isset($_GET['adjacent'])) $smarty->assign('system', $kill->getSolarSystemName());
	else
	{
		$sysnames = array();
		foreach($systems as $sys_id)
		{
			$system = new SolarSystem($sys_id);
			$sysnames[] = $system->getName();
		}
		$smarty->assign('system', implode(', ', $sysnames));
	}
}
$smarty->assign('firstts', $firstts);
$smarty->assign('lastts', $lastts);

$smarty->assign('overview', $smarty->fetch(get_tpl('battle_overview')));

$kill_summary = new KillSummaryTable($klist, $llist);
$kill_summary->generate();
$stats['kills'] = $kill_summary->getTotalKills();
$stats['losses'] = $kill_summary->getTotalLosses();
$stats['killISKM'] = round($kill_summary->getTotalKillISK()/1000000, 2);
$stats['lossISKM'] = round($kill_summary->getTotalLossISK()/1000000, 2);
$stats['killISKB'] = round($stats['killISKM']/1000, 2);
$stats['lossISKB'] = round($stats['lossISKM']/1000, 2);
if ($kill_summary->getTotalKillISK())
{
    $stats['efficiency'] = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
}
else
{
    $stats['efficiency'] = 0;
}
$smarty->assign_by_ref('stats', $stats);

if ($kill_summary->getTotalKillISK())
{
    $efficiency = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
}
else
{
    $efficiency = 0;
}

$ktable = new KillListTable($klist);
$smarty->assign('kills', $ktable->generate());

$ltable = new KillListTable($llist);
$smarty->assign('losses', $ltable->generate());

$menubox = new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "View");
if(!isset($_GET['adjacent'])) $menubox->addOption("link", "Include adjacent", "?a=kill_related&amp;adjacent&amp;kll_id=".$kll_id);
else $menubox->addOption("link", "Remove adjacent", "?a=kill_related&amp;kll_id=".$kll_id);
$menubox->addOption("link", "Back to Killmail", "?a=kill_detail&amp;kll_id=".$kll_id);
$menubox->addOption("link", "Kills &amp; losses", "?a=kill_related&amp;kll_id=".$kll_id);
$page->addContext($menubox->generate());

//$page->setContent($html);
$page->setContent($smarty->fetch(get_tpl('kill_related')));
$page->generate();
