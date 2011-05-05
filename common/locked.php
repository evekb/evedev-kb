<?php
/*
 * $Date: 2010-05-29 14:46:12 +1000 (Sat, 29 May 2010) $
 * $Revision: 699 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/login.php $
 */

class pLocked extends pageAssembly
{
	function __construct()
	{
		parent::__construct();

		$this->queue("start");
		$this->queue("content");
	}

	function start()
	{
		$this->page = new Page("Locked");
	}

	function content()
	{
		global $smarty;
		return $smarty->fetch(get_tpl("locked"));
	}

}

$locked = new pLocked();
event::call("locked_assembling", $locked);
$html = $locked->assemble();
$locked->page->setContent($html);

$locked->page->generate();