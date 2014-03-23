<?php
/**
 * @package EDK
 */

$page = new Page('Signature Listing');

$signatures = array();
$dir = opendir(dirname(__FILE__).'/signatures');
while ($line = readdir($dir))
{
    if (file_exists(dirname(__FILE__).'/signatures/'.$line.'/'.$line.'.php'))
    {
        $signatures[] = $line;
    }
}
$smarty->assign('signatures', $signatures);
$smarty->assign('kb_host', KB_HOST);
if(intval($_GET['ext'])) 
{
	$pilot = new Pilot(0, $_GET['ext']);
	$smarty->assign('pilot', intval($pilot->getID()));
}
else $smarty->assign('pilot', intval($_GET['i']));


$page->setContent($smarty->fetch('file:'.dirname(__FILE__).'/sig_list.tpl'));
$page->generate();
