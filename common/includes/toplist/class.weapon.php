<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_Weapon extends TopList_Base
{
    function generate()
    {
        // Does not need to be distinct (i.e. weapon was used by two different
        // pilots on one kill, but in this case using distinct is twice as fast.
        $sql = "select count(distinct ind.ind_kll_id) as cnt, ind.ind_wep_id as itm_id
                from kb3_inv_detail ind
                INNER JOIN kb3_kills kll on (kll.kll_id = ind.ind_kll_id)
                INNER JOIN kb3_invtypes itm on (typeID = ind.ind_wep_id)
                INNER JOIN kb3_item_types itt on (itm.groupID = itt.itt_id)";

        $this->setSQLTop($sql);
        // FIX for displaying only weapons/drones in weapons column, but not ships:
                // dont use iconID to rule out ships -> go via item category
        $sqlbottom .=" AND itt.itt_cat != 6".
            " group by ind.ind_wep_id order by 1 desc limit 20";
        $this->setSQLBottom($sqlbottom);
    }
}
