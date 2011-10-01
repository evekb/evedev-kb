<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Navigation - Top Navigation');
if ($_GET['incPrio']) {
    increasePriority($_GET['incPrio']);
} else if ($_GET['decPrio']) {
    decreasePriority($_GET['decPrio']);
} else if ($_POST) {
	if ($_POST['add']) {
		newPage($_POST['newname'], $_POST['newurl'], '_self');
	} else if ($_POST['rename']) {
		$id = array_search('rename', $_POST['rename']);
		renamePage($id, $_POST['name'][$id]);
	} else if ($_POST['change']) {
		$id = array_search('change', $_POST['change']);
		changeUrl($id, $_POST['url'][$id]);
	} else if ($_POST['delete']) {
		$id = array_search('delete', $_POST['delete']);
		delPage($id);
	} else if ($_POST['hide']) {
		$id = array_search('hide', $_POST['hide']);
		chgHideStatus($id, 1);
	} else if ($_POST['show']) {
		$id = array_search('show', $_POST['show']);
		chgHideStatus($id, 0);
	} else if ($_POST['reset']) {
		$nav = new Navigation();
		$nav->reset();
	}
}
$qry = DBFactory::getDBQuery(true);
$query = "select * from kb3_navigation WHERE intern = 1 AND KBSITE = '".KB_SITE."' AND descr <> 'About';";

$internal = array();
if ($qry->execute($query)) {
    while ($row = $qry->getRow()) {
		$internal[] = array('id'=>$row['ID'], 'name'=>$row['descr'], 'hidden'=>$row['hidden']);
    }
}
$query = "select * from kb3_navigation WHERE intern = 0 AND KBSITE = '".KB_SITE."';";

$external = array();
if ($qry->execute($query)) {
    while ($row = $qry->getRow()) {
		$external[] = array('id'=>$row['ID'], 'name'=>$row['descr'], 'url'=>$row['url']);
    }
}

$all = array();
$query = "select * from kb3_navigation WHERE nav_type = 'top' AND KBSITE = '".KB_SITE."' ORDER BY posnr ;";
if ($qry->execute($query)) {
    while ($row = $qry->getRow()) {
		$all[] = array('id'=>$row['ID'], 'name'=>$row['descr'], 'pos'=>$row['posnr']);
    }
}

$smarty->assign('inlinks', $internal);
$smarty->assign('outlinks', $external);
$smarty->assign('alllinks', $all);
$html = $smarty->fetch(get_tpl('admin_navmanager'));

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();

function increasePriority($id)
{
	$id = (int) $id;
    $qry = DBFactory::getDBQuery(true);
	$qry->autocommit(false);
    $query = "SELECT posnr FROM kb3_navigation WHERE ID = $id AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
    $row = $qry->getRow();
    $next = $row['posnr'] + 1;

    $query = "UPDATE kb3_navigation SET posnr = (posnr-1) WHERE nav_type = 'top' AND posnr = $next AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);

    $query = "UPDATE kb3_navigation SET posnr = (posnr+1) WHERE ID = $id AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
	$qry->autocommit(true);
}
function decreasePriority($id)
{
	$id = (int) $id;
    $qry = DBFactory::getDBQuery(true);
	$qry->autocommit(false);
    $query = "SELECT posnr FROM kb3_navigation WHERE ID = $id AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
    $row = $qry->getRow();
    $prev = $row['posnr']-1;

    $query = "UPDATE kb3_navigation SET posnr = (posnr+1) WHERE nav_type = 'top' AND posnr = $prev AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);

    $query = "UPDATE kb3_navigation SET posnr = (posnr-1) WHERE ID = $id AND KBSITE = '".KB_SITE."'";;
    $qry->execute($query);
	$qry->autocommit(true);
}

function renamePage($id, $name)
{
	$id = (int) $id;
    $qry = DBFactory::getDBQuery(true);
	$name = $qry->escape($name);
    $query = "UPDATE kb3_navigation SET descr ='$name' WHERE ID=$id AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
}

function changeUrl($id, $url)
{
    $qry = DBFactory::getDBQuery(true);
	$id = (int)$id;
	$url = $qry->escape($url);
    $query = "UPDATE kb3_navigation SET url ='$url' WHERE ID=$id AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
}

function newPage($descr, $url)
{
    $qry = DBFactory::getDBQuery(true);
	$descr = $qry->escape(preg_replace('/[^\w_-\d]/', '', $descr));
	$url = $qry->escape($url);
    $query = "SELECT max(posnr) as nr FROM kb3_navigation WHERE nav_type='top' AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
    $row = $qry->getRow();
    $posnr = $row['nr'] + 1;
    $query = "INSERT INTO kb3_navigation SET descr='$descr', intern=0, nav_type='top', url='$url', target ='', posnr=$posnr, page='ALL_PAGES', KBSITE = '".KB_SITE."'";
    $qry->execute($query);
}

function delPage($id)
{
	$id = (int) $id;
    $qry = DBFactory::getDBQuery(true);
    $query = "DELETE FROM kb3_navigation WHERE ID=$id AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
}

function chgHideStatus($id, $status)
{
	$id = (int) $id;
	$status = (int) $status % 2;
    $qry = DBFactory::getDBQuery(true);
    $query = "UPDATE kb3_navigation SET hidden ='$status' WHERE ID=$id AND KBSITE = '".KB_SITE."'";
    $qry->execute($query);
}