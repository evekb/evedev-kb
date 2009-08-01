<?php
require_once('common/includes/class.thumb.php');

if(isset($_GET['int'])) $thumb = new thumbInt($_GET['id'], intval($_GET['size']));
else $thumb = new thumb($_GET['id'], intval($_GET['size']), slashfix($_GET['type']));
$thumb->display();
?>