<?php
/*
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
*/

// **********************************************************************************************************************************************
// ****************                                   API Char list - /account/Characters.xml.aspx                               ****************
// **********************************************************************************************************************************************
class APIChar
{
	function fetchChars($apistring)
	{
		$data = $this->loaddata($apistring);

		$xml_parser = xml_parser_create();
		xml_set_object ( $xml_parser, $this );
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler ( $xml_parser, 'characterData' );

		if (!xml_parse($xml_parser, $data, true))
			return "<i>Error getting XML data from api.eve-online.com/account/Characters.xml.aspx  </i><br><br>";

		xml_parser_free($xml_parser);

		// add any characters not already in the kb
		$numchars = count($this->chars_);
		for ( $x = 0; $x < $numchars; $x++ )
		{
			// check if chars eveid exists in kb
			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $this->chars_[$x]['Name'] . '"';

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
					$qry->execute("update kb3_pilots set plt_externalid = " . intval($this->chars_[$x]['charID']) . "
                                     where plt_id = " . $pilot_id);
				}
			} else
			{
				// pilot is not in DB

				// Set Corp
				$pilotscorp = new Corporation();
				$pilotscorp->lookup($this->chars_[$x]['corpName']);
				// Check Corp was set, if not, add the Corp
				if ( !$pilotscorp->getID() )
				{
					$ialliance = new Alliance();
					$ialliance->add('NONE');
					$pilotscorp->add($this->chars_[$x]['corpName'], $ialliance, gmdate("Y-m-d H:i:s"));
				}
				$ipilot = new Pilot();
				$ipilot->add($this->chars_[$x]['Name'], $pilotscorp, gmdate("Y-m-d H:i:s"));
				$ipilot->setCharacterID(intval($this->chars_[$x]['charID']));
			}
		}

		return $this->chars_;
	}

	function startElement($parser, $name, $attribs)
	{
		global $character;

		if ($name == "ROW")
		{
			if (count($attribs))
			{
				foreach ($attribs as $k => $v)
				{
					switch ($k)
					{
						case "NAME":
							$character['Name'] = $v;
							break;
						case "CORPORATIONNAME":
							$character['corpName'] = $v;
							break;
						case "CHARACTERID":
							$character['charID'] = $v;
							break;
						case "CORPORATIONID":
							$character['corpID'] = $v;
							break;
					}
				}
			}
		}
	}

	function endElement($parser, $name)
	{
		global $character;

		if ($name == "ROW")
		{
			$this->chars_[] = $character;
			$character = array();
			unset($character);
		}
	}

	function characterData($parser, $data)
	{
		// nothing
	}

	function loaddata($apistring)
	{
		$path = '/account/Characters.xml.aspx';
		$fp = @fsockopen("api.eve-online.com", 80);

		if (!$fp)
		{
			echo "Error", "Could not connect to API URL<br>";
		} else
		{
			// request the xml
			fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
			fputs ($fp, "Host: api.eve-online.com\r\n");
			fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
			fputs ($fp, "User-Agent: PHPApi\r\n");
			fputs ($fp, "Content-Length: " . strlen($apistring) . "\r\n");
			fputs ($fp, "Connection: close\r\n\r\n");
			fputs ($fp, $apistring."\r\n");
			stream_set_timeout($fp, 10);

			// retrieve contents
			$contents = "";
			while (!feof($fp))
			{
				$contents .= fgets($fp);
			}

			// close connection
			fclose($fp);

			$start = strpos($contents, "?>");
			if ($start != false)
			{
				$contents = substr($contents, $start + strlen("\r\n\r\n"));
			}
		}
		return $contents;
	}
}

class API_Char extends APIChar
{
	
}
