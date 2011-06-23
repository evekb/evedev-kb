<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_Ship extends TopList_Base
{
	function generate()
	{
		$sqltop = "select count( ind.ind_kll_id) as cnt, ind.ind_shp_id as shp_id
              from kb3_inv_detail ind
			  INNER JOIN kb3_kills kll on (kll.kll_id = ind.ind_kll_id)
	      INNER JOIN kb3_ships shp on ( shp_id = ind.ind_shp_id )";

		$this->setSQLTop($sqltop);

		$sqlbottom .= " group by ind.ind_shp_id order by 1 desc".
			" limit 20";

		$this->setSQLBottom($sqlbottom);
	}
}
