<?php
require_once("common/admin/admin_menu.php");

if ($_POST['submit'])
{
	if($_POST['known_members_own']){
		config::set('known_members_own', '1');
		}
		else
		{
		config::set('known_members_own', '0');
		}
		$clmn = "";
		if($_POST['img']) {$clmn .=",img";}
		if($_POST['kll_pnts']) {$clmn .=",kll_pnts";}	
		if($_POST['dmg_dn']) {$clmn .=",dmg_dn";}	
		if($_POST['dmg_rcd']) {$clmn .=",dmg_rcd";}	
		if($_POST['eff']) {$clmn .=",eff";}
		if($_POST['lst_sn']) {$clmn .=",lst_sn";}
		config::set('known_members_clmn', $clmn);	
		$html .= "Setting Saved";
}

$page = new Page("Settings - known members");

$html .= "<div class=block-header2>Global options</div>";
$html .= "<form id=options name=options method=post action=>";
$html .= "<table class=kb-subtable>";
$html .= "<tr><td><b>Remove Known Members page for board owner:</b></td><td><input type=checkbox name=known_members_own id=known_members_own";
if (config::get('known_members_own'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr></table>";


$html .= "<div class=block-header2>Show Columns</div>";
$html .= "<table class=kb-subtable>";
$clmn = config::get('known_members_clmn');

$html .= "<tr><td><b>Add Char. Portrait:</b></td><td><input type=checkbox name=img id=img";
if (strpos($clmn,"img"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Kill Points:</b></td><td><input type=checkbox name=kll_pnts id=kll_pnts";
if (strpos($clmn,"kll_pnts"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Damage Done(isk):</b></td><td><input type=checkbox name=dmg_dn id=dmg_dn";
if (strpos($clmn,"dmg_dn"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Damage Recived(isk):</b></td><td><input type=checkbox name=dmg_rcd id=dmg_rcd";
if (strpos($clmn,"dmg_rcd"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Efficency:</b></td><td><input type=checkbox name=eff id=eff";
if (strpos($clmn,"eff"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Last Seen In:</b></td><td><input type=checkbox name=lst_sn id=lst_sn";
if (strpos($clmn,"lst_sn"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=120></td><td colspan=3 ><input type=submit name=submit value=\"Save\"></td></tr>";
$html .= "</table>";

$html .= "</form>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>