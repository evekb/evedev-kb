<?php
/**
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

 require_once("class.api.php");
/**
 * Retrieve Character list from CCP API
 * @package EDK
 */
class API_Char extends API
{
	private $error = false;
	private $chars = array();

	public function fetch($userID, $APIKey)
	{
		$data = $this->CallAPI( "account", "Characters", null, $userID, $APIKey );
		
		foreach($data->characters as $character) {
			$this->chars[] = array(
					'Name'=>strval($character->name),
					'corpName'=>strval($character->corporationName),
					'charID'=>strval($character->characterID),
					'corpID'=>strval($character->corporationID));

			// add any characters not already in the kb
			$this->updateChars();
					
			}
		return $this->chars;
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
}
