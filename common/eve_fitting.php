<?php
/**
 * @package EDK
 */

// Original by TEKAI
// Ammo addition and little modifications by Wes Lave

define( 'HIGHSLOT', 27);
define( 'MIDSLOT', 19);
define( 'LOWSLOT', 11);
define( 'RIGSLOT', 92);
define( 'DRONEBAY', 87);
define( 'SUBSYSTEM', 125);
define( 'CARGO', 5);

$kll_id = (int)edkURI::getArg('kll_id', 1);
$kill = Cacheable::factory('Kill', $kll_id);
$ship = $kill->getVictimShip();
$pilotname = $kill->getVictimName();
$shipclass = $ship->getClass();
$shipname = $ship->getName();
$killtitle .= $pilotname."'s ".$shipname;

$fitting_array[HIGHSLOT] = array();    // high slots
$fitting_array[MIDSLOT] = array();    // med slots
$fitting_array[LOWSLOT] = array();    // low slots
$fitting_array[RIGSLOT] = array();    // rig slots
$fitting_array[DRONEBAY] = array();    // drone bay
$fitting_array[SUBSYSTEM] = array();    // subsystems
$ammo_array[HIGHSLOT] = array();	// high ammo
$ammo_array[MIDSLOT] = array();	// mid ammo

if (count($kill->destroyeditems_) > 0)
{
	foreach($kill->destroyeditems_ as $destroyed)
	{
		$item = $destroyed->getItem();
		$i_qty = $destroyed->getQuantity();
		$i_name = $item->getName();
		$i_location = $destroyed->getLocationID();
		$i_id = $item->getID();
		$i_usedgroup = $item->get_used_launcher_group($i_name);
		//Fitting, KE - add destroyed items to an array of all fitted items.
		if($i_location != CARGO)
		{
			if(($i_usedgroup == 0))
			{
				for ($count = 0; $count < $i_qty; $count++)
				{
					if ($i_location == HIGHSLOT)
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
	}
}

if (count($kill->droppeditems_) > 0)
{
	foreach($kill->droppeditems_ as $dropped)
	{
		$item = $dropped->getItem();
		$i_qty = $dropped->getQuantity();
		$i_name = $item->getName();
		$i_location = $dropped->getLocationID();
		$i_id = $item->getID();
		$i_usedgroup = $item->get_used_launcher_group($i_name);
		//Fitting -KE, add dropped items to the list
		if($i_location != CARGO)
		{
			if(($i_usedgroup == 0))
			{
				for ($count = 0; $count < $i_qty; $count++)
				{
					if ($i_location == HIGHSLOT)
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
	}
}



$slots = array(LOWSLOT => "low slot",
	MIDSLOT => "med slot",
	HIGHSLOT => "hi slot",
	RIGSLOT => "rig slot",
	SUBSYSTEM => "subsystem slot",
	DRONEBAY => "drone bay");

$xml = "<?xml version=\"1.0\" ?>
	<fittings>\n";

$xml .= "\t\t<fitting name=\"".$killtitle."\">\n";
$xml .= "\t\t\t<description value=\"From ".KB_HOST."?a=kill_detail&amp;kll_id=".$kll_id."\"/>\n"; //keep hardcoded; we don't need a session key here
$xml .= "\t\t\t<shipType value=\"".$shipname."\"/>\n";

foreach ($slots as $i => $empty)
{
	if (!empty($fitting_array[$i]))
	{
		$usedslots = 0;
		foreach ($fitting_array[$i] as $k => $a_item)
		{
			$item = $a_item['Name'];
			$xml .= "\t\t\t<hardware ";
			if($i == DRONEBAY)
			{
				$xml .= "qty=\"1\" ";
				$xml .= "slot=\"".$slots[$i]."\" ";
				$xml .= "type=\"".$a_item['Name']."\"/>\n";
			}
			else
			{
				$xml .= "slot=\"".$slots[$i]." ".$usedslots."\" ";
				$xml .= "type=\"".$a_item['Name']."\"/>\n";
			}

			$usedslots++;
		}
	}
}
$xml .= "\t\t</fitting>\n\t</fittings>";

if(!IS_IGB)
{
	header("Content-Type: text/xml");
	header('Content-Disposition: attachment; filename="'.$shipname.'.xml"');
	echo $xml;
}
else
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ';
	echo '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> ';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr">';
	echo "<head><title>Eve fitting xml</title></head><body><form action = ''>\n<table><tr><td>\n<textarea id=\"fitting\" name=\"fitting\" cols=\"80\" rows=\"40\" readonly=\"readonly\">\n";
	echo htmlspecialchars($xml, ENT_NOQUOTES);
	echo "\n</textarea></td></tr>";
	echo '<tr><td><input type="button" value="Select All" onclick="this.form.fitting.select();this.form.fitting.focus(); document.execCommand(\'Copy\')" />';
	echo "</td></tr></table></form></body></html>";
}
