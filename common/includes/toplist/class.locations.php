<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top locations by kills in a solarsystem

class TopList_Locations extends TopList_Base
{
	function generate()
	{
                // the innermost sub-select lists all kills with all involved pilots etc according to the set filters
                // the outer subselect then only gets the unique kills and their locations
                // the top select then groups the kills by location
		$sql = "select 
                            count(killlist.kll_location) as cnt, killlist.kll_location as itemID, killlist.itemName as itemName
                        from 
                        ( 
                            SELECT DISTINCT sublist.kll_id, sublist.kll_location, sublist.itemName FROM
                            ( 
                                select 
                                        kll.kll_id, ind.ind_plt_id as plt_id, plt.plt_name, kll.kll_location, mdn.itemName as itemName
                                    from kb3_kills kll
                                    INNER JOIN kb3_inv_detail ind
                                            on ( ind.ind_kll_id = kll.kll_id )
                                    INNER JOIN kb3_pilots plt
                                            on ( plt.plt_id = ind.ind_plt_id )
                                    INNER JOIN kb3_mapdenormalize mdn
                                        on ( mdn.itemID = kll.kll_location ) ";

		$this->setSQLTop($sql);

		$this->setSQLBottom(") sublist ) killlist group by killlist.kll_location order by 1 desc limit ".$this->limit);
                
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
