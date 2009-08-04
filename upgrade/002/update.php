<?php
function update002(){
	// to correct the already existing Salvager in med slots.
	// missed it in update001
	if (CURRENT_DB_UPDATE < "002" )
	{
		require_once("common/includes/class.item.php");
		$SalvagerGroup  =  item::get_group_id("Salvager I");
		update_slot_of_group($SalvagerGroup,2,1);
		config::set("DBUpdate","002");
	}
}

