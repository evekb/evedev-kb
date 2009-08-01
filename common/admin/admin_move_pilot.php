<?php
require_once('common/includes/class.kill.php');
require_once('common/includes/class.pilot.php');
require_once('common/includes/class.corp.php');

$dbconn = new DBConnection();
$pilot = new Pilot($_GET['plt_id']);

$page = new Page('Administration - Change Pilots Corp ('.$pilot->getName().')');
$page->setAdmin();


if (!$pilot->exists())
{
    $html = "That pilot doesn't exist.";
    $page->generate($html);
    exit;
}

if($_POST['confirm'])
{
	$sql = "UPDATE `kb3_pilots` SET `plt_crp_id` = '".$_POST['crp_id']."' WHERE `plt_id` =".$_POST['plt_id'];
	$qry = new DBQuery();
	$qry->execute($sql);
	$html .= "Pilot Moved";
}
if($_REQUEST['crp'])
{
	$corp = new Corporation(intval($_GET['crp']));

	$html .= "<form id=change method=post action=><table class=kb-subtable>";
	$html .= "<tr><td><input name=crp_id type=hidden value=".$_GET['crp'].">";
	$html .= "<input name=plt_id type=hidden value=".$_GET['plt_id'].">";
	$html .= "Confirm move<b> ".$pilot->getName()."</b> to <b>".$corp->getName()."</b></td></tr>";
	$html .= "<tr><td><input type=submit name=confirm value=\"Move\"></td></tr>";
	$html .= "</table>";
}

if($_POST['search'])
{
	$sql = "SELECT * FROM `kb3_corps` WHERE crp_name LIKE '%".$_POST['search']."%'";
	$qry = new DBQuery();
	$qry->execute($sql);
										//$html .= $sql ;
	$html .= "<div class=block-header2>Results</div>";
	$html .= "<table class=kb-subtable>";
		while ($row = $qry->getRow())
		{
		$html .= "<tr><td><a href=\"?a=admin_move_pilot&plt_id=".$_GET['plt_id']."&crp=".$row['crp_id']."\">";
		$html .= $row['crp_name']."<br/>";
		$html .= "</td><tr>";
		}
	$html .= "</table>";

}

$html .= "<div class=block-header2>Search</div>";
$html .= "<form id=options name=options method=post action=>";
$html .= "<table class=kb-subtable>";
$html .= "<tr><td>Seach for corp</td><td><input name=search id=serach type=text size=10 /></td></tr>";
$html .= "<tr><td><input type=submit name=find value=\"Find\"></td><td></td></tr>";
$html .= "</table>";

$page->setContent($html);
$page->generate();
?>