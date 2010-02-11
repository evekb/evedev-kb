<?php

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
		$this->encoding = 'jpeg';

		$this->validate();
	}

	function display()
	{
		if (!$this->isCached())
		{
			if (!$this->genCache())
			{
				return false;
			}
		}

		if (headers_sent())
		{
			echo 'Error occured.<br/>';
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
				$this->thumb = KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2).'/'.$this->id.'_'.$this->size.'.jpg';
				break;
			case 'corp':
				$this->thumb = KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2).'/'.$this->id.'_'.$this->size.'.jpg';
				break;
			case 'alliance':
				$this->thumb = KB_CACHEDIR.'/img/alliances/'.$this->id.'_'.$this->size.'.png';
				break;
			case 'npc':
				$this->thumb = KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2).'/'.$this->id.'_'.$this->size.'.png';
				break;
		}
	}

	function genCache()
	{
		switch ($this->type)
		{
			case 'pilot':
				$this->genPilot();
				break;
			case 'corp':
				$this->genCorp();
				break;
			case 'alliance':
				$this->genAlliance();
				break;
			case 'npc':
				$this->genNPC();
				break;
		}
		return true;
	}

	function genPilot()
	{
		if (file_exists(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2).'/'.$this->id.'_256.jpg'))
		{
			$img = imagecreatefromjpeg(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2).'/'.$this->id.'_256.jpg');
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
					$this->thumb = 'img/portrait_0_'.$this->size.'.jpg';
					return;
				}
			}
			// Assume external id < 100,000 is NPC structure/ship
			if($this->id < 100000)
			{
				if(file_exists("img/ships/64_64/".$this->id.".png"))
				{
					$img = imagecreatefrompng("img/ships/64_64/".$this->id.".png");
					if(!file_exists(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2)))
						mkdir(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2));
					if(!file_exists(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2)))
						mkdir(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2));
				}
				else
				{
					// there is no such image so set it to 0
					$this->id = 0;
					$this->thumb = 'img/portrait_0_'.$this->size.'.jpg';
					return;
				}
			}
			else
			{
				$img = $this->fetchPilotImage();
			}
		}

		if ($img)
		{
			$newimg = imagecreatetruecolor($this->size, $this->size);
			$srcwidth = imagesx($img);
			$srcheight = imagesy($img);

			imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->size, $this->size, $srcwidth, $srcheight);
			imagejpeg($newimg, $this->thumb, 90);
			imagedestroy($newimg);
		}
		else
		{
			// fallback to a portrait with red !
			$this->thumb = 'img/portrait_0_'.$this->size.'.jpg';
		}
	}

	function genCorp()
	{
		$source = 'img/corps/'.$this->id.'.jpg';
		// id is not a number and the matching npc corp image does not exist.
		if (!file_exists($source) && !is_numeric($this->id))
		{
			$this->id = 0;
			$this->thumb = KB_CACHEDIR.'/img/corps/00/0_'.$this->size.'.jpg';
		}
		// id matches an npc image.
		elseif(file_exists($source));
		// no matching image found so let's try the cache.
		elseif (file_exists(KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2).'/'.$this->id.'_64.jpg'))
			$source = KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2).'/'.$this->id.'_64.jpg';
		// no image found in the image folder, or the cache, so let's make it.
		else
		{
			require_once("common/includes/class.eveapi.php");

			$myAPI = new API_CorporationSheet();
			$myAPI->setCorpID($this->id);

			$result .= $myAPI->fetchXML();

			$mylogo = $myAPI->getLogo();

			if ($result == "Corporation is not part of alliance.")
			{
				$this->thumb = KB_CACHEDIR.'/img/corps/0_'.$this->size. '.jpg';
			}
			elseif ($result == "")
			{
				require_once("common/includes/evelogo.php");
				// create two sized logo's in 2 places - this allows checks already in place not to keep requesting corp logos each time page is viewed
				// class.thumb.php cannot work with png (although saved as jpg these are actually pngs) therefore we have to create the 128 size for it
				// doing this prevents the images being rendered each time the function is called and allows it to use one in the cache instead.
				CorporationLogo( $mylogo, 64, $this->id );
				CorporationLogo( $mylogo, $this->size, $this->id );
			}
			return;
		}
		$img = imagecreatefromjpeg($source);
		if ($img)
		{
			$newimg = imagecreatetruecolor($this->size, $this->size);
			$oldx = imagesx($img);
			$oldy = imagesy($img);
			imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->size, $this->size, $oldx, $oldy);

			if($this->id && !file_exists(KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2)))
				mkdir(KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2));
			elseif($this->id == 0 && !file_exists(KB_CACHEDIR.'/img/corps/00'))
				mkdir(KB_CACHEDIR.'/img/corps/00');
			imagejpeg($newimg, $this->thumb, 90);
		}
		return;
	}

	function genAlliance()
	{
		if (!file_exists('img/alliances/'.$this->id.'.png'))
		{
			$this->id = 0;
		}
		$img = imagecreatefrompng('img/alliances/'.$this->id.'.png');
		if ($img)
		{
			$newimg = imagecreatetruecolor($this->size, $this->size);
			$color = imagecolortransparent($newimg, imagecolorallocatealpha($newimg, 0, 0, 0, 127));
			imagefill($newimg, 0, 0, $color);
			imagesavealpha($newimg, true);
			$oldx = imagesx($img);
			$oldy = imagesy($img);
			imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->size, $this->size, $oldx, $oldy);
			imagepng($newimg, $this->thumb);
		}
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
			$this->thumb = KB_CACHEDIR.'/img/corps/00/0_'.$this->size.'.png';
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

			if($this->id && !file_exists(KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2)))
				mkdir(KB_CACHEDIR.'/img/corps/'.substr($this->id,0,2));
			elseif($this->id == 0 && !file_exists(KB_CACHEDIR.'/img/corps/00'))
				mkdir(KB_CACHEDIR.'/img/corps/00');

			imagepng($newimg, $this->thumb);
		}
		return;
	}
	function fetchPilotImage()
	{
		if(!file_exists(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2)))
			mkdir(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2));
		if(!file_exists(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2)))
			mkdir(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2));

		if (function_exists('curl_init'))
		{
			// in case of a dead eve server we only want to wait 2 seconds
			@ini_set('default_socket_timeout', 2);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://img.eve.is/serv.asp?s=256&c='.$this->id);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 2);
			$file = curl_exec($ch);
			curl_close($ch);
		}
		else
		{
			// in case of a dead eve server we only want to wait 2 seconds
			@ini_set('default_socket_timeout', 2);
			$file = @file_get_contents('http://img.eve.is/serv.asp?s=256&c='.$this->id);
			if($file === false)
			{
				// try alternative access via fsockopen
				// happens if allow_url_fopen wrapper is false
				require_once('class.http.php');

				$url = 'http://img.eve.is/serv.asp?s=256&c='.$this->id;
				$http = new http_request($url);
				$file = $http->get_content();
			}
		}
		if ($img = @imagecreatefromstring($file))
		{
			$fp = fopen(KB_CACHEDIR.'/img/pilots/'.substr($this->id,0,2).'/'.substr($this->id,2,2).'/'.$this->id.'_256.jpg', 'w');
			if(!$fp) die("\nImage could not be fetched."); // Let's see those error messages.
			fwrite($fp, $file);
			fclose($fp);
		}
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
				require_once('common/includes/class.pilot.php');
				$pilot = new Pilot($int_id);
				$this->id = $pilot->getExternalID();

				$this->type = 'pilot';
				$this->encoding = 'jpeg';

				$this->validate();
				break;
			case 'corp':
			case 'npc':
				$this->type = 'corp';
				require_once('common/includes/class.corp.php');
				$corp = new Corporation($int_id);
				if(!$corp->getExternalID())
				{
					$this->id = 0;
				}
				$this->id = $corp->getExternalID();
				$this->encoding = 'jpeg';

				if($this->type == 'npc')
				{
					$this->type = 'npc';
					$this->encoding = 'png';
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
