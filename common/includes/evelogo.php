<?php
//-----------------------------------------------------------------------------
// evelogo - EVE Online Logo Generator
//
// Copyright (c)2008 Jamie "Entity" van den Berge <entity@vapor.com>
// 
// Permission is hereby granted, free of charge, to any person
// obtaining a copy of this software and associated documentation
// files (the "Software"), to deal in the Software without
// restriction, including without limitation the rights to use,
// copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following
// conditions:
// 
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
// OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
// OTHER DEALINGS IN THE SOFTWARE
//
//-----------------------------------------------------------------------------

define ("EVELOGOVERSION", "V1.2");

// Checks for configuration of files and folders
if (!file_exists("img/corps")) 
{
    if (!mkdir("img/corps", 0777))
	{
		// creating folder failed - spam something about that
		echo "Failed to create folder 'img/corps' you should create the folder yourself and set chmod 777";
	}
} 

function CorporationLogo($data, $size = 64, $filename)
{
	/* Generates corp logo defined by the parameters in data object. The data
object may be an eveapi logo element from the CorporationSheet, a dict
containing the shapes and colors, or a sequence containing a shapes- and colors
sequence. Optionally, size other than the default 64px may be specified, and
transparency can be turned off, in which case it will render the logo on
a background with the color of your choice if specified, otherwise black.*/

	$resourcePath = "img/corplogos";
	
	// eveapi corpsheet logo data
	$shape1 = $data["shape1"];
	$shape2 = $data["shape2"];
	$shape3 = $data["shape3"];
	
	$colour1 = $data["colour1"];
	$colour2 = $data["colour2"];
	$colour3 = $data["colour3"];
	
	//$logo = imagecreatefrompng($resourcePath . "/baselogo.png"); // open image
	$logo = imagecreatetruecolor(64,64);
	imagealphablending($logo, 1);
	imagesavealpha($logo, 1);

	if ($shape3)
	{
		$layer3 = imagecreatefrompng($resourcePath . "/" . $colour3 . "/" . $shape3 . ".png"); // open image
		imagealphablending($layer3, 1); // setting alpha blending on
		imagesavealpha($layer3, 1);
		imagecopy( $logo, $layer3, 0 , 0 , 0, 0,64 , 64);
	} 
	
	if ($shape2)
	{
		$layer2 = imagecreatefrompng($resourcePath . "/" . $colour2 . "/" . $shape2 . ".png"); // open image
		imagealphablending($layer2, 1); // setting alpha blending on
		imagesavealpha($layer2, 1);
		imagecopy( $logo , $layer2 , 0 , 0 , 0 , 0 , 64 , 64 );
	}
	if ($shape1)
	{
		$layer1 = imagecreatefrompng($resourcePath . "/" . $colour1 . "/" . $shape1 . ".png"); // open image
		imagealphablending($layer1, 1); // setting alpha blending on
		imagesavealpha($layer1, 1);
		imagecopy( $logo , $layer1 , 0 , 0 , 0 , 0 , 64 , 64 );
	} 
	
	for ($x=0 ; $x <= 64; $x++)
	{
		for ($y=0 ; $y <= 64; $y++)
		{
			$rgb = imagecolorat( $logo, $x, $y);
			list($r, $g, $b, $a) = imagecolorsforindex($logo, $rgb);
			
			if ($shape1)
			{
				$rgb1 = imagecolorat( $layer1, $x, $y);
				list($r1, $g1, $b1, $alayer1) = imagecolorsforindex($layer1, $rgb1);
				$a1 = ((255 - $alayer1) / 255.0);
			} else {
				$a1 = 1.0;
			} 
			if ($shape2)
			{
				$rgb2 = imagecolorat( $layer2, $x, $y);
				list($r2, $g2, $b2, $alayer2) = imagecolorsforindex($layer2, $rgb2);
				$a2 = ((255 - $alayer2) / 255.0);
			} else {
				$a2 = 1.0;
			} 
			if ($shape3)
			{
				$rgb3 = imagecolorat( $layer3, $x, $y);
				list($r3, $g3, $b3, $alayer3) = imagecolorsforindex($layer3, $rgb3);
				$a3 = ((255 - $alayer3) / 255.0);
			} else {
				$a3 = 1.0;
			} 
			$a = (1.0-($a1*$a2*$a3));
			if ($a)
			{
				$newpix = imagecolorallocatealpha($logo, int($r/$a), int($g/$a), int($b/$a), int(255*$a));
				imagesetpixel($logo, $x, $y, $newpix);
			}
		}
	}//*/
	
	if ($size != 64)
	{
		
		$newsize = imagecreatetruecolor($size, $size);
		imagealphablending ( $newsize , true );
		if(function_exists('imageantialias')) imageantialias ( $newsize , true );
		imagecopyresampled($newsize, $logo, 0, 0, 0, 0, $size, $size, 64, 64);
		//imagepng ( $newsize , "cache/corps/" . $filename . "_" . $size . ".jpg" );
		
	} else {	
		// write logo to disk
		//imagepng ( $logo , "img/corps/" . $filename . ".jpg" );
	}
	
	imagejpeg ( $logo , "img/corps/" . $filename . ".jpg" );
	
	imagedestroy($logo);
	if ($shape1)
		imagedestroy($layer1);
	if ($shape2)
		imagedestroy($layer2);
	if ($shape3)
		imagedestroy($layer3);
}
?>
