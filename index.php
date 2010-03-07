<?php

/*
* Request forwarder, look at common/index.php for the action and license
*/

include ('common/includes/class.edkerror.php');
@error_reporting(E_ALL ^ E_NOTICE);

if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

include('common/index.php');