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
		switch ($_REQUEST['searchtype'])
		{
			case "pilot":
				$query = "SELECT plt.plt_id, plt.plt_name, crp.crp_name
			FROM kb3_pilots plt, kb3_corps crp
			WHERE plt.plt_name LIKE '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%'
			AND plt.plt_crp_id = crp.crp_id
			ORDER BY plt.plt_name";
				break;
			case "corp":
				$query = "SELECT crp.crp_id, crp.crp_name, ali.all_name
			FROM kb3_corps crp, kb3_alliances ali
			WHERE crp.crp_name LIKE '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%'
			AND crp.crp_all_id = ali.all_id
			ORDER BY crp.crp_name";
				break;
			case "alliance":
				$query = "SELECT ali.all_id, ali.all_name
			FROM kb3_alliances ali
			WHERE ali.all_name LIKE '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%'
			ORDER BY ali.all_name";
				break;
		}

		$result = mysql_query($query);

		$unsharp = true;
		$results = array();
		while ($row = mysql_fetch_assoc($result))
		{
			switch ($_REQUEST['searchtype'])
			{
				case 'pilot':
					$link = "?step=5&amp;do=select&amp;a=0&amp;c=0&amp;p=".$row['plt_id'].'">Select';
					$descr = 'Pilot '.$row['plt_name'].', member of '.$row['crp_name'];
					if ($row['plt_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
					{
						$unsharp = false;
					}
					break;
				case 'corp':
					$link = "?step=5&amp;do=select&amp;a=0&amp;p=0&amp;c=".$row['crp_id'].'">Select';
					$descr = 'Corp '.$row['crp_name'].', member of '.$row['all_name'];
					if ($row['crp_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
					{
						$unsharp = false;
					}
					break;
				case 'alliance':
					$link = '?step=5&amp;do=select&amp;c=0&amp;p=0&amp;a='.$row['all_id'].'">Select';
					$descr = 'Alliance '.$row['all_name'];
					if (strtolower($row['all_name']) == strtolower(stripslashes($_REQUEST['searchphrase'])))
					{
						$unsharp = false;
					}
					break;
			}
			$results[] = array('descr' => $descr, 'link' => $link);
		}
		if (!count($results) || $unsharp)
		{
			$name = str_replace(" ", "%20", stripslashes($_REQUEST['searchphrase']) );
			$api = new Api();
			$idArr = $api->getCharId($name);

			// If a name was found check if it belongs to a corp.
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
				// Could check against the alliance xml but the download is big
				// enough to risk timeouts. For now just check if name exists
				// and is not a corporation.
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
		}
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
        $this->apiroot_ = "api.eve-online.com";
    }

    function getCharId($name)
    {
        // make the Namelist
        $query="names=".str_replace(' ', '%20', $name);

        return getdata($this->apiroot_,"/eve/CharacterID.xml.aspx",$query);
    }
    function getCorpInfo($id)
    {
		$query = "corporationID=".$id;
        return getdata($this->apiroot_,"/corp/CorporationSheet.xml.aspx",$query);
    }
}

function getdata($apiroot, $target, $query)
{
    $fp = fsockopen($apiroot, 80);

    if (!$fp)
    {
        $id = 0;
		$name = "";
		$corp = "";
    } else {
        // request the xml
        fputs ($fp, "POST $target HTTP/1.0\r\n");
        fputs ($fp, "Host: $apiroot\r\n");
        fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        fputs ($fp, "User-Agent: EDKInstaller\r\n");
        fputs ($fp, "Content-Length: " . strlen($query) . "\r\n");
        fputs ($fp, "Connection: close\r\n\r\n");
        fputs ($fp, "$query\r\n");

        // retrieve contents
        $contents = "";
		$id = "";
		$name = "";
		$corp = "";
        while (!feof($fp))
            $contents .= fgets($fp);

        // close connection
        fclose($fp);
        // Retrieve Char ID
        $start = strpos($contents, "characterID=\"");
        if ($start !== FALSE)
            $id = substr($contents, $start + strlen("characterID=\""));

        $start = strpos($id, "\"");
        if ($start !== FALSE)
            $id = substr($id, 0, (strlen(substr($id, $start)))*(-1));
		$id = intval($id);

		// Retrieve Char Name
        $start = strpos($contents, "row name=\"");
        if ($start !== FALSE)
            $name = substr($contents, $start + strlen("row name=\""));

        $start = strpos($name, "\"");
        if ($start !== FALSE)
            $name = substr($name, 0, (strlen(substr($name, $start)))*(-1));

		// Retrieve Corporation ID
        $start = strpos($contents, "<corporationID>");
        if ($start !== FALSE)
            $corp = substr($contents, $start + strlen("<corporationID>"));

        $start = strpos($corp, "</corporationID>");
        if ($start !== FALSE)
            $corp = substr($corp, 0, (strlen(substr($corp, $start)))*(-1));
		$corp = intval($corp);
    }
    return array('characterID' => $id, 'characterName' => $name, 'corporationID' => $corp);
}
