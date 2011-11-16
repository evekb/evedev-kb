<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$session = new Session();
$session->destroy();
header('Location: '.edkURI::page("admin"));