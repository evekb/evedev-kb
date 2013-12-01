<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Set version
$version = "28/9/2010";

$page = new Page('Fetcher - Item Values');
$page->setAdmin();

if($_POST['submit'])
{
	// Set timeout and memory, we neeeeed it ;)
	@set_time_limit(0);
	@ini_set('memory_limit',999999999);
	error_reporting(0);

	/**
	* 	Author: Niels Brink� (HyperBeanie)
	*
	*	Licence: Do what you like with it, credit me as the original author
	*		 Not warrantied for anything, might eat your cat.  Your responsibility.
	*/

	// Check if user wants to use a local file
	$url = $_POST['turl'];
	// If not set, use default
	if (!$url) $url = "http://eve.no-ip.de/prices/30d/prices-all.xml";
	config::set('fetchurl', $url);

	$fetch = new valueFetcher($url);

	$html = "<center>";
	try
	{
		$fetchfaction = false;
		if ($_POST['faction'] == "factionyes")
		{
			$fetchfaction = true;
		}
		$count = $fetch->fetch_values($fetchfaction);
		$html .= "Fetched and updated <b>". $count."</b> items!<br /><br />";

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

	$html .= 'Last update: '.$time.'<br /><br />';

	$html .= '<form method="post" action="'.edkURI::page("admin_value_fetch").'">';
	$html .= '<table width="100%" border="1">';
	$html .= '<tr><td>Update Faction Values</td><td><input type="radio" name="faction" value="factionyes" />Yes</td><td><input type="radio" name="faction" value="factionno" checked="checked" />No</td></tr>';
	$html .= '<tr><td>Filename</td><td colspan="2"><input type="text" name="turl" id="turl" value="'.$url.'" size="110" /></td></tr>';
	$html .= '<tr><td colspan="3" align="center"><i>Leave above field empty to reset to default.</i></td></tr>';
	if ((time() - $timestamp) < 86400)
	{
		$html .= '<tr><td colspan="3" align="center"><b>YOU HAVE UPDATED LESS THAN 24 HOURS AGO!</b></td></tr>';
	}
	$html .= '<tr><td colspan="3"><button value="submit" type="submit" name="submit">Fetch</button></td></tr>';
	$html .= '</table></form>';
	$html .= '<br /><a href="'.edkURI::page('admin_value_editor').'">Manually update values</a>';
}

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
