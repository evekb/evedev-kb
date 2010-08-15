<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */



class involved
{
	function involved()
	{
		trigger_error('The class "involved" may only be invoked statically.', E_USER_ERROR);
	}

	public static function load(&$killlist, $type = 'kill')
	{
		if ($type == 'kill')
		{
			if (config::get('cfg_pilotid'))
				$killlist->addInvolvedPilot(config::get('cfg_pilotid'));
			if (config::get('cfg_corpid'))
				$killlist->addInvolvedCorp(config::get('cfg_corpid'));
			if (config::get('cfg_allianceid'))
				$killlist->addInvolvedAlliance(config::get('cfg_allianceid'));
		}
		elseif ($type == 'loss')
		{
			if (config::get('cfg_pilotid'))
				$killlist->addVictimPilot(config::get('cfg_pilotid'));
			if (config::get('cfg_corpid'))
				$killlist->addVictimCorp(config::get('cfg_corpid'));
			if (config::get('cfg_allianceid'))
				$killlist->addVictimAlliance(config::get('cfg_allianceid'));
		}
		elseif ($type == 'combined')
		{
			if (config::get('cfg_pilotid'))
				$killlist->addCombinedPilot(config::get('cfg_pilotid'));
			if (config::get('cfg_corpid'))
				$killlist->addCombinedCorp(config::get('cfg_corpid'));
			if (config::get('cfg_allianceid'))
				$killlist->addCombinedAlliance(config::get('cfg_allianceid'));
		}
	}

	public static function add(&$arr, &$ids)
	{
		if (is_numeric($ids))
			$arr[] = $ids;
		else if (is_array($ids))
			$arr = array_merge($arr, $ids);
		else
			$arr[] = $ids->getID();
	}
}
