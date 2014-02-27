<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
if (!defined('LATEST_DB_UPDATE')) {
	define('LATEST_DB_UPDATE', "030");
}

define('KB_CACHEDIR', 'cache');
define('KB_PAGECACHEDIR', KB_CACHEDIR.'/page');
define('KB_MAILCACHEDIR', KB_CACHEDIR.'/mails');
define('KB_QUERYCACHEDIR', KB_CACHEDIR.'/SQL');
define('KB_UPDATE_URL', 'http://evekb.org/downloads');
define('API_SERVER', "http://api.eveonline.com");
//define('API_SERVER', "http://apitest.eveonline.com");
define('IMG_SERVER', "image.eveonline.com");

// current version: major.minor.sub.ccpDBupdateNo
// even numbers for minor = development version
define('KB_VERSION', '4.0.8.0');
define('KB_RELEASE', '(Rubicon 1.1)');
define('KB_CCP_DB_VERSION', '94322');
define('KB_CCP_DB_DATE', 'Jan 24, 2014');
define('ID_FEED_VERSION', 1.20);
define('KB_APIKEY_LEGACY', 1);
define('KB_APIKEY_CORP', 2);
define('KB_APIKEY_CHAR', 4);
define('KB_APIKEY_BADAUTH', 8);
define('KB_APIKEY_EXPIRED', 16);
