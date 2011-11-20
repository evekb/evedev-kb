<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/*
 * @package EDK
 */
class pInvtype extends pageAssembly
{
	/** @var integer */
	public $typeID;
	/** @var Page */
	public $page;
		
	function __construct()
	{
		parent::__construct();

		$this->queue("start");
		$this->queue("details");
	}

	function start()
	{
		$this->typeID = edkURI::getArg('id', 1);
		$this->page = new Page('Item Details');

	}

	function details()
	{
		global $smarty;
		$item = new dogma($this->typeID);

		if (!$item->isValid())
		{
			$this->page->setTitle('Error');
			return 'This ID is not a valid dogma ID.';
		}

		$this->page->setTitle('Item details - '.$item->get('typeName'));
		$this->page->addHeader('<meta name="robots" content="noindex, nofollow" />');
		$smarty->assignByRef('item', $item);

		if ($item->get('itt_cat') == 6)
		{
			//we have a ship, so get it from the db
			$ship = Ship::getByID($item->get('typeID'));
			$smarty->assign('shipImage', $ship->getImage(64));

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
			$i = new Item($this->typeID);
			$smarty->assign('itemImage', $i->getIcon(64, false));
			$html = $smarty->fetch(get_tpl('invtype_item'));
		}
		return $html;
	}
}


$invtype = new pInvtype();
event::call("invtype_assembling", $invtype);
$html = $invtype->assemble();
$invtype->page->setContent($html);

$invtype->page->generate();
