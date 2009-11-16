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
		$home->addMenuItem("caption","Signature");
		$home->addMenuItem("link","Link", "?a=sig_list&amp;i=".$home->pilot->getID());
	}
}
