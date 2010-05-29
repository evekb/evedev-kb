<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

$session = new Session();
$session->destroy();
header('Location: ?a=admin');
?>