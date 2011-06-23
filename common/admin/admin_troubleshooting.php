<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page('Administration - Troubleshooting');
$page->setAdmin();

$sections = array();
$trouble = array();

$sections['Graphics'] = 'Graphics';
if (function_exists('imagecreatefromstring'))
{
	$html = '  GD is available.<br />';
	$trouble['Graphics'][] = array('passed'=>true, 'text'=> $html);
	if (!function_exists('imagecreatetruecolor'))
	{
		$html =  '  Your GD is outdated though and will cause problems, please contact your system administrator to upgrade to GD 2.0 or higher.';
		$trouble['Graphics'][] = array('passed'=>false, 'text'=> $html);
	}
	if (function_exists('imagettftext'))
	{
		$html =  '  FreeType support is enabled';
		$trouble['Graphics'][] = array('passed'=>true, 'text'=> $html);
	}
	else
	{
		$html =  '  Unfortunately you do not have FreeType support so you cannot use all available signatures. :(';
		$trouble['Graphics'][] = array('passed'=>false, 'text'=> $html);
	}
}
else
{
	$html =  '  GD is NOT available.<br />The Killboard is unable to output character portraits or corporation logos, please speak with your system administrator to install GD 2.0 or higher.<br />';
	$html .=  '  However, you can continue to use the Killboard but it might not run smoothly.';
	$trouble['Graphics'][] = array('passed'=>false, 'text'=> $html);
}

if (is_writeable(KB_CACHEDIR))
{
	$html =  '  Cache directory is writeable';
	$trouble['Graphics'][] = array('passed'=>true, 'text'=> $html);
}
else
{
	$html = '  I cannot write into '.KB_CACHEDIR.'<br />';
	$html .= '  Please issue a "chmod 777 '.KB_CACHEDIR.'" and "chmod 777 '.KB_CACHEDIR.'/*" on the commandline inside of this directory<br />';
	$trouble['Graphics'][] = array('passed'=>false, 'text'=> $html);
}

// connectivity
$sections['Connectivity'] = 'Connectivity';

$url = 'http://www.eve-id.net/logo.png';
if (ini_get('allow_url_fopen'))
{
	if (count(file($url)))
	{
		$html =  '  allow_url_fopen is available.<br />';
		$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
	}
	else
	{
		$html =  '  I could not get the file, this might be a firewall related issue or the eve-dev server is not available.<br />';
		$trouble['Connectivity'][] = array('passed'=>false, 'text'=> $html);
	}
}

$http = new http_request($url);
if ($http->get_content())
{
	$html =  '  Socket Connect is available.';
	$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
}
else
{
	$html =  '  I could not get the file, this might be a firewall related issue or the eve-dev server is not available.<br />';
	$trouble['Connectivity'][] = array('passed'=>false, 'text'=> $html);
}

if (extension_loaded('openssl'))
{
	$html =  '  OpenSSL module is installed.<br />';
	$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
}
else
{
	$html =  '  OpenSSL module is not installed<br />';
	$trouble['Connectivity'][] = array('passed'=>false, 'text'=> $html);
}

if(array_search('https', stream_get_wrappers()))
{
	$html =  '  HTTPS wrapper is installed.';
	$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
}
else
{
	$html =  '  HTTPS wrapper is not installed<br />';
	$trouble['Connectivity'][] = array('passed'=>false, 'text'=> $html);
}

//yes this is a mess, pew pew and programming dont mix =P
function find_SQL_Version()
{
	$conn = new DBConnection();
	$value = (float) mysqli_get_server_info($conn->id());
	return $value;
}

$sections['Server'] = 'Server';

$sqlver = 'MYSQL version: ' . find_SQL_Version();
$phpver = 'PHP version: ' . phpversion();

$html = "$phpver  <br />";
if(phpversion() >= "5.1.2") $trouble['Server'][] = array('passed'=>true, 'text'=> $html);
else $trouble['Server'][] = array('passed'=>false, 'text'=> $html);

$html = "  $sqlver";
if(find_SQL_Version() >= 5) $trouble['Server'][] = array('passed'=>true, 'text'=> $html);
else $html = $trouble['Server'][] = array('passed'=>false, 'text'=> $html);

$smarty->assignByRef('sections', $sections);
$smarty->assignByRef('trouble', $trouble);

$page->setContent($smarty->fetch(get_tpl('admin_troubleshooting')));
$page->addContext($menubox->generate());
$page->generate();

