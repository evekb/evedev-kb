<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// admin menu now loads all admin pages with options
require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();

if (!$_GET['field'] && !$_GET['sub'])
{
    $_GET['field'] = 'Advanced';
    $_GET['sub'] = 'Configuration';
}
if ($_GET['field'] && $_GET['sub'])
{
    if ($_POST)
    {
        options::handlePost();
    }
    $page->setContent(options::genOptionsPage());
    $page->addContext(options::genAdminMenu());
    if ($_GET['sub'] == 'Configuration' && $_GET['field'] == 'Advanced')
    {
        $page->setTitle('Administration - Board Configuration (Current version: '.KB_VERSION.' '.KB_RELEASE.' Build '.SVN_REV.')');
    }
    $page->generate();
}
