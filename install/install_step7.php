<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;
global $smarty;

extract($_SESSION['sql']);
$dbhost = $host;
extract($_SESSION['sett']);
$adminpw = crypt($adminpw);

$smarty->assign('conf_exists', file_exists('../kbconfig.php'));
if (file_exists('../kbconfig.php'))
{
	//make sure we can write the new crypto'd password to the config file
	chmod('../kbconfig.php', 0777);
	$config = preg_replace("/\{([^\}]+)\}/e", "\\1", join('', file('./templates/config.tpl')));
	$fp = fopen('../kbconfig.php', 'w');
	fwrite($fp, trim($config));
	fclose($fp);
	//make file read-only
	chmod('../kbconfig.php', 0440);
	$smarty->assign('hi_config', highlight_string($config, true));

	require_once('../kbconfig.php');

	$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);

	// move stuff from the config to the database
	if($aid) insertConfig('cfg_allianceid', serialize(array($aid)));
	else insertConfig('cfg_allianceid', serialize(array()));
	if($cid) insertConfig('cfg_corpid', serialize(array($cid)));
	else insertConfig('cfg_corpid', serialize(array()));
	if($pid) insertConfig('cfg_pilotid', serialize(array($pid)));
	else insertConfig('cfg_pilotid', serialize(array()));

	insertConfig('cfg_img', $img);
	insertConfig('cfg_kbhost', $host);
	insertConfig('cfg_kbtitle', $title);

	insertConfig('cfg_mainsite', '');

	$confs = file('config.data');
	foreach ($confs as $line)
	{
		$valuepair = explode(chr(9), trim($line));
		if(!isset($valuepair[0])) continue;
		if(!isset($valuepair[1])) $valuepair[1] = '';
		insertConfig($valuepair[0], $valuepair[1]);
	}
	$stoppage = false;
}
// config is there, use it to create all config vars which arent there
// to prevent that ppl with running installs get new values

$smarty->assign('stoppage', $stoppage);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step7.tpl');

function insertConfig($key, $value)
{
	$localvars = array();
	$localvars[] = 'cfg_kbhost';
	$localvars[] = 'cfg_img';
	$localvars[] = 'cfg_kbtitle';
	$result = mysql_query('SELECT * FROM kb3_config WHERE cfg_site=\''.KB_SITE.'\' AND cfg_key=\''.$key.'\'');
	if (!$row = mysql_fetch_row($result))
	{
		$sql = "INSERT INTO kb3_config VALUES ('".KB_SITE."','".$key."','".$value."')";
		mysql_query($sql);
	}
	if (!in_array($key, $localvars)) {
		$result = mysql_query('SELECT * FROM kb3_config WHERE cfg_site=\'\' AND cfg_key=\''.$key.'\'');
		if (!$row = mysql_fetch_row($result))
		{
			$sql = "INSERT INTO kb3_config VALUES ('','".$key."','".$value."')";
			mysql_query($sql);
		}
	}
}