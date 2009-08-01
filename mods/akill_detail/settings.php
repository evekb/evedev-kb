<?php
require_once("common/admin/admin_menu.php");

$colours=array("apoc", "apoc_ammo", "apoc_notext");

if ($_POST['submit']) {
    if($_POST['sammo']) {
        config::set('apocfitting_showammo','1');
    } else {
        config::set('apocfitting_showammo','0');
    }

    if($_POST['sbox']) {
        config::set('apocfitting_showbox','1');
    } else {
        config::set('apocfitting_showbox','0');
    }

    if($_POST['siskd']) {
        config::set('apocfitting_showiskd','1');
    } else {
        config::set('apocfitting_showiskd','0');
    }

    if($_POST['sext']) {
        config::set('apocfitting_showext','1');
    } else {
        config::set('apocfitting_showext','0');
    }

    if($_POST['seft']) {
        config::set('apocfitting_showeft','1');
    } else {
        config::set('apocfitting_showeft','0');
    }

    if($_POST['seft2e']) {
        config::set('apocfitting_showeft2eve','1');
    } else {
        config::set('apocfitting_showeft2eve','0');
    }

    if($_POST['mapmod']) {
        config::set('apocfitting_mapmod','1');
    } else {
        config::set('apocfitting_mapmod','0');
    }

    if($_POST['sidemap']) {
        config::set('apocfitting_sidemap','1');
    } else {
        config::set('apocfitting_sidemap','0');
    }

	config::set('apocfitting_colour',$_POST['panel_colour']);
	config::set('apocfitting_themedir','panel');

	if($_POST['lgreen']) {
		config::set('apocfitting_dropped_colour','#006000');
	} else {
		config::set('apocfitting_dropped_colour','#004000');
	}

	$html .= "<b>Settings Saved</b><br /><br />";
}

$page = new Page("Settings - Apoc Fitting v2.0");
$apocfitting_db = config::get('apocfitting_db');
$showammo = config::get('apocfitting_showammo');
$showbox = config::get('apocfitting_showbox');
$showiskd = config::get('apocfitting_showiskd');
$showext = config::get('apocfitting_showext');
$showeft = config::get('apocfitting_showeft');
$showeft2e = config::get('apocfitting_showeft2eve');
$mapmod = config::get('apocfitting_mapmod');
$sidemap = config::get('apocfitting_sidemap');
$colour = config::get('apocfitting_colour');
$d_colour = config::get('apocfitting_dropped_colour');

// Apoc Fitting Options
/*
if (!$apocfitting_db) {
    include_once './mods/apoc_fitting/sql.php';
    if ($_GET['r'] == "createdb" && !$apocfitting_db) {
	$apocfitting_db_rdy = createtable();
	if ($apocfitting_db_rdy) {
	    config::set('apocfitting_db', '1');
	    $html .= "Table is created, you can enable the mod now.<br />";
	} else {
	    $html .= "Table not created.<br />";
	}
    } else {
	    $html .= "Database changes for Apoc Fitting not processed, <b><a href=\"?a=settings_apoc_fitting&amp;r=createdb\">Setup Database</a></b>.<br />Already processed MySQL changes manually? <a href=\"?a=settings_apoc_fitting&amp;r=dbdone\">Hide Message</a>.<br /><br />";
    }
}

if ($_GET['r'] == "dbdone") {
    config::set('apocfitting_db', '1');
}
*/
$html .= "<div>For further display options, configure the <a href='./?a=admin&amp;field=Appearance&amp;sub=Kill Details'>Kill Details</a> page.</div>";
$html .= "<div class=block-header2>Apoc Fitting Options</div>";
$html .= "<form id=options name=options method=post action=''>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";

$html .= "<tr><td width=300><b>Panel Style:</b></td><td>";
$html.='<select name="panel_colour">';

foreach($colours as $select) {
	$html .='<option value="'.$select.'"';
	if($select == $colour) {
	    $html .= ' selected="selected"';
	}
	$html .='>'.$select.'</option>';
}

$html .="</select></td></tr>";

$html .= "<tr><td><b>Show Ammo, charges, etc:</b></td><td><input type=checkbox name=sammo id=sammo";

if ($showammo == '1') {
    $html .= " checked=\"checked\"";
}

//$html .= "></td></tr></table><br>";
$html .= "></td></tr>";

$html .= "<tr><td><b>Show Total ISK Loss, Damage at top:</b></td><td><input type=checkbox name=siskd id=siskd";

if ($showiskd == '1') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .= "<tr><td><b>Show Top Damage Dealer/Final Blow Boxes:</b></td><td><input type=checkbox name=sbox id=sbox";

if ($showbox == '1') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .= "<tr><td><b>Show EXtended Fitting involved parties:</b></td><td><input type=checkbox name=sext id=sext";

if ($showext == '1') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .= "<tr><td><b>Show EFT Fitting (Menu Option):</b></td><td><input type=checkbox name=seft id=seft";

if ($showeft == '1') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .= "<tr><td><b>Use Lighter Green for Dropped Items:</b></td><td><input type=checkbox name=lgreen id=lgreen";

if ($d_colour == '#006000') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .="</table>";

// Third Party Mod Compatibility Options

$html .= "<div class=block-header2>Third-Party Mod Options</div>";
//$html .= "<form id=options name=options method=post action=>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";

$html .= "<tr><td><b>Show <a href='http://eve-id.net/forum/viewtopic.php?f=505&amp;t=14334' target='_blank'>EFT to EVE</a> (Menu Option):</b></td><td><input type=checkbox name=seft2e id=seft2e";

if ($showeft2e == '1') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .= "<tr><td><b><a href='http://eve-id.net/forum/viewtopic.php?f=505&amp;t=12920' target='_blank'>Map Mod</a> - Enable support:</b></td><td><input type=checkbox name=mapmod id=mapmod";

if ($mapmod == '1') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .= "<tr><td><b><a href='http://eve-id.net/forum/viewtopic.php?f=505&amp;t=12920' target='_blank'>Map Mod</a> - Remove Side Maps:</b></td><td><input type=checkbox name=sidemap id=sidemap";

if ($sidemap == '1') {
    $html .= " checked=\"checked\"";
}

$html .= "></td></tr>";

$html .="</table>";
$html .= "<table class=kb-subtable><tr><td width=120></td><td colspan=3 ><input type=submit name=submit value=\"Save\"></td></tr>";

$html .= "</table><br/>";
$html .= "</form>";

$html.='<hr><div style="font-size: 10px;"><b><a href="http://eve-id.net/forum/viewtopic.php?f=505&amp;t=14696" style="color: lightgreen;" target="_blank">Apoc Fitting v2.0</a> (2009 - <a href="http://www.btcentral.org.uk/" style="color: lightgreen;" target="_blank">Ben Thomas</a>)</b><br />Modified by Hon Kovell for EDK 2.0.0.<br /><br />Special Thanks:<br /><br />Anne Sapyx (EXtended Fitting MOD v. 0.96)<br />Tribalize (MOD Apoch Fitting Screen v1.2)<br /><br />Without you, this would never have been made :)</div>';

// Build Page

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();

?>