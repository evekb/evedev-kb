<?php

class Killboard
{
    function hasCampaigns($active = false)
    {
        $qry = new DBQuery();
        $sql = "select ctr_id
                 from kb3_contracts
	         where ctr_campaign = 1
	           and ctr_site = '".KB_SITE."'";
        if ($active) $sql .= " and ( ctr_ended is null or now() <= ctr_ended )";
        $qry->execute($sql);
        return ($qry->recordCount() > 0);
    }

    function hasContracts($active = false)
    {
        $qry = new DBQuery();
        $sql = "select ctr_id
                 from kb3_contracts
                 where ctr_campaign = 0
                   and ctr_site = '".KB_SITE."'";
        if ($active) $sql .= " and ( ctr_ended is null or now() <= ctr_ended )";
        $qry->execute($sql);
        return ($qry->recordCount() > 0);
    }
}
?>