<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_FinalBlow extends TopList_Base
{
	function generate()
	{
		$sql = "select count(ind.ind_kll_id) as cnt, kll.kll_fb_plt_id as plt_id
                from kb3_inv_detail ind
                INNER JOIN kb3_kills kll on (ind.ind_kll_id = kll.kll_id)";

		$this->setSQLTop($sql);

		$this->setSQLBottom("AND ind.ind_plt_id = kll.kll_fb_plt_id group by ind.ind_plt_id order by cnt desc
                            limit 10 /* TopList_FinalBlow */");
	}
}
