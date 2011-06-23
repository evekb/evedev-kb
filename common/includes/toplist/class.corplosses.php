<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_CorpLosses extends TopList_Base
{
	function generate()
	{
		$this->setSQLTop("select count(*) as cnt, kll.kll_crp_id as crp_id
                           from kb3_kills kll");
		$this->setSQLBottom("group by kll.kll_crp_id order by 1 desc
                            limit ".$this->limit);
		if (count($this->inc_vic_scl))
		{
			$this->setPodsNoobShips(true);
		}
		else
		{
			$this->setPodsNoobShips(config::get('podnoobs'));
		}
	}
}
