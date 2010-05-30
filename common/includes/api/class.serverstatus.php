<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                API Server Status - /server/ServerStatus.xml.aspx                             ****************
// **********************************************************************************************************************************************
class API_ServerStatus
{
   function getCachedUntil()
   {
      return $this->CachedUntil_;
   }

   function getCurrentTime()
   {
      return $this->CurrentTime_;
   }

   function getserverOpen()
   {
      return $this->serverOpen_;
   }

   function getonlinePlayers()
   {
      return $this->onlinePlayers_;
   }

    function fetchXML()
    {
        $data = API_Helpers::LoadGlobalData('/server/ServerStatus.xml.aspx');

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from api.eve-online.com/server/ServerStatus.xml.aspx</i><br><br>";

        xml_parser_free($xml_parser);

        return $this->html;
    }

	function startElement($parser, $name, $attribs)
    {
    // nothing to do here...
    }

    function endElement($parser, $name)
    {
      global $tempvalue;

      if ($name == "CURRENTTIME")
         $this->CurrentTime_ = $tempvalue;
      if ($name == "SERVEROPEN")
         $this->serverOpen_ = $tempvalue;
      if ($name == "ONLINEPLAYERS")
         $this->onlinePlayers_ = $tempvalue;
      if ($name == "CACHEDUNTIL")
      {
         $this->CachedUntil_ = $tempvalue;
		 ApiCache::set( 'API_server_ServerStatus' , $tempvalue);
      }
    }

	function characterData($parser, $data)
    {
      global $tempvalue;

      $tempvalue = $data;
    }
}
