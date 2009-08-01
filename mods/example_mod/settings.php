<?php
require_once( "common/admin/admin_menu.php" );

$page = new Page( "Settings - Example Mod" );
                                                                         
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();
?>