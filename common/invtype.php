<?php
if (!$id = intval($_GET['id']))
{
    $page = new Page('Error');
    $page->setContent('No valid ID specified.');
    $page->generate();
    exit;
}
include_once('common/includes/class.dogma.php');

$item = new dogma($id);

if (!$item->isValid())
{
    $page = new Page('Error');
    $page->setContent('This ID is not a valid dogma ID.');
    $page->generate();
    exit;
}

$page = new Page('Item details - '.$item->get('typeName'));

#$dump = var_export($item, true);
#$smarty->assign('dump', $dump);
$smarty->assign_by_ref('item', $item);

if ($item->get('itt_cat') == 6)
{
    $html = $smarty->fetch(get_tpl('invtype_ship'));
}
else
{
    $html = $smarty->fetch(get_tpl('invtype_item'));
}
$page->setContent($html);
$page->generate();
return;
?>