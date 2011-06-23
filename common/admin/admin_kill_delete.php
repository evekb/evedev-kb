<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$page = new Page('Administration - Deletion of Kill ID "'.intval($_GET['kll_id']).'"');
$page->setAdmin();
$dbconn = new DBConnection();
$kll_id = intval($_GET['kll_id']);

if (isset($_GET['confirm']))
{
	$kill = new Kill($kll_id);
	$kill->remove(true, false);
	$html .= "Kill ID \"".$kll_id."\" deleted!";
	$html .= "<br><br><a href=\"javascript:window.close();\">[close]</a>";
}
else if (isset($_GET['permanent']))
{
	$kill = new Kill($kll_id);
	$kill->remove(true, true);
	$html .= "Kill ID \"".$kll_id."\" deleted!";
	$html .= "<br><br><a href=\"javascript:window.close();\">[close]</a>";
}
else
{
	$html .= "Delete Kill ID \"".$kll_id."\": ";
	$html .= "<button onClick=\"window.location.href='?a=admin_kill_delete&confirm=yes&kll_id=".$kll_id."'\">Yes</button><br />";
	$html .= "Delete and prevent reposting: ";
	$html .= "<button onClick=\"window.location.href='?a=admin_kill_delete&permanent=yes&kll_id=".$kll_id."'\">Yes</button><br />";
	$html .= "Abort deletion and return: ";
	$html .= "<button onClick=\"window.close();\">No</button>";
}
$page->setContent($html);
$page->generate();
