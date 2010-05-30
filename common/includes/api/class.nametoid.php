<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                   API Name -> ID Conversion /eve/CharacterID.xml.aspx 	                     ****************
// **********************************************************************************************************************************************
class API_NametoID
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function setNames($names)
	{
		$this->API_Names_ = $names;
	}
	function getNameData()
	{
		return $this->NameData_;
	}

	function clear()
	{
		$this->NameData_ = array();
		unset($this->NameData_);
	}

	function fetchXML()
	{
		if ($this->API_Names_ != "")
		{
			$myKeyString = "names=" . $this->API_Names_;
			$data = $this->loaddata($myKeyString);
		} else {
			return "No Names have been input.";
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/CharacterID.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
	}

	function startElement($parser, $name, $attribs)
    {
		global $NameData;

		if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "NAME":
                            $NameData['name'] = $v;
                            break;
                        case "CHARACTERID":
                            $NameData['characterID'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $NameData;

		// Details
		if ($name == "ERROR")
			$this->html .= $this->characterDataValue;

		if ($name == "ROW")
		{
			$this->NameData_[] = $NameData;
			$NameData = array();
			unset($NameData);
		}

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL") // cache not needed for this process
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			//ApiCache::set( $this->CharName_ . 'CharacterID' , $this->characterDataValue);
		}
    }

    function characterData($parser, $data)
    {
		$this->characterDataValue = $data;
    }

	function loaddata($keystring)
    {
        $url = "http://api.eve-online.com/eve/CharacterID.xml.aspx" . $keystring;

        $path = '/eve/CharacterID.xml.aspx';


        $fp = @fsockopen("api.eve-online.com", 80);

        if (!$fp)
        {
            $this->Output_ .= "Could not connect to API URL";
        } else {
           	 // request the xml
            fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
            fputs ($fp, "Host: api.eve-online.com\r\n");
            fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            fputs ($fp, "User-Agent: PHPApi\r\n");
            fputs ($fp, "Content-Length: " . strlen($keystring) . "\r\n");
            fputs ($fp, "Connection: close\r\n\r\n");
            fputs ($fp, $keystring."\r\n");
			stream_set_timeout($fp, 10);

           	 // retrieve contents
            $contents = "";
            while (!feof($fp))
            {
                $contents .= fgets($fp);
            }

            // close connection
            fclose($fp);

            $start = strpos($contents, "?>");
            if ($start !== FALSE)
            {
                	$contents = substr($contents, $start + strlen("\r\n\r\n"));
            }
        }

        return $contents;
    }
}
