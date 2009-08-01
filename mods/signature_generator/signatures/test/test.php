<?php
header( "Content-type: image/jpeg" );
//require_once('mods/rank_mod/rank.php');

$font = 'mods/signature_generator/signatures/test/spaceage.ttf';

$im = imagecreatefrompng('mods/signature_generator/signatures/test/test.png');
$overlay = imagecreatefrompng('mods/signature_generator/signatures/test/test.png');
imagesavealpha($im, true);

//colors
$black = imagecolorallocate( $im, 0,0,0 );
$white = imagecolorallocate( $im, 255,255,255 );
$yellow = imagecolorallocate( $im, 255,205,71 );

//kills and last kill
$klist = new KillList();
$klist->setOrdered(true);
$klist->setPodsNoobships(false);
$klist->addInvolvedPilot($pilot);
$last_kill = $klist->getKill();
$klist->getallKills();

//last kill ship picture
$ship_id = $last_kill->getVictimShipExternalID();
$ship_img = imagecreatefrompng("img/ships/64_64/".$ship_id.".png");
imagecopyresampled($im, $ship_img, 60, 30, 0, 0, 24, 24, 64, 64);
imagedestroy($ship_img);

//strings
$pilot_str = $pilot->getName();
$corp_str = $corp->getName();
$killcount = $klist->getCount();
$victim_str = $last_kill->getVictimName() . " - " . $last_kill->getVictimCorpName();
$victimship_str = $last_kill->getVictimShipName();
$killsystem_str = $last_kill->getSolarSystemName();
$rank_str = "";


//rank stuff
/*
$medals=array();
$shipbadges=array();
$weaponbadges=array();
$rank_ttl =config::getnumerical('rankmod_titles');
$rank = GetPilotRank($pilot->GetID(), $killpoints, $medals, $shipbadges, $weaponbadges, $base_rps,$bonus_rps,$rps);
$rank_str = $rank_ttl[$rank]['abbr'];
*/

$name_str = $rank_str. " " .$pilot_str;
$killrow1_str = "Kill #". $killcount . " : " . $victimship_str . ", " . $killsystem_str;
$killrow2_str = $victim_str;


//draw texts
imagefttext($im, 15, 0, 59, 22, $black, $font, $name_str, array('hdpi'=>200));
imagefttext($im, 15, 0, 58, 21, $yellow, $font, $name_str, array('hdpi'=>200));

$box = imagettfbbox( 15, 0, $font, $corp_str );
imagefttext($im, 15, 0, 693-$box[4], 54, $black, $font, $corp_str, array('hdpi'=>200));
imagefttext($im, 15, 0, 692-$box[4], 53, $white, $font, $corp_str, array('hdpi'=>200));

imagefttext($im, 10, 0, 90, 40, $black, $font, $killrow1_str, array('hdpi'=>200));
imagefttext($im, 10, 0, 89, 39, $white, $font, $killrow1_str, array('hdpi'=>200));

imagefttext($im, 10, 0, 90, 54, $black, $font, $killrow2_str, array('hdpi'=>200));
imagefttext($im, 10, 0, 89, 53, $white, $font, $killrow2_str, array('hdpi'=>200));


// player portrait
$portrait_img = imagecreatefromjpeg("cache/portraits/".$pid."_256.jpg");
imagecopyresampled($im, $portrait_img, 8, 8, 0, 0, 44, 44, 256, 256);
imagedestroy($portrait_img);

imagecopyresized($im, $overlay, 0, 0, 0, 0, 700, 60, 700, 60);
imagedestroy($overlay);

imagejpeg($im,NULL,95);
//imagedestroy($im);

?>