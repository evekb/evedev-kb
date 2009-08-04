<?php
function update003(){
	// Warefare Links and Command Prozessor were midslot items in install file, should be high slot
	if (CURRENT_DB_UPDATE < "003" )
	{
		require_once("common/includes/class.item.php");
		$WarfareLinkGroup  =  item::get_group_id("Skirmish Warfare Link - Rapid Deployment");
		update_slot_of_group($WarfareLinkGroup,2,1);
		config::set("DBUpdate","003");
	}
}

