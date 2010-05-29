<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

trigger_error("Directly including class.dbmemcache.php is deprecated. If you need a direct include, use class.dbmemcachedquery.php", E_USER_DEPRECATED);

require_once('class.dbmemcachedquery.php');