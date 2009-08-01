<?php
require_once('common/includes/class.parser.php');
require_once('common/includes/class.phpmailer.php');
require_once('common/includes/class.kill.php');

$page = new Page('PHP BB3 Error page');
$kb = new Killboard(KB_SITE);
$html.='<h1>You Need to be logged to the forum before</h1>'; 
$page->setContent($html);
$page->generate();
unset($_SESSION['phpOK']);

?>