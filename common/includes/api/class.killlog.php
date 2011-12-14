<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

/**
 * API KillLog - /corp/Killlog.xml.aspx
 */
class API_KillLog extends API
{
	function Import($name, $id, $key, $flags)
	{
		$output = "";

		// Skip bad keys
		if ( $flags & KB_APIKEY_BADAUTH || $flags & KB_APIKEY_EXPIRED ) {
			return; // skip bad keys
		}

		// also skip legacy keys now
		if( $flags & KB_APIKEY_LEGACY)
			return;

		// reduces strain on DB
		if(function_exists("set_time_limit")) {
      		set_time_limit(0);
		}
		$lastdatakillid = 1;
		$currentdatakillid = 0;

		$logsource = "New XML";
		// Load new XML
		$output = "<i>Downloading latest XML file for $name</i><br><br>";

		$accts = new API_Account();
		$characters = $accts->fetch($id, $key);
		$posted = array();
		$skipped = array();

		foreach ($characters as $char) {
			$output .= "Processing ".$char['characterName']."<br><br>";
			$currentkill = 0;
			$lastkill = -1;
			while ($lastkill != $currentkill) {
				$lastkill = $currentkill;
				$args = array("characterID" => $char['characterID']);
				if ($lastkill) {
					$args["beforeKillID"] = $lastkill;
				}
				if ($flags & KB_APIKEY_CORP) {
					$killLog = $this->CallAPI("corp", "KillLog", $args, $id, $key);
				} else if ($flags & KB_APIKEY_CHAR) {
					$killLog = $this->CallAPI("char", "KillLog", $args, $id, $key);
				} else {
					$output .= "<div class='block-header2'>Key does not have access to KillLog</div>";
					break;
				}

				if ($this->getError() === null) {
					// Get oldest kill
					$currentkill = 0;
					$sxe = simplexml_load_string($this->pheal->xml);
					foreach ($sxe->result->rowset->row as $row) {
						if ($currentkill < (int) $row['killID']) {
							$currentkill = (int) $row['killID'];
						}
					}
				}

				if ($this->getError() !== null) {
					if ($this->getError() == 120 && $this->pheal->xml) {
						// Check if we just need to skip back a few kills
						// i.e. first page of kills is already fetched.
						$pos = strpos($this->pheal->xml, "Expected beforeKillID [");
						if ($pos) {
							$pos += 23;
							$pos2 = strpos($this->pheal->xml, "]", $pos);
							$currentkill = (int) substr($this->pheal->xml, $pos, $pos2 - $pos);
						}
					} else if (!$posted && !$skipped) {
						// Something went wrong and no kills were found.
						$qry = DBFactory::getDBQuery();
						$logtype = "Cron Job";

						$qry->execute("insert into kb3_apilog	values( '".KB_SITE."', '"
										.addslashes($name)."',"
										."0, "
										."0, "
										."0, "
										."0, "
										."0, '"
										."Error','"
										."Cron Job','"
										.$this->getError()."', "
										."UTC_TIMESTAMP() )");
						$output .= "<div class='block-header2'>".$this->getMessage()
										."</div>";
						break;
					} else {
						// We found kills!
						$qry = DBFactory::getDBQuery();
						$logtype = "Cron Job";

						$qry->execute("insert into kb3_apilog values( '".KB_SITE."', '"
										.addslashes($name)."',"
										.count($posted).","
										."0 ,"
										.count($skipped).","
										."0 ,"
										.(count($posted) + count($skipped)).",'"
										."New XML','"
										."Cron Job','"
										.($this->getError() == 119 ? 0 : $this->getError())."', "
										."UTC_TIMESTAMP() )");

						break;
					}
				}

				$feedfetch = new IDFeed();
				$feedfetch->setXML($this->pheal->xml);
				$feedfetch->setLogName("API");
				$feedfetch->setAcceptedTrust(-1);
				$feedfetch->setKillTrust(3);
				$feedfetch->read();

				$posted = array_merge($posted, $feedfetch->getPosted());
				$skipped = array_merge($skipped, $feedfetch->getSkipped());

				$output .= "<div class='block-header2'>"
								.count($posted)." kill".(count($posted) == 1 ? "" : "s")." posted, "
								.count($skipped)." skipped from feed: ".$id.".<br></div>";
				if (count($posted)) {
					$output .= "<div class='block-header2'>Posted</div>\n";
					foreach ($posted as $killid) {
						$output .= "<div><a href='"
										.edkURI::page('kill_detail', $killid[2], 'kll_id')
										."'>Kill ".$killid[0]."</a></div>";
					}
				}
			}
		}
		return $output;
	}
}
