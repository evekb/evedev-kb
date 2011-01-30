<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                   API ID -> Name Conversion /eve/CharacterID.xml.aspx 	                     ****************
// **********************************************************************************************************************************************
class API_IDtoName
{
	private $CachedUntil_ = '';
	private $CurrentTime_ = '';
	private $API_IDs_ = '';
	private $NameData_ = array();

	public function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	public function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	public function setIDs($IDs)
	{
		$this->API_IDs_ = $IDs;
	}
	public function getIDData()
	{
		return $this->NameData_;
	}

	public function clear()
	{
		$this->NameData_ = array();
		unset($this->NameData_);
	}

	public function fetchXML()
	{
		if ($this->API_IDs_ != "") $data = $this->loaddata($this->API_IDs_);
		else return "No IDs have been input.";

		if(!$data) return "Error fetching IDs";

		$sxe = @simplexml_load_string($data);
		
		if(!$sxe || strval($sxe->error)) return strval("Error code ".$sxe->error['code'].": ".$sxe->error);

		foreach($sxe->result->rowset->row as $row)
			$this->NameData_[] = array('name'=>strval($row['name']),
				'characterID'=>intval($row['characterID']));
		
		$this->CurrentTime_ = strval($sxe->currentTime);
		$this->CachedUntil_ = strval($sxe->cachedUntil);

		return "";
	}

	private function loaddata($ids)
    {
        $url = "http://".API_SERVER."/eve/CharacterName.xml.aspx?ids=" . urlencode($ids);

		$http = new http_request($url);
		$http->set_useragent("PHPApi");

		return $http->get_content();
	}
}
