<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
$page = new Page("File Verification");
$page->setAdmin();

function ReadDirectory($dir, $ignoreimg = false)
{
	$ignore = array(".", "..", ".svn", "checksums.sha1", "kbconfig.php");
	if ($ignoreimg)
		$ignore[] = "img";

	$ret = array();
	$dh = opendir($dir);
	while (false !== ($file = readdir($dh)))
	{
		if (!in_array($file, $ignore))
		{
			if (is_file($dir . "/" . $file))
			{
				if (strpos($file, ".php") !== false)
				{
					$contents = file_get_contents($dir . "/" . $file);
					$contents = preg_replace('/\$(Date|Revision|HeadURL)[^$]*\$/', '', $contents);
					$contents = preg_replace('/\r\n/', "\n", $contents);
					$sha1 = sha1($contents);
				}
				else
					$sha1 = sha1_file($dir . "/" . $file);
				$file = str_replace("\\", "/", $dir . "/" . $file);
				$ret[$file] = $sha1;
			}
			if (is_dir($dir . "/" . $file))
			{
				$ret = array_merge($ret, ReadDirectory($dir . "/" . $file));
			}
		}
	}
	return $ret;
}


$html = "";
if (!isset($_POST['submit']))
{
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
	$localfiles = ReadDirectory(".", $ignoreImages);
	$missing = array();
	$invalid = array();
	$valid = array();
	foreach ($data as $file => $hash)
	{
		if (stristr($file, "./img") && $ignoreImages)
			continue;
		if (!isset($localfiles[$file]))
			$missing[] = $file;
		elseif ($localfiles[$file] != $hash)
			$invalid[$file] = array($hash, $localfiles[$file]);
		else
			$valid[] = $file;
	}
	$smarty->assign("invalid", $invalid);
	$smarty->assign("missing", $missing);
	$smarty->assign("count", count($valid) + count($invalid) + count($missing));
	$html .= $smarty->fetch(get_tpl("admin_verify_results"));
}
$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
