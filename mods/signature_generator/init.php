<?php
/*
 * $Date: 2010-07-10 22:11:02 +1000 (Sat, 10 Jul 2010) $
 * $Revision: 844 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/about.php $
 */


$modInfo['signature_generator']['name'] = "Signature Generator";
$modInfo['signature_generator']['abstract'] = "Generates signature images showing most kill information by a pilot.";
$modInfo['signature_generator']['about'] = "Core distribution mod.";

event::register("pilotDetail_context_assembling", "signature::addSig");

class signature
{
	public static function addSig($home)
	{
		$home->addBefore("menu", "signature::menuOptions");
	}
	public static function menuOptions($home)
	{
		$home->addMenuItem("caption","Signature");
		$home->addMenuItem("link","Link", "?a=sig_list&amp;i=".$home->pilot->getID());
	}
}
