<?php
require_once( "common/admin/admin_menu.php" );
$version = "5/4 2008 - 1";
$page = new Page( "Settings - Value fetcher" );
// Set version
$html = '<center>Mod version: <b><a href="http://www.eve-dev.net/e107_plugins/forum/forum_viewtopic.php?9153.0">'. $version .'</a></b><br><br>';

$html .= '<form method="post" action="?a=fetch_values">';
$html .= '<table width="350" border="1"><tr><td>Php Version</td><td>Update Ship Values</td><td>Faction Prices</td></tr>';
$html .= '<tr><td><input type="radio" name="php" value="PHP5" checked>PHP5</td><td><input type="radio" name="ship" value="shipyes" checked>Yes</td><td><input type="radio" name="faction" value="factionyes">Yes</td></tr>';
$html .= '<tr><td><input type="radio" name="php" value="PHP4">PHP4</td><td><input type="radio" name="ship" value="shipno">No</td><td><input type="radio" name="faction" value="factionno" checked>No</td></tr>';
$html .= '<tr><td colspan="3"><button value="submit" type="submit" name="submit">Fetch</button></td></tr>';
$html .= '</table></center>';

$html .= "<br><br><br><center><b><a href='?a=fetch_values&type=3'>Update ship values from items table.</a></b></center>";
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();
?>
