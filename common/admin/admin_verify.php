<?php
/**
 * @package EDK
 */
$page = new Page("File Verification");
$page->setAdmin();

function AdditionalMods($dir, $ignoreimg = false)
{
	$ignore = array(".", "..", "ajcron", "forum_post", "known_members", "mail_forward", "rss_feed", "signature_generator");
	if ($ignoreimg)
		$ignore[] = "img";

	$ret = array();
	$dh = opendir($dir);
	while (false !== ($file = readdir($dh)))
	{
		if (!in_array($file, $ignore))
		{
			if (is_dir($dir . "/" . $file))
			{
				$ret[$file] = $file;
			}
		}
	}
	return $ret;
}


$html = "";
if (!isset($_POST['submit']))
{
	$smarty->assign('url', edkURI::page("admin_verify"));
	$html = $smarty->fetch(get_tpl("admin_verify"));
}
else
{
	$data = array();
	foreach(file("cache/checksums.sha1") as $file)
	{
		$file = explode(":", $file);
		$data[$file[0]] = trim($file[1]);
	}

	$ignoreImages = ( $_POST['images'] == "on" ? false : true );
	$missing = array();
	$invalid = array();
	$valid = array();
	foreach ($data as $file => $hash)
	{
		if( !file_exists( $file ) ) {
			$missing[] = $file;
		} else {
			$localhash = sha1_file( $file );

			if ($localhash != $hash)
				$invalid[$file] = array($hash, $localhash);
			else
				$valid[] = $file;
		}
	}
	$smarty->assign("modifications",AdditionalMods('mods'));
	$smarty->assign("invalid", $invalid);
	$smarty->assign("missing", $missing);
	$smarty->assign("count", count($valid) + count($invalid) + count($missing));
	$html .= $smarty->fetch(get_tpl("admin_verify_results"));
}
$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
