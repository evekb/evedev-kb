<?php
require_once("common/admin/admin_menu.php");

$colours =array("ArmyGreen" ,
"CoolGray" ,
"DarkOpaque" ,
"Desert" ,
"Revelations" ,
"RevelationsII" ,
"Silver" ,
"Stealth" ,
"SteelGray" ,
"Trinity" ,
"Gold" ,
"Red" ,
"Blue" ,
"Green" ,
"Yellow" ,
"LightBlue" ,
"Black" );

$p_styles =array("Windowed" ,
"OldWindow" ,
"Border" ,
"Faded" );

$s_styles =array("ring" ,
"square" ,
"round" ,
"backglowing" );

$a_styles =array("solid" ,
"transparent" );


if ($_POST['submit'])
{
	$data = ",2tag";
	if($_POST['ftag']) {$data .=",ftag";}
	if($_POST['dtag']) {$data .=",dtag";}	
	if($_POST['otag']) {$data .=",otag";}	
	if($_POST['2fit']) {$data .=",2fit";}	
	if($_POST['ffit']) {$data .=",ffit";}
	if($_POST['dfit']) {$data .=",dfit";}
	if($_POST['ofit']) {$data .=",ofit";}
	config::set('fittingxp_data', $data);
	config::set('fittingxp_colour',$_POST['panel_colour']);
	config::set('fittingxp_style',$_POST['panel_style']);
	if ($_POST['panel_colour'] == 'Black')
	{
		config::set('fittingxp_themedir','panel/black');
	}
	else
	{
		config::set('fittingxp_themedir','panel');
	}
	config::set('fittingxp_item_style',$_POST['panel_show_style']);
	config::set('fittingxp_ammo_style',$_POST['panel_ammo_style']);
	if($_POST['lgreen'])
	{
		config::set('fittingxp_dropped_colour','#006000');
	}
	else
	{
		config::set('fittingxp_dropped_colour','#004000');

	}
	$html .= "Settings Saved";
}

$page = new Page("Settings - Fitting XP version 3.5");

$colour = config::get('fittingxp_colour');
$d_colour = config::get('fittingxp_dropped_colour');
$style = config::get('fittingxp_style');
$i_style = config::get('fittingxp_item_style');
$a_style = config::get('fittingxp_ammo_style');
$data = config::get('fittingxp_data');
$html .= "<div class=block-header2>Ship Details Options</div>";
$html .= "<form id=options name=options method=post action=>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td><b>Show Faction Tag:</b></td><td><input type=checkbox name=ftag id=ftag";
if (strpos($data,"ftag"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Show Deadspace Tag:</b></td><td><input type=checkbox name=dtag id=dtag";
if (strpos($data,"dtag"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Show Officer Tag:</b></td><td><input type=checkbox name=otag id=otag";
if (strpos($data,"otag"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Use Lighter Green for Dropped Items:</b></td><td><input type=checkbox name=lgreen id=lgreen";
if ($d_colour == '#006000')
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr></table><br>";


$html .= "<div class=block-header2>Fitting Panel Options</div>";


$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";

$html .= "<tr><td width=300><b>Panel Colour:</b></td><td>";
$html.='<select name="panel_colour">';
foreach($colours as $select)
{
	$html .='<option value="'.$select.'"';
	if($select == $colour)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$select.'</option>';
}
$html .="</select></td></tr>";

$html .= "<tr><td width=300><b>Panel Style:</b></td><td width=60>";
$html.='<select name="panel_style">';
foreach($p_styles as $sts)
{
	$html .='<option value="'.$sts.'"';
	if($sts == $style)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$sts.'</option>';
}
$html .="</select></td></tr>";

$html .= "<tr><td width=300><b>Highlight Item Style:</b></td><td>";
$html.='<select name="panel_show_style">';
foreach($s_styles as $show)
{
	$html .='<option value="'.$show.'"';
	if($show == $i_style)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$show.'</option>';
}
$html .="</select></td></tr>";

$html .= "<tr><td width=300><b>Highlight Ammo Background</b></td><td>";
$html.='<select name="panel_ammo_style">';
foreach($a_styles as $ammo)
{
	$html .='<option value="'.$ammo.'"';
	if($ammo == $a_style)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$ammo.'</option>';
}
$html .="</select></td></tr>";

$html .= "<tr><td width=300><b>Highlight T2 Items:</b></td><td><input type=checkbox name=2fit id=2fit";
if (strpos($data,"2fit"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Highlight Faction Items:</b></td><td><input type=checkbox name=ffit id=ffit";
if (strpos($data,"ffit"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Highlight Deadspace Items:</b></td><td><input type=checkbox name=dfit id=dfit";
if (strpos($data,"dfit"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Highlight Officer Items:</b></td><td><input type=checkbox name=ofit id=ofit";
if (strpos($data,"ofit"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr></table>";

$html .= "<table class=kb-subtable><tr><td width=120></td><td colspan=3 ><input type=submit name=submit value=\"Save\"></td></tr>";
$html .= "</table>";

$html .= "</form>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>