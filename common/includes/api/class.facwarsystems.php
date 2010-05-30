<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

require_once('class.eveapi.php'); 
// **********************************************************************************************************************************************
// ****************                           API Faction War Systems - /map/FacWarSystems.xml.aspx                              ****************
// **********************************************************************************************************************************************

class API_FacWarSystems
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getFacWarSystems()
	{
		return $this->FacWarSystems_;
	}

    function fetchXML()
    {
        $data = LoadGlobalData('/map/FacWarSystems.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/map/FacWarSystems.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

        return $this->FacWarSystems_;
    }

    function startElement($parser, $name, $attribs)
    {
		global $FacWarSystem;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "SOLARSYSTEMID":
                            $FacWarSystem['solarSystemID'] = $v;
                            break;
                        case "SOLARSYSTEMNAME":
                            $FacWarSystem['solarSystemName'] = $v;
                            break;
						case "OCCUPYINGFACTIONID":
                            $FacWarSystem['occupyingFactionID'] = $v;
                            break;
						case "OCCUPYINGFACTIONNAME":
                            $FacWarSystem['occupyingFactionName'] = $v;
                            break;
						case "CONTESTED":
                            $FacWarSystem['contested'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $FacWarSystem;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_facwarsystems') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else {
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_map_FacWarSystems' , $this->CachedUntil_);
		}

        if ($name == "ROW")
		{
			$this->FacWarSystems_[] = $FacWarSystem;
			$FacWarSystem = array();
			unset($FacWarSystem);
		}
    }

    function characterData($parser, $data)
    {
        global $tempvalue;

		$tempvalue = $data;
    }
}
