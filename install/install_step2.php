<?php
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = false;

// graphics
echo '<div class="block-header2">Graphics</div>';
if (function_exists('imagecreatefromstring'))
{
    echo 'GD is available.<br/>';
    if (!function_exists('imagecreatetruecolor'))
    {
        echo 'Your GD is outdated though and will cause problems, please contact your system administrator to upgrade to GD 2.0 or higher.<br/>';
    }
    echo 'Now let\'s see if you got the FreeType library needed for painting TrueType .ttf fonts onto images<br/>';
    if (function_exists('imagettftext'))
    {
        echo 'I found FreeType support, this is needed by the signature mod. Good!<br/>';
    }
    else
    {
        echo 'Unfortunately I was unable to locate FreeType support so you cannot use all available signatures. :(<br/>';
    }
}
else
{
    echo 'GD is NOT available.<br/>The Killboard will be unable to output character portraits or corporation logos, please speak with your system administrator to install GD 2.0 or higher.<br/>';
    echo 'You can continue the installation but the Killboard might not run smoothly.<br/>';
}

// directorys
echo '<br/><div class="block-header2">Directory structure</div>';
function checkdir($dir)
{
    if (!file_exists($dir))
    {
        echo 'Creating '.$dir.' for you...<br/>';
        mkdir($dir);
        chmod($dir, 0777);
    }
    if (is_writeable($dir))
    {
        echo 'Directory '.$dir.' is there and writeable, excellent.<br/>';
    }
    else
    {
        echo 'I cannot write into '.$dir.', you need to fix that for me before you can continue.<br/>';
        echo 'Please issue a "chmod 777 '.$dir.'" on the commandline inside of this directory<br/>';
        global $stoppage;
        $stoppage = true;
    }
}

if (is_writeable('../cache'))
{
    echo 'Cache directory is writeable, testing for subdirs now:<br/>';
    checkdir('../cache/cache');
    checkdir('../cache/api');
    checkdir('../cache/corps');
    checkdir('../cache/data');
    checkdir('../cache/map');
    checkdir('../cache/mails');
    checkdir('../cache/portraits');
    checkdir('../cache/templates_c');
}
else
{
    $stoppage = true;
    echo 'I cannot write into ../cache, you need to fix that for me before you can continue.<br/>';
    echo 'Please issue a "chmod 777 ../cache" and "chmod 777 ../cache/*" on the commandline inside of this directory<br/>';
}

echo '<br/><div class="block-header2">Config</div>';
if (!file_exists('../kbconfig.php'))
{
    $stoppage = true;
    echo 'Please create the file \'kbconfig.php\' and make it writeable by the webserver.<br/>';
}
elseif (is_writeable('../kbconfig.php'))
{
    echo 'The config file \'../kbconfig.php\' is there and writeable, excellent!<br/>';
}
else
{
    $stoppage = true;
    echo 'I cannot write into ../kbconfig.php, you need to fix that for me before you can continue.<br/>';
    echo 'Please issue a "chmod 777 ../kbconfig" on the commandline inside of this directory<br/>';
}

echo '<br/><div class="block-header2">Connectivity</div>';
// connectivity
$url = 'http://www.eve-dev.net/logo.png';
if (ini_get('allow_url_fopen'))
{
    echo 'allow_url_fopen is on, I will try to fetch a testpage from "'.$url.'".<br/>';
    if (count(file($url)))
    {
        echo 'Seems to be ok, I got the file.<br/>';
    }
    else
    {
        echo 'I could not get the file this might be a firewall related issue or the eve-dev server is not available.<br/>';
    }
}
else
{
    include('../common/includes/class.http.php');
    echo 'allow_url_fopen is disabled, nethertheless I will try a socket connect now.<br/>';

    $http = new http_request($url);
    if ($http->get_content())
    {
        echo 'Seems to be ok, I got the file.<br/>';
    }
    else
    {
        echo 'I could not get the file. This might be a firewall related issue or the eve-dev server is not available.<br/>';
    }
}

if (file_exists('../kbconfig.php'))
{
	include('../kbconfig.php');
	if(defined('KB_SITE') && defined('DB_HOST') && defined('DB_USER')
		&& defined('DB_NAME') && defined('DB_PASS'))
	{
		$conn = mysql_connect(DB_HOST.':'.DB_PORT, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		$res = mysql_query("SELECT * FROM kb3_config WHERE cfg_site = '".KB_SITE."'", $conn);
		if($res && mysql_num_rows($res))
		{
			echo '<br/><div class="block-header2">EVE Development Killboard Installed</div>';
			echo 'EVE Development Killboard is already installed. Proceeding with install may damage the existing installation.<br/>';
			echo 'If you do not wish to install then remove the /install directory from your EVE Development Killboard installation to ensure it is not run again.';
		}
	}
}

?>

<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>
