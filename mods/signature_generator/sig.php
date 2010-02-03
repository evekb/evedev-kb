<?php
if (!$sig_name = $_GET['s'])
{
	$sig_name = 'default';
}
$sig_name = str_replace('.', '', $sig_name);
$sig_name = str_replace('/', '', $sig_name);

function errorPic($string)
{
	$im = imagecreate(200, 60);
	$black = imagecolorallocate($im, 0, 0, 0);
	$red = imagecolorallocate($im, 250, 200, 20);
	imagefill($im, 1, 1, $black);
	imagestring($im, 3, 10, 10, 'Error: '.$string, $red);
	header('Content-Type: image/jpeg');
	imagejpeg($im);
	exit;
}

if (!$plt_id = intval($_GET['i']))
{
	errorPic('No pilot id specified.');
}
require_once("common/includes/class.pilot.php");
require_once("common/includes/class.corp.php");
require_once("common/includes/class.alliance.php");
require_once("common/includes/class.killlist.php");

$pilot = new Pilot($plt_id);
if (!$pilot->exists())
{
	errorPic('That pilot doesnt exist.');
}

$corp = $pilot->getCorp();
$alliance = $corp->getAlliance();

// we dont generate pictures for non-member
if (ALLIANCE_ID && $alliance->getID() != ALLIANCE_ID)
{
	errorPic('Wrong alliance.');
}
elseif (CORP_ID && $corp->getID() != CORP_ID)
{
	errorPic('Wrong corporation.');
}

$id = abs(crc32($sig_name));
// check for cached version
if (file_exists(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id))
{
	$age = filemtime(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id);

	// cache files for 30 minutes
	if (time() - $age < 30*60)
	{
		if (file_exists('mods/signature_generator/signatures/'.$sig_name.'/typ.png'))
		{
			header('Content-Type: image/png');
		}
		else
		{
			header('Content-Type: image/jpeg');
		}
		readfile(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id);
		return;
	}
}

$pid = $pilot->getExternalID();
$cachePath = KB_CACHEDIR.'/img/pilots/'.substr($pid,0,2).'/'.substr($pid,2,2).'/'.$pid.'_256.jpg';

if (!file_exists($cachePath))
{
	if(!is_dir(KB_CACHEDIR.'/img/pilots/'.substr($pid,0,2).'/'))
			mkdir(KB_CACHEDIR.'/img/pilots/'.substr($pid,0,2).'/');
	if(!is_dir(KB_CACHEDIR.'/img/pilots/'.substr($pid,0,2).'/'.substr($pid,2,2).'/'))
			mkdir(KB_CACHEDIR.'/img/pilots/'.substr($pid,0,2).'/'.substr($pid,2,2).'/');
	// in case of a dead eve server we only want to wait 5 seconds
	@ini_set('default_socket_timeout', 5);
	$file = @file_get_contents('http://img.eve.is/serv.asp?s=256&c='.$pid);
	if ($img = @imagecreatefromstring($file))
	{
		$fp = fopen($cachePath, 'w');
		fwrite($fp, $file);
		fclose($fp);
	}
	else
	{
		// try alternative access via fsockopen
		// happens if allow_url_fopen wrapper is false
		require_once('class.http.php');

		$url = 'http://img.eve.is/serv.asp?s=256&c='.$pid;
		$http = new http_request($url);
		$file = $http->get_content();

		if ($img = @imagecreatefromstring($file))
		{
			$fp = fopen($cachePath, 'w');
			fwrite($fp, $file);
		}
	}
}


// check template
if (!is_dir('mods/signature_generator/signatures/'.$sig_name))
{
	errorPic('Template not found.');
}

// let the template do the work, we just output $im
require('mods/signature_generator/signatures/'.$sig_name.'/'.$sig_name.'.php');

if (file_exists('mods/signature_generator/signatures/'.$sig_name.'/typ.png'))
{
	header('Content-Type: image/png');
	imagepng($im, 'cache/data/sig_'.$id.'_'.$plt_id);
}
else
{
	header('Content-Type: image/jpeg');
	imagejpeg($im, 'cache/data/sig_'.$id.'_'.$plt_id, 90);
}
readfile(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id);