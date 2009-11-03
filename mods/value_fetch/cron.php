#!/usr/bin/php
<?php
/********************************************
* Cron script for value fetcher by Beansman
* Made for the www.eve-dev.net killboard.
* Available at http://svn.nsbit.dk/itemfetch
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
$KB_HOME = preg_replace('/([\/\\\\]cron)$|([\/\\\\]mods[\/\\\\]value_fetch)$/', '', getcwd());
chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/class.config.php');
require_once('common/includes/db.php');
require_once('mods/value_fetch/fetcher.php');

$url = config::get('fetchurl');
if ($url == null || $url == "")
	$url = "http://eve.no-ip.de/prices/30d/prices-all.xml";

$fetch = new Fetcher($url);

// Uncomment the type of fetch you want! And remove the die!
//die("You have been a BAD boy, configure before use\n");

// Fetch
$count = $fetch->fetch_values();
// Ship values (Default)
$fetch->updateShips();

// Echo result
//echo $count."\n";
