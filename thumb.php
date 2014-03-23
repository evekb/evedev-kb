<?php
/**
 * $Date: 2010-05-29 20:59:20 +1000 (Sat, 29 May 2010) $
 * $Revision: 705 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/thumb.php $
 *
 * @package EDK
 */
@error_reporting(E_ERROR);

// We don't alter images often. Let's save time and leave it up to the
// browser to force a refresh if they want to.
if(isset($_SERVER['HTTP_IF_NONE_MATCH'])
		|| isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
{
	header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
	die;
}

// The exciting new thumb/blah/xxx
// Some servers set PATH_INFO when it's empty.
if(isset($_SERVER['PATH_INFO']) && !isset($_GET['id']))
{
	// Split on /, _, or . => works with:
	// thumb/Character/123456/32 as well as thumb/Character/123456_32.jpg
	$args = preg_split('/[\/_.]/', trim($_SERVER['PATH_INFO'], "/"));

	$type = "type";
	$size = 64;
	$id = 0;

	if(isset($args[0]) && is_numeric($args[0]))
	{
		$id = intval($args[0]);
		if(isset($args[1]))
		{
			$size = intval($args[1]);
		}
	}
	else if(isset($args[1]))
	{
		$type = strtolower($args[0]);
		$id = intval($args[1]);
		if(isset($args[2]))
		{
			$size = intval($args[2]);
		}
	}
}
// Ye olde thumb.php?type=blah&id=xxx
else
{
	if(!isset($_GET['size'])) $size = 64;
	else $size = @intval($_GET['size']);

	if(!isset($_GET['type'])) $type = "type";
	else $type = $_GET['type'];
	$type = strtolower($type);

	if(!isset($_GET['id'])) $id = 0;
	else $id = @intval($_GET['id']);
}

$imghost = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$imghost = substr($imghost, 0, strrpos($imghost, '/', 1)).'/';

if(preg_match("/[^\w\d-_]/", $type))
{
	header("Location: {$imghost}img/portrait_0_{$size}.jpg");
	die;
}

switch($type)
{
	case 'pilot':
	case 'corp':
	case 'alliance':
		goPCA($type, $id, $size, $imghost);
		break;
	case 'character':
		goPCA('pilot', $id, $size, $imghost);
		break;
	case 'corporation':
		goPCA('corp', $id, $size, $imghost);
		break;

	case 'region':
	case 'map':
	case 'cons':
		goMap($type, $id, $size);
		break;

	case 'type':
	case 'ship':
		goType($type, $id, $size, $imghost);
		break;
	case 'inventorytype':
		goType('type', $id, $size, $imghost);
		break;
	case 'render':
		goType('ship', $id, $size, $imghost);
}


header("Location: {$imghost}img/portrait_0_64.jpg");
die;

function goPCA($type, $id, $size = 64, $imghost = "")
{
	//TODO integrate the existing common/includes/class.thumb.php

	if($size != 32 && $size != 64 && $size != 128 && $size != 256) show404();

	$year = 31536000; // 365 * 24 * 60 * 60
	// PHP5.3+ only
	//header_remove();
	// Instead:
	header("Cache-Control: public");
	header("Expires: ".gmdate("D, d M Y H:i:s", time() + $year)." GMT");

	header('Last-Modified: '.gmdate("D, d M Y H:i:s")." GMT");
	global $mc, $config;
	include_once('kbconfig.php');
	require_once('common/includes/globals.php');
	$config = new Config();
	if(isset($_GET['int']))
	{
		header("Location: {$imghost}?a=thumb&id=$id&int=1&size=$size&type=$type");
		die;
	}
	else $thumb = new thumb($id, $size, $type);
	$thumb->display();
	die;
}

function goMap($type, $id, $size=200)
{
	global $mc, $config;
	//TODO integrate the existing common/includes/class.mapview.php
	include_once('kbconfig.php');
	require_once('common/includes/globals.php');
	$config = new Config();

	$view = new MapView($type, $size);
	$view->setSystemID($id);
	switch($type)
	{
		case "map":
			$view->setTitle("Region");
			$view->showLines(config::get('map_map_showlines'));
			$view->paintSecurity(config::get('map_map_security'));
			checkColors('map', $view);
			break;
		case "region":
			$view->setTitle("Constellation");
			$view->showLines(config::get('map_reg_showlines'));
			$view->paintSecurity(config::get('map_reg_security'));
			$view->setOffset(25);
			checkColors('reg', $view);
			break;
		case "cons":
			$view->showLines(config::get('map_con_showlines'));
			$view->showSysNames(config::get('map_con_shownames'));
			$view->paintSecurity(config::get('map_con_security'));
			$view->setOffset(25);
			checkColors('con', $view);
			break;
	}
	$view->generate();

	die;
}

function goType($type, $id, $size = 64, $imghost = "")
{
	if($size != 32 && $size != 64 && $size != 24 && $size != 48 &&
			!($type == "ship" && ($size == 256 || $size = 512))) show404();

	if($id == 0)
	{
		if($size == 32 || $size == 64 || $size == 128 || $size == 256)
			header("Location: {$imghost}img/portrait_0_{$size}.jpg");
		else header("Location: {$imghost}img/portrait_0_64.jpg");
		die;
	}

	define('KB_CACHEDIR', 'cache');
	require_once("common/includes/class.cachehandler.php");

	// If it's in the cache then read it from there.
	if(CacheHandler::exists("{$id}_{$size}.png", "img"))
	{
		expiryHeaders("png", CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		readfile(CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		die;
	}

	//TODO: add an optional memcache backed by the filecache

	//Ships are available at 256 and 512
	if($type == 'ship' && $size > 64)
		$img = fetchImage($id, "Render", $size, "png");
	// 48x48 & 64x64 images
	else if($size > 32)
	{
		if(CacheHandler::exists("{$id}_64.png", "img"))
				$img = imagecreatefrompng(CacheHandler::getInternal("{$id}_64.png", "img"));
		else $img = fetchImage($id, "InventoryType", 64, "png");
		if($img && $size != 64)
				resize($size, 64, $img, CacheHandler::getInternal("{$id}_{$size}.png", "img"));
	}
	// 24x24 & 32x32 images
	else
	{
		if(CacheHandler::exists("{$id}_32.png", "img"))
				$img = imagecreatefrompng(CacheHandler::getInternal("{$id}_32.png", "img"));
		else $img = fetchImage($id, "InventoryType", 32, "png");
		if($img && $size != 32)
				resize($size, 32, $img, CacheHandler::getInternal("{$id}_{$size}.png", "img"));
	}

	if(!$img) show404();

	expiryHeaders("png", CacheHandler::getInternal("{$id}_{$size}.png", "img"));
	readfile(CacheHandler::getInternal("{$id}_{$size}.png", "img"));
	die;
}

function expiryHeaders($type="png", $path = "")
{
	$year = 31536000; // 365 * 24 * 60 * 60
	// PHP5.3+ only
	//header_remove();
	// Instead:
	header("Cache-Control: public");
	header("Content-Type: image/".$type);
	header("Expires: ".gmdate("D, d M Y H:i:s", time() + $year)." GMT");

	if($path)
			header('Last-Modified: '.gmdate("D, d M Y H:i:s", filemtime($path))." GMT");
	else header('Last-Modified: '.gmdate("D, d M Y H:i:s")." GMT");
}

function checkColors($context, $view)
{
	$a = array('line', 'bg', 'hl', 'normal', 'capt');
	foreach($a as $b)
	{
		if($string = config::get('map_'.$context.'_cl_'.$b))
		{
			$tmp = explode(',', $string);
			$function = 'set'.$b.'color';
			$view->$function($tmp[0], $tmp[1], $tmp[2]);
		}
	}
}

function show404()
{
	//header("HTTP:/1.1 404 Not Found");
	header("Status: 404 Not Found");
	echo "Image not found";
	die;
}

function fetchImage($id, $type = 'Character', $size = 128, $ext = "jpg")
{
	include_once('kbconfig.php');
	require_once('common/includes/globals.php');
	require_once("common/includes/class.cachehandler.php");

	$url = 'http://'.IMG_SERVER."/".$type."/".$id."_".$size.".".$ext;
	if(function_exists('curl_init'))
	{
		// in case of a dead eve server we only want to wait 2 seconds
		@ini_set('default_socket_timeout', 2);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		// CURLOPT_FOLLOWLOCATION doesn't work if safe mode or open_basedir is set
		// For pilots we should try from oldportraits.eveonline.com if the main server doesn't have them.
		//if($type != 'Character') curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		//curl_setopt($ch, CURLOPT_HEADER, true);
		$file = curl_exec($ch);
		//list($header, $file) = explode("\n\n", $file, 2);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code != 200)
		{
			if($type == 'Character')
			{
				$url = "http://oldportraits.eveonline.com/Character/".$id."_".$size.".".$ext;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				$file = curl_exec($ch);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if($http_code != 200) $file = file_get_contents("img/1_$size.jpg");
			}
			else if($type == 'Alliance')
				$file = file_get_contents("img/alliances/default.png");
			else if($type == 'Corporation')
				$file = file_get_contents("img/corps/default.png");
			else show404();
		}
		curl_close($ch);
	}
	else
	{
		require_once('common/includes/class.httprequest.php');

		// in case of a dead eve server we only want to wait 2 seconds
		@ini_set('default_socket_timeout', 2);
		// try alternative access via fsockopen
		// happens if allow_url_fopen wrapper is false
		$http = new http_request($url);
		$file = $http->get_content();
		$http_code = $http->get_http_code();
		if($http_code != 200)
		{
			if($type == 'Character')
			{
				$url = "http://oldportraits.eveonline.com/Character/".$id."_".$size.".".$ext;
				$http = new http_request($url);
				$file = $http->get_content();
				$http_code = $http->get_http_code();
				if($http_code != 200) $file = file_get_contents("img/1_$size.jpg");
			}
			else if($type == 'Alliance')
				$file = file_get_contents("img/alliances/default.png");
			else if($type == 'Corporation')
				$file = file_get_contents("img/corps/default.png");
			else show404();
		}
	}
	if($img = @imagecreatefromstring($file))
			CacheHandler::put($id.'_'.$size.'.'.$ext, $file, 'img');
	return $img;
}

function resize($newsize, $oldsize, $img, $path)
{
	$newimg = imagecreatetruecolor($newsize, $newsize);
	$colour = imagecolortransparent($newimg, imagecolorallocatealpha($newimg, 0, 0, 0, 127));
	imagefill($newimg, 0, 0, $colour);
	imagesavealpha($newimg, true);

	imagecopyresampled($newimg, $img, 0, 0, 0, 0, $newsize, $newsize, $oldsize, $oldsize);

	imagepng($newimg, $path);
	imagedestroy($newimg);
}