<?php
/**
 * @package EDK
 */

edkloader::register('shipImage', dirname(__FILE__)."/shipImage.php");

ob_start();
if (!$sig_name = edkURI::getArg('s', 2)) {
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

function lastKill($id)
{
	$id = (int)$id;
	if (!$id) {
		return false;
	}
	$sql = "SELECT UNIX_TIMESTAMP(max(ind_timestamp)) as last "
			."FROM kb3_inv_detail WHERE ind_plt_id = $id";
	$qry = DBFactory::getDBQuery();
	$qry->execute($sql);

	$row = $qry->getRow();
	if (!$row['last']) {
		return false;
	} else {
		return time() - (int)$row['last'];
	}
}

$plt_id = (int)edkURI::getArg('i');
if ($plt_id) {
	$pilot = Cacheable::factory('Pilot', $plt_id);
} else {
	$plt_ext_id = (int)edkURI::getArg('ext');
	if ($plt_ext_id) {
		$pilot = new Pilot(0, $plt_id);
		$plt_id = $pilot->getID();
	} else {
		$plt_id = edkURI::getArg('id');
		if (!$plt_id) {
			errorPic('No pilot id specified.');
			$pilot = new Pilot();
		} else if ($plt_id < 1000000) {
			$pilot = Cacheable::factory('Pilot', $plt_id);
		} else {
			$pilot = new Pilot(0, $plt_id);
			$plt_id = $pilot->getID();
		}
	}
}
if (!$plt_ext_id) {
	$plt_ext_id = $pilot->getExternalID();
}
// If we still don't have an external ID then just use the internal for names.
if (!$plt_ext_id) {
	$plt_ext_id = $plt_id;
}
if (!$pilot->exists()) {
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
if (file_exists(CacheHandler::exists("{$plt_ext_id}_sig_{$id}.jpg", 'img'))) {
	// cache files for 120 minutes
	if (time() - CacheHandler::age("{$plt_ext_id}_sig_{$id}.jpg", 'img') < 120*60
			|| lastKill($plt_id) > 120 *60)
	{
		if(isset($_SERVER['HTTP_IF_NONE_MATCH'])
		|| isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
			die;
		} else {
			header('Content-Type: image/jpeg');
			readfile(CacheHandler::get("{$plt_ext_id}_sig_{$id}.jpg", 'img'));
			die;
		}
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

if (headers_sent()) {
	trigger_error('An error occured. Headers have already been sent.<br/>', E_USER_ERROR);
}
if (ob_get_contents()) {
	trigger_error('An error occured. Content has already been sent.<br/>', E_USER_ERROR);
} else {
	header('Content-Type: image/jpeg');
	imagejpeg($im, CacheHandler::getInternal("{$plt_ext_id}_sig_{$id}.jpg", 'img'), 90);
	readfile(CacheHandler::getInternal("{$plt_ext_id}_sig_{$id}.jpg", 'img'));
}

ob_end_flush();

die;