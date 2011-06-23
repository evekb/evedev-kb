<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_Score extends TopList_Base
{
	function TopList_Score()
	{
		$this->limit = 30;
	}

	function generate()
	{
		$sql = "select sum(kll.kll_points) as cnt, ind.ind_plt_id as plt_id, plt.plt_name
                from kb3_kills kll
	      INNER JOIN kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              INNER JOIN kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id )";
// Restrict results to pilots in the involved corp/all/pilot lists.
		$sqlB = "";
 		if ($this->inv_crp || $this->inv_all || $this->inv_plt)
		{
			$invP = array();
			if ($this->inv_plt)
				$invP[] = "ind.ind_plt_id IN ( ".implode(",", $this->inv_plt)." )";
			if ($this->inv_crp)
				$invP[] = "ind.ind_crp_id IN ( ".implode(",", $this->inv_crp)." )";
			if ($this->inv_all)
				$invP[] = "ind.ind_all_id IN ( ".implode(",", $this->inv_all)." )";
			$sqlB = " AND (".implode(" OR ", $invP).") ";
		}

		$this->setSQLTop($sql);

		$this->setSQLBottom($sqlB." group by ind.ind_plt_id order by 1 desc
                            limit ".$this->limit);
	}
}
