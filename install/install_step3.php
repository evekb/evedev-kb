<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;
$pass_img = '../img/sta_alliance.png';
$fail_img = '../img/sta_horrible.png';
$amb_img = '../img/sta_bad.png';
global $smarty;
$smarty->assign('db_image', $fail_img);

if (!empty($_POST['submit']) && $_POST['submit'] == 'Test')
{
	$_SESSION['sql']['host'] = $_POST['host'];
	$_SESSION['sql']['user'] = $_POST['user'];
	$_SESSION['sql']['pass'] = $_POST['dbpass'];
	$_SESSION['sql']['db'] = $_POST['db'];
	$_SESSION['sql']['engine'] = $_POST['engine'];
}

if (empty($_SESSION['sql']['host']))
{
	$host = 'localhost';
}
else $host = $_SESSION['sql']['host'];

//check if we already have a config file
    
if (file_exists('../kbconfig.php') && (empty($_POST['submit']) || $_POST['submit'] != 'Test'))
{
	if (filesize('../kbconfig.php') > 0)
	{
		$smarty->assign('conf_exists', true);
		$smarty->assign('conf_image', $amb_img);
		include_once('../kbconfig.php');
		$_SESSION['sql'] = array();
		$_SESSION['sql']['host'] = DB_HOST;

		if($_SESSION['sql']['host'] != "DB_HOST")
		{
			$_SESSION['sql']['user'] = DB_USER;
			$_SESSION['sql']['pass'] = DB_PASS;
			$_SESSION['sql']['db'] = DB_NAME;
			$_SESSION['sql']['engine'] = "InnoDB";
		}
		else {
			clearConnectionStrings();
			$_SESSION['sql']['host'] = $host;
			$smarty->assign('conf_exists', false);
		}
	}
	else
	{
		clearConnectionStrings();
	}
}
if (empty($_SESSION['sql']['host']))
	$smarty->assign('db_host', $host);
else $smarty->assign('db_host', $_SESSION['sql']['host']);
$smarty->assign('db_user', $_SESSION['sql']['user']);
$smarty->assign('db_pass', $_SESSION['sql']['pass']);
$smarty->assign('db_db', $_SESSION['sql']['db']);
$smarty->assign('db_engine', $_SESSION['sql']['engine']);

if ($_SESSION['sql']['db'])
{
    $db = @mysql_connect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);

    $smarty->assign('test_db', is_resource($db));
    if (is_resource($db))
    {
	    $result = mysql_query('SELECT VERSION() AS version');
	    $result = mysql_fetch_assoc($result);
	    $smarty->assign('test_sql', $result);
	    if (!$result)
	    {
		    $stoppage = true;
		    $smarty->assign('test_error', mysql_error());
	    }
	    else
	    {
		    $smarty->assign('test_version', $result['version']);
		    $version_ok = $result['version'] >= "5";
		    $smarty->assign("version_ok", $version_ok);
		    if (!$version_ok)
				$stoppage = true;
			else
			{
				$smarty->assign('test_select', mysql_select_db($_SESSION['sql']['db']));
				if (mysql_select_db($_SESSION['sql']['db']))
				{
					$stoppage = false;
					$smarty->assign('db_image', $pass_img);
					//InnoDB check
					if ($stoppage == false && $_SESSION['sql']['engine'] == 'InnoDB')
					{
						$smarty->assign('test_inno', true);
						$stoppage = true;

						$result = mysql_query('SHOW ENGINES;');
						while (($row = mysql_fetch_row($result)) &&  $stoppage == true){
							if ($row[0] == 'InnoDB'){
								if ($row[1] == 'YES' || $row[1] == 'DEFAULT'){ // (YES / NO / DEFAULT)
									$stoppage = false;
								}
							}
						}
						if ($stoppage){
							$smarty->assign('db_image', $fail_img);
							$smarty->assign('test_error_inno', true);
						}
					}
				}
				else
				{
					$smarty->assign('test_error', mysql_error());
				}
		    }
	    }
    }
    else
    {
	    $smarty->assign('test_error', mysql_error());
    }
}
$smarty->assign('stoppage', $stoppage);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step3.tpl');

function clearConnectionStrings()
{
	if(!isset($_SESSION['sql']['db']))
	{
		$_SESSION['sql'] = array();
		$_SESSION['sql']['host'] = '';
		$_SESSION['sql']['user'] = '';
		$_SESSION['sql']['pass'] = '';
		$_SESSION['sql']['db'] = '';
		$_SESSION['sql']['engine'] = '';
	}
}
?>