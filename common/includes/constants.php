<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
if (!defined('LATEST_DB_UPDATE')) {
    define('LATEST_DB_UPDATE', "040");
}

/** path to cache folder, relative to EDK root */
define('KB_CACHEDIR', 'cache');
/** path to cache folder for page caching, relative to EDK root */
define('KB_PAGECACHEDIR', KB_CACHEDIR . '/page');
/** path to mail cache folder for caching killmails, relative to EDK root */
define('KB_MAILCACHEDIR', KB_CACHEDIR . '/mails');
/** path to SQL query cache folder, relative to EDK root */
define('KB_QUERYCACHEDIR', KB_CACHEDIR . '/SQL');
/** URL where to find EDK update information */
define('KB_UPDATE_URL', 'http://evekb.org/downloads');
/** base URL for connecting to CCP's XML API */
define('API_SERVER', "https://api.eveonline.com");
/** base URL for the image server */
define('IMG_SERVER', "https://imageserver.eveonline.com");
/** base URL for connecting to public CREST endpoints */
define('CREST_PUBLIC_URL', 'https://crest-tq.eveonline.com');

/** 
 * current version: major.minor.sub.ccpDBupdateNo
 * even numbers for minor = development version
 */
define('KB_VERSION', '4.2.32.0');
/** release name */
define('KB_RELEASE', '(YC-119-6 1.0)');
/** version of the SDE used to produce the current static database */
define('KB_CCP_DB_VERSION', '20170613');
/** release date of the SDE used to produce the current static database */
define('KB_CCP_DB_DATE', 'Jun 13, 2017');
/** the version of IDFeed used by this killboard, gets reported to clients */
define('ID_FEED_VERSION', 1.50);
/** the version of zKBFetch used by this killboard */
define('ZKB_FETCH_VERSION', 1.1);
/** flag indicating an API key is a legacy key */
define('KB_APIKEY_LEGACY', 1);
/** flag indicating an API key is a corp key */
define('KB_APIKEY_CORP', 2);
/** flag indicating an API key is a character key */
define('KB_APIKEY_CHAR', 4);
/** flag indicating an API key/vcode is incorrect and rejected by the API */
define('KB_APIKEY_BADAUTH', 8);
/** flag indicating an API key is expired */
define('KB_APIKEY_EXPIRED', 16);
