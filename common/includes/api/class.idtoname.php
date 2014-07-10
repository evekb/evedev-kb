<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

require_once("class.api.php");
// **********************************************************************************************************************************************
// ****************                                   API ID -> Name Conversion /eve/CharacterID.xml.aspx 	                     ****************
// **********************************************************************************************************************************************
class API_IDtoName  extends API
{

	private $API_IDs_ = '';
	private $IDData_ = array();

	public function setIDs($IDs)
	{
		$this->API_IDs_ = $IDs;
	}
	public function getIDData()
	{
		return $this->IDData_;
	}

	public function clear()
	{
		$this->IDData_ = array();
		unset($this->IDData_);
	}

	public function fetchXML()
	{
		if ($this->API_IDs_ == "")
                    return "No IDs have been input.";

                $data = $this->CallAPI( "eve", "CharacterName", array( "ids" => $this->API_IDs_ ) , null, null );
                
		if($data == false) return "Error fetching Names";

		foreach($data->characters as $character) {
			$this->IDData_[] = array(
                            'name'=>strval($character->name),
                            'characterID'=>intval($character->characterID));
                }
		return "";
	}
}
