<?php
/*
 * $Date: 2011-04-22 17:06:57 +1000 (Fri, 22 Apr 2011) $
 * $Revision: 1274 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/branches/3.2/common/includes/class.toplist.php $
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_Kills extends TopList_Base
{
	function generate()
	{
		$sql = "select count(ind.ind_kll_id) as cnt, ind.ind_plt_id as plt_id, plt.plt_name
                from kb3_kills kll
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              INNER JOIN kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id )";

		$this->setSQLTop($sql);

		$this->setSQLBottom($sqlB." group by ind.ind_plt_id order by 1 desc
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
