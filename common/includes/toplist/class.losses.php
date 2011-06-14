<?php
/*
 * $Date: 2011-04-22 17:06:57 +1000 (Fri, 22 Apr 2011) $
 * $Revision: 1274 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/branches/3.2/common/includes/class.toplist.php $
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_Losses extends TopList_Base
{
	function generate()
	{
		$this->setSQLTop("select count(*) as cnt, kll.kll_victim_id as plt_id
                           from kb3_kills kll");
		$this->setSQLBottom("group by kll.kll_victim_id order by 1 desc
                            limit ".$this->limit);
		if (!count($this->inc_vic_scl))
		{
			$this->setPodsNoobShips(config::get('podnoobs'));
		}
	}
}
