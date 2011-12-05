<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$session = new Session();
$session->destroy();
header('Location: '.html_entity_decode(edkURI::page("login")));