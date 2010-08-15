<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

if(config::get('cfg_allianceid'))
{
	$alls = config::get('cfg_allianceid');
	$_GET['all_id'] = $alls[0];
	unset($alls);
	include('alliance_detail.php');
}
elseif(config::get('cfg_corpid'))
{
	$corps = config::get('cfg_corpid');
	$_GET['crp_id'] = $corps[0];
	unset($corps);
	include('corp_detail.php');
}
elseif(PILOT_ID)
{
	$pilots = config::get('cfg_pilotid');
	$_GET['plt_id'] = $pilots[0];
	unset($pilots);
	include('pilot_detail.php');
}
else
{
	include("about.php");
}
