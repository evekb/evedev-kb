<?php
require_once('common/includes/class.kill.php');

$kll_id = intval($_GET['kll_id']);

$html .= "<html><head><title>IGB Killboard</title></head><body>";
$html .= "<a href=\"".KB_HOST."?a=post_igb\">Post killmail</a> | <a href=\"".KB_HOST."?a=portrait_grab\">Update portrait</a> | <a href=\"".KB_HOST."?a=igb&mode=kills\">Kills</a> | <a href=\"".KB_HOST."?a=igb&mode=losses\">Losses</a><br><br>";

$kill = new Kill($kll_id);
if ($kill){
	$raw = $kill->getRawMail();
	$html.= "<pre>$raw</pre>";
}else{
	$html .= "Killmail not found.";
}
echo $html;
?>