<?php
require_once('common/admin/admin_menu.php');

$page = new Page('Administration - Troubleshooting');
$page->setAdmin();

$html .= '<table class="kb-table" cellspacing="1">';
$html .= '<div class="block-header2">Graphics</div>';
if (function_exists('imagecreatefromstring'))
{
	$html .= '<img src="'.IMG_URL .'/panel/working.jpg" border="0" alt="" />';
    $html .= '  GD is available.<br /></tr>';
    if (!function_exists('imagecreatetruecolor'))
    {
	    $html .=  '<img src="'.IMG_URL .'/panel/error.jpg" border="0" alt="" />';
        $html .=  '  Your GD is outdated though and will cause problems, please contact your system administrator to upgrade to GD 2.0 or higher.<br />';
    }
    if (function_exists('imagettftext'))
    {
	    $html .=  '<img src="'.IMG_URL .'/panel/working.jpg" border="0" alt="" />';
        $html .=  '  FreeType support is enabled<br />';
    }
    else
    {
	    $html .=  '<img src="'.IMG_URL .'/panel/error.jpg" border="0" alt="" />';
        $html .=  '  Unfortunatly you do not have FreeType support so you cannot use all available signatures. :(<br />';
    }
}
else
{
	$html .=  '<img src="'.IMG_URL .'/panel/error.jpg" border="0" alt="" />';
    $html .=  '  GD is NOT available.<br />The Killboard is unable to output character portraits or corporation logos, please speak with your system administrator to install GD 2.0 or higher.<br />';
    $html .=  '  However, you can continue to use the Killboard but it might not run smoothly.<br />';
}

function checkdir($dir)
{
    if (is_writeable($dir))
    {
	    //not working atm, might be fixed later
        $html .= 'Directory '.$dir.' is there and writeable, excellent.<br />';
    }
    else
    {
	$html .=  '<img src="'.IMG_URL .'/panel/error.jpg" border="0" alt="" />';
    $html .= '  I cannot write into '.$dir.'.<br />';
    $html .= '  Please issue a "chmod 777 '.$dir.'" and "chmod 777 '.$dir.'/*" on the commandline inside of this directory<br />';
        global $stoppage;
        $stoppage = true;
    }
}

if (is_writeable(KB_CACHEDIR))
{
	$html .=  '<img src="'.IMG_URL .'/panel/working.jpg" border="0" alt="" />';
    $html .=  '  Cache directory is writeable<br />';
    checkdir(KB_PAGECACHEDIR);
    checkdir(KB_QUERYCACHEDIR);
    checkdir(KB_CACHEDIR.'/data');
    checkdir(KB_CACHEDIR.'/map');
    checkdir(KB_CACHEDIR.'/img/pilots');
    checkdir(KB_CACHEDIR.'/img/corps');
    checkdir(KB_CACHEDIR.'/img/alliances');
    checkdir(KB_CACHEDIR.'/templates_c');
}
else
{
    $stoppage = true;
    $html .=  '<img src="'.IMG_URL .'/panel/error.jpg" border="0" alt="" />';
    $html .= '  I cannot write into '.KB_CACHEDIR.'<br />';
    $html .= '  Please issue a "chmod 777 '.KB_CACHEDIR.'" and "chmod 777 '.KB_CACHEDIR.'/*" on the commandline inside of this directory<br />';
}


$html .=  '<br /><div class="block-header2">Connectivity</div>';
// connectivity
$url = 'http://www.eve-id.net/logo.png';
if (ini_get('allow_url_fopen'))
{
    if (count(file($url)))
    {
	    $html .=  '<img src="'.IMG_URL .'/panel/working.jpg" border="0" alt="" />';
        $html .=  '  allow_url_fopen is available.<br />';
    }
    else
    {
	    $html .=  '<img src="'.IMG_URL .'/panel/error.jpg" border="0" alt="" />';
        $html .=  '  I could not get the file, this might be a firewall related issue or the eve-dev server is not available.<br />';
    }
}

{
    include('common/includes/class.http.php');

    $http = new http_request($url);
    if ($http->get_content())
    {
	    $html .=  '<img src="'.IMG_URL .'/panel/working.jpg" border="0" alt="" />';
        $html .=  '  Socket Connect is available.<br />';
    }
    else
    {
	    $html .=  '<img src="'.IMG_URL .'/panel/error.jpg" border="0" alt="" />';
        $html .=  '  I could not get the file, this might be a firewall related issue or the eve-dev server is not available.<br />';
    }
}

//yes this is a mess, pew pew and programming dont mix =P
function find_SQL_Version()
{
	$conn = new DBConnection();
	$value = (float) mysqli_get_server_info($conn->id());
	return $value;
// shell_exec is often not supported so ask the connection instead.
//   $output = shell_exec('mysql -V');
//   preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
//   return $version[0];
}

$sqlver = 'MYSQL version: ' . find_SQL_Version();
$phpver = 'PHP version: ' . phpversion();
$html .= '<div class="block-header2">Server</div>';
$html .= '<img src="'.IMG_URL .'/panel/working.jpg" border="0" alt="" />';
$html .= "  $phpver  <br />";
$html .= '<img src="'.IMG_URL .'/panel/working.jpg" border="0" alt="" />';
$html .= "  $sqlver";


$html .= "</table>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>
