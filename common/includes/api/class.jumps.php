<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

require_once('class.eveapi.php'); 
// **********************************************************************************************************************************************
// ****************                                   API Jumps list - /map/Jumps.xml.aspx                                   ****************
// **********************************************************************************************************************************************

class API_Jumps
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getDataTime()
	{
		return $this->DataTime_;
	}

	function getJumps()
	{
		return $this->Jumps_;
	}

    function fetchXML()
    {
        $data = LoadGlobalData('/map/Jumps.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/map/Jumps.xml.aspx </i><br><br>";

        xml_parser_free($xml_parser);

        return $this->html;
    }

    function startElement($parser, $name, $attribs)
    {
		global $JumpData;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "SOLARSYSTEMID":
                            $JumpData['solarSystemID'] = $v;
                            break;
                        case "SHIPJUMPS":
                            $JumpData['shipJumps'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $JumpData;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "DATATIME")
			$this->DataTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $tempvalue;
			ApiCache::set('API_map_Jumps' , $tempvalue);
		}

        if ($name == "ROW")
		{
			$this->Jumps_[] = $JumpData;
			$JumpData = array();
			unset($JumpData);
		}
    }

    function characterData($parser, $data)
    {
        global $tempvalue;

		$tempvalue = $data;
    }
}
