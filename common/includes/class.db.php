<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


trigger_error("Directly including class.db.php is deprecated. If you need a direct include, use class.dbquery.php or class.dbnormalquery.php", E_USER_DEPRECATED);

require_once('class.dbquery.php');
require_once('class.dbnormalquery.php');
