<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

// **********************************************************************************************************************************************
// ****************                               API Reference Types - /eve/RefTypes.xml.aspx                                   ****************
// **********************************************************************************************************************************************
class API_RefTypes
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getRefTypes()
	{
		return $this->RefTypes_;
	}

    function fetchXML()
    {
        $data = API_Helpers::LoadGlobalData('/eve/RefTypes.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from ".API_SERVER."/eve/RefTypes.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

        return $this->html;
    }

    function startElement($parser, $name, $attribs)
    {
		global $RefTypeData;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "REFTYPEID":
                            $RefTypeData['refTypeID'] = $v;
                            break;
                        case "REFTYPENAME":
                            $RefTypeData['refTypeName'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $RefTypeData;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $tempvalue;
			ApiCache::set('API_eve_RefTypes' , $tempvalue);
		}

        if ($name == "ROW")
		{
			$this->RefTypes_[] = $RefTypeData;
			$RefTypeData = array();
			unset($RefTypeData);
		}
    }

    function characterData($parser, $data)
    {
        global $tempvalue;

		$tempvalue = $data;
    }
}
