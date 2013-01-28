<?php
/**
 * @package EDK
 */

if(!$installrunning)
{
	header('Location: index.php');
	die();
}
$stoppage = true;
global $smarty;

$db = mysql_connect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
mysql_select_db($_SESSION['sql']['db']);

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'create')
{
	if (!empty($_REQUEST['a']))
	{
		$result = mysql_query('SELECT all_id FROM kb3_alliances WHERE all_name LIKE \'%'.addslashes(stripslashes($_REQUEST['a'])).'%\'');
		if ($row = @mysql_fetch_row($result))
		{
			$id = $row[0];
		}
		else
		{
			$query = 'INSERT INTO kb3_alliances (all_name) VALUES (\''.addslashes(stripslashes($_REQUEST['a'])).'\')';
			mysql_query($query);
			$id = mysql_insert_id();
		}
		$_REQUEST['a'] = $id;
	}
	else if(!empty($_REQUEST['c']))
	{
		$result = mysql_query('SELECT all_id FROM kb3_alliances WHERE all_name like \'%Unknown%\'');
		if ($row = @mysql_fetch_row($result))
		{
			$id = $row[0];
		}
		else
		{
			$query = 'INSERT INTO kb3_alliances (all_name) VALUES (\'Unknown\')';
			mysql_query($query);
			$id = mysql_insert_id();
		}
		$query = 'SELECT crp_id FROM kb3_corps WHERE crp_name LIKE \'%'.addslashes(stripslashes($_REQUEST['c'])).'%\'';
		$result = mysql_query($query);

		if ($row = @mysql_fetch_row($result))
		{
			$id = $row[0];
		}
		else
		{
			$query = 'INSERT INTO kb3_corps (crp_name, crp_all_id) VALUES (\''.addslashes(stripslashes($_REQUEST['c'])).'\','.$id.')';
			mysql_query($query);
			$id = mysql_insert_id();
		}
		$_REQUEST['c'] = $id;
	}
	else
	{
		$result = mysql_query('SELECT all_id FROM kb3_alliances WHERE all_name like \'%Unknown%\'');
		if ($row = @mysql_fetch_row($result))
		{
			$id = $row[0];
		}
		else
		{
			$query = 'INSERT INTO kb3_alliances (all_name) VALUES (\'Unknown\')';
			mysql_query($query);
			$id = mysql_insert_id();
		}
		$result = mysql_query('SELECT crp_id FROM kb3_corps WHERE crp_name like \'%Unknown%\'');
		if ($row = @mysql_fetch_row($result))
		{
			$id = $row[0];
		}
		else
		{
			$query = 'INSERT INTO kb3_corps (crp_name, crp_all_id) VALUES (\'Unknown\', '.$id.')';
			mysql_query($query);
			$id = mysql_insert_id();
		}
		$query = 'SELECT plt_id FROM kb3_pilots WHERE plt_name LIKE \'%'.addslashes(stripslashes($_REQUEST['p'])).'%\'';
		$result = mysql_query($query);

		if ($row = @mysql_fetch_row($result))
		{
			$id = $row[0];
		}
		else
		{
			$query = 'INSERT INTO kb3_pilots (plt_name, plt_crp_id) VALUES (\''.addslashes(stripslashes($_REQUEST['p'])).'\','.$id.')';
			mysql_query($query);
			$id = mysql_insert_id();
		}
		$_REQUEST['p'] = $id;
	}
	$_SESSION['sett']['aid'] = $_REQUEST['a'];
	$_SESSION['sett']['cid'] = $_REQUEST['c'];
	$_SESSION['sett']['pid'] = $_REQUEST['p'];
	$stoppage = false;
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'select')
{
	$_SESSION['sett']['aid'] = $_REQUEST['a'];
	$_SESSION['sett']['cid'] = $_REQUEST['c'];
	$_SESSION['sett']['pid'] = $_REQUEST['p'];
	$stoppage = false;
}
if ($stoppage)
{
	if (!empty($_REQUEST['searchphrase']) && strlen($_REQUEST['searchphrase']) >= 3)
	{
		$name = stripslashes($_REQUEST['searchphrase']);
		$api = new Api();
		$idArr = $api->getCharId($name);

		// If a name was found check if we have a corporation instead of a character.
		if(!empty($idArr['characterID']))
		{
			$api = new Api();
			$cidArr = $api->getCorpInfo($idArr['characterID']);
		}

		if ($_REQUEST['searchtype'] == 'corp')
		{
			// If a name was found check that it belongs to a corp.
			if(empty($cidArr['corporationID']))
			{
				$link = '?step=5&amp;do=create&amp;c='.stripslashes($_REQUEST['searchphrase']).'&amp;a=0">Create';
				$descr = 'Corporation not found. Check spelling.';
			}
			else
			{
				$link = '?step=5&amp;do=create&amp;c='.htmlentities($idArr['characterName']).'&amp;a=0">Create';
				$descr = 'Corporation: '.htmlentities($idArr['characterName']);
			}
		}
		else if($_REQUEST['searchtype'] == 'alliance')
		{
			// Could check against the alliance xml but the download is big enough to risk timeouts. 
			// For now just check if name exists and is not a corporation.
			if(empty($idArr['characterID']) || !empty($cidArr['corporationID']))
			{
				$link = '?step=5&amp;do=create&amp;a='.stripslashes($_REQUEST['searchphrase']).'&amp;c=0&amp;p=0">Create';
				$descr = 'Alliance not found. Check spelling.';
			}
			else
			{
				$link = '?step=5&amp;do=create&amp;a='.htmlentities($idArr['characterName']).'&amp;c=0&amp;p=0">Create';
				$descr = 'Alliance: '.htmlentities($idArr['characterName']);
			}
		}
		else
		{
			if(empty($idArr['characterID']) || !empty($cidArr['corporationID']))
			{
				$link = '?step=5&amp;do=create&amp;p='.stripslashes($_REQUEST['searchphrase']).'&amp;c=0&amp;a=0">Create';
				$descr = 'Pilot not found. Check spelling.';
			}
			else
			{
				$link = '?step=5&amp;do=create&amp;p='.htmlentities($idArr['characterName']).'&amp;c=0&amp;a=0">Create';
				$descr = 'Pilot: '.htmlentities($idArr['characterName']);
			}
		}
		$results[] = array('descr' => $descr, 'link' => $link);
		$smarty->assign('res_check', count($results) > 0);
		$smarty->assign('results', $results);
	}
}
$smarty->assign('stoppage', $stoppage);
$smarty->assign('conflict', empty($_SESSION['sett']['aid']) && empty($_SESSION['sett']['cid']) && empty($_SESSION['sett']['pid']));
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step5.tpl');

class Api
{
    function Api()
    {
		require_once "../common/pheal/Pheal.php";
		spl_autoload_register("Pheal::classload");
		PhealConfig::getInstance()->http_method = 'curl';
		PhealConfig::getInstance()->http_post = false;
		PhealConfig::getInstance()->http_keepalive = true;
		PhealConfig::getInstance()->http_keepalive = 10; 
		PhealConfig::getInstance()->http_timeout = 60;
		PhealConfig::getInstance()->http_ssl_verifypeer = false;
    }

    function getCharId($name)
    {
		$pheal = new Pheal();
		$pheal->scope = "eve";

		try {
			$result = $pheal->CharacterID(array("names" => $name));
		} catch (PhealAPIException $e) {
			return array();
		}
		return array('characterID' => $result->characters[0]->characterID, 'characterName' => $result->characters[0]->name, 'corporationID' => $result->characters[0]->corporationID);
    }
    function getCorpInfo($id)
    {
		$pheal = new Pheal();
		$pheal->scope = "corp";
	
		try {
			$result = $pheal->CorporationSheet(array("corporationID" => $id));
		} catch (PhealAPIException $e) {
			return array();
		}
		return array('characterID' => -1, 'characterName' => $result->corporationName, 'corporationID' => $result->corporationID);
    }
}
