<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
if (!defined('LATEST_DB_UPDATE')) {
    define('LATEST_DB_UPDATE', "043");
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
/** base URL for the image server */
define('IMG_SERVER', "https://imageserver.eveonline.com");
/** data source for ESI calls */
define('ESI_DATA_SOURCE', 'tranquility');
/** SOO OAuth base URL */
define('OAUTH_BASE_URL', 'https://login.eveonline.com/oauth');

/** 
 * current version: major.minor.sub.ccpDBupdateNo
 * even numbers for minor = development version
 */
define('KB_VERSION', '4.4.3.0');
/** release name */
define('KB_RELEASE', '(July 2019 1.0)');
/** version of the SDE used to produce the current static database */
define('KB_CCP_DB_VERSION', '20190625');
/** release date of the SDE used to produce the current static database */
define('KB_CCP_DB_DATE', 'Jun 25, 2019');
/** the version of IDFeed used by this killboard, gets reported to clients */
define('ID_FEED_VERSION', 1.6);
/** the version of zKBFetch used by this killboard */
define('ZKB_FETCH_VERSION', 1.5);
/** user agent */
define('EDK_USER_AGENT', 'Eve Development Killboard '.KB_VERSION.', Forums: http://evekb.org/forum Contact: Salvoxia <salvoxia@blindfish.info>');
