<?php
/*
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
*/

// **********************************************************************************************************************************************
// ****************                                   API Char list - /account/Characters.xml.aspx                               ****************
// **********************************************************************************************************************************************
class API_Char
{
	//! Deprecated fetch function, user fetch(id, key) instead
	function fetchChars($apistring)
	{
		trigger_error("fetchChars is deprecated. Use fetch(userID, APIKey) instead.", E_USER_NOTICE);
		
        $parts = explode("&", $apistring);

		foreach($parts as &$part)
		{
			$part = explode("=", $part);
		}

		return $this->fetch($parts[0][1], $parts[1][1]);
	}

	function fetch($userID, $APIKey)
	{
		$data = $this->loaddata($userID, $APIKey);

		$sxe = simplexml_load_string($data);

		foreach($sxe->result->rowset->row as $row)
		{
			$this->chars_[] = array(
				'Name'=>strval($row['name']),
				'corpName'=>strval($row['corporationName']),
				'charID'=>strval($row['characterID']),
				'corpID'=>strval($row['corporationID']));
		}

		// add any characters not already in the kb
		return $this->updateChars();
	}

	private function loaddata($userID, $APIKey)
	{
        $url = "http://api.eve-online.com/account/Characters.xml.aspx";

		$http = new http_request($url, "POST");
		$http->set_useragent("PHPApi");

		$http->set_postform('userID', $userID);
		$http->set_postform('APIKey', $APIKey);

		return $http->get_content();
	}

	private function updateChars()
	{
		if(empty($this->chars_)) $this->chars_;
		foreach($this->chars_ as $char )
		{
			// check if chars eveid exists in kb
			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $char['Name'] . '"';

			$qry = DBFactory::getDBQuery();
			$qry->execute($sql);
			if ($qry->recordCount() != 0)
			{
				// pilot is in kb db, check he has his char id
				$row = $qry->getRow();

				$pilot_id = $row['plt_id'];
				$pilot_external_id = $row['plt_externalid'];

				if ( $pilot_external_id == 0 && $pilot_id != 0 )
				{
					// update DB with ID
					$qry->execute("UPDATE kb3_pilots SET plt_externalid = " . intval($char['charID']) . "
                                     WHERE plt_id = " . $pilot_id);
				}
			}
			else
			{
				// pilot is not in DB

				// Set Corp
				$pilotscorp = new Corporation();
				$pilotscorp->lookup($char['corpName']);
				// Check Corp was set, if not, add the Corp
				if ( !$pilotscorp->getID() )
				{
					$ialliance = new Alliance();
					$ialliance->add('None');
					$pilotscorp->add($char['corpName'], $ialliance, gmdate("Y-m-d H:i:s"));
				}
				$ipilot = new Pilot();
				$ipilot->add($char['Name'], $pilotscorp, gmdate("Y-m-d H:i:s"), intval($char['charID']));
			}
		}

		return $this->chars_;
	}
}

//! Legacy stub.
class APIChar extends API_Char
{
	
}
