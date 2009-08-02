<?php

/**
* Author: Doctor Z
* eMail:  east4now11@hotmail.com
*
*/

require_once("common/includes/class.corp.php");
require_once("common/includes/class.alliance.php");
require_once("common/includes/class.killlist.php");
require_once("common/includes/class.killlisttable.php");
require_once("class.rss.php");

header('Content-Type: text/xml');
$html .= "<"."?xml version=\"1.0\"?".">
<rss version=\"2.0\">
<channel>
<title>".KB_TITLE."</title>";
if(!isset($_REQUEST['losses']))
	$html .= "<description>20 Most Recent Kills</description>";
else
	$html .= "<description>20 Most Recent Losses</description>";
$html .= "<link>".KB_HOST."</link>
<copyright>".KB_TITLE."</copyright>\n";

$klist = new KillList();
$klist->setOrdered(true);
if(isset($_REQUEST['losses']) && isset($_REQUEST['kills']))
	involved::load($klist,'combined');
if(!isset($_REQUEST['losses']) && isset($_REQUEST['kills']))
	involved::load($klist,'kill');
else
	involved::load($klist,'kill');
if ($_GET['scl_id'])
{
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
}
else
{
    $klist->setPodsNoobShips(false);
}
$klist->setLimit(20);

$table = new RSSTable($klist);
$html .= $table->generate();

$html .= "</channel>
</rss>";
echo $html;
?>