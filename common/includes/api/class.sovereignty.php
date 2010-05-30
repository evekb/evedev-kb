<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

require_once('class.eveapi.php'); 
// **********************************************************************************************************************************************
// ****************                            API Alliance Sovereignty - /map/Sovereignty.xml.aspx                              ****************
// **********************************************************************************************************************************************
class API_Sovereignty
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

	function getSovereignty()
	{
		return $this->Sovereignty_;
	}

    function fetchXML()
    {
        $data = LoadGlobalData('/map/Sovereignty.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/map/Sovereignty.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

        return $this->html;
    }

    function startElement($parser, $name, $attribs)
    {
		global $SovereigntyData;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "SOLARSYSTEMID":
                            $SovereigntyData['solarSystemID'] = $v;
                            break;
                        case "ALLIANCEID":
                            $SovereigntyData['allianceID'] = $v;
                            break;
						case "CONSTELLATIONSOVEREIGNTY":
                            $SovereigntyData['constellationSovereignty'] = $v;
                            break;
						case "SOVEREIGNTYLEVEL":
                            $SovereigntyData['sovereigntyLevel'] = $v;
                            break;
						case "FACTIONID":
                            $SovereigntyData['factionID'] = $v;
                            break;
						case "SOLARSYSTEMNAME":
                            $SovereigntyData['solarSystemName'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $SovereigntyData;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "DATATIME")
			$this->DataTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_sovereignty') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else {
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_map_Sovereignty' , $this->CachedUntil_);
		}

        if ($name == "ROW")
		{
			$this->Sovereignty_[] = $SovereigntyData;
			$SovereigntyData = array();
			unset($SovereigntyData);
		}
    }

    function characterData($parser, $data)
    {
		global $tempvalue;

		$tempvalue = $data;
    }

	function getSystemDetails($sysname)
	{
		if (!isset($this->Sovereignty_))
            $this->fetchXML();

        if (!isset($this->Sovereignty_))
            return false;

		$Sov = $this->Sovereignty_;

		foreach ($Sov as $myTempData)
		{
			if ($myTempData['solarSystemName'] == $sysname)
				return $myTempData;
		}

		return;
	}

	function getSystemIDDetails($sysID)
	{
		if (!isset($this->Sovereignty_))
            $this->fetchXML();

        if (!isset($this->Sovereignty_))
            return false;

		//echo var_dump($this->Sovereignty_);

		$Sov = $this->Sovereignty_;

		foreach ($Sov as $myTempData)
		{
			if ($myTempData['solarSystemID'] == $sysID)
				return $myTempData;
		}

		return false;
	}
}
