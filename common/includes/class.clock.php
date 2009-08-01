<?php

//! A simple clock implementation
class Clock
{
	//! Generate html for a clock from the template file.
	
	//! \return html generated frm the clock template.
	function generate()
    {
        global $smarty;

        $smarty->assign('clocktime', gmdate('H:i'));
        return $smarty->fetch(get_tpl('clock'));
    }
}
?>