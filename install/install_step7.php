<?php
/**
 * @package EDK
 */
require_once('../common/includes/constants.php');
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;
global $smarty;

extract($_SESSION['sql']);
$dbhost = $host;
extract($_SESSION['sett']);
$adminpw = crypt($adminpw);

// prepare replacements for writing configuration
$replacements = array(
	'$site' => $site,
	'$adminpw' => $adminpw,
	'$dbhost' => $dbhost,
	'$db' => $db,
	'$user' => $user,
	'$pass' => $pass
);

$smarty->assign('conf_exists', file_exists('../kbconfig.php'));
if (file_exists('../kbconfig.php'))
{
	//make sure we can write the new crypto'd password to the config file
	chmod('../kbconfig.php', 0777);
	
	$config = preg_replace_callback("/\{([^\}]+)\}/", "matchesConfigKeyword", implode('', file('./templates/config.tpl')));
	
	
	$fp = fopen('../kbconfig.php', 'w');
	fwrite($fp, trim($config));
	fclose($fp);
	//make file read-only
	chmod('../kbconfig.php', 0440);
	$smarty->assign('hi_config', highlight_string($config, true));

	require_once('../kbconfig.php');

	$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	// move stuff from the config to the database
	if($aid) insertConfig($db, 'cfg_allianceid', serialize(array($aid)));
	else insertConfig($db, 'cfg_allianceid', serialize(array()));
	if($cid) insertConfig($db, 'cfg_corpid', serialize(array($cid)));
	else insertConfig($db, 'cfg_corpid', serialize(array()));
	if($pid) insertConfig($db, 'cfg_pilotid', serialize(array($pid)));
	else insertConfig($db, 'cfg_pilotid', serialize(array()));

	insertConfig($db, 'cfg_img', $img);
	insertConfig($db, 'cfg_kbhost', $host);
	insertConfig($db, 'cfg_kbtitle', $title);

	insertConfig($db, 'cfg_mainsite', '');
	// write current CCP DB Version to config
	insertConfig($db, 'CCPDbVersion', KB_CCP_DB_VERSION);
        
        // write current DBUpdate to config
        insertConfig($db, 'DBUpdate', LATEST_DB_UPDATE);

	$confs = file('config.data');
	foreach ($confs as $line)
	{
		$valuepair = explode(chr(9), trim($line));
		if(!isset($valuepair[0])) continue;
		if(!isset($valuepair[1])) $valuepair[1] = '';
		insertConfig($db, $valuepair[0], $valuepair[1]);
	}
	$stoppage = false;
}
// config is there, use it to create all config vars which arent there
// to prevent that ppl with running installs get new values

$smarty->assign('stoppage', $stoppage);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step7.tpl');

function insertConfig($db, $key, $value)
{
	$localvars = array();
	$localvars[] = 'cfg_kbhost';
	$localvars[] = 'cfg_img';
	$localvars[] = 'cfg_kbtitle';
	$result = $db->query('SELECT * FROM kb3_config WHERE cfg_site=\''.KB_SITE.'\' AND cfg_key=\''.$key.'\'');
	if (!$row = $result->fetch_assoc())
	{
		$sql = "INSERT INTO kb3_config VALUES ('".KB_SITE."','".$key."','".$value."')";
		$db->query($sql);
	}
	if (!in_array($key, $localvars)) {
		$result = $db->query('SELECT * FROM kb3_config WHERE cfg_site=\'\' AND cfg_key=\''.$key.'\'');
		if (!$row = $result->fetch_assoc())
		{
			$sql = "INSERT INTO kb3_config VALUES ('','".$key."','".$value."')";
			$db->query($sql);
		}
	}
}

function matchesConfigKeyword($input)
{
	global $replacements;
	// return the first back-reference
	return $replacements[$input[1]];
}