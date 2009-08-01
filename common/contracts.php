<?php
require_once('common/includes/class.contract.php');

$page = new Page('Contracts');

switch ($_GET['view'])
{
    case '':
        $activelist = new ContractList();
        $activelist->setCampaigns(false);
        $activelist->setActive('yes');
        $page->setTitle('Active contracts');
        $table = new ContractListTable($activelist);
        $html .= $table->generate();
        break;
    case 'past':
        $pastlist = new ContractList();
        $pastlist->setCampaigns(false);
        $pastlist->setActive('no');
        $page->setTitle('Past contracts');
        $table = new ContractListTable($pastlist);
        $html .= $table->generate();
        break;
}

$menubox = new box('Menu');
$menubox->setIcon('menu-item.gif');
$menubox->addOption('link', 'Active contracts', '?a=contracts');
$menubox->addOption('link', 'Past contracts', '?a=contracts&view=past');

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>