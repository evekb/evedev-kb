<?php

event::register("pilotDetail_context_assembling", "signature::addSig");

class signature
{
	function addSig($home)
	{
		$home->addBefore("menu", "signature::menuOptions");
	}
	function menuOptions($home)
	{
		$home->menuOptions[] = array("caption","Signature");
		$home->menuOptions[] = array("link","Link", "?a=sig_list&amp;i=".$home->pilot->getID());
	}
}
