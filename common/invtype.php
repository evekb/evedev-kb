<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

if (!$id = intval($_GET['id']))
{
	$page = new Page('Error');
	$page->setContent('No valid ID specified.');
	$page->generate();
	exit;
}
include_once('common/includes/class.dogma.php');

$item = new dogma($id);

if (!$item->isValid())
{
	$page = new Page('Error');
	$page->setContent('This ID is not a valid dogma ID.');
	$page->generate();
	exit;
}

$page = new Page('Item details - '.$item->get('typeName'));
$page->addHeader('<meta name="robots" content="noindex, nofollow" />');
#$dump = var_export($item, true);
#$smarty->assign('dump', $dump);
$smarty->assignByRef('item', $item);

if ($item->get('itt_cat') == 6)
{
	//we have a ship, so get it from the db
	include_once('common/includes/class.ship.php');
	$ship = new Ship(0, $item->get('typeID'));
	$smarty->assign('shiptechlevel', $ship->getTechLevel());
	$smarty->assign('shipisfaction', $ship->isFaction());

	$smarty->assign('armour', array('armorHP','armorEmDamageResonance',
		'armorExplosiveDamageResonance','armorKineticDamageResonance',
		'armorThermalDamageResonance'));
	$smarty->assign('shield', array('shieldCapacity','shieldRechargeRate',
		'shieldEmDamageResonance','shieldExplosiveDamageResonance',
		'shieldKineticDamageResonance','shieldThermalDamageResonance'));
	$smarty->assign('propulsion', array('maxVelocity','agility','droneCapacity',
		'capacitorCapacity','rechargeRate'));
	$smarty->assign('fitting', array('hiSlots','medSlots','lowSlots','rigSlots',
		'upgradeCapacity','droneBandwidth','launcherSlotsLeft','turretSlotsLeft',
		'powerOutput','cpuOutput'));
	$smarty->assign('targetting', array('maxTargetRange','scanResolution',
		'maxLockedTargets','scanRadarStrength','scanLadarStrength',
		'scanMagnetometricStrength','scanGravimetricStrength','signatureRadius'));
	$smarty->assign('miscellaneous', array('techLevel','propulsionFusionStrength',
		'propulsionIonStrength','propulsionMagpulseStrength',
		'propulsionPlasmaStrength'));
	$html = $smarty->fetch(get_tpl('invtype_ship'));
}
else
{
	$html = $smarty->fetch(get_tpl('invtype_item'));
}
$page->setContent($html);
$page->generate();
