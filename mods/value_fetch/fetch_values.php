<?php

// Set timeout and memory, we neeeeed it ;)
@set_time_limit(0);
@ini_set('memory_limit',999999999);

require_once('common/admin/admin_menu.php');
require_once('mods/value_fetch/fetcher.php');
/**
* 	Author: Niels Brinkø (HyperBeanie)
*
*	Licence: Do what you like with it, credit me as the original author
*		 Not warrantied for anything, might eat your cat.  Your responsibility.
*/


$page = new Page();
$page->setAdmin();
$page->setTitle('Fetcher - Item Values');

$fetch = new Fetcher();
$html = "<center>";
if ($_POST['faction'] == 'factionyes')
	$faction = true;
else
	$faction = false;

if ($_POST['php'] == "PHP4")
{
	$count = $fetch->fetch_values_php4($faction);
	$fetch->destroy();
	$html .= $count."</b> from eve-central cached file!<br><br>";
	$html .= "They were added to the database.<br><br>";
}
elseif ($_POST['php'] == "PHP5")
{
	$count = $fetch->fetch_values_php5($faction);
	$html .= $count."</b> from eve-central cached file!<br><br>";
	$html .= "They were added to the database.<br><br>";
}
else
{
	$html .= "No values fetched from eve-central cache";
}

if (($_POST['ship'] == "shipyes") || ($_GET['type'] == 3))
{
	$ships = $fetch->updateShips();
	$html .= "Updated ".$ships." ships.";
}
$html .= "</center>";

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>
