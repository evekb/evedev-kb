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
		$this->setSQLTop("SELECT COUNT(*) AS cnt, kll.kll_crp_id AS crp_id, "
				."crp.crp_name, crp.crp_external_id "
                ."FROM kb3_kills kll "
				."JOIN kb3_corps crp ON kll.kll_crp_id = crp.crp_id ");
		$this->setSQLBottom("GROUP BY kll.kll_crp_id ORDER BY cnt DESC
                            LIMIT ".$this->limit);
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
