<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                   API Error list - /eve/ErrorList.xml.aspx                                   ****************
// **********************************************************************************************************************************************

class API_ErrorList
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getErrorList()
	{
		return $this->Error_;
	}

    function fetchXML()
    {
        $data = API_Helpers::LoadGlobalData('/eve/ErrorList.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/eve/ErrorList.xml.aspx </i><br><br>";

        xml_parser_free($xml_parser);

        return $this->html;
    }

    function startElement($parser, $name, $attribs)
    {
		global $ErrorData;

        if ($name == "ROW")
        {
            if (count($attribs))
            {
                foreach ($attribs as $k => $v)
                {
                    switch ($k)
                    {
                        case "ERRORCODE":
                            $ErrorData['errorCode'] = $v;
                            break;
                        case "ERRORTEXT":
                            $ErrorData['errorText'] = $v;
                            break;
                    }
                }
            }
        }
    }

    function endElement($parser, $name)
    {
		global $ErrorData;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $tempvalue;
			ApiCache::set('API_eve_ErrorList' , $tempvalue);
		}

        if ($name == "ROW")
		{
			$this->Error_[] = $ErrorData;
			$ErrorData = array();
			unset($ErrorData);
		}
    }

    function characterData($parser, $data)
    {
        global $tempvalue;

		$tempvalue = $data;
    }
}
