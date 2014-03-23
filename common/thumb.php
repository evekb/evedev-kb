<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


if(isset($_GET['int'])) $thumb = new thumbInt($_GET['id'], intval($_GET['size']), slashfix($_GET['type']));
else $thumb = new thumb($_GET['id'], intval($_GET['size']), slashfix($_GET['type']));
$thumb->display();
