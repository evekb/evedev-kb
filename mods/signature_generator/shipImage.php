<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 *
 * @package EDK
 */


class shipImage
{
	/**
	 * Get an image.
	 *
	 * Returns an image resource or false on failure. Fetches the image from
	 * CCP if needed.
	 * @param integer $id
	 * @param integer $size
	 * @return resource Image resource or false on failure.
	 */
	static function get($id, $size = 64)
	{
		$id = (int)$id;
		$size = (int)$size;

		if($size != 32 && $size != 64) {
			return false;
		}

		if(!$id) {
			return imagecreatefromjpeg("img/portrait_0_{$size}.jpg");
		}

		// If it's in the cache then read it from there.
		if(CacheHandler::exists("{$id}_{$size}.png", "img")) {
			return imagecreatefrompng(
					CacheHandler::getInternal("{$id}_{$size}.png", "img"));
		} else {
			$img = self::fetchImage($id, $size);
			imagepng($img, CacheHandler::getInternal("{$id}_{$size}.png", "img"));
			return $img ?
				$img : imagecreatefromjpeg("img/portrait_0_{$size}.jpg");
		}
	}

	/**
	 * Fetch an image from the image server.
	 *
	 * Returns the image resource or false on failure.
	 * @param integer $id
	 * @param integer $size
	 * @return resource|boolean The image resource or false on failure.
	 */
	private static function fetchImage($id, $size = 64)
	{
		$url = 'http://'.IMG_SERVER."/"."InventoryType"."/".$id."_".$size.".png";
		if(function_exists('curl_init'))
		{
			// in case of a dead eve server we only want to wait 2 seconds
			@ini_set('default_socket_timeout', 2);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
			$file = curl_exec($ch);
			//list($header, $file) = explode("\n\n", $file, 2);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		}
		else
		{
			// in case of a dead eve server we only want to wait 2 seconds
			@ini_set('default_socket_timeout', 2);
			// try alternative access via fsockopen
			// happens if allow_url_fopen wrapper is false
			$http = new http_request($url);
			$file = $http->get_content();
			$http_code = $http->get_http_code();
		}
		return @imagecreatefromstring($file);
	}
}