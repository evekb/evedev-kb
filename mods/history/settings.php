<?php
require_once( "common/admin/admin_menu.php" );

$page = new Page( "Settings - Revision History" );

$html .= "This Mod only works with PHP5!";
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();
?>
