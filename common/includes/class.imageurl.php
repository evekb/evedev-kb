<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Generate the correct URL for an image.
 * @package EDK
 */
class imageURL
{
	private static $callbacks = array();
	
	static function getURL($type, $id, $size, $internal = false)
	{
		$id = (int)$id;
		$size = (int)$size;
		
		foreach(self::$callbacks as $callback) {
			if ($result = call_user_func_array($callback, func_get_args())) {
				return $result;
			}
		}

		if ($id == 0) {
			if ($size == 32 || $size == 64 || $size == 128 || $size == 256) {
				return KB_HOST."/img/portrait_0_{$size}.jpg";
			} else {
				return KB_HOST."/img/portrait_0_64.jpg";
			}
		}

		// We reduce ammo images to 24x24 on kill_details.
		if ($size < 32) {
			$internal = true;
		}
		// Images are handled by the killboard if they are maps or we specify
		// (globally or just this instance)
		if ($type == 'map' || $type == 'region' || $type == 'cons'
				|| !config::get('cfg_ccpimages') || $internal) {
			$ccp = false;
			$url = KB_HOST."/thumb.php";
		} else {
			$ccp = true;
			$url = "http://".IMG_SERVER;
		}

		if ($ccp || config::get('cfg_pathinfo')) {
			switch ($type) {
				case 'Character':
				case 'Pilot':
					$url .= "/Character/{$id}_{$size}.jpg";
					break;
				case 'Corporation':
				case 'Alliance':
					// Check for NPC alliances recorded as corps.
					if ($id > 500000 && $id < 500021) {
						$url .= "/Alliance/{$id}_{$size}.png";
					} else {
						$url .= "/$type/{$id}_{$size}.png";
					}
					break;
				case 'Type':
				case 'InventoryType':
				case 'Ship':
					if ($size > 64 && $type == 'Ship')
						$url .= "/Render/{$id}_{$size}.png";
					else
						$url .= "/InventoryType/{$id}_{$size}.png";
					break;
				case 'Render':
					$url .= "/Render/{$id}_{$size}.png";
					break;
				default:
					$url .= "/{$type}/{$id}_{$size}.png";
					break;
			}
		} else {
			$url .= "?type=$type&amp;id=$id&amp;size=$size";
		}
		if ($internal) {
			$url .= "&amp;int=1";
		}
		return $url;
	}

	static function registerHandler($callback)
	{
		if (!is_callable($callback)) {
			trigger_error('The supplied callback has to be callable.', E_USER_WARNING);
			return;
		}
		
		self::$callbacks[] = $callback;
	}
}