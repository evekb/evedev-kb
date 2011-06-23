<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
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
