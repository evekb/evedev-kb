<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

//! Generate the correct URL for an image.
class imageURL
{
	const QUERYSTRING = 1;
	const PATH = 2;

	static private $imageServer = null;
	static private $queryType = null;

	static function getURL($type, $id, $size, $internal = false)
	{
		if(is_null(self::$imageServer)) self::init();
		$url = self::$imageServer;

		$id = intval($id);
		$size = intval($size);
		// We reduce ammo images to 24x24 on kill_details.
		if($size < 32) $internal = true;
		if($internal) $url = KB_HOST."/thumb.php";

		if(self::$queryType == self::PATH && !$internal)
		{
			switch($type)
			{
				case 'Character':
				case 'Pilot':
					$url .= "/Character/{$id}_{$size}.jpg";
					break;
				case 'Corporation':
				case 'Alliance':
					$url .= "/$type/{$id}_{$size}.png";
					break;
				case 'Type':
				case 'InventoryType':
				case 'Ship':
					if($size > 64 && $type == 'Ship')
						$url .= "/Render/{$id}_{$size}.png";
					else $url .= "/InventoryType/{$id}_{$size}.png";
					break;
				case 'Render':
					$url .= "/Render/{$id}_{$size}.png";
					break;
			}
		}
		else
		{
			switch($type)
			{
				case 'Character':
				case 'Pilot':
					$url .= "?type=$type&amp;id=$id&amp;size=$size";
					if($internal) $url .= "&amp;int=1";
					break;
				case 'Corporation':
				case 'Alliance':
				case 'Type':
				case 'InventoryType':
				case 'Ship':
				case 'Render':
					$url .= "?type=$type&amp;id=$id&amp;size=$size";
					break;
			}
		}
		return $url;
	}

	static function init()
	{
		if(config::get('cfg_ccpimages'))
		{
			self::$imageServer = "http://".IMG_SERVER;
			self::$queryType = self::PATH;
		}
		else
		{
			self::$imageServer = KB_HOST."/thumb.php";
			self::$queryType = self::QUERYSTRING;
			//self::$queryType = config::get('cfg_imagequery');
			//if(!self::$queryType) self::$queryType = 2;
		}
	}

}