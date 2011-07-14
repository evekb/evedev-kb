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
		$this->setSQLTop("SELECT COUNT(*) AS cnt, plt.plt_id, "
			."plt.plt_name, plt.plt_externalid FROM kb3_kills kll "
			."JOIN kb3_pilots plt on plt.plt_id = kll.kll_victim_id");
		$this->setSQLBottom("GROUP BY kll.kll_victim_id ORDER BY cnt DESC
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
