#!/usr/bin/php
<?php
/********************************************
* Cron script for value fetcher by Beansman
* Made for the www.eve-id.net killboard.
* Previous mod version available at
* http://svn.nsbit.dk/itemfetch
*
* Read though the script and change variables
* as needed.
*
* Made from liqs feed cron script ;)
*
********************************************/

@set_time_limit(0);

// Has to be run from the KB main directory for nested includes to work
//$KB_HOME = "/home/www/killboard/";
$KB_HOME = preg_replace('/([\/\\\\]cron)$/', '', getcwd());
chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/class.config.php');
require_once('common/includes/db.php');
require_once('common/includes/class.valuefetcher.php');

$url = config::get('fetchurl');
if ($url == null || $url == "")
	$url = "http://eve.no-ip.de/prices/30d/prices-all.xml";

$fetch = new valueFetcher($url);

// Fetch
$count = $fetch->fetch_values();
// Ship values (Default)
$fetch->updateShips();

// Echo result
echo $count." Items updated\n";
