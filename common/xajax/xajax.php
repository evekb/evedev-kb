<?php

require_once('common/xajax/xajax_core/xajax.inc.php');
$xajax = new xajax();
event::register('page_assembleheader', 'edk_xajax::insertHTML');

// if mods depend on xajax they can register to xajax_initialised
// it gets called after all mods have been initialized
event::register('mods_initialised', 'edk_xajax::lateProcess');

class edk_xajax
{
	function xajax()
	{
		global $xajax_enable;
		$xajax_enable = true;
	}

	// on page assembly check whether or not xajax is needed
	function insertHTML($obj)
	{
		global $xajax_enable;
		if (!isset($xajax_enable))
		{
			return;
		}

		global $xajax;
		$obj->addBody($xajax->getJavascript("common/xajax/"));
	}

	function lateProcess()
	{
		// let all mods know we're here so they can register their functions
		event::call('xajax_initialised', $this);
		// Also register this for old mods registered to the ajax mod.
		event::call('mod_xajax_initialised', $this);

		// now process all xajax calls
		global $xajax;
		$xajax->processRequest();
	}
}

// Catch calls from old mods.
class mod_ajax
{
	function xajax()
	{
		edk_ajax::xajax();
	}
}
