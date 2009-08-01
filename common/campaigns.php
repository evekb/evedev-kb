<?php
require_once('common/includes/class.contract.php');

$page = new Page('Campaigns');

switch ($_GET['view'])
{
    case '':
        $activelist = new ContractList();
        $activelist->setCampaigns(true);
        $activelist->setActive('yes');
        $page->setTitle('Active campaigns');
        $table = new ContractListTable($activelist);
        $table->paginate(10, $_GET['page']);
        $html .= $table->generate();
        break;
    case 'past':
        $pastlist = new ContractList();
        $pastlist->setCampaigns(true);
        $pastlist->setActive('no');
        $page->setTitle('Past campaigns');
        $table = new ContractListTable($pastlist);
        $table->paginate(10, $_GET['page']);
        $html .= $table->generate();
        break;
}

$menubox = new Box('Menu');
$menubox->setIcon('menu-item.gif');
$menubox->addOption('link', 'Active campaigns', '?a=campaigns');
$menubox->addOption('link', 'Past campaigns', '?a=campaigns&amp;view=past');

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>