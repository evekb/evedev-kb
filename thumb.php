<?php

/*
 * $Date: 2010-05-29 20:59:20 +1000 (Sat, 29 May 2010) $
 * $Revision: 705 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/thumb.php $
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
if(isset($_SERVER['PATH_INFO']))
{
	// Split on /, _, or . => works with:
	// thumb/Character/123456/32 as well as thumb/Character/123456_32.jpg
	$args = preg_split('/[\/_.]/', trim($_SERVER['PATH_INFO'],"/"));

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
	case 'character':
	case 'corp':
	case 'corporation':
	case 'alliance':
		if($type == 'character') $type = 'pilot';
		else if ($type == 'corporation') $type = 'corp';
		goPCA($type, $id, $size, $imghost);
		break;

	case 'region':
	case 'map':
	case 'cons':
		goMap($type, $id, $size);
		break;

	case 'type':
	case 'ship':
		goType($type, $id, $size, $imghost);
}


header("Location: {$imghost}img/portrait_0_64.jpg");
die;



function goPCA($type, $id, $size = 64, $imghost = "")
{
	//TODO integrate the existing common/includes/class.thumb.php
	$year = 31536000; // 365 * 24 * 60 * 60

	// PHP5.3+ only
	//header_remove();
	// Instead:
	header("Cache-Control: public");
	header("Expires: ".gmdate("D, d M Y H:i:s", time() + $year)." GMT");

	header('Last-Modified: '.gmdate("D, d M Y H:i:s")." GMT");
	
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
	//TODO integrate the existing common/includes/class.mapview.php
	include_once('kbconfig.php');
	require_once('common/includes/globals.php');
	$config = new Config();
	$_GET['mode'] = $type;
	$_GET['sys_id'] = $id;
	$_GET['size'] = $size;
	require_once('common/mapview.php');
	die;
}

function goType($type, $id, $size = 64, $imghost = "")
{
	define('KB_CACHEDIR', 'cache');
	if($type == 'ship' && $size == 256 && file_exists("img/ships/256_256/{$id}.png"))
	{
		expiryHeaders("png", "img/ships/256_256/{$id}.png");
		readfile("img/ships/256_256/{$id}.png");
		die;
	}

	require_once("common/includes/class.cachehandler.php");
	//TODO: add an optional memcache backed by the filecache
//	require_once("common/includes/class.cachehandlerhashed");
//	require_once("kbconfig.php");
//	if(defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true)
//	{
//		if(!defined('DB_MEMCACHE_SERVER') || !defined('DB_MEMCACHE_PORT'))
//			die("DB_MEMCACHE_SERVER and DB_MEMCACHE_PORT not defined. Memcache not started.");
//
//		$mc = new Memcache();
//		if(!@$mc->pconnect(DB_MEMCACHE_SERVER, DB_MEMCACHE_PORT))
//			die("ERROR: Unable to connect to memcached server, disabling
//				memcached. Please check your settings (server, port) and make
//				sure the memcached server is running");
//		require_once("common/includes/class.cachehandlerhashedmem");
//
//		$cachehandler = new CacheHandlerHashedMem();
//	}
//	else
//	{
//		$cachehandler = new CacheHandlerHashed();
//	}

	// Read straight from the img folder if it's 64x64.
	if($size == 64 && file_exists("img/types/64_64/{$id}.png"))
	{
		expiryHeaders("png", "img/types/64_64/{$id}.png");
		readfile("img/types/64_64/{$id}.png");
		die;
	}
	// Give up if it doesn't exist.
	else if(!file_exists("img/types/64_64/{$id}.png"))
	{
		header("Location: {$imghost}img/portrait_0_64.jpg");
		die;
	}
	// If it's in the cache then read it from there.
	else if(CacheHandler::exists("{$id}_{$size}.png", "img"))
	{
		expiryHeaders("png", CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		readfile(CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		die;
	}
	// Make the size needed from the 64x64 image in the img directory
	else
	{
		$img = imagecreatefrompng("img/types/64_64/{$id}.png");
		$newimg = imagecreatetruecolor($size, $size);
		$colour = imagecolortransparent($newimg, imagecolorallocatealpha($newimg, 0, 0, 0, 127));
		imagefill($newimg, 0, 0, $colour);
		imagesavealpha($newimg, true);

		imagecopyresampled($newimg, $img, 0, 0, 0, 0, $size, $size, 64, 64);

		imagepng($newimg, CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		imagedestroy($newimg);

		expiryHeaders("png", CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		readfile(CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		die;
	}
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

	if($path) header('Last-Modified: '.gmdate("D, d M Y H:i:s", filemtime($path))." GMT");
	else header('Last-Modified: '.gmdate("D, d M Y H:i:s")." GMT");
}
