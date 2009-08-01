<?php
$page = new Page('Signature Listing');

$signatures = array();
$dir = opendir('mods/signature_generator/signatures');
while ($line = readdir($dir))
{
    if (file_exists('mods/signature_generator/signatures/'.$line.'/'.$line.'.php'))
    {
        $signatures[] = $line;
    }
}
$smarty->assign('signatures', $signatures);
$smarty->assign('pilot', intval($_REQUEST['i']));
$smarty->assign('kb_host', KB_HOST);

$page->setContent($smarty->fetch('file:'.getcwd().'/mods/signature_generator/sig_list.tpl'));
$page->generate();
?>