<?php
require_once('common/includes/class.kill.php');

$page = new Page('Administration - Deletion of Kill ID "'.$_GET['kll_id'].'"');
$page->setAdmin();
$dbconn = new DBConnection();
$kll_id = $_GET['kll_id'];

if ($_GET['confirm'])
{
    $kill = new Kill($kll_id);
    $kill->remove();
    $html .= "Kill ID \"".$_GET['kll_id']."\" deleted!";
    $html .= "<br><br><a href=\"javascript:window.close();\">[close]</a>";
}
else
{
    $html .= "Confirm deletion of Kill ID \"".$_GET['kll_id']."\": ";
    $html .= "<button onClick=\"window.location.href='?a=admin_kill_delete&confirm=yes&kll_id=".$_GET['kll_id']."'\">Yes</button> ";
    $html .= "<button onClick=\"window.close();\">No</button>";
}
$page->setContent($html);
$page->generate();
?>