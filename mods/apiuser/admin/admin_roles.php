<?php
$page = new Page();
$page->setAdmin();
/* Setup xajax script */

if ($_REQUEST['action'] == 'delete')
{
    $qry = new DBQuery();
    $qry->execute("delete from kb3_titles where ttl_id='" . intval($_REQUEST['id']) . "' and ttl_site='" . KB_SITE . "'");
}
$page->setTitle('Administration - Role Management');

mod_xajax::xajax();
// Affichage Unique
$qry = new DBQuery();
if ($_GET['action'] == 'create_title')
{
    $qry->execute("INSERT into kb3_titles  values (0,'Enter Name Here','Enter Description Here','" . KB_SITE . "')");
}
$qry->execute('select * from kb3_titles  where ttl_site=\'' . KB_SITE . "' order by ttl_name");
$title = array();
while ($row = $qry->getRow())
$title[] = $row;

if ($_REQUEST['action'] == 'assign')
{
    $qry = new DBQuery();
    $tmp = role::_get($_REQUEST['role']);
    var_dump($tmp);
    // $qry->execute('select usr_login from kb3_user where usr_login like '."'%".$search."%'");
}elseif ($_REQUEST['action'] == 'create')
{
    $page->addContext($menubox->generate());

    $page->setContent($smarty->fetch(get_tpl('admin_roles_create')));
    $page->generate();
}
else
{
    $hardcoded = &role::get(true);
    $softcoded = &role::get();

    $smarty->assign_by_ref('hroles', $hardcoded);
    $smarty->assign_by_ref('sroles', $softcoded);
    $smarty->assign_by_ref('lsttitle', $title);

    $qry->execute("select rol_name,c.ttl_id  ,ttl_name
from kb3_titles c
left join kb3_titles_roles b  on ( c.ttl_id=b.ttl_id)
left join kb3_roles a  on (b.rol_id=a.rol_id)
order by rol_name
	");
    $lstRoles = array();
    while ($row = $qry->getRow())
    $lstRoles[$row['ttl_id']] .= $row['rol_name'] . ', ';

    foreach ($lstRoles as $key => $val)
    {
        $lstRoles[$key] = substr($lstRoles[$key], 0, strlen($lstRoles[$key]) - 2);
        if (strlen($lstRoles[$key]) == 0)
            $lstRoles[$key] = 'Nothing';
    }
    foreach ($title as $key => $val)
    {
        $title[$key]['lstRoles'] = $lstRoles[$val['ttl_id']];
    }
    $smarty->assign_by_ref('lstRoles', $lstRoles);
    $page->addContext($menubox->generate());
    $page->setContent($smarty->fetch('../mods/apiuser/templates/admin_roles.tpl'));
    $page->generate();
}