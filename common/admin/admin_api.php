<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once("common/admin/admin_menu.php");

$page = new Page("Settings - Kill Log API");
$page->setCachable(false);
$page->setAdmin();

if (is_string($key_id = edkURI::getArg('delete')) ) {
	$qry2 = new DBQuery();
	$sql = "DELETE from kb3_api_keys WHERE key_id = '".$key_id."' AND key_kbsite = '".KB_SITE."'";
	$qry2->execute($sql);
}

function clearcache($pattern) {
	$files = glob($pattern."*.xml");
	foreach ((array) $files as $file) {
		if ($file == '..' || $file == '.') continue;
		if (is_file($file)) {
			unlink($file);
		}
	}
	$dirs = glob($pattern."*");
	foreach ((array) $dirs as $dir) {
		if ($dir == '..' || $dir == '.') continue;
		if (is_dir($dir)) {
			clearcache($dir.'/');
		}
	}
}

if ($_POST['clearapicache']) {
	$cachepath = KB_CACHEDIR.'/api/';

	clearcache($cachepath);

	$html .= "Cache cleared.<br />";
	$html .= "<script type=\"text/javascript\">window.location = \"?a=admin_api\"</script>"; //*/
}

if ($_POST['add'] ) {
	$qry = DBFactory::getDBQuery(true);
	$key_name = $qry->escape($_POST['keyname']);
	$key_id = $qry->escape($_POST['keyid']);
	$key_key = $qry->escape($_POST['keycode']);

	if(($key_id != 0) && ($key_name != '') && ($key_key != "")) {
		$sql = "INSERT INTO kb3_api_keys( key_name, key_id, key_key, key_kbsite, key_flags ) VALUES ( '$key_name', '$key_id', '$key_key', '".KB_SITE."', 0 )";
		$qry->execute($sql);
	}
	else {
		$html .= "Invalid key not added (Please fill out Name, ID and Verification Code)<br/>";
	}
}


if ($_POST['submit'] || $_POST['import']) {
	if ($_POST['API_MultipleMode']) {
		config::set('API_MultipleMode', '1');
	} else {
		config::set('API_MultipleMode', '0');
	}

	if ($_POST['post_no_npc_only']) {
		config::set('post_no_npc_only', '1');
	} else {
		config::set('post_no_npc_only', '0');
	}

	if ($_POST['API_NoOfAPICalls']) {
		$tmp = intval($_POST['API_NoOfAPICalls']);
		if($tmp <= 0 || $tmp > 100) $tmp = 1;
                config::set('apikillmails_numberofcalls', $tmp);
        }

	$html .= "Settings Saved.<br />";
}

if ($_POST['import'] || isset($_GET['Process'])) {
	// Importing of mails
	$myEveAPI = new API_KillLog();
	$myEveAPI->iscronjob_ = false;

	if (isset($_GET['Process'])) {
		$processindex = $_GET['Process'];
	} else {
		$processindex = 1;
	}

	if (config::get("API_MultipleMode")) { // save output to file and load when complete
		$i = $processindex;
		$qry = new DBQuery();
		$qry->execute("SELECT * FROM kb3_api_keys WHERE key_kbsite = '".KB_SITE."' ORDER BY key_name");
		$count = 0;
		while ($row = $qry->getRow()) {
			$count++;
			if ($i == $count) {
				$myEveAPI->Output_ .= "Importing Mails for ".$row['key_name']."<br />";
				$out = $myEveAPI->Import($row['key_name'], $row['key_id'], $row['key_key'],
								$row['key_flags']);
				$file = @fopen(KB_CACHEDIR.'/api/report.txt', 'a');
				fwrite($file, $out);
				fclose($file);

				$processindex++;
				if ($qry->getRow()) {
					$html .= "<script type=\"text/javascript\">window.location = \"?a=admin_api&Process=".$processindex."\"</script>"; //*/
					break;
				} else { // load report.txt to $html
					$fp = @fopen(KB_CACHEDIR.'/api/report.txt', 'r');
					$html .= fread($fp, filesize(KB_CACHEDIR.'/api/report.txt'));
					fclose($fp);
					@unlink(KB_CACHEDIR.'/api/report.txt'); // delete file, it was temporary
				}
			}
		}
	} else {
		$qry = new DBQuery();
		$qry->execute("SELECT * FROM kb3_api_keys WHERE key_kbsite = '".KB_SITE."' ORDER BY key_name");
		while ($row = $qry->getRow()) {
			$myEveAPI->Output_ .= "Importing Mails for ".$row['key_name']."<br />";
			$html .= $myEveAPI->Import($row['key_name'], $row['key_id'], $row['key_key'],
							$row['key_flags']);
		}
	}
}

// calculate cache size
$deld = 0;
$dsize = 0;
$cachepath = KB_CACHEDIR.'/api/*';
$match = "*";

dirsize($cachepath.$match);

function dirsize($pattern) {
	global $dsize, $deld;
	$files = glob($pattern);
	foreach ((array) $files as $file) {
		if ($file == '..' || $file == '.') {
			continue;
		}
		if (is_file($file)) {
			$dsize += filesize($file);
			$deld++;
		} else if (is_dir($file)) {
			dirsize($file.'/*');
		}
	}
}

if ($_POST['clearlog']) {
	$qry = DBFactory::getDBQuery();
	$qry->execute("DELETE FROM kb3_apilog WHERE log_site = '".KB_SITE."'");
}

if ($_POST['apilog']) {
	$html .= "<div class='block-header2'>API Log</div>";
	$html .= "<form id='options' name='options' method='post' action='".KB_HOST."/?a=admin_api'>";

	$sql = 'SELECT *
			FROM kb3_apilog
			WHERE log_site = "' .KB_SITE . '"
			ORDER BY log_timestamp DESC limit 250';

	$qry = DBFactory::getDBQuery();
	$qry->execute($sql) or die($qry->getErrorMsg());

	$html .= '<table class="kb-table" style="width:740px; margin-left:10px;">';
	$html .= "	<tr class='kb-table-header'>
			<td align='center' width='150'>Key Name</td>
			<td width='50'>Posted</td>
			<td width='50'>Malformed</td>
			<td width='50'>Ignored</td>
			<td width='50'>Verified</td>
			<td width='80'>Total Mails</td>
			<td width='70'>Source</td>
			<td width='60'>Type</td>
			<td width='60'>Code</td>
			<td width='180'>Time Stamp</td>
		</tr>";
	$odd = false;
	while ($row = $qry->getRow()) {
		if ($odd) {
			$class = "kb-table-row-even";
			$odd = false;
		} else {
			$class = "kb-table-row-odd";
			$odd = true;
		}
		// colour checks - makes things clearer
		$numposted = $row['log_posted'];
		$numerrors = $row['log_errors'];
		$numverified = $row['log_verified'];
		$numignored = $row['log_ignored'];
		$datasource = $row['log_source'];

		if ($numposted > 0) {
			$numposted = "<span style='color:00FF00'>".$numposted."</span>";
		}
		if ($numverified > 0) {
			$numverified = "<span style='color:00FF00'>".$numverified."</span>";
		}
		if ($numerrors > 0) {
			$numerrors = "<span style='color:FF0000'>".$numerrors."</span>";
		}
		if ($numignored > 0) {
			$numignored = "<span style='color:FF0000'>".$numignored."</span>";
		}
		if ($datasource == "Error") {
			$datasource = "<span style='color:FF0000'>".$datasource."</span>";
		}
		if ($datasource == "New XML") {
			$datasource = "<span style='color:00FF00'>".$datasource."</span>";
		}

		$html .= "<tr class='".$class."'>";
		$html .= "<td align='center'><b>".stripslashes($row['log_keyname'])."</b></td>";
		$html .= "<td>".$numposted."</td>";
		$html .= "<td>".$numerrors."</td>";
		$html .= "<td>".$numignored."</td>";
		$html .= "<td>".$numverified."</td>";
		$html .= "<td>".$row['log_totalmails']."</td>";
		$html .= "<td>".$datasource."</td>";
		$html .= "<td>".$row['log_type']."</td>";
		$html .= "<td>".$row['log_errorcode']."</td>";
		$html .= "<td>".$row['log_timestamp']."</td>";
		$html .= "</tr>";
	}
	$html .= "</table>";

	$html .= "<br />";
	$html .= "<table><tr><td width='60'><input type=\"submit\" name=\"back\" value=\"Back\" /></td><td><input type='submit' name='clearlog' value='Clear Log' /></td></tr></table>";
	$html .= "</form>";
} else {
	// API Settings
	$html .= "<div class='block-header2'>API Key Details (must be CEO/Director to retrieve corp mails)</div>";
	$html .= "<form id='options' name='options' method='post' action='".KB_HOST."/?a=admin_api'>";

	// show current server time
	$html .= "Servers current time: <span style='color:00FF00'>".date("M d Y H:i")."</span><br /><br />";

	function cmp($a, $b) {
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}

	$order = array();
	for ($i = 1; $i <= $keycount; $i++) {
		$corpName = config::get("API_Name_$i");
		$order[$i] = $corpName;
	}
	uasort($order, "cmp");

	// Remove blank entries from the beginning
	foreach ($order as $key => $value) {
		if ($order[$key] == "") unset($order[$key]);
	}

	$html .= "<table style='width: 100%' class='kb-subtable'>";
	$html .= "<thead><tr><td>Name</td><td>ID</td><td>Owner</td><td>Corp</td><td>Char</td><td style=\"width: 200px;\">Status</td><td></td></tr></thead>";


	$qry = new DBQuery();
	$qry->execute("SELECT * FROM kb3_api_keys WHERE key_kbsite = '".KB_SITE."' ORDER BY key_name");
	while ($row = $qry->getRow()) {
		$html .= ($cycle) ? "<tr class='kb-table-row-even'>" : "<tr class='kb-table-row-odd'>";
		$html .= "<td>".$row['key_name']."</td>";
		$html .= "<td title='".$row['key_key']."'>".$row['key_id']."</td>";

		$flags = $row['key_flags'];

		if ($flags == 0) {
			$act = new API_Account();
			if ($act->CheckAccess($row['key_id'], $row['key_key'], 256)) {
				// valid new style key with valid access
				switch ($act->GetType($row['key_id'], $row['key_key'])) {
					case "Character":
						$flags |= KB_APIKEY_CHAR;
						break;
					case "Corporation":
						$flags |= KB_APIKEY_CORP;
						break;
					default: //Just to be sure...
						$flags |= KB_APIKEY_BADAUTH;
						break;
				}
			} else {
				if ($act->getError() !== null) {
					switch ($act->getError()) {
						case 203:
							if ($act->isOldKey($row['key_id'], $row['key_key'])) {
								$flags |= KB_APIKEY_LEGACY;
								break;
							}
							$flags |= KB_APIKEY_BADAUTH;
							break;
						case 222:
							if ($act->isOldKey($row['key_id'], $row['key_key'])) {
								$flags |= KB_APIKEY_LEGACY;
								break;
							}
							$flags |= KB_APIKEY_EXPIRED;
							break;
						default: // All other errors are taken as BADAUTH, so we
							 // won't fail later in the code
							$flags |= KB_APIKEY_BADAUTH;
					}
				} else {
					// no error so user didn't have '256' access
				}
			}
			$qry2 = new DBQuery();
			$sql = "UPDATE kb3_api_keys SET key_flags = $flags WHERE key_name='"
					.$qry->escape($row['key_name'])."' AND key_id='"
					.$qry->escape($row['key_id'])."' AND key_key='"
					.$qry->escape($row['key_key'])."' AND key_kbsite = '".KB_SITE."'";
			$qry2->execute($sql);
		}
                $act = NULL;
		if ($flags & KB_APIKEY_LEGACY) {
			$html .= "<td></td><td>-</td><td>-</td>";
		} else {
			$html .= "<td>";
			$chars = array();
			if (!($flags & KB_APIKEY_BADAUTH || $flags & KB_APIKEY_EXPIRED)) {
				$act = new API_Account();
                                $characters = $act->fetch($row['key_id'], $row['key_key']);
                                if($characters)
                                {
                                    foreach ($characters as $character) {
                                            $chars[] = $character["characterName"].", ".$character["corporationName"];
                                    }
                                }
			}
			$html .= join('<br />', $chars);
			$html .= "</td>";
			if ($flags & KB_APIKEY_CORP) {
				$html .= "<td>X</td>";
			} else {
				$html .= "<td></td>";
			}

			if ($flags & KB_APIKEY_CHAR) {
				$html .= "<td>X</td>";
			} else {
				$html .= "<td></td>";
			}
		}

		// status column
		$html .= "<td>";
                if($act && $act->getMessage())
                {
                    $html .= $act->getMessage();
                    if($act->getError())
                    {
                        $html .= " (Code: ".$act->getError().")";
                    }
                }
		else if ($flags == 0) {
			$html .= "No Status";
		} else {
			if ($flags & KB_APIKEY_LEGACY) {
				$html .= "Requires Updated Key";
			}
			if ($flags & KB_APIKEY_BADAUTH) {
				$html .= "Bad Authentication";
			}
			if ($flags & KB_APIKEY_EXPIRED) {
				$html .= "Expired Key";
			}
		}
		$html .= "</td>";
		$html .= "<td><a href='?a=admin_api&amp;delete=".$row['key_id']."'>Del</a></td>";
		$html .= "</tr>";
		$cycle = !$cycle;
	}

	$html .= "</table>";

	$html .= "<div class='block-header2'>Add a new API Key</div>";
	$html .= "<i> Your API key ID and verification Code can be obtained <a href=\"https://community.eveonline.com/support/api-key/CreatePredefined?accessMask=256\" target=\"_blank\">here</a></i><br /><br />";

	$html .= "<table style='width: 100%' class='kb-subtable'>";
	$html .= "<thead><tr><td>Name</td><td>ID</td><td>Verification Code</td><td></td></tr></thead>";

	$html .= "<tbody><tr>";
	$html .= "<td><input type='text' name='keyname' id='keyname' size='20' /></td>";
	$html .= "<td><input type='text' name='keyid' id='keyid' size='10' maxlength='64' /></td>";
	$html .= "<td><input type='text' name='keycode' id='keycode' size='64' maxlength='64' /></td><td colspan='3'><input id='add' name='add' type='submit' value='Add' /></td></tr>";
	$html .= "<tr><td colspan='6'>&nbsp;</td></tr></tbody></table>";

	$html .= "<div class='block-header2'>Options</div><table>";

	$html .= "<tr><td  style='padding: 10px 0' colspan='2'>(".$deld." files with a total size of ".number_format($dsize,
									"0", ".", ",")." bytes)</td></tr>";

	$html .= "<tr><td style='padding: 10px 0' colspan='2'><input type='submit' id='submitCache' name='clearapicache' value=\"Clear Cache\" /></td></tr>";
	$html .= "</table>";

	$html .= "<table><tr><td style='height:30px; width:150px'>Ignore NPC only deaths?</td>";
	$html .= "<td><input type='checkbox' name='post_no_npc_only' id='post_no_npc_only'";
	if (config::get('post_no_npc_only')) $html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	$html .= "<tr><td style='height:30px; width:150px'>Import multiple keys one at a time? </td>";
	$html .= "<td><input type='checkbox' name='API_MultipleMode' id='API_MultipleMode'";
	if (config::get('API_MultipleMode')) $html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	$html .= "<tr><td style='height:30px; width:150px'>Max. number of API calls per key [1..100] </td>";
	$html .= "<td><input type='text' name='API_NoOfAPICalls' id='API_NoOfAPICalls' size='5'";
	if ($NoAPICalls = config::get('apikillmails_numberofcalls')) {
		 $html .= " value=\"". $NoAPICalls ."\"";
	}
	else {
		 $html .= " value=\"1\"";
	}
	$html .= " /></td></tr>";

	// Import
	$html .= "<tr><td style='padding: 10px 0' colspan=\"2\"><input type='submit' id='submitMails' name='import' value=\"Import Mails\" /></td></tr>";
	$html .= "</table>";
	// Save
	$html .= "<div class='block-header2'></div>";
	$html .= "<table><tr><td><input type=\"submit\" name=\"submit\" value=\"Save Settings\" /></td><td>&nbsp;</td><td><input type=\"submit\" name=\"apilog\" value=\"View Log\" /></td></tr>";
	$html .= "</table>";
	$html .= "</form>";
}

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();

function getPlayerDetails($characteridentitifier)
{
	$sql = 'select plts.plt_id, plts.plt_name from kb3_pilots plts where plts.plt_externalid = "'
			.$qry->escape($characteridentitifier).'"';

	$qry = DBFactory::getDBQuery();
	$qry->execute($sql);
	$row = $qry->getRow();

	$pilot_id = $row['plt_id'];
	$pilot_name = $row['plt_name'];

	if ($pilot_name != "") {
		return ' (<a href="'.KB_HOST.'/?a=pilot_detail&amp;plt_id='.$pilot_id.'">'.$pilot_name.'</a>)';
	} else {
		return "";
	}
}
