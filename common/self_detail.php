<?php
if(PILOT_ID)
{
	$_GET['plt_id'] = PILOT_ID;
	include('pilot_detail.php');
}
elseif(CORP_ID)
{
	$_GET['crp_id'] = CORP_ID;
	include('corp_detail.php');
}
elseif(ALLIANCE_ID)
{
	$_GET['all_id'] = ALLIANCE_ID;
	include('alliance_detail.php');
}
else
{
	include("about.php");
}
