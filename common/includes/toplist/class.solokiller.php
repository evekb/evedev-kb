<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_SoloKiller extends TopList_Base
{
	function generate()
	{
		$sql = "SELECT ind.ind_plt_id AS plt_id, count(ind_kll_id) AS cnt".
			" FROM kb3_inv_detail ind".
			" JOIN kb3_kills kll ON kll.kll_id = ind.ind_kll_id AND ind.ind_order = 0 ";

		$this->setSQLTop($sql);

		$this->setSQLBottom(" AND ".
			"NOT EXISTS (SELECT 1 FROM kb3_inv_detail ind2 ".
			"WHERE ind2.ind_kll_id = ind.ind_kll_id AND ".
			"ind2.ind_order = 1 ) ".
			"GROUP BY ind.ind_plt_id ".
			"ORDER BY cnt DESC ".
			"limit ".$this->limit);
	}
}
