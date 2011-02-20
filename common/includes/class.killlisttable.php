<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class KillListTable
{
	function KillListTable($kill_list)
	{
		$this->limit = 0;
		$this->offset = 0;

		$this->kill_list_ = $kill_list;
		$this->daybreak_ = true;
	}

	function setDayBreak($daybreak)
	{
		$this->daybreak_ = $daybreak;
	}

	function setLimit($limit)
	{
		$this->limit_ = $limit;
	}

	function setCombined($combined = false)
	{
		$this->combined_ = $combined;
	}

	function generate()
	{
		global $smarty;
		$prevdate = "";
		$this->kill_list_->rewind();
		$smarty->assign('daybreak', $this->daybreak_);
		$smarty->assign('comments_count', config::get('comments_count'));

		// evil hardcode-hack, don't do this at home kids ! ;)
		if (config::get('style_name') == 'revelations')
		{
			$smarty->assign('comment_white', '_white');
		}

		$c = 0;
		while ($kill = $this->kill_list_->getKill())
		{
			if ($this->limit_ && $c >= $this->limit_)
			{
				break;
			}
			else
			{
				$c++;
			}

			$curdate = substr($kill->getTimeStamp(), 0, 10);
			if ($curdate != $prevdate)
			{
				if (count($kills) && $this->daybreak_)
				{
					$kl[] = array('kills' => $kills, 'date' => strtotime($prevdate));
					$kills = array();
				}
				$prevdate = $curdate;
			}
			$kll = array();
			$kll['id'] = $kill->getID();
			$kll['victimshipimage'] = $kill->getVictimShipImage(32);
			// Still needs a db query for every row. Add to Killlist and add
			// a get function in Kill?
			$kll['victimshipname'] = $kill->getVictimShipName();
			$kll['victimshipclass'] = $kill->getVictimShipClassName();
			$kll['victimshipindicator'] = $kill->getVictimShipValueIndicator();
			$kll['victim'] = $kill->getVictimName();
			$kll['victimcorp'] = $kill->getVictimCorpName();
			$kll['victimalliancename'] = $kill->getVictimAllianceName();
			$kll['victimiskloss'] = $kill->getISKLoss();
			$kll['fb'] = $kill->getFBPilotName();
			$kll['fbcorp'] = $kill->getFBCorpName();
			$kll['system'] = $kill->getSolarSystemName();
			if (config::get('killlist_regionnames'))
			{
				if ($kill->isClassified() && !Session::isAdmin())
					$kll['region'] = "Classified";
				else
					$kll['region'] = $kill->getSystem()->getRegionName();
			}
			$kll['systemsecurity'] = $kill->getSolarSystemSecurity();
			$kll['victimid'] = $kill->getVictimID();
			$kll['victimcorpid'] = $kill->getVictimCorpID();
			$kll['victimallianceid'] = $kill->getVictimAllianceID();
			$kll['victimshipid'] = $kill->getVictimShipExternalID();
			$kll['fbid'] = $kill->getFBPilotID();
			$kll['fbcorpid'] = $kill->getFBCorpID();
			if (config::get('killlist_involved')) $kll['inv'] = $kill->getInvolvedPartyCount();
			$kll['timestamp'] = $kill->getTimeStamp();
			if (config::get('killlist_alogo'))
			{
				// Need to return yet another value from killlists.
				$all = new Alliance($kill->getVictimAllianceID());
				if($all->getName()!="None")
				{
					$kll['allianceexists'] = true;
					$kll['victimallianceicon'] = $all->getPortraitURL(32);
				}
				else
				{
					$kll['allianceexists'] = true;
					$crp = new Corporation($kill->getVictimCorpID());
					$kll['victimallianceicon'] = $crp->getPortraitURL(32);
				}

//				$kll['victimallianceicon'] = preg_replace('/[^a-zA-Z0-9]/', '', $kll['victimalliancename']);
//				if(CacheHandler::exists($kll['victimallianceicon']."_32.png", 'img'))
//				{
//					$kll['allianceexists'] = true;
//					$kll['victimallianceicon'] = CacheHandler::getExternal($kll['victimallianceicon']."_32.png", 'img');
//				}
//				elseif(file_exists('img/alliances/'.$kll['victimallianceicon'].'.png'))
//				{
//					$kll['allianceexists'] = true;
//					$kll['victimallianceicon'] = '?a=thumb&amp;type=alliance&amp;id='.$kll['victimallianceicon'];
//				}
//				else $kll['allianceexists'] = false;
			}

			if (isset($kill->_tag))
			{
				$kll['tag'] = $kill->_tag;
			}

			if ($kill->fbplt_ext_)
			{
				$kll['fbplext'] = $kill->fbplt_ext_;
			}
			else
			{
				$kll['fbplext'] = null;
			}
			if ($kill->plt_ext_)
			{
				$kll['plext'] = $kill->plt_ext_;
			}
			else
			{
				$kll['plext'] = null;
			}
			if (config::get('comments_count'))
			{
				$kll['commentcount'] = $kill->countComment($kill->getID());
			}
			if ($this->combined_)
			{
				if(config::get('cfg_allianceid') && in_array($kill->getVictimAllianceID(), config::get('cfg_allianceid'))) $kll['loss'] = true;
				elseif (config::get('cfg_corpid') && in_array($kill->getVictimCorpID(), config::get('cfg_corpid'))) $kll['loss'] = true;
				elseif (config::get('cfg_pilotid') && in_array($kill->getVictimID(), config::get('cfg_pilotid'))) $kll['loss'] = true;
				else $kll['loss'] = false;
				$kll['kill'] = !$kll['loss'];
			}
			event::call('killlist_table_kill', $kll);
			$kills[] = $kll;
		}
		event::call('killlist_table_kills', $kills);
		if (count($kills))
		{
			$kl[] = array('kills' => $kills, 'date' => strtotime($prevdate));
		}

		$smarty->assignByRef('killlist', $kl);
		return $smarty->fetch(get_tpl('killlisttable'));
	}
}