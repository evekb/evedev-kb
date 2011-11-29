<?php
/**
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

/**
 * Retrieve Character list from CCP API
 * @package EDK
 */
class API_Char
{
	private $error = false;
	private $chars = array();

	public function fetch($userID, $APIKey)
	{
		$data = $this->loaddata($userID, $APIKey);

		$sxe = @simplexml_load_string($data);

		if($sxe->error)
		{
			$this->error = array();
			$this->error['code'] = strval($sxe->error['code']);
			$this->error['message'] = strval($sxe->error);
		}
		else if($sxe)
		{
			foreach($sxe->result->rowset->row as $row)
			{
				$this->chars[] = array(
					'Name'=>strval($row['name']),
					'corpName'=>strval($row['corporationName']),
					'charID'=>strval($row['characterID']),
					'corpID'=>strval($row['corporationID']));
			}

			// add any characters not already in the kb
			$this->updateChars();
		}

		return $this->chars;
	}

	private function loaddata($userID, $APIKey)
	{
        $url = API_SERVER."/account/Characters.xml.aspx";

		$http = new http_request($url, "POST");
		$http->set_useragent("PHPApi");

		$http->set_postform('userID', $userID);
		$http->set_postform('APIKey', $APIKey);

		$result = $http->get_content();

		$this->error = array();
		$this->error['code'] = $http->get_http_code();
		$this->error['message'] = $http->getError();

		return $result;
	}

	private function updateChars()
	{
		if(empty($this->chars)) return $this->chars;
		foreach($this->chars as $char )
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
				$pilotscorp = Corporation::lookup($char['corpName']);
				// Check Corp was set, if not, add the Corp
				if ( !$pilotscorp->getID() )
				{
					$ialliance = Alliance::add('None');
					$pilotscorp = Corporation::add($char['corpName'], $ialliance, gmdate("Y-m-d H:i:s"));
				}
				Pilot::add($char['Name'], $pilotscorp, gmdate("Y-m-d H:i:s"), intval($char['charID']));
			}
		}

		return;
	}
	/**
	 * Return any errors encountered or false if none.
	 */
	function getError()
	{
		return $this->error;

	}
}
