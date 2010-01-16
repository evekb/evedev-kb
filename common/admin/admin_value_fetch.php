<?php
require_once( "common/admin/admin_menu.php" );
// Set version
$version = "22/9 2009 - 1";

if($_POST['submit'])
{
	// Set timeout and memory, we neeeeed it ;)
	@set_time_limit(0);
	@ini_set('memory_limit',999999999);
	error_reporting(0);

	require_once('common/admin/admin_menu.php');
	require_once('class.valuefetcher.php');
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

	$fetch = new valueFetcher($url);

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
}
else
{
	// Get from config
	$url = config::get('fetchurl');
	$timestamp = config::get('lastfetch');
	$time = date('r', $timestamp);
	if ($url == null)
	{
		$url = "http://eve.no-ip.de/prices/30d/prices-all.xml";
	}

	$page = new Page( "Settings - Value fetcher" );
	$html = '<center>Mod version: <b><a href="http://eve-id.net/forum/viewtopic.php?f=505&t=9653">'. $version .'</a></b><br><br>';
	$html .= 'Last update: '.$time.'<br><br>';

	$html .= '<form method="post" action="?a=admin_value_fetch">';
	$html .= '<table width="100%" border="1"><tr><td>Update Ship Values</td><td><input type="radio" name="ship" value="shipyes" checked>Yes</td><td><input type="radio" name="ship" value="shipno">No</td></tr>';
	$html .= '<tr><td>Filename</td><td colspan="2"><input type="text" name="turl" id="turl" value="'.$url.'" size=110/></td></tr>';
	$html .= '<tr><td colspan="3" align="center"><i>Leave above field empty to reset to default.</i></td></tr>';
	if ((time() - $timestamp) < 86400)
	{
		$html .= '<tr><td colspan="3" align="center"><b>YOU HAVE UPDATED LESS THAN 24 HOURS AGO!</b></td></tr>';
	}
	$html .= '<tr><td colspan="3"><button value="submit" type="submit" name="submit">Fetch</button></td></tr>';
	$html .= '</table></center>';
}

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
