<?php
function update001(){
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "001" )
	{
		require_once("common/includes/class.item.php");
		// Changing ShieldBooster Slot from None to Mid Slot
		$ShieldBoosterGroup = item::get_group_id("Small Shield Booster I");
		update_slot_of_group($ShieldBoosterGroup,0,2);

		// Changing Tracking Scripts Slot from None to Mid Slot
		$ScriptGroupID1 = item::get_group_id("Optimal Range");
		update_slot_of_group($ScriptGroupID1,0,2);

		// Changing Warp Disruption Scripts Slot from None to Mid Slot
		$ScriptGroupID2 = item::get_group_id("Focused Warp Disruption");
		update_slot_of_group($ScriptGroupID2,0,2);

		// Changing Tracking Disruption Scripts Slot from None to Mid Slot
		$ScriptGroupID3 = item::get_group_id("Optimal Range Disruption");
		update_slot_of_group($ScriptGroupID3,0,2);

		// Changing Sensor Booster Scripts Slot from None to Mid Slot
		$ScriptGroupID4 = item::get_group_id("Targeting Range");
		update_slot_of_group($ScriptGroupID4,0,2);

		// Changing Sensor Dampener Scripts Slot from None to Mid Slot
		$ScriptGroupID5 = item::get_group_id("Scan Resolution Dampening");
		update_slot_of_group($ScriptGroupID5,0,2);

		// Changing Energy Weapon Slot from None to High Slot
		$EnergyWeaponGroup = item::get_group_id("Gatling Pulse Laser I");
		update_slot_of_group($EnergyWeaponGroup,0,1);

		// Changing Group of Salvager I to same as Small Tractor Beam I
		$item = new Item();
		$item->lookup("Salvager I");
		$SalvagerTypeId =  $item->getId();
		$SalvagerGroup  =  item::get_group_id("Salvager I");
		$TractorBeam    =  item::get_group_id("Small Tractor Beam I");
		move_item_to_group($SalvagerTypeId,$SalvagerGroup ,$TractorBeam);

		//writing Update Status into ConfigDB
		config::set("DBUpdate","001");
	}
}

