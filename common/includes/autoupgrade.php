<?php
// for easier patching

// Note: DO UPDATES ALWAYS OVER THE ITEM NAMES, SO NO KB's
//       DATABASES GET SCREWED UP.
//
//
if (!config::get("DBUpdate"))
	config::set("DBUpdate","000");
	
// Current update version of Database
define('CURRENT_DB_UPDATE', config::get("DBUpdate"));

function updateDB(){
	// if update necessary run upgrade.php
	if (CURRENT_DB_UPDATE < LASTEST_DB_UPDATE ){
		// Check db is installed.
		if(config::get('cfg_kbhost'))
		{
			header('Location: '.KB_HOST."/upgrade.php");
			die;
		}
	}
}