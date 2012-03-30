<?php
/**
 * @todo This whole class will go away.
 * @package EDK
 */
class Killboard
{
	public static function hasCampaigns($active = false)
	{
		$qry = DBFactory::getDBQuery();
		$sql = "select ctr_id
                 from kb3_contracts
	         where ctr_site = '".KB_SITE."'";
		if ($active) $sql .= " and ( ctr_ended is null or now() <= ctr_ended ) limit 1";
		$qry->execute($sql);
		return ($qry->recordCount() > 0);
	}
}
