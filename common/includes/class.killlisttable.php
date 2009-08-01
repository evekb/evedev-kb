<?php
class KillListTable
{
    function KillListTable($kill_list)
    {
        $this->limit = 0;
        $this->offset = 0;

        $this->kill_list_ = $kill_list;
        $this->daybreak_ = true;
    }

    function setBrowsable($browsable)
    {
        $this->browsable = $browsable;
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
            $kll['victimshipname'] = $kill->getVictimShipName();
            $kll['victimshipclass'] = $kill->getVictimShipClassName();
            $kll['victimshipindicator'] = $kill->getVictimShipValueIndicator();
            $kll['victim'] = $kill->getVictimName();
            $kll['victimcorp'] = $kill->getVictimCorpName();
            $kll['victimalliancename'] = $kill->getVictimAllianceName();
            $kll['fb'] = $kill->getFBPilotName();
            $kll['fbcorp'] = $kill->getFBCorpName();
            $kll['system'] = $kill->getSolarSystemName();
            $kll['systemsecurity'] = $kill->getSolarSystemSecurity();
            if (config::get('killlist_involved')) $kll['inv'] = $kill->getInvolvedPartyCount();
            $kll['timestamp'] = $kill->getTimeStamp();
            if (config::get('killlist_alogo'))
            {
                $kll['victimallianceicon'] = preg_replace('/[^a-zA-Z0-9]/', '', $kll['victimalliancename']);
                $kll['allianceexists'] = file_exists('img/alliances/'.$kll['victimallianceicon'].'.png');
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
                    if(ALLIANCE_ID && $kill->getVictimAllianceID() == ALLIANCE_ID) $kll['loss'] = true;
                    elseif(CORP_ID && $kill->getVictimCorpID() == CORP_ID) $kll['loss'] = true;
                    elseif(PILOT_ID && $kill->getVictimID() == PILOT_ID) $kll['loss'] = true;
                    else $kll['kill'] = true;
            }
            $kills[] = $kll;
        }
        if (count($kills))
        {
            $kl[] = array('kills' => $kills, 'date' => strtotime($prevdate));
        }

        $smarty->assign_by_ref('killlist', $kl);
        return $smarty->fetch(get_tpl('killlisttable'));
    }
}
?>