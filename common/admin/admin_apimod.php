<?php
@error_reporting(E_ALL ^ E_NOTICE);
//
// Eve-Dev API Killmail parser by Captain Thunk! (ISK donations are all gratefully received)
//

require_once('common/includes/class.eveapi.php');
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

// find select char name loop
for ( $i = 1; $i <= $keycount; $i++ )
{
    if (isset($_POST['select'.$i]))
    {
        $html .= "Click the character you'd like to set:<br><br>";
        $selectid = $i;

        $apistring = 'userID=' . config::get('API_UserID_' . $selectid) . '&apiKey=' . config::get('API_Key_' . $selectid);
        $myCharSelect = new APIChar();
        $CharList = $myCharSelect->fetchChars($apistring);
        $charcount = count($CharList);
        if ( $charcount > 0 ) 
        {
            $isupdated = true;
            for ( $x = 0; $x < $charcount; $x++ )
            {
                $html .= '<a href="?a=admin_apimod&CharID=' . $CharList[$x]['charID'] . '&SetNum=' . $selectid . '">'. $CharList[$x]['Name'] . '</a><br>';
            }
        } else {
            $html .= "No characters found, check your details are correct and the Eve API is online";
        } 
    }
	
	// Set cachetimes in variable array (solves unable to read last cachetime problem when importing
	$apicachetime[$i] = ApiCache::get("API_CachedUntil_" . $i);
}

if ($_POST['clearapicache'])
{
	$deld = 0;
	$dsize = 0;
	$cachepath = getcwd().'/cache/api/*';
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
	// drop table
	$db = new DBQuery(true);
	$db->execute("TRUNCATE TABLE `kb3_apicache`");
	$html .= "Cache cleared.<br>";
	$html .= "<script type=\"text/javascript\">window.location = \"?a=admin_apimod\"</script>"; //*/
}

if ($_POST['submit'] || $_POST['import']  )
{
    if (ctype_digit($_POST['API_Key_count']) && $_POST['API_Key_count'] > 0)
    {
        $keycount = $_POST['API_Key_count'];
        config::set('API_Key_count', $keycount);
        for ( $i = 99; $i > $keycount; $i-- )
        {
            config::del('API_Name_' . $i);
            config::del('API_Key_' . $i);
            config::del('API_UserID_' . $i);
            config::del('API_CharID_' . $i);
            config::del('API_Type_' . $i);
            ApiCache::del('API_CachedUntil_' . $i);
        }
    }
    for ($i = 1; $i <= $keycount; $i++)
    {
        config::set("API_Name_" . $i . "", $_POST["API_Name_" . $i]);
        config::set("API_Key_" . $i . "", $_POST["API_Key_" . $i]);
        config::set("API_UserID_" . $i . "", $_POST["API_UserID_" . $i]);
        config::set("API_CharID_" . $i . "", $_POST["API_CharID_" . $i]);
        if ($_POST["API_Type_". $i] == "char")
            config::set("API_Type_" . $i, "char");
        else
            config::set("API_Type_" . $i, "corp");
    }	

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

	if ($_POST['API_CCPErrorCorrecting'])
        config::set('API_CCPErrorCorrecting', '0');
    else
        config::set('API_CCPErrorCorrecting', '1');
	
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
	
	if ($_POST['API_extendedtimer_facwarsystems'])
        config::set('API_extendedtimer_facwarsystems', '0');
    else
        config::set('API_extendedtimer_facwarsystems', '1');
		
	//if ($_POST['API_ForceDST'])
        //config::set('API_ForceDST', '0');
    //else
        //config::set('API_ForceDST', '1');
		
	if ($_POST['API_ConvertTimestamp'])
        config::set('API_ConvertTimestamp', '0');
    else
        config::set('API_ConvertTimestamp', '1');
		
    $html .= "Settings Saved.<br>";
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
	
    if ($keycount > 0 )
	{
		if (config::get("API_MultipleMode") == 0 )
		{ // save output to file and load when complete
            $i = $processindex;
            $myEveAPI->Output_ .= "Importing Mails for " . config::get("API_Name_" . $i) . "<br>";
            $keystring = 'userID=' . config::get('API_UserID_' . $i) . '&apiKey=' . config::get('API_Key_' . $i) . '&characterID=' . config::get('API_CharID_' . $i);
            $typestring = config::get("API_Type_" . $i);
            $outputdata .= $myEveAPI->Import($keystring, $typestring, $i);
			$apicachetime[$i] = $myEveAPI->CachedUntil_;
			
			$file = @fopen(getcwd().'/cache/data/report.txt', 'a');
        	fwrite($file, $outputdata);
       		fclose($file);
			
			//ApiCache::set('API_CachedUntil_' . $keyindex, $myEveAPI->cachetext_);
			$processindex++;
			if ($processindex <= $keycount)
			{
			    //$html .= "<a href=\"http:?a=admin_apimod&Process=" . $processindex . "\">Click to process next Key</a>";
				$html .= "<script type=\"text/javascript\">window.location = \"?a=admin_apimod&Process=" .$processindex . "\"</script>"; //*/
			} else { // load report.txt to $html
				$fp = @fopen(getcwd().'/cache/data/report.txt', 'r');
        		$html .= fread($fp, filesize(getcwd().'/cache/data/report.txt'));
        		fclose($fp);
				@unlink(getcwd().'/cache/data/report.txt'); // delete file, it was temporary
				
			}
		} else {
			for ( $i = 1; $i <= $keycount; $i++ )
			{
			    $myEveAPI->Output_ .= "Importing Mails for " . config::get("API_Name_" . $i) . "<br>";
                $keystring = 'userID=' . config::get('API_UserID_' . $i) . '&apiKey=' . config::get('API_Key_' . $i) . '&characterID=' . config::get('API_CharID_' . $i);
                $typestring = config::get("API_Type_" . $i);
				//$myEveAPI->cachetext_ = "";
				//$myEveAPI->cacheflag_ = false;
                $html .= $myEveAPI->Import($keystring, $typestring, $i);
				$apicachetime[$i] = $myEveAPI->CachedUntil_;
		    }
        }
    }
}

// calculate cache size
$deld = 0;
$dsize = 0;
$cachepath = getcwd().'/cache/api/*';
$match = "*";

$files = glob($cachepath.$match);
foreach((array)$files as $file){
	if(is_file($file)){
		$dsize += filesize($file);
		$deld++;
	}
}

if ($_POST['apilog'])
{
	$html .= "<div class=block-header2>API Log</div>";
	$html .= "<form id=options name=options method=post action=?a=admin_apimod>";

	$sql = 'SELECT * 
			FROM kb3_apilog
			WHERE log_site = "' .KB_SITE . '" 
			ORDER BY log_timestamp DESC limit 250';

	$qry = new DBQuery();
	$qry->execute($sql) or die($qry->getErrorMsg());

	$html .= '<table class="kb-table">';
	$html .= "<tr class=kb-table-header><td align=center width=150>Key Name</td><td width=60>Posted</td><td width=60>Malformed</td><td width=60>Ignored</td><td width=60>Verified</td><td width=80>Total Mails</td><td width=60>Source</td><td width=60>Type</td><td width=150>Time Stamp</td></tr>";
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
			
    	$html .= "<tr class=" . $class . ">";
    	$html .= "<td align=center><b>" . $row['log_keyname'] . "</b></td>";
    	$html .= "<td>" . $numposted . "</td>";
    	$html .= "<td>" . $numerrors . "</td>";
    	$html .= "<td>" . $numignored . "</td>";
    	$html .= "<td>" . $numverified . "</td>";
		$html .= "<td>" . $row['log_totalmails'] . "</td>";
		$html .= "<td>" . $datasource . "</td>";
		$html .= "<td>" . $row['log_type'] . "</td>";
		$html .= "<td>" . $row['log_timestamp'] . "</td>";
    	$html .= "</tr>";
	}
	$html .= "</table>";

	$html .= "<br>";
	$html .= "<table><tr><td width=60><input type=\"submit\" name=\"back\" value=\"Back\" width=60></td></tr></table>";
	$html .= "</form>";
	
} else {
	// API Settings 
	$html .= "<div class=block-header2>API Key Details (must be CEO/Director to retrieve corp mails)</div>";
	$html .= "<form id=options name=options method=post action=?a=admin_apimod>";
	
	// show current server time
	$html .= "Servers current time: <font color = \"#00FF00\">" . date("M d Y H:i") . "</font><br><br>";

	// Key Details
	for ( $i = 1; $i <= $keycount; $i++ )
	{
   	 	$characteridentitifier = config::get("API_CharID_" . $i);
    	$html .= "<table class=kb-subtable>";
    	$html .= "<tr><td>Key Name #" . $i .":</td><td><input type=\"text\" name=\"API_Name_" . $i . "\" value=\"".config::get("API_Name_" . $i) . "\"> (This is just to remind yourself which key is being used)</td></tr>";
    	$html .= "<tr><td>Full API Key #" . $i . ":</td><td><input type=\"password\" size=26 name=\"API_Key_" . $i . "\" value=\"".config::get("API_Key_" . $i) . "\"></td></tr>";
    	$html .= "<tr><td>API CharID #" . $i . ":</td><td><input type=\"text\"  name=\"API_CharID_" . $i . "\" value=\"" . $characteridentitifier . "\">";
    	if ($characteridentitifier != "") {
        	$html .= getPlayerDetails($characteridentitifier);
    	}
    	if (config::get("API_Key_" . $i) != "" && config::get("API_UserID_" . $i) != "") {
        	$html .= "</td></td><td colspan=\"2\"><input type=submit id=\"w00t\"" . " name=select" . $i . " value=\"Select Character\">";
    	}
    	$html .= "<td></tr>";
    	$html .= "<tr><td>API UserID #" . $i . ":</td><td><input type=\"text\" name=\"API_UserID_" . $i . "\" value=\"".config::get("API_UserID_" . $i) . "\"></td></tr>";
    	$html .= "<tr><td>Key Type #" . $i .":</td>";
    	$html .= "<td>Corp <input type=\"radio\" name=\"API_Type_" . $i . "\" value=\"corp\" ";
    	if (config::get("API_Type_" . $i) != "char") {
        	$html .= "checked";
    	}
    	$html .= ">";
    	$html .= "       Player <input type=\"radio\" name=\"API_Type_" . $i . "\" value=\"char\" ";
    	if (config::get("API_Type_" . $i) == "char") {
        	$html .= "checked";
    	}
   	 	$html .= "></td></tr>";
		$cachetime = ConvertTimestamp($apicachetime[$i]);
    	//$cachetime = date("Y-m-d H:i:s",  strtotime($apicachetime[$i]) + $gmoffset);
    	if ($cachetime == "")
    	{
        	$cachetime = "unknown";
        	$txtcolour = "<font color = \"#FF0000\">";
    	} else {
        	if (strtotime(gmdate("M d Y H:i:s")) - strtotime($apicachetime[$i]) > 0)
        	{
           	 	$txtcolour = "<font color = \"#00FF00\">";
            	//$txtcolour = "<style=\"color:green\">";
        	} else {
            	$txtcolour = "<font color = \"#FF0000\">";
        	}
    	}
    	$html .= "<tr><td>Data is cached until:</td><td>" . $txtcolour . $cachetime . "</font></td></tr>";
    	$html .= "</table><br>";
	}
	$html .= "<table class=api-keys>";
	$html .= "<tr><td height=30px width=150px>Number of API Keys:</td>";
	$html .= "<td><input type=text name=API_Key_count size=2 maxlength=2 class=password value=\"" . $keycount . "\"></td></tr>";
	$html .= "</table><br>";
	$html .= "<i> Your UserID and API FULL Key can be obtained <a href=\"http://myeve.eve-online.com/api/default.asp\">here</a></i><br>";
	$html .= "<i> Once your UserID and API Key have been entered and the settings saved you can then select the available character IDs if you don't know them by clicking the \"select character\" button that appears and then clicking the character name</i><br><br>";

	// API Caching Options
	$html .= "<div class=block-header2>API XML Caching Options</div><table>";

	$html .= "<tr><td height=30px width=150px>Enable API XML Caching?</td>";
	$html .= "<td><input type=checkbox name=API_UseCache id=API_UseCache";
	if (!config::get('API_UseCache'))
    	$html .= " checked=\"checked\"";
	$html .= "></tr>";

	$html .= "<tr><td height=30px width=150px>Convert Cache Times to local time?</i></td>";
	$html .= "<td><input type=checkbox name=API_ConvertTimestamp id=API_ConvertTimestamp";
	if (!config::get('API_ConvertTimestamp'))
    	$html .= " checked=\"checked\"";
	$html .= "></tr>";

	//$html .= "<tr><td height=30px width=150px>Force Daylight Saving Time on displayed cache times?</i></td>";
	//$html .= "<td><input type=checkbox name=API_ForceDST id=API_ForceDST";
	//if (!config::get('API_ForceDST'))
    	//$html .= " checked=\"checked\"";
	//$html .= "></tr>";

	$html .= "<tr><td height=30px width=150px>Use Extended 24hr cache timer for Sovereignty.xml?</td>";
	$html .= "<td><input type=checkbox name=API_extendedtimer_sovereignty id=API_extendedtimer_sovereignty";
	if (!config::get('API_extendedtimer_sovereignty'))
    	$html .= " checked=\"checked\"";
	$html .= ">";
	//$tempcachetime = date("Y-m-d H:i:s",  strtotime(ApiCache::get('API_map_Sovereignty')) + $gmoffset);
	$tempcachetime = ConvertTimestamp(ApiCache::get('API_map_Sovereignty'));
	if ($tempcachetime == "")
	{
		$html .= "</tr>";
	} else {
		if (strtotime(gmdate("M d Y H:i:s")) - strtotime(ApiCache::get('API_map_Sovereignty')) > 0)
		{
		$txtcolour = "<font color = \"#00FF00\">";
		//$txtcolour = "<style=\"color:green\">";
    	} else {
    		$txtcolour = "<font color = \"#FF0000\">";
    	}
		$html .= "<td>Data is cached until:</td><td>" . $txtcolour . $tempcachetime . "</font></td></tr>";
	}

	$html .= "<tr><td height=30px width=150px>Use Extended 24hr cache timer for AllianceList.xml?</td>";
	$html .= "<td><input type=checkbox name=API_extendedtimer_alliancelist id=API_extendedtimer_alliancelist";
	if (!config::get('API_extendedtimer_alliancelist'))
    	$html .= " checked=\"checked\"";
	$html .= ">";
	//$tempcachetime =  date("Y-m-d H:i:s",  strtotime(ApiCache::get('API_eve_AllianceList')) + $gmoffset);
	$tempcachetime = ConvertTimestamp(ApiCache::get('API_eve_AllianceList'));
	if ($tempcachetime == "")
	{
		$html .= "</tr>";
	} else {
		if (strtotime(gmdate("M d Y H:i:s")) - strtotime(ApiCache::get('API_eve_AllianceList')) > 0)
		{
		$txtcolour = "<font color = \"#00FF00\">";
		//$txtcolour = "<style=\"color:green\">";
    	} else {
    		$txtcolour = "<font color = \"#FF0000\">";
    	}
		$html .= "<td>Data is cached until:</td><td>" . $txtcolour . $tempcachetime . "</font></td></tr>";
	}

	$html .= "<tr><td height=30px width=150px>Use Extended 24hr cache timer for ConquerableStationList.xml?</td>";
	$html .= "<td><input type=checkbox name=API_extendedtimer_conq id=API_extendedtimer_conq";
	if (!config::get('API_extendedtimer_conq'))
    	$html .= " checked=\"checked\"";
	$html .= ">";
	//$tempcachetime =  date("Y-m-d H:i:s",  strtotime(ApiCache::get('API_eve_ConquerableStationList')) + $gmoffset);
	$tempcachetime = ConvertTimestamp(ApiCache::get('API_eve_ConquerableStationList'));
	if ($tempcachetime == "")
	{
		$html .= "</tr>";
	} else {
		if (strtotime(gmdate("M d Y H:i:s")) - strtotime(ApiCache::get('API_eve_ConquerableStationList')) > 0)
		{
		$txtcolour = "<font color = \"#00FF00\">";
		//$txtcolour = "<style=\"color:green\">";
    	} else {
    		$txtcolour = "<font color = \"#FF0000\">";
    	}
		$html .= "<td>Data is cached until:</td><td>" . $txtcolour . $tempcachetime . "</font></td></tr>";
	}

	$html .= "<tr><td height=30px width=150px>Use Extended 24hr cache timer for FacWarSystems.xml?</td>";
	$html .= "<td><input type=checkbox name=API_extendedtimer_facwarsystems id=API_extendedtimer_facwarsystems";
	if (!config::get('API_extendedtimer_facwarsystems'))
    	$html .= " checked=\"checked\"";
	$html .= ">";
	//$tempcachetime =  date("Y-m-d H:i:s",  strtotime(ApiCache::get('API_map_FacWarSystems')) + $gmoffset);
	$tempcachetime = ConvertTimestamp(ApiCache::get('API_map_FacWarSystems'));
	if ($tempcachetime == "")
	{
		$html .= "</tr>";
	} else {
		if (strtotime(gmdate("M d Y H:i:s")) - strtotime(ApiCache::get('API_map_FacWarSystems')) > 0)
		{
			$txtcolour = "<font color = \"#00FF00\">";
			//$txtcolour = "<style=\"color:green\">";
    	} else {
    		$txtcolour = "<font color = \"#FF0000\">";
    	}
		$html .= "<td>Data is cached until:</td><td>" . $txtcolour . $tempcachetime . "</font></td></tr>";
	}

	$html .= "<tr><td height=\"10\"></td></tr>"; // spacer
	$html .= "<tr><td>(" . $deld . " files with a total size of " . number_format($dsize,"0",".",",") . " bytes)</td></tr>";

	$html .= "<tr><td height=\"10\"></td></tr>"; // spacer
	$html .= "<tr><td colspan=\"2\"><input type=submit id=submit name=clearapicache value=\"Clear Cache\"><td></tr>";

	$html .= "</table>";

	// Killmail Parser Options
	$html .= "<div class=block-header2>Killmail API Parsing Options</div><table>";
	$html .= "<tr><td height=50px width=150px>Comment for automatically parsed killmails?</td>";
	$html .= "<td><input type=text size=50 class=password name=API_Comment id=API_Comment value=\"";
	if (config::get('API_Comment'))
    	$html .= config::get('API_Comment');
	$html .= "\"><br><i> (leave blank for none)</i><br></td></tr>";

	$html .= "<tr><td height=30px width=150px>Update Portraits?</td>";
	$html .= "<td><input type=checkbox name=API_Update id=API_Update";
	if (!config::get('API_Update'))
    	$html .= " checked=\"checked\"";
	$html .= "></td></tr>";

	$html .= "<tr><td height=30px width=150px>Ignore NPC only deaths? <i>(This includes kills by POSs)</i></td>";
	$html .= "<td><input type=checkbox name=API_IgnoreNPC id=API_IgnoreNPC";
	if (!config::get('API_IgnoreNPC'))
    	$html .= " checked=\"checked\"";
	$html .= "></td></tr>";

	$html .= "<tr><td height=30px width=150px>Ignore Friendly Fire? </td>";
	$html .= "<td><input type=checkbox name=API_IgnoreCorpFF id=API_IgnoreCorpFF";
	if (!config::get('API_IgnoreCorpFF'))
    	$html .= " checked=\"checked\"";
	$html .= "<td> Corps <input type=checkbox name=API_IgnoreAllianceFF id=API_IgnoreAllianceFF";
	if (!config::get('API_IgnoreAllianceFF'))
    	$html .= " checked=\"checked\"";	
	$html .= "> Alliance</td></tr>";

	$html .= "<tr><td height=30px width=150px>Ignore POS Structures? </td>";
	$html .= "<td><input type=checkbox name=API_IgnoreFriendPos id=API_IgnoreFriendPos";
	if (!config::get('API_IgnoreFriendPos'))
    	$html .= " checked=\"checked\"";
	$html .= "<td> Friend <input type=checkbox name=API_IgnoreEnemyPos id=API_IgnoreEnemyPos";
	if (!config::get('API_IgnoreEnemyPos'))
    	$html .= " checked=\"checked\"";	
	$html .= "> Enemy</td></tr>";

	$html .= "<tr><td height=30px width=150px>Concise cronjob e-mail? </td>";
	$html .= "<td><input type=checkbox name=API_NoSpam id=API_NoSpam";
	if (!config::get('API_NoSpam'))
   	 	$html .= " checked=\"checked\"";
	$html .= "></td></tr>";

	$html .= "<tr><td height=30px width=150px>Import multiple keys one at a time? </td>";
	$html .= "<td><input type=checkbox name=API_MultipleMode id=API_MultipleMode";
	if (!config::get('API_MultipleMode'))
    	$html .= " checked=\"checked\"";
	$html .= "></td></tr>";

	$html .= "<tr><td height=30px width=150px>Enable CCP error correction? </td>";
	$html .= "<td><input type=checkbox name=API_CCPErrorCorrecting id=API_CCPErrorCorrecting";
	if (!config::get('API_CCPErrorCorrecting'))
    	$html .= " checked=\"checked\"";
	$html .= "></td></tr>";
	// Import
	$html .= "<tr><td height=\"10\"></td></tr>"; // spacer
	$html .= "<tr><td colspan=\"2\"><input type=submit id=submit name=import value=\"Import Mails\"></td></tr>";
	$html .= "</table>";
	// Save
	$html .= "<div class=block-header2></div>";
	$html .= "<table><tr><td colspan=\"2\"><input type=\"submit\" name=\"submit\" value=\"Save Settings\"></td><td>&nbsp;</td><td colspan=\"2\"><input type=\"submit\" name=\"apilog\" value=\"View Log\"></td></tr>";
	$html .= "</table>";
	$html .= "</form>";
}
$html .= "<div class=block-header2></div>";
$html .= "<div>Written by " . FindThunk() . " (<a href=\"http://eve-id.net/forum/viewtopic.php?f=505&t=8827\" >Support</a>)</div>";
	
$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();

function getPlayerDetails( $characteridentitifier )
{
    $sql = 'select plts.plt_id, plts.plt_name from kb3_pilots plts where plts.plt_externalid = "' . $characteridentitifier . '"';

    $qry = new DBQuery();
    $qry->execute($sql);
    $row = $qry->getRow();

    $pilot_id = $row['plt_id'];
    $pilot_name = $row['plt_name'];

    if ($pilot_name != "")	{
        return ' (<a href="?a=pilot_detail&plt_id=' . $pilot_id . '">'. $pilot_name . '</a>)';
    } else {
        return "";
    }
}

function checkDBforAPI()
{
	$qry = new DBQuery();
	
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