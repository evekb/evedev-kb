<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

trigger_error("Directly including class.dbprepared.php is deprecated. If you need a direct include, use class.dbpreparedquery.php", E_USER_DEPRECATED);

require_once("class.dbpreparedquery.php");