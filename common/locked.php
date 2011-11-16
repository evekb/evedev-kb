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
class pLocked extends pageAssembly
{
	/** @var Page */
	public $page = null;

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