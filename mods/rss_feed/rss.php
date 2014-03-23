<?php
/**
 * Author: Doctor Z
 * eMail:  east4now11@hotmail.com
 * @package EDK
 */
require_once("class.rss.php");

$scl_id = (int)edkURI::getArg('scl_id', 2);
header('Content-Type: text/xml');
$html .= "<" . "?xml version=\"1.0\"?" . ">
<rss version=\"2.0\">
<channel>
<title>" . config::get('cfg_kbtitle') . "</title>";
if (edkURI::getArg('losses', 1)) {
	$html .= "<description>20 Most Recent Kills</description>";
} else {
	$html .= "<description>20 Most Recent Losses</description>";
}
$html .= "<link>" . KB_HOST . "</link>
<copyright>" . config::get('cfg_kbtitle') . "</copyright>\n";

$klist = new KillList();
$klist->setOrdered(true);
if (edkURI::getArg('all') || edkURI::getArg('', 1) == 'all') {
	involved::load($klist, 'combined');
} else if (edkURI::getArg('losses') || edkURI::getArg('', 1) == 'losses') {
	involved::load($klist, 'loss');
} else {
	involved::load($klist, 'kill');
}
if ($scl_id) {
	$klist->addVictimShipClass($scl_id);
} else {
	$klist->setPodsNoobShips(false);
}
$klist->setLimit(20);

$table = new RSSTable($klist);
$html .= $table->generate();

$html .= "</channel>
</rss>";
echo $html;