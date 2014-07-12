<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

require_once("class.api.php");
/**
 * Retrieve Character Info from CCP API
 * @package EDK
 */
class API_CharacterInfo extends API
{
	private $API_ID = '';
	private $data = array();
        private $currentTime;

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
        
        public function getCurrentTime()
        {
            return $this->currentTime;
        }

	public function fetchXML()
	{
		if ($this->API_ID == "")
                    return "No IDs have been input.";

                
                $data = $this->CallAPI( "eve", "CharacterInfo", array( "characterID" => urlencode($this->API_ID) ) , null, null );
                
                if($data == false) return "Error fetching IDs";
                $this->currentTime = $data->currentTime;
                $data = $data->toArray();
                $this->data = $data['result'];	
                


		return "";
	}
}
