<?php
if(PILOT_ID)
{
	header("Location: ".KB_HOST."?a=pilot_detail&plt_id=".PILOT_ID);
}
elseif(CORP_ID)
{
	header("Location: ".KB_HOST."?a=corp_detail&crp_id=".CORP_ID);
}
elseif(ALLIANCE_ID)
{
	header("Location: ".KB_HOST."?a=alliance_detail&all_id=".ALLIANCE_ID);
}
die;