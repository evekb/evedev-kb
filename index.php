<?php

/*
* Request forwarder, look at common/index.php for the action and license
*/

require_once ('common/includes/class.edkerror.php');

if(!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);
if(!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', 16384);

set_error_handler(array('EDKError', 'handler'),E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED) );

@error_reporting(E_ERROR);

if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

include('common/index.php');