<?php
// common/includes/class.page.php
// templates/index.tpl
// you should not change anything in this file but the image generator / whatever bypass change the functions.php
// bypass some of the image generators..
if ($page != "thumb" && $page != "mapview" && $page != "sig")
{
    require_once('mods/xajax/xajax_core/xajax.inc.php');
    $xajax = new xajax();
    require_once('mods/xajax/functions.php');
    event::register('page_assembleheader', 'mod_xajax::insertHTML');

    // if mods depend on xajax they can register to mod_xajax_initialised
    // it gets called after all mods have been initialized
    event::register('mods_initialised', 'mod_xajax::lateProcess');
}

class mod_xajax
{
    function xajax()
    {
        global $mod_xajax_enable;
        $mod_xajax_enable = true;
    }

    // on page assembly check whether or not xajax is needed
    function insertHTML($obj)
    {
        global $mod_xajax_enable;
        if (!isset($mod_xajax_enable))
        {
            return;
        }

        global $xajax;
        $obj->addBody($xajax->getJavascript("mods/xajax/"));
    }

    function lateProcess()
    {
        // let all mods know we're here so they can register their functions
        event::call('mod_xajax_initialised', $this);

        // now process all xajax calls
        global $xajax;
        $xajax->processRequest();
    }
}