<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Original by TEKAI
// Ammo addition and little modifications by Wes Lave

$kll_id = (int)edkURI::getArg('kll_id', 1);
$kill = Cacheable::factory('Kill', $kll_id);
$ship = $kill->getVictimShip();
$pilotname = $kill->getVictimName();
$shipclass = $ship->getClass();
$shipname = $ship->getName();
$killtitle .= $pilotname."'s ".$shipname;

$fitting_array[InventoryFlag::$HIGH_SLOT_1] = array();    // high slots
$fitting_array[InventoryFlag::$MED_SLOT_1] = array();    // med slots
$fitting_array[InventoryFlag::$LOW_SLOT_1] = array();    // low slots
$fitting_array[InventoryFlag::$RIG_SLOT_1] = array();    // rig slots
$fitting_array[InventoryFlag::$DRONE_BAY] = array();    // drone bay
$fitting_array[InventoryFlag::$SUB_SYSTEM_SLOT_1] = array();    // subsystems
$ammo_array[InventoryFlag::$HIGH_SLOT_1] = array();	// high ammo
$ammo_array[InventoryFlag::$MED_SLOT_1] = array();	// mid ammo
$ammo_array[InventoryFlag::$LOW_SLOT_1] = array();	// low ammo


if (count($kill->destroyeditems_) > 0)
{
	foreach($kill->destroyeditems_ as $destroyed)
	{
		$item = $destroyed->getItem();
		$i_qty = $destroyed->getQuantity();
		$i_name = $item->getName();
		$i_location = InventoryFlag::collapse($destroyed->getLocationID());
		$i_id = $item->getID();
		$i_usedgroup = $item->get_used_launcher_group($i_name);
		
		// Nanite Repair Paste for ancillary armor repairers is a special snowflake
		// there are no type attributes indicating a used group
		// if item is nanite repair paste
		if($i_id == 28668) 
		{
			// ancillary armor repairers
			$i_usedgroup = 1199;
		}
		//Fitting, KE - add destroyed items to an array of all fitted items.
		if($i_location != InventoryFlag::$CARGO)
		{
			if(($i_usedgroup != 0))
			{
				if ($i_location == InventoryFlag::$HIGH_SLOT_1)
				{
					$i_ammo=$item->get_ammo_size($i_name);

				}
				else
				{
					$i_ammo = 0;
				}
				$ammo_array[$i_location][]=array('Name'=>$i_name, 'usedgroupID' => $i_usedgroup, 'size' => $i_ammo);
			} else
			{
				for ($count = 0; $count < $i_qty; $count++)
				{
					if ($i_location == InventoryFlag::$HIGH_SLOT_1)
					{
						$i_charge=$item->get_used_charge_size($i_name);
					}
					else
					{
						$i_charge = 0;
					}
					$fitting_array[$i_location][]=array('Name'=>$i_name, 'groupID' => $item->get_group_id($i_name), 'chargeSize' => $i_charge);
				}
			}
		}
	//fitting thing end
	}
}

if (count($kill->droppeditems_) > 0)
{
	foreach($kill->droppeditems_ as $dropped)
	{
		$item = $dropped->getItem();
		$i_qty = $dropped->getQuantity();
		$i_name = $item->getName();
		$i_location = InventoryFlag::collapse($dropped->getLocationID());
		$i_id = $item->getID();
		$i_usedgroup = $item->get_used_launcher_group($i_name);
		// Nanite Repair Paste for ancillary armor repairers is a special snowflake
		// there are no type attributes indicating a used group
		// if item is nanite repair paste
		if($i_id == 28668) 
		{
			// ancillary armor repairers
			$i_usedgroup = 1199;
		}
		
		//Fitting -KE, add dropped items to the list
		if($i_location != InventoryFlag::$CARGO)
		{
			if(($i_usedgroup != 0))
			{
				if ($i_location == InventoryFlag::$HIGH_SLOT_1)
				{
					$i_ammo=$item->get_ammo_size($i_name);
				}
				else
				{
					$i_ammo = 0;
				}
				$ammo_array[$i_location][]=array('Name'=>$i_name, 'usedgroupID' => $i_usedgroup, 'size' => $i_ammo);
			} else
			{
				for ($count = 0; $count < $i_qty; $count++)
				{
					if ($i_location == InventoryFlag::$HIGH_SLOT_1)
					{
						$i_charge=$item->get_used_charge_size($i_name);
					}
					else
					{
						$i_charge = 0;
					}
					$fitting_array[$i_location][]=array('Name'=>$i_name, 'groupID' => $item->get_group_id($i_name), 'chargeSize' => $i_charge);
				}
			}
		}
	//fitting thing end


	}
}

//Fitting - KE, sort the fitted items into groupID order, so that several of the same item apear next to each other.
if(!(empty($fitting_array[InventoryFlag::$HIGH_SLOT_1])))
{
	foreach ($fitting_array[InventoryFlag::$HIGH_SLOT_1] as $array_rowh)
	{
		$sort_by_nameh["groupID"][] = $array_rowh["groupID"];
	}
	array_multisort($sort_by_nameh["groupID"],SORT_ASC,$fitting_array[InventoryFlag::$HIGH_SLOT_1]);
}

if(!(empty($fitting_array[InventoryFlag::$MED_SLOT_1])))
{
	foreach ($fitting_array[InventoryFlag::$MED_SLOT_1] as $array_rowm)
	{
		$sort_by_namem["groupID"][] = $array_rowm["groupID"];
	}
	array_multisort($sort_by_namem["groupID"],SORT_ASC,$fitting_array[InventoryFlag::$MED_SLOT_1]);
}

if(!(empty($fitting_array[InventoryFlag::$LOW_SLOT_1])))
{
	foreach ($fitting_array[InventoryFlag::$LOW_SLOT_1] as $array_rowl)
	{
		$sort_by_namel["groupID"][] = $array_rowl["groupID"];
	}
	array_multisort($sort_by_namel["groupID"],SORT_ASC,$fitting_array[InventoryFlag::$LOW_SLOT_1]);
}

if(!(empty($fitting_array[InventoryFlag::$CARGO])))
{
	foreach ($fitting_array[InventoryFlag::$CARGO] as $array_rowr)
	{
		$sort_by_namer["Name"][] = $array_rowr["Name"];
	}
	array_multisort($sort_by_namer["Name"],SORT_ASC,$fitting_array[InventoryFlag::$CARGO]);
}

if(!(empty($fitting_array[InventoryFlag::$DRONE_BAY])))
{
	foreach ($fitting_array[InventoryFlag::$DRONE_BAY] as $array_rowd)
	{
		$sort_by_named["Name"][] = $array_rowd["Name"];
	}
	array_multisort($sort_by_named["Name"],SORT_ASC,$fitting_array[InventoryFlag::$DRONE_BAY]);
}

if(!(empty($fitting_array[InventoryFlag::$SUB_SYSTEM_SLOT_1])))
{
	foreach ($fitting_array[InventoryFlag::$SUB_SYSTEM_SLOT_1] as $array_rowd)
	{
		$sort_by_names["Name"][] = $array_rowd["Name"];
	}
	array_multisort($sort_by_names["Name"],SORT_ASC,$fitting_array[InventoryFlag::$SUB_SYSTEM_SLOT_1]);
}

//Fitting - KE, sort the fitted items into name order, so that several of the same item apear next to each other. -end

$length = count($ammo_array[InventoryFlag::$HIGH_SLOT_1]);
$temp = array();
if(is_array($fitting_array[InventoryFlag::$HIGH_SLOT_1]))
{
	$hiammo = array();
	foreach ($fitting_array[InventoryFlag::$HIGH_SLOT_1] as $highfit)
	{
		$group = $highfit["groupID"];
		$size = $highfit["chargeSize"];
		if($group == 483 // Modulated Deep Core Miner II, Modulated Strip Miner II and Modulated Deep Core Strip Miner II
			|| $group == 53 // Laser Turrets
			|| $group == 55 // Projectile Turrets
			|| $group == 74 // Hybrid Turrets
			|| ($group >= 506 && $group <= 511) // Some Missile Lauchers
			|| $group == 481 // Probe Launchers
			|| $group == 899 // Warp Disruption Field Generator I
			|| $group == 771 // Heavy Assault Missile Launchers
			|| $group == 589 // Interdiction Sphere Lauchers
			|| $group == 524 // Citadel Torpedo Launchers
		)
		{
			$found = 0;
			if ($group == 511)
			{ $group = 509; } // Assault Missile Lauchers uses same ammo as Standard Missile Lauchers
			if(is_array($ammo_array[InventoryFlag::$HIGH_SLOT_1]))
			{
				$i = 0;
				while (!($found) && $i<$length)
				{
					$temp = array_shift($ammo_array[InventoryFlag::$HIGH_SLOT_1]);
					if (($temp["usedgroupID"] == $group) && ($temp["size"] == $size))
					{
						$hiammo[]=$temp["Name"];
						$found = 1;
					}
					array_push($ammo_array[InventoryFlag::$HIGH_SLOT_1],$temp);
					$i++;
				}
			}
			if (!($found))
			{
				$hiammo[]=0;
			}
		} else
		{
			$hiammo[]=0;
		}
	}
}

$length = count($ammo_array[InventoryFlag::$MED_SLOT_1]);
if(is_array($fitting_array[InventoryFlag::$MED_SLOT_1]))
{
	$midammo = array();
	foreach ($fitting_array[InventoryFlag::$MED_SLOT_1] as $midfit)
	{
		$group = $midfit["groupID"];
		if($group == 76 // Capacitor Boosters
			|| $group == 208 // Remote Sensor Dampeners
			|| $group == 212 // Sensor Boosters
			|| $group == 291 // Tracking Disruptors
			|| $group == 213 // Tracking Computers
			|| $group == 209 // Tracking Links
			|| $group == 290 // Remote Sensor Boosters
		)
		{
			$found = 0;
			if(is_array($ammo_array[InventoryFlag::$MED_SLOT_1]))
			{
				$i = 0;
				while (!($found) && $i<$length)
				{
					$temp = array_shift($ammo_array[InventoryFlag::$MED_SLOT_1]);
					if ($temp["usedgroupID"] == $group)
					{
						$midammo[]=$temp["Name"];
						$found = 1;
					}
					array_push($ammo_array[InventoryFlag::$MED_SLOT_1],$temp);
					$i++;
				}
			}
			if (!($found))
			{
				$midammo[]=0;
			}
		}
		else
		{
			$midammo[]=0;
		}
	}
}

$length = count($ammo_array[InventoryFlag::$LOW_SLOT_1]);
if(is_array($fitting_array[InventoryFlag::$LOW_SLOT_1]))
{
	$lowammo = array();
	foreach ($fitting_array[InventoryFlag::$LOW_SLOT_1] as $midfit)
	{
		$group = $midfit["groupID"];
		if ($group == 1199 // Ancillary Armor Repairers
		) {
			$found = 0;
			if(is_array($ammo_array[InventoryFlag::$LOW_SLOT_1]))
			{
				$i = 0;
				while (!($found) && $i<$length)
				{
					$temp = array_shift($ammo_array[InventoryFlag::$LOW_SLOT_1]);
					if ($temp["usedgroupID"] == $group)
					{
						$lowammo[]=$temp["Name"];
						$found = 1;
					}
					array_push($ammo_array[InventoryFlag::$LOW_SLOT_1],$temp);
					$i++;
				}
			}
			if (!($found))
			{
				$lowammo[]=0;
			}
		}
		else
		{
			$lowammo[]=0;
		}
	}
}

$slots = array(InventoryFlag::$LOW_SLOT_1 => "[empty low slot]",
	InventoryFlag::$MED_SLOT_1 => "[empty mid slot]",
	InventoryFlag::$HIGH_SLOT_1 => "[empty high slot]",
	InventoryFlag::$RIG_SLOT_1 => "[empty rig slot]",
	InventoryFlag::$SUB_SYSTEM_SLOT_1 => "",
	InventoryFlag::$DRONE_BAY => "");

?>
popup|<form>
<table class="popup-table" height="100%" width="355px">
<tr>
	<td align="center"><strong>EFT Fitting</strong></td>
</tr>
<tr>
	<td align="center"><input type="button" value="Close" onClick="ReverseContentDisplay('popup');"></td>
</tr>
<tr>
<td valign="top" align="center">
<textarea class="killmail" name="killmail" cols="60" rows="30" readonly="readonly">
[<?php echo $shipname; ?>, <?php echo $killtitle; ?>]
<?php
foreach ($slots as $i => $empty)
{
	if (empty($fitting_array[$i]))
	{
		echo $empty."\n";
	}
	else
	{
		foreach ($fitting_array[$i] as $k => $a_item)
		{
			$item = $a_item['Name'];
			if ($i == InventoryFlag::$DRONE_BAY)
			{
				$item .= ' x1';
			}
			elseif ($i == InventoryFlag::$HIGH_SLOT_1)
			{
				if ($hiammo[$k])
				{
					$item .=','.$hiammo[$k];
				}
			}
			elseif ($i == InventoryFlag::$MED_SLOT_1)
			{
				if ($midammo[$k])
				{
					$item .=','.$midammo[$k];
				}
			}
			elseif($i == InventoryFlag::$LOW_SLOT_1)
			{
				if($lowammo[$k])
				{
					$item .=",".$lowammo[$k];
				}
			}
			echo $item."\n";
		}
	}
	echo "\n";
}
?>
</textarea></td></tr>
<tr><td align="center"><input type="button" value="Select All" onClick="this.form.killmail.select();this.form.killmail.focus(); document.execCommand('Copy')">&nbsp;<input type="button" value="Close" onClick="ReverseContentDisplay('popup');"></td>
</tr>
</table>
</form>