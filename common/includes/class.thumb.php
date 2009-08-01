<?php

class thumb
{
    function thumb($str_id, $size, $type = 'pilot')
    {
        $this->_id = $str_id;
        $this->_size = $size;
        $this->_type = $type;
        $this->_encoding = 'jpeg';

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
        if ($this->_encoding == 'jpeg')
        {
            header("Content-Type: image/jpeg");
            readfile($this->_thumb);
        }
        elseif ($this->_encoding == 'png')
        {
            header("Content-Type: image/png");
            readfile($this->_thumb);
        }
    }

    function validate()
    {
        if (!$this->_size)
        {
            $this->_size = 32;
        }
        switch ($this->_type)
        {
            case 'corp':
                $this->_id = preg_replace('/[^a-z0-9]/', '', $this->_id);
                break;
            case 'alliance':
                $this->_id = preg_replace('/[^a-zA-Z0-9]/', '', $this->_id);
                if (!strlen($this->_id))
                {
                    $this->_id = 'default';
                }
                break;
            default:
                $this->_type = 'pilot';
                $this->_id = intval($this->_id);
        }
    }

    function isCached()
    {
        switch ($this->_type)
        {
            case 'pilot':
                $this->_thumb = 'cache/portraits/'.$this->_id.'_'.$this->_size.'.jpg';
                break;
            case 'corp':
                $this->_thumb = 'cache/corps/'.$this->_id.'_'.$this->_size.'.jpg';
                break;
            case 'alliance':
                $this->_thumb = 'cache/corps/all'.$this->_id.'_'.$this->_size.'.png';
                break;
        }

        if (file_exists($this->_thumb))
        {
            touch($this->_thumb);
            return true;
        }
    }

    function genCache()
    {
        switch ($this->_type)
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
        }
        return true;
    }

    function genPilot()
    {
        if (file_exists('cache/portraits/'.$this->_id.'_256.jpg'))
        {
            touch('cache/portraits/'.$this->_id.'_256.jpg');
            $img = imagecreatefromjpeg('cache/portraits/'.$this->_id.'_256.jpg');
        }
        // 20070911 - Gate: Support EVE/Capture/Portraits images
        elseif (file_exists('img/portraits/'.$this->_id.'.jpg'))
        {
			$img = imagecreatefromjpeg('img/portraits/'.$this->_id.'.jpg');
        }
        else
        {
            if ($this->_id)
            {
                // check for a valid, known external id
                $qry = new DBQuery();
                $qry->execute('SELECT plt_externalid FROM kb3_pilots WHERE plt_externalid = '.$this->_id.' LIMIT 1');
                $row = $qry->getRow();
                if (!$id = $row['plt_externalid'])
                {
                    // there is no such id so set it to 0
                    $this->_id = 0;
                    $this->_thumb = 'img/portrait_0_'.$this->_size.'.jpg';
                    return;
                }
            }
            // in case of a dead eve server we only want to wait 2 seconds
            @ini_set('default_socket_timeout', 2);
			if (function_exists('curl_init'))
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://img.eve.is/serv.asp?s=256&c='.$this->_id);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 2);
				$file = curl_exec($ch);
				curl_close($ch);
				if ($img = @imagecreatefromstring($file))
				{
					$fp = fopen('cache/portraits/'.$this->_id.'_256.jpg', 'w');
					fwrite($fp, $file);
					fclose($fp);
				}
			}
			else
			{
				$file = @file_get_contents('http://img.eve.is/serv.asp?s=256&c='.$this->_id);
				if ($img = @imagecreatefromstring($file))
				{
					$fp = fopen('cache/portraits/'.$this->_id.'_256.jpg', 'w');
					fwrite($fp, $file);
					fclose($fp);
				}
				else
				{
					// try alternative access via fsockopen
					// happens if allow_url_fopen wrapper is false
					require_once('class.http.php');

					$url = 'http://img.eve.is/serv.asp?s=256&c='.$this->_id;
					$http = new http_request($url);
					$file = $http->get_content();

					if ($img = @imagecreatefromstring($file))
					{
						$fp = fopen('cache/portraits/'.$id.'_256.jpg', 'w');
						fwrite($fp, $file);
					}
				}
			}
        }

        if ($img)
        {
            $newimg = imagecreatetruecolor($this->_size, $this->_size);
            $srcwidth = imagesx($img);
            $srcheight = imagesy($img);

            imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->_size, $this->_size, $srcwidth, $srcheight);
            imagejpeg($newimg, $this->_thumb, 90);
            imagedestroy($newimg);
        }
        else
        {
            // fallback to a portrait with red !
            $this->_thumb = 'img/portrait_0_'.$this->_size.'.jpg';
        }
    }

    function genCorp()
    {
        if (!file_exists('img/corps/'.$this->_id.'.jpg'))
        {
            $this->_id = 0;
        }
        $img = imagecreatefromjpeg('img/corps/'.$this->_id.'.jpg');
        if ($img)
        {
            $newimg = imagecreatetruecolor($this->_size, $this->_size);
            $oldx = imagesx($img);
            $oldy = imagesy($img);
            imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->_size, $this->_size, $oldx, $oldy);
            imagejpeg($newimg, $this->_thumb, 90);
        }
    }

    function genAlliance()
    {
        if (!file_exists('img/alliances/'.$this->_id.'.png'))
        {
            $this->_id = 0;
        }
        $img = imagecreatefromjpeg('img/alliances/'.$this->_id.'.png');
        if ($img)
        {
            $newimg = imagecreatetruecolor($this->_size, $this->_size);
            $oldx = imagesx($img);
            $oldy = imagesy($img);
            imagecopyresampled($newimg, $img, 0, 0, 0, 0, $this->_size, $this->_size, $oldx, $oldy);
            imagepng($newimg, $this->_thumb);
        }
    }
}

class thumbInt extends thumb
{
	function thumbInt($int_id, $size)
	{
		require_once('common/includes/class.pilot.php');
		$pilot = new Pilot($int_id);
		$this->_id = $pilot->getExternalID();
        $this->_size = $size;
        $this->_type = 'pilot';
        $this->_encoding = 'jpeg';

        $this->validate();
	}
}
?>