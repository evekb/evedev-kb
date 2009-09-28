<?php

// Set timeout and memory, we neeeeed it ;)
@set_time_limit(0);
@ini_set('memory_limit',999999999);
error_reporting(0);

require_once('common/admin/admin_menu.php');
require_once('mods/value_fetch/fetcher.php');
/**
* 	Author: Niels Brink� (HyperBeanie)
*
*	Licence: Do what you like with it, credit me as the original author
*		 Not warrantied for anything, might eat your cat.  Your responsibility.
*/


$page = new Page();
$page->setAdmin();
$page->setTitle('Fetcher - Item Values');

// Check if user wants to use a local file
$url = $_POST['turl'];
// If not set, use default
if (!$url) $url = "http://eve.no-ip.de/prices/30d/prices-all.xml";
config::set('fetchurl', $url);

$fetch = new Fetcher($url);

$html = "<center>";
try
{
	$count = $fetch->fetch_values();
	$html .= "Fetched and updated <b>". $count."</b> items!<br /><br />";

	if ($_POST['ship'] == "shipyes")
	{
		$ships = $fetch->updateShips();
		$html .= "Updated ".$ships." ships.";
	}
}
catch (Exception $e)
{
	$html .= "Error in fetch: " . $e->getMessage();
	$html .= "<br />This was probably caused by an incorrect filename";
}
$html .= "</center>";

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>