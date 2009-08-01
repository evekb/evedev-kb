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
$KB_HOME = "/var/www/killboard/";
chdir($KB_HOME); 

require_once('kbconfig.php');
require_once('common/includes/db.php');
require_once('mods/value_fetch/fetcher.php');

$fetch = new Fetcher();

// Uncomment the type of fetch you want! And remove the die!
die("You have been a BAD boy, configure before use\n");
// PHP5
//$count = $fetch->fetch_values_php5(false);
// PHP5 and faction items (Default)
$count = $fetch->fetch_values_php5(true);
// PHP4
//$count = $fetch->fetch_values_php4(false);
// PHP4 and faction items
//$count = $fetch->fetch_values_php4(true);
// Ship values (Default)
$fetch->updateShips();

// Echo result
//echo $count."\n";

?>
