<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


trigger_error("Directly including class.dbcache.php is deprecated. If you need a direct include, use class.dbcachedquery.php", E_USER_NOTICE);

require_once('class.dbcachedquery.php');
