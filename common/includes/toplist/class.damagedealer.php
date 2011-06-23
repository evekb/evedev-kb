<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_DamageDealer extends TopList_Base
{
	function generate()
	{
		$sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id and ind.ind_order = 0)
              INNER JOIN kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";

		$sql .= ")";

		$this->setSQLTop($sql);

		$this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit ".$this->limit);
	}
}
