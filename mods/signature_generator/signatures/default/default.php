<?php
/**
 * @package EDK
 */

define('MPATH', dirname(__FILE__)."/");
$im = imagecreatefrompng(MPATH.'default.png');

$red = imagecolorallocate($im, 255, 10, 10);
$orange = imagecolorallocate($im, 150, 120, 20);
$blue = imagecolorallocate($im, 0, 0, 200);
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);

$grey_trans = imagecolorallocatealpha($im, 50, 50, 50, 50);
$greyred_trans = imagecolorallocatealpha($im, 50, 10, 10, 50);

$name = $pilot->getName();

$list = new KillList();
$list->setOrdered(true);
$list->setLimit(1);
$list->setPodsNoobships(false);
$list->addInvolvedPilot($pilot);
$kill = $list->getKill();

// corp
$box = imagettfbbox(10, 0, MPATH.'GUNSHIP2.TTF', $corp->getName());
$width = $box[4];
imagettftext($im, 10, 0, 319-$width, 71, $black, MPATH.'GUNSHIP2.TTF', $corp->getName());
imagettftext($im, 10, 0, 318-$width, 70, $red, MPATH.'GUNSHIP2.TTF', $corp->getName());

// player
$box = imagettfbbox(16, 0, MPATH.'GUNSHIP2.TTF', $name);
$width = $box[4];
imagettftext($im, 16, 0, 319-$width, 91, $black, MPATH.'GUNSHIP2.TTF', $name);
imagettftext($im, 16, 0, 318-$width, 90, $red, MPATH.'GUNSHIP2.TTF', $name);

// time, victim, victim corp and ship killed
imagettftext($im, 11, 0, 39, 16, $black, MPATH.'spaceage.ttf', $kill->getTimeStamp());
imagettftext($im, 11, 0, 38, 15, $white, MPATH.'spaceage.ttf', $kill->getTimeStamp());
imagettftext($im, 11, 0, 39, 26, $black, MPATH.'spaceage.ttf', $kill->getVictimName());
imagettftext($im, 11, 0, 38, 25, $white, MPATH.'spaceage.ttf', $kill->getVictimName());
imagettftext($im, 11, 0, 39, 36, $black, MPATH.'spaceage.ttf', $kill->getVictimCorpName());
imagettftext($im, 11, 0, 38, 35, $white, MPATH.'spaceage.ttf', $kill->getVictimCorpName());

imagettftext($im, 11, 0, 6, 46, $black, MPATH.'spaceage.ttf', $kill->getVictimShipName());
imagettftext($im, 11, 0, 5, 45, $white, MPATH.'spaceage.ttf', $kill->getVictimShipName());

// ship
$sid = $kill->getVictimShipExternalID();
$img = shipImage::get($sid);
imagecopyresampled($im, $img, 5, 5, 0, 0, 30, 30, 64, 64);

// player portrait

if (!$pid)
{
    $pid = 0;
}
$img = imagecreatefromjpeg(Pilot::getPortraitPath(256,$pid));
imagefilledrectangle($im, 318, 18, 392, 92, $greyred_trans);
imagecopyresampled($im, $img, 320, 20, 0, 0, 70, 70, 256, 256);
imagedestroy($img);
?>