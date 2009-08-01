<?php
$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Role Management');

if ($_REQUEST['action'] == 'search')
{
    $hitlist = array();
    $search = slashfix($_REQUEST['search']);
    $qry = new DBQuery();
    $qry->execute('select usr_login from kb3_user where usr_login like '."'%".$search."%'");
    while ($row = $qry->getRow())
    {
        $hitlist[] = $row['usr_login'];
    }

    $smarty->assign_by_ref('role', $_REQUEST['role']);
    $smarty->assign_by_ref('user', $hitlist);

    $page->addContext($menubox->generate());
    $page->setContent($smarty->fetch(get_tpl('admin_roles_assign')));
    $page->generate();
}
elseif ($_REQUEST['action'] == 'assign')
{
    $qry = new DBQuery();
    $tmp = role::_get($_REQUEST['role']);
    var_dump($tmp);
    #$qry->execute('select usr_login from kb3_user where usr_login like '."'%".$search."%'");
}
elseif ($_REQUEST['action'] == 'create')
{
    $page->addContext($menubox->generate());
    $page->setContent('to be done');
    $page->generate();
}
else
{
    $hardcoded = &role::get(true);
    $softcoded = &role::get();

    $smarty->assign_by_ref('hroles', $hardcoded);
    $smarty->assign_by_ref('sroles', $softcoded);

    $page->addContext($menubox->generate());
    $page->setContent($smarty->fetch(get_tpl('admin_roles')));
    $page->generate();
}
?>