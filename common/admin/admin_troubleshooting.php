<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$URL_FETCHING_TEST_URL = 'http://www.evekb.org/downloads/update2.xml';

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

if (ini_get('allow_url_fopen'))
{
	if (count(file($URL_FETCHING_TEST_URL)))
	{
		$html =  '  allow_url_fopen is available.<br />';
		$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
	}
	else
	{
		$html =  '  I could not get the file, this might be a firewall related issue or the evekb server is not available.<br />';
		$trouble['Connectivity'][] = array('passed'=>false, 'text'=> $html);
	}
}

$http = new http_request($URL_FETCHING_TEST_URL);
if ($http->get_content())
{
	$html =  '  Socket Connect is available.';
	$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
}
else
{
	$html =  '  I could not get the file, this might be a firewall related issue or the evekb server is not available.<br />';
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

if(in_array('https', stream_get_wrappers()))
{
	$html =  '  HTTPS wrapper is installed.';
	$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
}
else
{
	$html =  '  HTTPS wrapper is not installed<br />';
	$trouble['Connectivity'][] = array('passed'=>false, 'text'=> $html);
}

if(API_Helpers::isCurlSupported())
{
	$html =  '  cURL with SSL support is available.';
	$trouble['Connectivity'][] = array('passed'=>true, 'text'=> $html);
}
else
{
	$html =  '  cURL with SSL support is NOT available.<br />';
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
else $trouble['Server'][] = array('passed'=>false, 'text'=> $html);

// checks for API caching
$sections['API Caching'] = 'API Caching';
// get current API caching folder
$cachingFolderApi = getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR. DIRECTORY_SEPARATOR . "api";
$html = 'Current API caching folder is set to '.$cachingFolderApi;
$trouble['API Caching'][] = array('passed'=>true, 'text'=>$html);

// check if folder exists
if(file_exists($cachingFolderApi)) 
{
    $html =  '  API Caching folder exists.';
    $trouble['API Caching'][] = array('passed'=>true, 'text'=> $html);
    
    // check if folder is writable
    if(@touch($cachingFolderApi. DIRECTORY_SEPARATOR . "write_check.tst" )) 
    {
        $html =  '  API Caching folder is writable.';
        $trouble['API Caching'][] = array('passed'=>true, 'text'=> $html);
        @unlink($cachingFolderApi. DIRECTORY_SEPARATOR . "write_check.tst");
        
        // test XML API connection
        try 
        {
            API_Helpers::testXmlApiConnection();
            $html =  '  Successfully connected to XML API';
            $trouble['API Caching'][] = array('passed'=>true, 'text'=> $html);
        } 
        catch (EDKApiConnectionException $e) 
        {
            $html =  '  Connection to XML API NOT successul, Error: '.$e->getMessage().' (Code: '.$e->getCode().')';
            $trouble['API Caching'][] = array('passed'=>false, 'text'=> $html);
        }
               
        // connectivity check for CREST
        try
        {
            API_Helpers::testCrestApiConnection();
            $html =  '  Successfully connected to CREST API';
            $trouble['API Caching'][] = array('passed'=>true, 'text'=> $html);
        }
        catch(EDKApiConnectionException $e)
        {
            $html =  '  Connection to CREST API NOT successul, Error: '.$e->getMessage().' (Code: '.$e->getCode().')';
            $trouble['API Caching'][] = array('passed'=>false, 'text'=> $html);
        }
        
    }
    else 
    {
        $html =  '  API Caching is NOT writable.';
        $trouble['API Caching'][] = array('passed'=>false, 'text'=> $html);
    }
}
else 
{
    $html =  '  API Caching folder does NOT exist.';
    $trouble['API Caching'][] = array('passed'=>false, 'text'=> $html);
}



// checks for SQL query caching
$sections['SQL Caching'] = 'SQL Caching (File)';
// get current SQL caching folder
$cachingFolderSql = getcwd() . DIRECTORY_SEPARATOR . KB_QUERYCACHEDIR;
$html = 'Current SQL caching folder is set to '.$cachingFolderSql;
$trouble['SQL Caching'][] = array('passed'=>true, 'text'=>$html);

// check if folder exists
if(file_exists($cachingFolderSql)) 
{
    $html =  '  SQL Caching folder exists.';
    $trouble['SQL Caching'][] = array('passed'=>true, 'text'=> $html);
    
    // check if folder is writable
    if(@touch($cachingFolderSql. DIRECTORY_SEPARATOR . "write_check.tst" )) 
    {
        $html =  '  SQL Caching folder is writable.';
        $trouble['SQL Caching'][] = array('passed'=>true, 'text'=> $html);
        @unlink($cachingFolderSql. DIRECTORY_SEPARATOR . "write_check.tst");
    }
    else 
    {
        $html =  '  SQL Caching is NOT writable.';
        $trouble['SQL Caching'][] = array('passed'=>false, 'text'=> $html);
    }
}
else 
{
    $html =  '  SQL Caching folder does NOT exist.';
    $trouble['SQL Caching'][] = array('passed'=>false, 'text'=> $html);
}




$sections['Object Caching'] = 'Object Caching (File)';
// get current Object caching folder
$cachingFolderObject = getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR. DIRECTORY_SEPARATOR . "store";
$html = 'Current Object caching folder is set to '.$cachingFolderObject;
$trouble['Object Caching'][] = array('passed'=>true, 'text'=>$html);

// check if folder exists
if(file_exists($cachingFolderObject)) 
{
    $html =  '  Object Caching folder exists.';
    $trouble['Object Caching'][] = array('passed'=>true, 'text'=> $html);
    
    // check if folder is writable
    if(@touch($cachingFolderObject. DIRECTORY_SEPARATOR . "write_check.tst" )) 
    {
        $html =  '  Object Caching folder is writable.';
        $trouble['Object Caching'][] = array('passed'=>true, 'text'=> $html);
        @unlink($cachingFolderObject. DIRECTORY_SEPARATOR . "write_check.tst");
    }
    else 
    {
        $html =  '  Object Caching is NOT writable.';
        $trouble['Object Caching'][] = array('passed'=>false, 'text'=> $html);
    }
}
else 
{
    $html =  '  Object Caching folder does NOT exist.';
    $trouble['Object Caching'][] = array('passed'=>false, 'text'=> $html);
}




$smarty->assignByRef('sections', $sections);
$smarty->assignByRef('trouble', $trouble);

$page->setContent($smarty->fetch(get_tpl('admin_troubleshooting')));
$page->addContext($menubox->generate());
$page->generate();

