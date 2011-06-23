<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

// **********************************************************************************************************************************************
// ****************                                   API Name -> ID Conversion /eve/CharacterID.xml.aspx 	                     ****************
// **********************************************************************************************************************************************
class API_NametoID
{
	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function setNames($names)
	{
		$this->API_Names_ = $names;
	}
	function getNameData()
	{
		return $this->NameData_;
	}

	function clear()
	{
		$this->NameData_ = array();
		unset($this->NameData_);
	}

	public function fetchXML()
	{
		if ($this->API_Names_ != "") $data = $this->loaddata($this->API_Names_);
		else return "No Names have been input.";

		if(!$data) return "Error fetching names";

		$sxe = @simplexml_load_string($data);

		if(!$sxe || strval($sxe->error)) return strval("Error code ".$sxe->error['code'].": ".$sxe->error);

		foreach($sxe->result->rowset->row as $row)
			$this->NameData_[] = array('name'=>strval($row['name']),
				'characterID'=>intval($row['characterID']));

		$this->CurrentTime_ = strval($sxe->currentTime);
		$this->CachedUntil_ = strval($sxe->cachedUntil);

		return "";
	}

	private function loaddata($names)
    {
        $url = API_SERVER."/eve/CharacterID.xml.aspx?names=".urlencode($names);

		$http = new http_request($url);
		$http->set_useragent("PHPApi");
		return $http->get_content();
	}
}
