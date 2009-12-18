<?php
if(!$installrunning) {header('Location: index.php');die();}
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
	$_SESSION['sett']['aid'] = $_REQUEST['a'];
	$_SESSION['sett']['cid'] = $_REQUEST['c'];
	$stoppage = false;
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'select')
{
    $_SESSION['sett']['aid'] = $_REQUEST['a'];
    $_SESSION['sett']['cid'] = $_REQUEST['c'];
    $stoppage = false;
}
if ($stoppage)
{

    if (!empty($_REQUEST['searchphrase']) && strlen($_REQUEST['searchphrase']) >= 3)
    {
	switch ($_REQUEST['searchtype'])
	{
	    case "corp":
		$query = "SELECT crp.crp_id, crp.crp_name, ali.all_name
			FROM kb3_corps crp, kb3_alliances ali
			WHERE lower( crp.crp_name ) LIKE lower( '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%' )
			AND crp.crp_all_id = ali.all_id
			ORDER BY crp.crp_name";
		break;
	    case "alliance":
		$query = "SELECT ali.all_id, ali.all_name
			FROM kb3_alliances ali
			WHERE lower( ali.all_name ) LIKE lower( '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%' )
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
		case 'corp':
		    $link = "?step=5&do=select&a=0&c=".$row['crp_id'].'">Select';
		    $descr = 'Corp '.$row['crp_name'].', member of '.$row['all_name'];
		    if ($row['crp_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
		    {
			$unsharp = false;
		    }
		    break;
		case 'alliance':
		    $link = '?step=5&do=select&c=0&a='.$row['all_id'].'">Select';
		    $descr = 'Alliance '.$row['all_name'];
		    if ($row['all_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
		    {
			$unsharp = false;
		    }
		    break;
	    }
	    $results[] = array('descr' => $descr, 'link' => $link);
	}
	if (!count($results) || $unsharp)
	{
	    if ($_REQUEST['searchtype'] == 'corp')
	    {
		$link = '?step=5&do=create&c='.stripslashes($_REQUEST['searchphrase']).'&a=0">Create';
		$descr = 'Corporation: '.stripslashes($_REQUEST['searchphrase']);
	    }
	    else
	    {
		$link = '?step=5&do=create&a='.stripslashes($_REQUEST['searchphrase']).'&c=0">Create';
		$descr = 'Alliance: '.stripslashes($_REQUEST['searchphrase']);
	    }
	    $results[] = array('descr' => $descr, 'link' => $link);
	}
	$smarty->assign('res_check', count($results) > 0);
	$smarty->assign('results', $results);
    }
}
$smarty->assign('stoppage', $stoppage);
$smarty->assign('conflict', $_SESSION['sett']['aid'] == 0 && $_SESSION['sett']['cid'] == 0);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step5.tpl');
?>