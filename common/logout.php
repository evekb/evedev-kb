<?php
$session = new Session();
$session->destroy();
header('Location: ?a=admin');
?>