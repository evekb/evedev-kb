<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * A simple clock implementation
 * @package EDK
 */
class Clock
{
	/**
	 * Generate html for a clock from the template file.
	 *
	 * @global Smarty $smarty
	 * @return string html generated frm the clock template.
	 */
	function generate()
    {
        global $smarty;

        $smarty->assign('clocktime', gmdate('H:i'));
        return $smarty->fetch(get_tpl('clock'));
    }
}
