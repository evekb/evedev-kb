<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                   API ID -> Name Conversion /eve/CharacterID.xml.aspx 	                     ****************
// **********************************************************************************************************************************************
class API_CharacterInfo
{
	private $CachedUntil = '';
	private $CurrentTime = '';
	private $API_ID = '';
	private $data = array();

	public function getCachedUntil()
	{
		return $this->CachedUntil;
	}

	public function getCurrentTime()
	{
		return $this->CurrentTime;
	}

	public function setID($ID)
	{
		$this->API_ID = $ID;
	}
	public function getData()
	{
		return $this->data;
	}

	public function clear()
	{
		$this->data = array();
		unset($this->data);
	}

	public function fetchXML()
	{
		if ($this->API_ID != "") $data = $this->loaddata($this->API_ID);
		else return "No IDs have been input.";

		if(!$data) return "Error fetching IDs";

		$sxe = @simplexml_load_string($data);
		
		if(!$sxe || strval($sxe->error)) return strval("Error code ".$sxe->error['code'].": ".$sxe->error);
		foreach($sxe->result->children() as $a => $b) $this->data[strval($a)] = strval($b);
		
		$this->CurrentTime = strval($sxe->currentTime);
		$this->CachedUntil = strval($sxe->cachedUntil);

		return "";
	}

	private function loaddata($id)
    {
        $url = "http://".API_SERVER."/eve/CharacterInfo.xml.aspx?characterID=" . urlencode($id);

		$http = new http_request($url);
		$http->set_useragent("PHPApi");

		return $http->get_content();
	}
}
