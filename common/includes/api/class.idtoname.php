<?php
/**
 * API ID -> Name Conversion /eve/CharacterID.xml.aspx
 */
class API_IDtoName extends API
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
		if ($this->API_IDs_ == "")
			return "No IDs have been input.";

		$data = $this->CallAPI( "eve", "CharacterName", array( "ids" => $this->API_IDs_ ) , null, null );

		if($data == false) return "Error fetching IDs";

		foreach($data->characters as $character) {
			$this->NameData_[] = array(
					'name'=>strval($character->name),
					'characterID'=>intval($character->characterID));
		}

		return "";
	}
}
