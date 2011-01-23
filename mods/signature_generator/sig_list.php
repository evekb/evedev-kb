<?php
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
$smarty->assign('pilot', intval($_REQUEST['i']));
$smarty->assign('kb_host', KB_HOST);

$page->setContent($smarty->fetch('file:'.dirname(__FILE__).'/sig_list.tpl'));
$page->generate();
