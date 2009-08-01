<?php
// admin menu now loads all admin pages with options
require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();

$svn_revNew = @exec('svnversion');
if (!empty($svn_revNew))
{
    @unlink('cache/svnrev.php');
    $data = '<'.'?php $svn_rev = "'.$svn_revNew.'";';
    file_put_contents('cache/svnrev.php', $data);
}

if (!$_REQUEST['field'] && !$_REQUEST['sub'])
{
    $_REQUEST['field'] = 'Advanced';
    $_REQUEST['sub'] = 'Configuration';
}
if ($_REQUEST['field'] && $_REQUEST['sub'])
{
    if ($_POST)
    {
        options::handlePost();
    }
    $page->setContent(options::genOptionsPage());
    $page->addContext(options::genAdminMenu());
    if ($_REQUEST['sub'] == 'Configuration' && $_REQUEST['field'] == 'Advanced')
    {
        $page->setTitle('Administration - Board Configuration (Current version: '.KB_VERSION.' '.KB_RELEASE.' Build '.SVN_REV.')');
    }
    $page->generate();
}