<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
*/

trigger_error("Directly including feed_fetcher.php is deprecated. If you need a direct include, use class.fetcher.php", E_USER_NOTICE);

require_once("class.fetcher.php");