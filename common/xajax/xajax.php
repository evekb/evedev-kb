<?php
/**
 * @package EDK
 */

require_once('common/xajax/xajax_core/xajax.inc.php');
$uri = html_entity_decode(edkURI::build(edkURI::parseURI()));
if(strpos($uri, "?") === false) $uri .= "?xajax=1";
else $uri .= "&xajax=1";

$xajax = new xajax($uri);
event::register('page_assembleheader', 'edk_xajax::insertHTML');

// if mods depend on xajax they can register to xajax_initialised
// it gets called after all mods have been initialized
//event::register('smarty_displayindex', 'edk_xajax::lateProcess');
//event::register('page_assembleheader', 'edk_xajax::lateProcess');
event::register('mods_initialised', 'edk_xajax::lateProcess');
//event::register('page_initialisation', 'edk_xajax::lateProcess');

/**
 * @package EDK
 */
class edk_xajax
{
    public static function xajax()
    {
        global $xajax_enable;
        $xajax_enable = true;
    }

    // on page assembly check whether or not xajax is needed
    public static function insertHTML($obj)
    {
        global $xajax_enable;
        if (!isset($xajax_enable)) {
            return;
        }

        global $xajax;
        $xajax->configure("javascript URI", config::get('cfg_kbhost')."/common/xajax/");
        $obj->addHeader($xajax->getJavascript(config::get('cfg_kbhost')."/common/xajax/"));
    }

    public static function lateProcess()
    {
        $class = get_class();	
        // let all mods know we're here so they can register their functions
        event::call('xajax_initialised', $class);
        // Also register this for old mods registered to the ajax mod.
        event::call('mod_xajax_initialised', $class);

        // now process all xajax calls
        global $xajax;
        $xajax->processRequest();
    }
}

/**
 * Catch calls from old mods.
 * @package EDK
 */
class mod_xajax
{
    public static function xajax()
    {
        edk_xajax::xajax();
    }
}
