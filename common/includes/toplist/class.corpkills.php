<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_CorpKills extends TopList_Base
{
	function generate()
	{
		$sql = "select count(distinct(kll.kll_id)) as cnt, ind.ind_crp_id as crp_id
                from kb3_kills kll
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )";

		$this->setSQLTop($sql);

		$this->setSQLBottom("group by ind.ind_crp_id order by 1 desc
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
