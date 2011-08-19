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
class pKillRelated extends pageAssembly
{
	function __construct()
	{
		parent::__construct();
		$this->queue("start");
		$this->queue("getInvolved");
		$this->queue("buildStats");
		$this->queue("summaryTable");
		$this->queue("overview");
		$this->queue("battleStats");
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
		$this->page = new Page('Related kills & losses');
		$this->page->addHeader('<meta name="robots" content="index, nofollow" />');

		$this->kll_id = (int)edkURI::getArg('kll_id', 1);
		if (!$this->kll_id) {
			$this->kll_external_id = (int)edkURI::getArg('kll_ext_id');
			if (!$this->kll_external_id) {
				// internal and external ids easily overlap so we can't guess which
				$this->kll_id = (int)edkURI::getArg(null, 1);
			} else {
				$this->kill = new Kill($this->kll_external_id, true);
				$this->kll_id = $this->kill->getID();
			}
		} else {
			$this->kill = Cacheable::factory('Kill', $this->kll_id);
		}
		$this->adjacent = edkURI::getArg('adjacent');
		$this->scl_id = (int)edkURI::getArg('scl_id');

		$this->menuOptions = array();

		if (!$this->kll_id || !$this->kill->exists()) {
			echo 'No valid kill id specified';
			exit;
		}
		if($this->kill->isClassified()) {
			Header("Location: ".KB_HOST."/?a=kill_detail&kll_id=".$this->kll_id);
			die();
		}
//		$this->getInvolved();
//		$this->buildStats();
	}
	public function getInvolved()
	{
		$this->victimAll = array();
		$this->invAll = array();
		$this->victimCorp = array();
		$this->invCorp = array();
		// Find all involved parties not in the same corp/alliance as the victim. If
		// the board has an owner swap sides if necessary so board owner is the killer
		foreach ($this->kill->getInvolved() as $inv)
		{
			if(strcasecmp($inv->getAlliance()->getName(),'None'))
			{
				if($inv->getAllianceID() != $this->kill->getVictimAllianceID())
				{
					$this->invAll[$inv->getAllianceID()] = $inv->getAllianceID();
				}
			}
			elseif($inv->getCorpID() != $this->kill->getVictimCorpID())
				$this->invCorp[$inv->getCorpID()] = $inv->getCorpID();

		}
		if(strcasecmp($this->kill->getVictimAllianceName(), 'None' ))
			$this->victimAll[$this->kill->getVictimAllianceID()] = $this->kill->getVictimAllianceID();
		else $this->victimCorp[$this->kill->getVictimCorpID()] = $this->kill->getVictimCorpID();

		// Check which side board owner is on and make that the kill side. The other
		// side is the loss side. If board owner is on neither then victim is the loss
		// side.
		if(in_array($this->kill->getVictimAllianceID(), config::get('cfg_allianceid'))
			|| in_array($this->kill->getVictimCorpID(), config::get('cfg_corpid')))
		{
			$tmp = $this->victimAll;
			$this->victimAll = $this->invAll;
			$this->invAll = $tmp;
			$tmp = $this->victimCorp;
			$this->victimCorp = $this->invCorp;
			$this->invCorp = $tmp;
		}
	}
	public function buildStats()
	{
		// this is a fast query to get the system and timestamp
		$rqry = DBFactory::getDBQuery();
		if($this->adjacent)
			$rsql = 'SELECT kll_timestamp, b.sys_id from kb3_kills
			join kb3_systems a ON (a.sys_id = kll_system_id)
			join kb3_system_jumps on (sjp_from = a.sys_eve_id)
			join kb3_systems b ON (b.sys_eve_id = sjp_to)
			where kll_id = '.$this->kll_id.' UNION
			SELECT kll_timestamp, kll_system_id as sys_id from kb3_kills
			where kll_id = '.$this->kll_id;
		else
			$rsql = 'SELECT kll_timestamp, kll_system_id as sys_id from kb3_kills
			where kll_id = '.$this->kll_id;
		$rqry->execute($rsql);
		while($rrow = $rqry->getRow())
		{
			$this->systems[] = $rrow['sys_id'];
			$basetime = $rrow['kll_timestamp'];
		}

				// now we get all kills in that system for +-4 hours
		$query = 'SELECT kll.kll_timestamp AS ts FROM kb3_kills kll WHERE kll.kll_system_id IN ('.implode(',', $this->systems).
					') AND kll.kll_timestamp <= "'.(date('Y-m-d H:i:s',strtotime($basetime) +  4 * 60 * 60)).'"'.
					' AND kll.kll_timestamp >= "'.(date('Y-m-d H:i:s',strtotime($basetime) -  4 * 60 * 60)).'"'.
					' ORDER BY kll.kll_timestamp ASC';
		$qry = DBFactory::getDBQuery();
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
		$this->firstts = array_shift($times);
		$this->lastts = array_pop($times);

		$this->kslist = new KillList();
		$this->kslist->setOrdered(true);
		foreach($this->systems as $system) $this->kslist->addSystem($system);
		$this->kslist->setStartDate($this->firstts);
		$this->kslist->setEndDate($this->lastts);
		//involved::load($this->kslist,'kill');
		foreach($this->invCorp as $ic) $this->kslist->addInvolvedCorp($ic);
		foreach($this->invAll as $ia) $this->kslist->addInvolvedAlliance($ia);

		$this->lslist = new KillList();
		$this->lslist->setOrdered(true);
		foreach($this->systems as $system) $this->lslist->addSystem($system);
		$this->lslist->setStartDate($this->firstts);
		$this->lslist->setEndDate($this->lastts);
		//involved::load($this->lslist,'loss');
		foreach($this->invCorp as $ic) $this->lslist->addVictimCorp($ic);
		foreach($this->invAll as $ia) $this->lslist->addVictimAlliance($ia);

		$this->klist = new KillList();
		$this->klist->setOrdered(true);
		$this->klist->setCountComments(true);
		$this->klist->setCountInvolved(true);
		foreach($this->systems as $system) $this->klist->addSystem($system);
		$this->klist->setStartDate($this->firstts);
		$this->klist->setEndDate($this->lastts);
		//involved::load($this->klist,'kill');
		foreach($this->invCorp as $ic) $this->klist->addInvolvedCorp($ic);
		foreach($this->invAll as $ia) $this->klist->addInvolvedAlliance($ia);

		$this->llist = new KillList();
		$this->llist->setOrdered(true);
		$this->llist->setCountComments(true);
		$this->llist->setCountInvolved(true);
		foreach($this->systems as $system) $this->llist->addSystem($system);
		$this->llist->setStartDate($this->firstts);
		$this->llist->setEndDate($this->lastts);
		//involved::load($this->llist,'loss');
		foreach($this->invCorp as $ic) $this->llist->addVictimCorp($ic);
		foreach($this->invAll as $ia) $this->llist->addVictimAlliance($ia);

		if ($this->scl_id)
		{
			$this->klist->addVictimShipClass($this->scl_id);
			$this->llist->addVictimShipClass($this->scl_id);
		}

		$this->destroyed = $this->pods = array();
		$this->pilots = array('a' => array(), 'e' => array());
		$this->kslist->rewind();
		$classified = false;
		while ($this->kill = $this->kslist->getKill())
		{
			if (in_array($this->kill->getVictimAllianceID(), $this->invAll)
					 || in_array($this->kill->getVictimCorpID(), $this->invCorp))
			{
				$this->handle_involved($this->kill, 'e');
				$this->handle_destroyed($this->kill, 'a');
			}
			else
			{
				$this->handle_involved($this->kill, 'a');
				$this->handle_destroyed($this->kill, 'e');
			}
			if ($this->kill->isClassified())
			{
				$classified = true;
			}
		}
		$this->lslist->rewind();
		while ($this->kill = $this->lslist->getKill())
		{
			if (in_array($this->kill->getVictimAllianceID(), $this->victimAll)
					 || in_array($this->kill->getVictimCorpID(), $this->victimCorp))
			{
				$this->handle_involved($this->kill, 'a');
				$this->handle_destroyed($this->kill, 'e');
			}
			else
			{
				$this->handle_involved($this->kill, 'e');
				$this->handle_destroyed($this->kill, 'a');
			}
			if ($this->kill->isClassified())
			{
				$classified = true;
			}
		}

		// sort pilot ships, order pods after ships
		foreach ($this->pilots as $side => $pilot)
		{
			foreach ($pilot as $id => $kll)
			{
				usort($this->pilots[$side][$id], array($this,'cmp_ts_func'));
			}
		}

		// sort arrays, ships with high points first
		uasort($this->pilots['a'], array($this,'cmp_func'));
		uasort($this->pilots['e'], array($this,'cmp_func'));

		// now get the pods out and mark the ships the've flown as podded
		foreach ($this->pilots as $side => $pilot)
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
							$this->pilots[$side][$id][$i-1]['podded'] = true;
							$this->pilots[$side][$id][$i-1]['podid'] = $kll[$i]['kll_id'];
							unset($this->pilots[$side][$id][$i]);
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
								unset($this->pilots[$side][$id][$i]);
							}
						}
					}
				}
			}
		}
	}
	public function overview()
	{
		global $smarty;
		$smarty->assignByRef('pilots_a', $this->pilots['a']);
		$smarty->assignByRef('pilots_e', $this->pilots['e']);

		$pod = new Ship(6, 670);
		$smarty->assign('podpic', $pod->getImage(32));
		$smarty->assign('friendlycnt', count($this->pilots['a']));
		$smarty->assign('hostilecnt', count($this->pilots['e']));
		if ($classified)
		{
			$smarty->assign('system', 'Classified System');
		}
		else
		{
			$this->kill = new Kill($this->kll_id);
			if($this->adjacent) $smarty->assign('system', $this->kill->getSolarSystemName());
			else
			{
				$sysnames = array();
				foreach($this->systems as $sys_id)
				{
					$system = new SolarSystem($sys_id);
					$sysnames[] = $system->getName();
				}
				$smarty->assign('system', implode(', ', $sysnames));
			}
		}
		$smarty->assign('firstts', $this->firstts);
		$smarty->assign('lastts', $this->lastts);

		return $smarty->fetch(get_tpl('kill_related_battle_overview'));
	}
	public function battleStats()
	{
		global $smarty;

		$this->kill_summary = new KillSummaryTable($this->klist, $this->llist);
		$this->kill_summary->generate();
		$stats['kills'] = $this->kill_summary->getTotalKills();
		$stats['losses'] = $this->kill_summary->getTotalLosses();
		$stats['killISKM'] = round($this->kill_summary->getTotalKillISK()/1000000, 2);
		$stats['lossISKM'] = round($this->kill_summary->getTotalLossISK()/1000000, 2);
		$stats['killISKB'] = round($stats['killISKM']/1000, 2);
		$stats['lossISKB'] = round($stats['lossISKM']/1000, 2);
		if ($this->kill_summary->getTotalKillISK())
		{
			$stats['efficiency'] = round($this->kill_summary->getTotalKillISK() / ($this->kill_summary->getTotalKillISK() + $this->kill_summary->getTotalLossISK()) * 100, 2);
		}
		else
		{
			$stats['efficiency'] = 0;
		}
		$smarty->assignByRef('stats', $stats);

		if ($this->kill_summary->getTotalKillISK())
		{
			$efficiency = round($this->kill_summary->getTotalKillISK() / ($this->kill_summary->getTotalKillISK() + $this->kill_summary->getTotalLossISK()) * 100, 2);
		}
		else
		{
			$efficiency = 0;
		}
		return $smarty->fetch(get_tpl('kill_related_battle_stats'));
	}
	public function summaryTable()
	{
		$this->kslist->rewind();
		$this->lslist->rewind();
		$summarytable = new KillSummaryTable($this->kslist, $this->lslist);
		return $summarytable->generate();
	}
	public function killList()
	{
		$html = '<div class="kb-kills-header">Related kills</div>';

		$ktable = new KillListTable($this->klist);
		$html .= $ktable->generate();
		$html .= '<div class="kb-losses-header">Related losses</div>';

		$ltable = new KillListTable($this->llist);
		$html .= $ltable->generate();

		return $html;
	}

	private function cmp_func($a, $b)
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

	private function is_destroyed($pilot)
	{
		if ($result = array_search((string)$pilot, $this->destroyed))
		{
			global $smarty;

			$smarty->assign('kll_id', $result);
			return true;
		}
		return false;
	}

	private function podded($pilot)
	{
		if ($result = array_search((string)$pilot, $this->pods))
		{
			global $smarty;

			$smarty->assign('pod_kll_id', $result);
			return true;
		}
		return false;
	}

	private function cmp_ts_func($a, $b)
	{
		if ($a['ts'] < $b['ts'])
		{
			return -1;
		}
		return 1;
	}
	private function handle_involved($kill, $side)
	{
		// we need to get all involved pilots, killlists dont supply them
		$qry = DBFactory::getDBQuery();
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
			if (isset($this->pilots[$side][$row['ind_plt_id']]))
			{
				if ($row['scl_id'] == 18 || $row['scl_id'] == 2)
				{
					continue;
				}
			}

			// search for ships with the same id
			if (isset($this->pilots[$side][$row['ind_plt_id']]))
			{
				foreach ($this->pilots[$side][$row['ind_plt_id']] as $id => $_ship)
				{
					if ($row['ind_shp_id'] == $_ship['sid'])
					{
						// we already got that pilot in this ship, continue
						continue 2;
					}
				}
			}
			$shipimage = imageURL::getURL('Ship', $row['shp_externalid'], 32);
			$this->pilots[$side][$row['ind_plt_id']][] = array('name' => $row['plt_name'], 'sid' => $row['ind_shp_id'],
				   'spic' => $shipimage, 'aid' => $row['ind_all_id'], 'ts' => strtotime($kill->getTimeStamp()),
				   'corp' =>$row['crp_name'], 'alliance' => $row['all_name'], 'scl' => $row['scl_points'],
				   'ship' => $row['shp_name'], 'weapon' => $row['itm_name'], 'cid' => $row['ind_crp_id'],
					'shpclass' => $row['scl_id']);
		}
	}

	/**
	 * @param Kill $kill
	 * @param string $side a,e for ally, enemy
	 */
	private function handle_destroyed($kill, $side)
	{
		$this->destroyed[$kill->getID()] = $kill->getVictimID();

		$ship = $kill->getVictimShip();
		$shipc = $ship->getClass();

		$ts = strtotime($kill->getTimeStamp());

		// mark the pilot as podded
		if ($shipc->getID() == 18 || $shipc->getID() == 2)
		{
			// increase the timestamp of a podkill by 1 so its after the shipkill
			$ts++;
			$this->pods[$kill->getID()] = $kill->getVictimID();

			// return when we've added him already
			if (isset($this->pilots[$side][$kill->getVictimId()]))
			{
				#return;
			}
		}

		// search for ships with the same id
		if (isset($this->pilots[$side][$kill->getVictimId()]))
		{
			foreach ($this->pilots[$side][$kill->getVictimId()] as $id => $_ship)
			{
				if ($ship->getID() == $_ship['sid'])
				{
					$this->pilots[$side][$kill->getVictimId()][$id]['destroyed'] = true;

					if (!isset($this->pilots[$side][$kill->getVictimId()][$id]['kll_id']))
					{
						$this->pilots[$side][$kill->getVictimId()][$id]['kll_id'] = $kill->getID();
					}
					return;
				}
			}
		}

		$this->pilots[$side][$kill->getVictimId()][] = array('name' => $kill->getVictimName(), 'kll_id' => $kill->getID(),
			   'spic' => $ship->getImage(32), 'scl' => $shipc->getPoints(), 'destroyed' => true,
			   'corp' => $kill->getVictimCorpName(), 'alliance' => $kill->getVictimAllianceName(), 'aid' => $kill->getVictimAllianceID(),
			   'ship' => $kill->getVictimShipname(), 'sid' => $ship->getID(), 'cid' => $kill->getVictimCorpID(), 'ts' => $ts);
}
	public function menuSetup()
	{
		$this->addMenuItem("caption", "View");
		if($this->adjacent) {
			$this->addMenuItem("link", "Include adjacent",
					edkURI::build(array('kll_id', $this->kll_id, true),
					array('adjacent', true, true)));
		} else {
			$this->addMenuItem("link", "Include adjacent",
					edkURI::build(array('kll_id', $this->kll_id, true)));
		}
		$this->addMenuItem("link", "Back to Killmail",
					edkURI::build(array('a', 'kill_detail', true),
							array('kll_id', $this->kll_id, true)));
	}
	public function menu()
	{
		$menubox=new Box("Menu");
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

}

$killRelated = new pKillRelated();
event::call("killRelated_assembling", $killRelated);
$html = $killRelated->assemble();
$killRelated->page->setContent($html);

$killRelated->context();
event::call("killRelated_context_assembling", $killRelated);
$context = $killRelated->assemble();
$killRelated->page->addContext($context);

$killRelated->page->generate();
