<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Role Management');

if ($_POST['action'] == 'search')
{
	$hitlist = array();
	$search = slashfix($_POST['search']);
	$qry = DBFactory::getDBQuery();
	$qry->execute('select usr_login from kb3_user where usr_login like '."'%".$search."%'");
	while ($row = $qry->getRow())
	{
		$hitlist[] = $row['usr_login'];
	}

	$smarty->assignByRef('role', $_POST['role']);
	$smarty->assignByRef('user', $hitlist);

	$page->addContext($menubox->generate());
	$page->setContent($smarty->fetch(get_tpl('admin_roles_assign')));
	$page->generate();
}
elseif ($_POST['action'] == 'assign')
{
	$qry = DBFactory::getDBQuery();
	$tmp = role::_get($_POST['role']);
	var_dump($tmp);
	#$qry->execute('select usr_login from kb3_user where usr_login like '."'%".$search."%'");
}
elseif ($_POST['action'] == 'create')
{
	$page->addContext($menubox->generate());
	$page->setContent('to be done');
	$page->generate();
}
else
{
	$hardcoded = &role::get(true);
	$softcoded = &role::get();

	$smarty->assignByRef('hroles', $hardcoded);
	$smarty->assignByRef('sroles', $softcoded);

	$page->addContext($menubox->generate());
	$page->setContent($smarty->fetch(get_tpl('admin_roles')));
	$page->generate();
}
