<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class Killboard
{
	public static function hasCampaigns($active = false)
	{
		$qry = DBFactory::getDBQuery();
		$sql = "select ctr_id
                 from kb3_contracts
	         where ctr_campaign = 1
	           and ctr_site = '".KB_SITE."'";
		if ($active) $sql .= " and ( ctr_ended is null or now() <= ctr_ended ) limit 1";
		$qry->execute($sql);
		return ($qry->recordCount() > 0);
	}

	public static function hasContracts($active = false)
	{
		$qry = DBFactory::getDBQuery();
		$sql = "select ctr_id
                 from kb3_contracts
                 where ctr_campaign = 0
                   and ctr_site = '".KB_SITE."'";
		if ($active) $sql .= " and ( ctr_ended is null or now() <= ctr_ended ) limit 1";
		$qry->execute($sql);
		return ($qry->recordCount() > 0);
	}
}
