<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

/**
 * API Conquerable Station/Outpost list - /eve/ConquerableStationList.xml.aspx
 * @package EDK
 */
class API_ConquerableStationList
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getStations()
	{
		return $this->Stations_;
	}

    function fetchXML()
    {
        $data = API_Helpers::LoadGlobalData('/eve/ConquerableStationList.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from ".API_SERVER."/eve/ConquerableStationList.xml.aspx </i><br><br>";

        xml_parser_free($xml_parser);

        return $this->html;
    }

    function startElement($parser, $name, $attribs)
    {
		global $Station;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "STATIONID":
                            $Station['stationID'] = $v;
                            break;
                        case "STATIONNAME":
                            $Station['stationName'] = $v;
                            break;
                        case "STATIONTYPEID":
                            $Station['stationTypeID'] = $v;
                            break;
                        case "SOLARSYSTEMID":
                            $Station['solarSystemID'] = $v;
                            break;
						case "CORPORATIONID":
                            $Station['corporationID'] = $v;
                            break;
						case "CORPORATIONNAME":
                            $Station['corporationName'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $Station;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_conq') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else {
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_eve_ConquerableStationList' , $this->CachedUntil_);
		}

        if ($name == "ROW")
		{
			$this->Stations_[] = $Station;
			$Station = array();
			unset($Station);
		}
    }

    function characterData($parser, $data)
    {
        global $tempvalue;

		$tempvalue = $data;
    }
}
