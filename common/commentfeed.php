<?php
/**
 * Create a feed of comments stored on this board.
 *
 * Flags
 * kll_id = show one kill only.
 * kll_ext_id = show one kill only.
 *
 * @package EDK
 */

$commentfeedversion = "1.0";

$xml = "<?xml version='1.0' encoding='UTF-8'?>
<edkapi feed='comments' version='$commentfeedversion'>
</edkapi>";
$sxe = new SimpleXMLElement($xml);

$killID = edkURI::getArg('kll_id');
if ($killID !== false)
	$killID = (int)$killID;
else
	unset($killID);

$extKillID = edkURI::getArg('kll_ext_id');
if ($extKillID !== false) {
	$kill = new Kill($extKillID, true);
	$killID = $killID->getID();
}
unset($extKillID);

if (isset($killID) && $killID != 0) {
	$comments = new Comments($killID);
	$comments = $comments->getXml();
	if ($comments !== false)
		sxe_append($sxe, $comments->{$comments->getName()});
}

if (edkURI::getArg('html')) {
	foreach($sxe->comments->comment as $comment) {//move to xajax_functions?
		$html .= 'Time: '.$comment->time.'<br />';
		$html .= 'Name: '.$comment->name.'<br />';
		$html .= 'Text: '.$comment->text.'<br />';
	}
	
	echo $html;
} else {
	header("Content-Type: text/xml");
	echo $sxe->asXML();
}
