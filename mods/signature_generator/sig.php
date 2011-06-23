<?php
/**
 * @package EDK
 */

ob_start();
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

if ($plt_id = intval($_GET['i']))
{
	$pilot = new Pilot($plt_id);
}
else if ($plt_id = intval($_GET['ext']))
{
	$pilot = new Pilot(0, $plt_id);
	$plt_id = $pilot->getID();
}
else
{
	errorPic('No pilot id specified.');
	$pilot = new Pilot();
}

if (!$pilot->exists())
{
	errorPic('That pilot doesnt exist.');
}

$corp = $pilot->getCorp();
$alliance = $corp->getAlliance();

// we dont generate pictures for non-members
if (array_search($alliance->getID(), config::get('cfg_allianceid')) === false
		&& !array_search($corp->getID(), config::get('cfg_corpid')) === false
		&& !array_search($pilot->getID(), config::get('cfg_pilotid')) === false)
{
	errorPic('Invalid pilot');
}

$id = abs(crc32($sig_name));
// check for cached version
if (file_exists(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id))
{
	$age = filemtime(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id);

	// cache files for 30 minutes
	if (time() - $age < 30*60)
	{
		if (file_exists(dirname(__FILE__).'/signatures/'.$sig_name.'/typ.png'))
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
$cachePath = $pilot->getPortraitPath(256);

$thumb = new thumb($pid, 256);
if(!$thumb->isCached()) $thumb->genCache();

// check template
if (!is_dir(dirname(__FILE__).'/signatures/'.$sig_name))
{
	errorPic('Template not found.');
}

// let the template do the work, we just output $im
require(dirname(__FILE__).'/signatures/'.$sig_name.'/'.$sig_name.'.php');

if (headers_sent())
{
	trigger_error('An error occured. Headers have already been sent.<br/>', E_USER_ERROR);
}
if (ob_get_contents())
{
	trigger_error('An error occured. Content has already been sent.<br/>', E_USER_ERROR);
}
else if (file_exists(dirname(__FILE__).'/signatures/'.$sig_name.'/typ.png'))
{
	header('Content-Type: image/png');
	imagepng($im, 'cache/data/sig_'.$id.'_'.$plt_id);
	readfile(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id);}
else
{
	header('Content-Type: image/jpeg');
	imagejpeg($im, 'cache/data/sig_'.$id.'_'.$plt_id, 90);
	readfile(KB_CACHEDIR.'/data/sig_'.$id.'_'.$plt_id);
}

ob_end_flush();