<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
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

		if(!$data) return "Error fetching status";

		$sxe = @simplexml_load_string($data);

		if(!$sxe)
		{
			 trigger_error("Error retrieving API XML", E_USER_WARNING);
			 return "Error retrieving API XML";
		}
		if(strval($sxe->error)) return strval("Error code ".$sxe->error['code'].": ".$sxe->error);

        $this->serverOpen_ = strval($sxe->result->serverOpen);
        $this->onlinePlayers_ = strval($sxe->result->onlinePlayers);

		$this->CurrentTime_ = strval($sxe->currentTime);
		$this->CachedUntil_ = strval($sxe->cachedUntil);
		if($this->CachedUntil_) ApiCache::set( 'API_server_ServerStatus' , $this->CachedUntil_);

		return "";
	}
}
