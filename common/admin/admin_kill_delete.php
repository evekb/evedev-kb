<?php
/**
 * @package EDK
 */
$page = new Page();
$page->setAdmin();

$kll_id = (int) edkURI::getArg('kll_id', 1);
$page->setTitle('Administration - Deletion of Kill ID "'.$kll_id.'"');
if (isset($_GET['confirm'])) {
	$kill = Kill::getByID($kll_id);
	$kill->remove(true, false);
	$html .= "Kill ID \"".$kll_id."\" deleted!";
	$html .= "<br><br><a href=\"javascript:window.close();\">[close]</a>";
} else if (isset($_GET['permanent'])) {
	$kill = Kill::getByID($kll_id);
	$kill->remove(true, true);
	$html .= "Kill ID \"".$kll_id."\" deleted!";
	$html .= "<br><br><a href=\"javascript:window.close();\">[close]</a>";
} else {
	$cargs = array();
	$cargs[] = array("a", "admin_kill_delete", true);
	$cargs[] = array("kll_id", $kll_id, true);
	$pargs = $cargs;
	$cargs[] = array("confirm", "yes", false);
	$pargs[] = array("permanent", "yes", false);
	$html .= "Delete Kill ID \"".$kll_id."\": ";
	$html .= "<button onClick=\"window.location.href='".edkURI::build($cargs)
			."'\">Yes</button><br />";
	$html .= "Delete and prevent reposting: ";
	$html .= "<button onClick=\"window.location.href='".edkURI::build($pargs)
			."'\">Yes</button><br />";
	$html .= "Abort deletion and return: ";
	$html .= "<button onClick=\"window.close();\">No</button>";
}
$page->setContent($html);
$page->generate();
