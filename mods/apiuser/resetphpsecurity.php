<?php
require_once( "common/admin/admin_menu.php" );
$page = new Page( "Settings - API user management" );
$page->setCachable(false);
$page->setAdmin();

   config::set('apiuser_registerphpbb', '0');
   config::set('apiuser_forcephpbb', '0');

$html.='PHP BB Security have been neutralized ';
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();

?>
