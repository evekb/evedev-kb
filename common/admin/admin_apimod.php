<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

//
// Eve-Dev API Killmail parser by Captain Thunk! (ISK donations are all gratefully received)
//

require_once("common/admin/admin_menu.php");

$page = new Page("Settings - API Mod " . APIVERSION);
$page->setCachable(false);
$page->setAdmin();

$isupdated = false;

// check db update for API fields
if (config::get('API_DBUpdate') != 1)
	checkDBforAPI();

if ($_GET['CharID'])
{
    if ($_GET['SetNum'])
    {
        config::set("API_CharID_" . $_GET["SetNum"], $_GET["CharID"]);
        $html .= "Character updated.";
    }
}

if (config::get('API_Key_count'))
    $keycount = config::get('API_Key_count');
else
    $keycount = 1;

if ($_POST['clearapicache'])
{
	$deld = 0;
	$dsize = 0;
	$cachepath = KB_CACHEDIR.'/api/*';
	$match = "*";

	//$dirs = glob($cachepath."*");
	$files = glob($cachepath.$match);
	foreach((array)$files as $file){
		if(is_file($file)){
			$dsize += filesize($file);
			unlink($file);
			$deld++;
		}
	}

	$html .= "Cache cleared.<br />";
	$html .= "<script type=\"text/javascript\">window.location = \"?a=admin_apimod\"</script>"; //*/
}

if ($_POST['add'] ) {
	$key_name = $_POST['keyname'];
	$key_id = $_POST['keyid'];
	$key_key = $_POST['keycode'];

	$qry = DBFactory::getDBQuery(true);
	$sql = "INSERT INTO kb3_api_keys( key_name, key_id, key_key, key_kbsite, key_flags ) VALUES ( '$key_name', '$key_id', '$key_key', '" . KB_SITE . "', 0 )";
	$qry->execute($sql);
}


if ($_POST['submit'] || $_POST['import']  )
{
    if ($_POST['API_Comment'])
        config::set('API_Comment', $_POST['API_Comment']);
    else
        config::set('API_Comment', '');

    if ($_POST['API_Update'])
        config::set('API_Update', '0');
    else
        config::set('API_Update', '1');

    if ($_POST['API_IgnoreNPC'])
        config::set('API_IgnoreNPC', '0');
    else
        config::set('API_IgnoreNPC', '1');

    if ($_POST['API_IgnoreCorpFF'])
        config::set('API_IgnoreCorpFF', '0');
    else
        config::set('API_IgnoreCorpFF', '1');

    if ($_POST['API_IgnoreAllianceFF'])
        config::set('API_IgnoreAllianceFF', '0');
    else
        config::set('API_IgnoreAllianceFF', '1');

    if ($_POST['API_IgnoreFriendPos'])
        config::set('API_IgnoreFriendPos', '0');
    else
        config::set('API_IgnoreFriendPos', '1');

    if ($_POST['API_IgnoreEnemyPos'])
        config::set('API_IgnoreEnemyPos', '0');
    else
        config::set('API_IgnoreEnemyPos', '1');

    if ($_POST['API_NoSpam'])
        config::set('API_NoSpam', '0');
    else
        config::set('API_NoSpam', '1');

	if ($_POST['API_UseCache'])
        config::set('API_UseCache', '0');
    else
        config::set('API_UseCache', '1');

    if ($_POST['API_MultipleMode'])
        config::set('API_MultipleMode', '0');
    else
        config::set('API_MultipleMode', '1');

	if ($_POST['API_extendedtimer_sovereignty'])
        config::set('API_extendedtimer_sovereignty', '0');
    else
        config::set('API_extendedtimer_sovereignty', '1');

	if ($_POST['API_extendedtimer_alliancelist'])
        config::set('API_extendedtimer_alliancelist', '0');
    else
        config::set('API_extendedtimer_alliancelist', '1');

	if ($_POST['API_extendedtimer_conq'])
        config::set('API_extendedtimer_conq', '0');
    else
        config::set('API_extendedtimer_conq', '1');

	if ($_POST['API_ConvertTimestamp'])
        config::set('API_ConvertTimestamp', '0');
    else
        config::set('API_ConvertTimestamp', '1');

    $html .= "Settings Saved.<br />";
}

if ($_POST['import'] || isset($_GET['Process']))
{
    // Importing of mails
    $myEveAPI = new API_KillLog();
    $myEveAPI->iscronjob_ = false;

    if ($_GET['Process'])
    {
        $processindex = $_GET['Process'];
    } else {
        $processindex = 1;
    }

	if (config::get("API_MultipleMode") == 0 )
	{ // save output to file and load when complete
		$i = $processindex;
		$myEveAPI->Output_ .= "Importing Mails for " . config::get("API_Name_" . $i) . "<br />";
		$keystring = 'userID=' . config::get('API_UserID_' . $i) . '&apiKey=' . config::get('API_Key_' . $i) . '&characterID=' . config::get('API_CharID_' . $i);
		$typestring = config::get("API_Type_" . $i);
		$outputdata .= $myEveAPI->Import($keystring, $typestring, $i);
		$apicachetime[$i] = $myEveAPI->CachedUntil_;

		$file = @fopen(KB_CACHEDIR.'/data/report.txt', 'a');
		fwrite($file, $outputdata);
		fclose($file);

		//ApiCache::set('API_CachedUntil_' . $keyindex, $myEveAPI->cachetext_);
		$processindex++;
		if ($processindex <= $keycount)
		{
			//$html .= "<a href=\"http:?a=admin_apimod&Process=" . $processindex . "\">Click to process next Key</a>";
			$html .= "<script type=\"text/javascript\">window.location = \"?a=admin_apimod&Process=" .$processindex . "\"</script>"; //*/
		} else { // load report.txt to $html
			$fp = @fopen(KB_CACHEDIR.'/data/report.txt', 'r');
			$html .= fread($fp, filesize(KB_CACHEDIR.'/data/report.txt'));
			fclose($fp);
			@unlink(KB_CACHEDIR.'/data/report.txt'); // delete file, it was temporary

		}
	} else {
		$qry = new DBQuery();
		$qry->execute("SELECT * FROM kb3_api_keys WHERE key_kbsite = '" . KB_SITE . "' ORDER BY key_name");
		while ($row = $qry->getRow()) {
			$myEveAPI->Output_ .= "Importing Mails for " . $row['key_name'] . "<br />";
			$html .= $myEveAPI->Import($row['key_name'], $row['key_id'], $row['key_key'], $row['key_flags']);
			$apicachetime[$i] = $myEveAPI->CachedUntil_;
		}
	}
}

// calculate cache size
$deld = 0;
$dsize = 0;
$cachepath = KB_CACHEDIR.'/api/*';
$match = "*";

$files = glob($cachepath.$match);
foreach((array)$files as $file){
	if(is_file($file)){
		$dsize += filesize($file);
		$deld++;
	}
}

if ($_POST['clearlog'])
{
	$qry = DBFactory::getDBQuery();
	$qry->execute("DELETE FROM kb3_apilog WHERE log_site = '" . KB_SITE . "'");
}

if ($_POST['apilog'])
{
	$html .= "<div class='block-header2'>API Log</div>";
	$html .= "<form id='options' name='options' method='post' action='".KB_HOST."/?a=admin_apimod'>";

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
	while ($row = $qry->getRow())
	{
    	if ($odd)
    	{
       	 	$class = "kb-table-row-even";
        	$odd = false;
    	}
    	else
    	{
        	$class = "kb-table-row-odd";
        	$odd = true;
    	}
		// colour checks - makes things clearer
		$numposted = $row['log_posted'];
		$numerrors = $row['log_errors'];
		$numverified = $row['log_verified'];
		$numignored = $row['log_ignored'];
		$datasource = $row['log_source'];

		if ( $numposted > 0 )
			$numposted = "<font color = \"#00FF00\">" . $numposted . "</font>";
		if ( $numverified > 0 )
			$numverified = "<font color = \"#00FF00\">" . $numverified . "</font>";
		if ( $numerrors > 0 )
			$numerrors = "<font color = \"#FF0000\">" . $numerrors . "</font>";
		if ( $numignored > 0 )
			$numignored = "<font color = \"#FF0000\">" . $numignored . "</font>";
		if ( $datasource == "Error" )
			$datasource = "<font color = \"#FF0000\">" . $datasource . "</font>";
		if ( $datasource == "New XML" )
			$datasource = "<font color = \"#00FF00\">" . $datasource . "</font>";

    	$html .= "<tr class='" . $class . "'>";
    	$html .= "<td align='center'><b>" . stripslashes($row['log_keyname']) . "</b></td>";
    	$html .= "<td>" . $numposted . "</td>";
    	$html .= "<td>" . $numerrors . "</td>";
    	$html .= "<td>" . $numignored . "</td>";
    	$html .= "<td>" . $numverified . "</td>";
		$html .= "<td>" . $row['log_totalmails'] . "</td>";
		$html .= "<td>" . $datasource . "</td>";
		$html .= "<td>" . $row['log_type'] . "</td>";
		$html .= "<td>" . $row['log_errorcode'] . "</td>";
		$html .= "<td>" . $row['log_timestamp'] . "</td>";
    	$html .= "</tr>";
	}
	$html .= "</table>";

	$html .= "<br />";
	$html .= "<table><tr><td width='60'><input type=\"submit\" name=\"back\" value=\"Back\" /></td><td><input type='submit' name='clearlog' value='Clear Log' /></td></tr></table>";
	$html .= "</form>";

} else {
	// API Settings
	$html .= "<div class='block-header2'>API Key Details (must be CEO/Director to retrieve corp mails)</div>";
	$html .= "<form id='options' name='options' method='post' action='".KB_HOST."/?a=admin_apimod'>";

	// show current server time
	$html .= "Servers current time: <font color = \"#00FF00\">" . date("M d Y H:i") . "</font><br /><br />";

function cmp($a, $b) {
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

$order = array();
for ($i = 1; $i <= $keycount; $i++ )
{
        $corpName = config::get("API_Name_$i");
        $order[$i] = $corpName;
}
uasort($order, "cmp");

// Remove blank entries from the beginning
foreach($order as $key => $value ) {
	if ($order[$key] == "" ) unset ($order[$key]);
}

$html .= "<i> Your UserID and API FULL Key can be obtained <a href=\"http://support.eveonline.com/api/Key/CreatePredefined/256\">here</a></i><br /><br />";

$html .= "<table style='width: 100%' class='kb-subtable'>";
$html .= "<tr><td>Name</td><td>ID</td><td>Verification Code</td><td>Corp</td><td>Char</td><td>Status</td></tr>";

$html .= "<tr class='kb-table-row-even'>";
$html .= "<td><input type='text' name='keyname' id='keyname' size='20' /></td>";
$html .= "<td><input type='text' name='keyid' id='keyid' size='10' maxlength='64' /></td>";
$html .= "<td><input type='text' name='keycode' id='keycode' size='32' maxlength='64' /></td><td colspan='3'><input id='add' name='add' type='submit' value='Add' /></td></tr>";
$html .= "<tr><td colspan='6'>&nbsp;</td></tr>";
$qry = new DBQuery();
$qry->execute("SELECT * FROM kb3_api_keys WHERE key_kbsite = '" . KB_SITE . "' ORDER BY key_name");
while ($row = $qry->getRow()) {
	$html .= ($cycle) ? "<tr class='kb-table-row-even'>" : "<tr class='kb-table-row-odd'>";
	$html .= "<td>".$row['key_name']."</td>";
	$html .= "<td>".$row['key_id']."</td>";
	$html .= "<td>".$row['key_key']."</td>";

	$flags = $row['key_flags'];

	if ($flags == 0 ) {
		$act = new API_Account();
		if ($act->CheckAccess($row['key_id'], $row['key_key'],256)) {
			// valid new style key with valid access
			switch( $act->GetType($row['key_id'], $row['key_key']) ) {
				case "Account":
					$flags |= KB_APIKEY_CHAR;
					break;
				case "Corporation":
					$flags |= KB_APIKEY_CORP;
					break;
			}
		} else {
			if( $act->getError() !== null ) {
				switch ( $act->getError() ) {
					case 203:
						if( $act->isOldKey($row['key_id'], $row['key_key']) ) {
							$flags |= KB_APIKEY_LEGACY;
							break;
						}
						$flags |= KB_APIKEY_BADAUTH;
						break;
					case 222:
						if( $act->isOldKey($row['key_id'], $row['key_key']) ) {
							$flags |= KB_APIKEY_LEGACY;
							break;
						}
						$flags |= KB_APIKEY_EXPIRED;
						break;
					default:
				}
			} else {
				// no error so user didn't have '256' access
			}
		}
		$qry2 = new DBQuery();
		$sql = "UPDATE kb3_api_keys SET key_flags = $flags WHERE key_name='".$row['key_name']."' AND key_id='".$row['key_id']."' AND key_key='".$row['key_key']."' AND key_kbsite = '" . KB_SITE . "'";
		$qry2->execute($sql);
	}

	if ($flags & KB_APIKEY_LEGACY) {
		$html .= "<td>-</td><td>-</td>";
	} else {
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
	if ($flags == 0 ) {
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
	$html .= "</tr>";
	$cycle = !$cycle;
}
$html .= "</table>";

	// API Caching Options
	$html .= "<div class='block-header2'>API XML Caching Options</div><table>";

	$html .= "<tr><td height='30' width='150'>Enable API XML Caching?</td>";
	$html .= "<td><input type='checkbox' name='API_UseCache' id='API_UseCache'";
	if (!config::get('API_UseCache'))
    	$html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	$html .= "<tr><td height='30' width='150'>Convert Cache Times to local time?</td>";
	$html .= "<td><input type='checkbox' name='API_ConvertTimestamp' id='API_ConvertTimestamp'";
	if (!config::get('API_ConvertTimestamp'))
    	$html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	$html .= "<tr><td height='30' width='150'>Use Extended 24hr cache timer for AllianceList.xml?</td>";
	$html .= "<td><input type='checkbox' name='API_extendedtimer_alliancelist' id='API_extendedtimer_alliancelist'";
	if (!config::get('API_extendedtimer_alliancelist'))
    	$html .= " checked=\"checked\"";
	$html .= " />";
	//$tempcachetime =  date("Y-m-d H:i:s",  strtotime(ApiCache::get('API_eve_AllianceList')) + $gmoffset);
	$tempcachetime = API_Helpers::ConvertTimestamp(ApiCache::get('API_eve_AllianceList'));
	if ($tempcachetime == "")
	{
		$html .= "</td></tr>";
	} else {
		if (strtotime(gmdate("M d Y H:i:s")) - strtotime(ApiCache::get('API_eve_AllianceList')) > 0)
		{
		$txtcolour = "<font color = \"#00FF00\">";
		//$txtcolour = "<style=\"color:green\">";
    	} else {
    		$txtcolour = "<font color = \"#FF0000\">";
    	}
		$html .= "Data is cached until:</td><td>" . $txtcolour . $tempcachetime . "</font></td></tr>";
	}

	$html .= "<tr><td height='30' width='150'>Use Extended 24hr cache timer for ConquerableStationList.xml?</td>";
	$html .= "<td><input type='checkbox' name='API_extendedtimer_conq' id='API_extendedtimer_conq'";
	if (!config::get('API_extendedtimer_conq'))
    	$html .= " checked=\"checked\"";
	$html .= " />";
	//$tempcachetime =  date("Y-m-d H:i:s",  strtotime(ApiCache::get('API_eve_ConquerableStationList')) + $gmoffset);
	$tempcachetime = API_Helpers::ConvertTimestamp(ApiCache::get('API_eve_ConquerableStationList'));
	if ($tempcachetime == "")
	{
		$html .= "</td></tr>";
	} else {
		if (strtotime(gmdate("M d Y H:i:s")) - strtotime(ApiCache::get('API_eve_ConquerableStationList')) > 0)
		{
		$txtcolour = "<font color = \"#00FF00\">";
		//$txtcolour = "<style=\"color:green\">";
    	} else {
    		$txtcolour = "<font color = \"#FF0000\">";
    	}
		$html .= "Data is cached until:</td><td>" . $txtcolour . $tempcachetime . "</font></td></tr>";
	}

	$html .= "<tr><td height=\"10\"></td></tr>"; // spacer
	$html .= "<tr><td>(" . $deld . " files with a total size of " . number_format($dsize,"0",".",",") . " bytes)</td></tr>";

	$html .= "<tr><td height=\"10\"></td></tr>"; // spacer
	$html .= "<tr><td colspan=\"2\"><input type='submit' id='submitCache' name='clearapicache' value=\"Clear Cache\" /></td></tr>";

	$html .= "</table>";

	// Killmail Parser Options
	$html .= "<div class='block-header2'>Killmail API Parsing Options</div><table>";
	$html .= "<tr><td height='50' width='150'>Comment for automatically parsed killmails?</td>";
	$html .= "<td><input type='text' size='50' class='password' name='API_Comment' id='API_Comment' value=\"";
	if (config::get('API_Comment'))
    	$html .= config::get('API_Comment');
	$html .= "\" /><br /><i> (leave blank for none)</i><br /></td></tr>";

	$html .= "<tr><td height='30' width='150'>Update Portraits?</td>";
	$html .= "<td><input type='checkbox' name='API_Update' id='API_Update'";
	if (!config::get('API_Update'))
    	$html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	$html .= "<tr><td height='30' width='150'>Ignore NPC only deaths? <i>(This includes kills by POSs)</i></td>";
	$html .= "<td><input type='checkbox' name='API_IgnoreNPC' id='API_IgnoreNPC'";
	if (!config::get('API_IgnoreNPC'))
    	$html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	$html .= "<tr><td height='30' width='150'>Ignore Friendly Fire? </td>";
	$html .= "<td><input type='checkbox' name='API_IgnoreCorpFF' id='API_IgnoreCorpFF'";
	if (!config::get('API_IgnoreCorpFF'))
    	$html .= " checked=\"checked\"";
	$html .= " /> Corps <input type='checkbox' name='API_IgnoreAllianceFF' id='API_IgnoreAllianceFF'";
	if (!config::get('API_IgnoreAllianceFF'))
    	$html .= " checked=\"checked\"";
	$html .= " /> Alliance</td></tr>";

	$html .= "<tr><td height='30' width='150'>Ignore POS Structures? </td>";
	$html .= "<td><input type='checkbox' name='API_IgnoreFriendPos' id='API_IgnoreFriendPos'";
	if (!config::get('API_IgnoreFriendPos'))
    	$html .= " checked=\"checked\"";
	$html .= " /> Friend <input type='checkbox' name='API_IgnoreEnemyPos' id='API_IgnoreEnemyPos'";
	if (!config::get('API_IgnoreEnemyPos'))
    	$html .= " checked=\"checked\"";
	$html .= " /> Enemy</td></tr>";

	$html .= "<tr><td height='30' width='150'>Concise cronjob e-mail? </td>";
	$html .= "<td><input type='checkbox' name='API_NoSpam' id='API_NoSpam'";
	if (!config::get('API_NoSpam'))
   	 	$html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	$html .= "<tr><td height='30' width='150'>Import multiple keys one at a time? </td>";
	$html .= "<td><input type='checkbox' name='API_MultipleMode' id='API_MultipleMode'";
	if (!config::get('API_MultipleMode'))
    	$html .= " checked=\"checked\"";
	$html .= " /></td></tr>";

	// Import
	$html .= "<tr><td height=\"10\"></td></tr>"; // spacer
	$html .= "<tr><td colspan=\"2\"><input type='submit' id='submitMails' name='import' value=\"Import Mails\" /></td></tr>";
	$html .= "</table>";
	// Save
	$html .= "<div class='block-header2'></div>";
	$html .= "<table><tr><td colspan=\"2\"><input type=\"submit\" name=\"submit\" value=\"Save Settings\" /></td><td>&nbsp;</td><td colspan=\"2\"><input type=\"submit\" name=\"apilog\" value=\"View Log\" /></td></tr>";
	$html .= "</table>";
	$html .= "</form>";
}
$html .= "<div class='block-header2'></div>";
$html .= "<div>Written by " . API_Helpers::FindThunk() . " (<a href=\"http://eve-id.net/forum/viewtopic.php?f=505&amp;t=8827\" >Support</a>)</div>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();

function getPlayerDetails( $characteridentitifier )
{
    $sql = 'select plts.plt_id, plts.plt_name from kb3_pilots plts where plts.plt_externalid = "' . $characteridentitifier . '"';

    $qry = DBFactory::getDBQuery();
    $qry->execute($sql);
    $row = $qry->getRow();

    $pilot_id = $row['plt_id'];
    $pilot_name = $row['plt_name'];

    if ($pilot_name != "")	{
        return ' (<a href="'.KB_HOST.'/?a=pilot_detail&amp;plt_id=' . $pilot_id . '">'. $pilot_name . '</a>)';
    } else {
        return "";
    }
}

function checkDBforAPI()
{
	$qry = DBFactory::getDBQuery();

	// check kb3_kills table and if necessary add extra field for API kll_external_id
	$isKB3KillsUpdated = false;
	$qry->execute("SHOW COLUMNS FROM kb3_kills");

	while ($row = $qry->getRow())
	{
		if ($row['Field'] == "kll_external_id")
			$isKB3KillsUpdated = true;
	}
	if (!$isKB3KillsUpdated)
	{
		// add new column
		$qry->execute("ALTER TABLE `kb3_kills`
						ADD `kll_external_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
						ADD UNIQUE ( kll_external_id )");
	}

	// check kb3_alliances table and if necessary add extra field for API all_external_id
	$isKB3AllianceUpdated = false;
	$qry->execute("SHOW COLUMNS FROM kb3_alliances");

	while ($row = $qry->getRow())
	{
		if ($row['Field'] == "all_external_id")
			$isKB3AllianceUpdated = true;
	}
	if (!$isKB3AllianceUpdated)
	{
		// add new column
		$qry->execute("ALTER TABLE `kb3_alliances`
						ADD `all_external_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
						ADD UNIQUE ( all_external_id )");
	}

	// check kb3_corps table and if necessary add extra field for API crp_external_id
	$isKB3CorpsUpdated = false;
	$qry->execute("SHOW COLUMNS FROM kb3_corps");

	while ($row = $qry->getRow())
	{
		if ($row['Field'] == "crp_external_id")
			$isKB3CorpsUpdated = true;
	}
	if (!$isKB3CorpsUpdated)
	{
		// add new column
		$qry->execute("ALTER TABLE `kb3_corps`
						ADD `crp_external_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
						ADD UNIQUE ( crp_external_id )");
	}

	// create kb3_apilog table
	$qry->execute("CREATE TABLE IF NOT EXISTS `kb3_apilog` (
			`log_site` VARCHAR( 20 ) NOT NULL ,
			`log_keyname` VARCHAR( 20 ) NOT NULL ,
			`log_posted` INT NOT NULL ,
			`log_errors` INT NOT NULL ,
			`log_ignored` INT NOT NULL ,
			`log_verified` INT NOT NULL ,
			`log_totalmails` INT NOT NULL ,
			`log_source` VARCHAR( 20 ) NOT NULL ,
			`log_type` VARCHAR( 20 ) NOT NULL ,
			`log_timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
			) ENGINE = MYISAM ");

	// set update complete
	config::set('API_DBUpdate', '1');
}
?>