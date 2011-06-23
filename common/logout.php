<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$session = new Session();
$session->destroy();
header('Location: ?a=admin');
?>