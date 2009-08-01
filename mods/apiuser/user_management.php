<?php
require_once( "common/admin/admin_menu.php" );
require_once('common/includes/class.ship.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once( "common/includes/class.eveapi.php" );

$page = new Page( "Settings - API user management" );
$page->setCachable(false);
$page->setAdmin();

/* Setup xajax script */
mod_xajax::xajax();

//***
if (isset($_GET['delete']) && isset($_GET['userID']))
{
	$qry = new DBQuery();
	$qry->execute("select usr_id from kb3_api_user where usr_id>0 and userID=".intval($_GET['userID']));
	$row = $qry->getRow();
	$qry->execute("delete from kb3_api_user where userID=".intval($_GET['userID']));
	$qry->execute("delete FROM kb3_user_titles where ust_usr_id=".$row['usr_id']);
	$qry->execute("delete FROM kb3_user where usr_id=".$row['usr_id']." and usr_site='".KB_SITE."'");
}


define (APIUSER_VERSION,'0.5');

$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpfile=config::get('apiuser_phpbbrelative').'/common.' . $phpEx;


$html .= "<div class=block-header2>List Of ";
if (config::get('apiuser_show3char'))
	$html.='<i>all stored</i>';
	else
	$html.='<i>valid</i>';
$html.=' character</div>';


$html.=apiuser::affListMember();

#$html.='<div class="block-header2">Roles/Title Management</div>';
#$html.='<a href="?a=admin_roles">Acces to the roles/Titles managment</a>';

$html.='<span class="killcount">APIUser Version '.config::get('apiuser_version').'</span>';
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();