<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class thumb
{
	protected $id = 0;
	protected $size = 256;
	protected $type = 'pilot';
	protected $encoding = 'jpeg';
	protected $thumb = '';

	function thumb($str_id, $size, $type = 'pilot')
	{
		$this->id = $str_id;
		$this->size = $size;
		$this->type = $type;
		switch ($this->type)
		{
			case 'corp':
			case 'npc':
			case 'alliance':
				$this->encoding = 'png';
				break;
			default:
				$this->encoding = 'jpeg';
		}

		$this->validate();
	}

	function display()
	{
		ob_start();
		if (!$this->isCached())
		{
			if (!$this->genCache())
			{
				if(file_exists('img/portrait_0_'.$this->size.'.jpg'))
				{
					header("Content-Type: image/jpeg");
					readfile('img/portrait_0_'.$this->size.'.jpg');
				}
				else
				{
					echo "The image could not be displayed.";
				}
				return false;
			}
		}

		if (headers_sent() || ob_get_contents())
		{
			echo 'An error occurred.<br/>';
			return false;
		}
		if ($this->encoding == 'jpeg')
		{
			header("Content-Type: image/jpeg");
			readfile($this->thumb);
		}
		elseif ($this->encoding == 'png')
		{
			header("Content-Type: image/png");
			readfile($this->thumb);
		}
		ob_end_flush();
		return true;
	}

	function validate()
	{
		if (!$this->size)
		{
			$this->size = 32;
		}
		switch ($this->type)
		{
			case 'corp':
			case 'npc':
				$this->id = preg_replace('/[^a-z0-9]/', '', $this->id);
				break;
			case 'alliance':
				$this->id = preg_replace('/[^a-zA-Z0-9]/', '', $this->id);
				if (!strlen($this->id))
				{
					$this->id = 'default';
				}
				break;
			default:
				$this->type = 'pilot';
				$this->id = intval($this->id);
		}
	}

	function isCached()
	{
		switch ($this->type)
		{
			case 'pilot':
				$this->thumbName = $this->id.'_'.$this->size.'.jpg';
				$this->thumbDir = 'img';
				break;
			case 'corp':
				$this->thumbName = $this->id.'_'.$this->size.'.png';
				$this->thumbDir = 'img';
				break;
			case 'alliance':
				$this->thumbName = $this->id.'_'.$this->size.'.png';
				$this->thumbDir = 'img';
				break;
			case 'npc':
				$this->thumbName = $this->id.'_'.$this->size.'.png';
				$this->thumbDir = 'img';
				break;
		}
				$this->thumb = CacheHandler::getInternal($this->thumbName, $this->thumbDir);
				return CacheHandler::exists($this->thumbName, $this->thumbDir);
	}

	function genCache()
	{
		switch ($this->type)
		{
			case 'pilot':
				return $this->genPilot();
				break;
			case 'corp':
				return $this->genCorp();
				break;
			case 'alliance':
				return $this->genAlliance();
				break;
			case 'npc':
				return $this->genNPC();
				break;
		}
		return true;
	}

	function genPilot()
	{
		if (CacheHandler::exists($this->id.'_256.jpg', $this->thumbDir))
		{
			$img = imagecreatefromjpeg(CacheHandler::getInternal($this->id.'_256.jpg', 'img'));
		}
		else
		{
			if ($this->id)
			{
				// check for a valid, known external id
				$qry = DBFactory::getDBQuery();;
				$qry->execute('SELECT plt_externalid FROM kb3_pilots WHERE plt_externalid = '.$this->id.' LIMIT 1');
				if (!$qry->recordCount())
				{
					// there is no such id so set it to 0
					$this->id = 0;
					$this->thumbName = 'portrait_0_'.$this->size.'.jpg';
					$this->thumbDir = 'img';
					$this->thumb = CacheHandler::getInternal('portrait_0_'.$this->size.'.jpg', 'img/pilots');
					return true;
				}
			}
			// Assume external id < 100,000 is NPC structure/ship
			if($this->id < 100000)
			{
				if(file_exists("img/ships/64_64/".$this->id.".png"))
				{
					$img = imagecreatefrompng("img/ships/64_64/".$this->id.".png");
				}
				else
				{
					// there is no such image so set it to 0
					$this->id = 0;
					$this->thumbName = 'portrait_0_'.$this->size.'.jpg';
					$this->thumbDir = 'img';
					$this->thumb = CacheHandler::getInternal('portrait_0_'.$this->size.'.jpg', 'img/pilots');
					return true;
				}
			}
			else
			{
				$img = $this->fetchImage('pilot', 256);
			}
		}

		if ($img)
		{
			$newimg = imagecreatetruecolor($this->size, $this->size);
			$srcwidth = imagesx($img);
			$srcheight = imagesy($img);

			imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->size, $this->size, $srcwidth, $srcheight);
			CacheHandler::getInternal($this->thumbName, $this->thumbDir);
			imagejpeg($newimg, $this->thumb, 90);
			imagedestroy($newimg);
		}
		else
		{
			// fallback to a portrait with red !
			$this->thumb = 'img/portrait_0_'.$this->size.'.jpg';
		}
		return true;
	}

	function genCorp()
	{
		$source = 'img/corps/'.$this->id.'.png';
		// id is not a number and the matching npc corp image does not exist.
//		if (!file_exists($source) && !is_numeric($this->id))
//		{
//			$this->id = 0;
//			$this->thumbName = '0_'.$this->size.'.png';
//			$this->thumbDir = 'img';
//			$this->thumb = CacheHandler::getInternal($this->thumbName, $this->thumbDir);
//		}
		// id matches an npc image.
		if(file_exists($source)) $img = imagecreatefrompng($source);
		// no matching image found so let's try the cache.
		else if(CacheHandler::exists($this->id.'_128.png', 'img'))
		{
			$img = imagecreatefrompng(CacheHandler::getInternal($this->id.'_128.png', 'img'));
		}
		// no image found in the image folder, or the cache, so let's make it.
		else
		{
			$img = $this->fetchImage('Corporation', 128);
			if($this->size == 128) return true;
		}
//		{
//
//			$myAPI = new API_CorporationSheet();
//			$myAPI->setCorpID($this->id);
//
//			$result .= $myAPI->fetchXML();
//
//			$mylogo = $myAPI->getLogo();
//
//			if ($result == "Corporation is not part of alliance.")
//			{
//				$this->thumbName = '0_'.$this->size.'.png';
//				$this->thumbDir = 'img';
//				$this->thumb = CacheHandler::getInternal($this->thumbName, $this->thumbDir);
//			}
//			elseif ($result == "")
//			{
//				require_once("common/includes/evelogo.php");
//				// create two sized logo's in 2 places - this allows checks already in place not to keep requesting corp logos each time page is viewed
//				// class.thumb.php cannot work with png (although saved as jpg these are actually pngs) therefore we have to create the 128 size for it
//				// doing this prevents the images being rendered each time the function is called and allows it to use one in the cache instead.
//				CorporationLogo( $mylogo, 128, $this->id );
//				CorporationLogo( $mylogo, $this->size, $this->id );
//			}
//			return true;
//		}
		if ($img)
		{
			$newimg = imagecreatetruecolor($this->size, $this->size);
			$oldx = imagesx($img);
			$oldy = imagesy($img);
			imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->size, $this->size, $oldx, $oldy);

			$this->thumb = CacheHandler::getInternal($this->id.'_'.$this->size.'.png', 'img');
			imagepng($newimg, $this->thumb);
		}
		return true;
	}

	function genAlliance()
	{
		$source = 'img/alliances/'.$this->id.'.png';
		if (!file_exists('img/alliances/'.$this->id.'.png') && !is_numeric($this->id))
		{
			$this->id = 'default';
			$source = 'img/alliances/'.$this->id.'.png';
			$img = imagecreatefrompng($source);
		}
		else if (is_numeric($this->id))
		{
			$img = $this->fetchImage("Alliance", 128);
			if($this->size == 128 && $img) return true;
			//else $source = CacheHandler::getInternal($this->id.'_256.png', 'img');
		}
		else $img = imagecreatefrompng($source);
		if ($img)
		{
			$newimg = imagecreatetruecolor($this->size, $this->size);
			$color = imagecolortransparent($newimg, imagecolorallocatealpha($newimg, 0, 0, 0, 127));
			imagefill($newimg, 0, 0, $color);
			imagesavealpha($newimg, true);
			$oldx = imagesx($img);
			$oldy = imagesy($img);
			imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->size, $this->size, $oldx, $oldy);
			return imagepng($newimg, $this->thumb);
		}
		return false;
	}

	//as npc corp images from a static dump, all this ought to be doing is the resizing of the image
	//and copying to the cache
	function genNPC()
	{
		$source = 'img/corps/'.$this->id.'.png';
		if(!file_exists($source))
		{
			$this->id = 0;
			$source = 'img/corps/0.png';
			$this->thumb = CacheHandler::getInternal($this->id.'_'.$this->size.'.png','img');
		}
		$img = imagecreatefrompng($source);
		if ($img)
		{
			$newimg = imagecreatetruecolor($this->size, $this->size);
			$color = imagecolortransparent($newimg, imagecolorallocatealpha($newimg, 0, 0, 0, 127));
			imagefill($newimg, 0, 0, $color);
			imagesavealpha($newimg, true);
			$oldx = imagesx($img);
			$oldy = imagesy($img);
			imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->size, $this->size, $oldx, $oldy);

			// Check the cache directories have been created.
			CacheHandler::getInternal($this->id.'_'.$this->size.'.png', 'img');
			return imagepng($newimg, $this->thumb);
		}
		return false;
	}
	function fetchPilotImage()
	{
		return $this->fetchImage('Character', 256);
	}
	function fetchImage($type = 'Character', $size = 128)
	{
		if($type == 'pilot') $type = 'Character';
		elseif($type == 'corp') $type = 'Corporation';
		elseif($type == 'alliance') $type = 'Alliance';

		if($this->encoding == 'jpeg') $ext = 'jpg';
		else $ext = 'png';

		if($type != 'Character' & $type != 'Corporation' && $type != 'Alliance') return false;
		$url = "http://image.eveonline.com/".$type."/".$this->id."_".$size.".".$ext;
		//$url = 'http://img.eve.is/serv.asp?s=256&c='.$this->id;
		if (function_exists('curl_init'))
		{
			// in case of a dead eve server we only want to wait 2 seconds
			@ini_set('default_socket_timeout', 2);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 2);
			$file = curl_exec($ch);
			curl_close($ch);
		}
		else
		{
			// in case of a dead eve server we only want to wait 2 seconds
			@ini_set('default_socket_timeout', 2);
			$file = @file_get_contents($url);
			if($file === false)
			{
				// try alternative access via fsockopen
				// happens if allow_url_fopen wrapper is false
				require_once('class.http.php');

				$http = new http_request($url);
				$file = $http->get_content();
			}
		}
		if ($img = @imagecreatefromstring($file))
			CacheHandler::put($this->id.'_'.$size.'.'.$ext, $file, 'img');
		return $img;
	}
	function getThumb()
	{
		if (!$this->isCached())
		{
			if (!$this->genCache())
			{
				return false;
			}
		}
		return $this->thumb;
	}
}

class thumbInt extends thumb
{
	function thumbInt($int_id, $size, $type)
	{
		$this->size = $size;
		switch($this->type)
		{
			case 'pilot':
			case '':
				$pilot = new Pilot($int_id);
				$this->id = $pilot->getExternalID();

				$this->type = 'pilot';
				$this->encoding = 'jpeg';

				$this->validate();
				break;
			case 'corp':
			case 'npc':
				$this->type = 'corp';
				$corp = new Corporation($int_id);
				if(!$corp->getExternalID())
				{
					$this->id = 0;
				}
				$this->id = $corp->getExternalID();
				$this->encoding = 'png';

				if($this->type == 'npc')
				{
					$this->type = 'npc';
					//$this->encoding = 'png';
				}

				$this->validate();
				break;

			default:
				$this->id = $str_id;
				$this->type = $type;
				$this->encoding = 'jpeg';

				$this->validate();
		}
	}
}
