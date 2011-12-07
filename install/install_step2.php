<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php');die();}
$stoppage = false;
global $smarty;
$pass_img = '../img/sta_alliance.png';
$fail_img = '../img/sta_horrible.png';
$amb_img = '../img/sta_bad.png';

//already installed check
if (file_exists('../kbconfig.php'))
{
	include('../kbconfig.php');
	if(defined('KB_SITE') && defined('DB_HOST') && defined('DB_USER')
		&& defined('DB_NAME') && defined('DB_PASS'))
	{
		$conn = mysql_connect(DB_HOST.':'.DB_PORT, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		if($_GET['erase']==1)
		{
			$res = mysql_query("SHOW TABLES", $conn);
			if($res && mysql_num_rows($res))
			{
				while($row = mysql_fetch_array($res))
					mysql_query("DROP TABLE ".$row[0], $conn);

			}
		}
		else
		{
			$res = mysql_query("SELECT * FROM kb3_config WHERE cfg_site = '".KB_SITE."'", $conn);
			if($res && mysql_num_rows($res))
			{
				$smarty->assign('previous_install', true);
				$smarty->assign('previous_image', $fail_img);
				$smarty->assign('update', substr($_SERVER['REQUEST_URI'],0,strrpos($_SERVER['REQUEST_URI'],'install'))."update/");
				$stoppage = true;
			}
		}
	}
}

//PHP version check
$php_ok = phpversion() >= "5";
$mysqli_ok = function_exists("mysqli_connect");

$smarty->assign("php_ok", $php_ok);
$smarty->assign("mysqli_ok", $mysqli_ok);

if ($php_ok && $mysqli_ok)
{
	$smarty->assign("php_image", $pass_img);
}
else
{
	$smarty->assign("php_image", $fail_img);
	$stoppage = true;
}

// graphics
$smarty->assign('gd_exists', function_exists('imagecreatefromstring'));
if (function_exists('imagecreatefromstring'))
{
	$smarty->assign('gd_truecolour', function_exists('imagecreatetruecolor'));
	$smarty->assign('dg_ttf', function_exists('imagettftext'));
	$smarty->assign('gd_image', $pass_img);
}
else $smarty->assign('gd_image', $fail_img);

// directories
$smarty->assign('dir_writable', is_writeable('../cache'));
if(is_writeable('../cache'))
{
	$text = checkdir('../cache/SQL');
	$text .= checkdir('../cache/api');
	$text .= checkdir('../cache/data');
	$text .= checkdir('../cache/mails');
	$text .= checkdir('../cache/img');
	$text .= checkdir('../cache/img/map');
	$text .= checkdir('../cache/store');
	$text .= checkdir('../cache/templates_c');

	$smarty->assign('dir_text', $text);
	$smarty->assign('dir_image', $pass_img);
}
else
{
	$stoppage = true;
	$smarty->assign('dir_image', $fail_img);
}

//config file
$smarty->assign('conf_exists', file_exists('../kbconfig.php'));
if (!file_exists('../kbconfig.php'))
{
	$stoppage = true;
	$smarty->assign('conf_image', $fail_img);
}
elseif (is_writeable('../kbconfig.php'))
{
	$smarty->assign('conf_image', $pass_img);
}
else
{
	$stoppage = true;
	$smarty->assign('conf_conditional', true);
	$smarty->assign('conf_image', $fail_img);
}

// connectivity
$url = 'http://www.eve-id.net/logo.png';
$smarty->assign('conn_url', $url);
$smarty->assign('conn_fopen_exists', ini_get('allow_url_fopen'));
$smarty->assign('conn_image', $pass_img);
if (ini_get('allow_url_fopen'))
{
	$smarty->assign('conn_fopen_success', count(file($url)));
}
else
{
	include('../common/includes/class.httprequest.php');
	$http = new http_request($url);
	$smarty->assign('conn_http_success', $http->get_content());
	if (!$http->get_content())
	{
		$smarty->assign('conn_image', $amb_img);
	}
}

$smarty->assign('conn_curl_exists', function_exists('curl_init'));
if (function_exists('curl_init')) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$content = curl_exec($ch);
	curl_close($ch);

	if($content) {
	    $smarty->assign('conn_curl_success', true);
	}
}

$smarty->assign('stoppage', $stoppage);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step2.tpl');


function checkdir($dir)
{
	$text = '';
    if (!file_exists($dir))
    {
        $text = '<b>Creating '.$dir.' for you...</b><br/>';
        mkdir($dir);
        chmod($dir, 0777);
    }
    if (is_writeable($dir))
    {
        $text .= 'Directory '.$dir.' exists and is writeable. Excellent!<br/>';
    }
    else
    {
        $text .= 'I can\'t write into '.$dir.'. You need to fix that for me before you can continue.<br/>';
        $text .= 'Please issue a "chmod 777 '.$dir.'" on the commandline inside of this directory.<br/>';
        global $stoppage;
        $stoppage = true;
    }
    return $text;
}
?>

