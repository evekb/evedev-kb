<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

/**
 * API Error list - /eve/ErrorList.xml.aspx
 * @package EDK
 */
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

		if(!$data) return "Error fetching IDs";

		$sxe = @simplexml_load_string($data);

		if(!$sxe || strval($sxe->error)) return strval("Error code ".$sxe->error['code'].": ".$sxe->error);
		foreach($sxe->result->rowset->row as $a)
		{
			$error['errorCode'] = strval($a['errorCode']);
			$error['errorText'] = strval($a['errorText']);
			$this->Error_[] = $error;
		}

		$this->CurrentTime_ = strval($sxe->currentTime);
		$this->CachedUntil_ = strval($sxe->cachedUntil);

		return "";
    }
}
