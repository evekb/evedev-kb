<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/option_acache.php');

require_once('update/CCPDB/xml.parser.php');
require_once('update/CCPDB/file.cacher.php');
require_once('update/CCPDB/db.updater.php');

$page = new Page('Administration - Upgrade');
$page->setAdmin();
$page_error = array();

//torch the update definition file. This forces it to be redownloaded.
if (isset($_GET['refresh'])) {
	if (file_exists(KB_CACHEDIR."/update/update.xml")) {
			unlink(KB_CACHEDIR."/update/update.xml");
	}
}

$parser = new UpdateXMLParser();

//check for errors
switch ($parser->getXML()) {
	//not validated
	case 3:
		$page_error[] = "The updates descriptor file was downloaded, but it has failed to validate correctly.";
		break;
	//file not found
	case 4:
		$page_error[] = "Could not download the update descriptor file. It may be that the host is down or that remote connections are disallowed.";
		break;
}

$parser->retrieveData();

if (isset($_GET['reset_db'])) {
	Config::set('upd_dbVersion', $parser->getLatestDBVersion());
}

if (count($page_error) == 0) {
	$dbversion = Config::get('upd_dbVersion');
	$codeversion = KB_VERSION;

	if ($dbversion == '') {
		Config::set('upd_dbVersion', '0.0.0');
		$dbversion = '0.0.0';
	}

	if ($codeversion == '') {
		Config::set('upd_codeVersion', KB_VERSION);
		$codeversion = KB_VERSION;
	}


	//cache a database update to the cache directory
	if (isset($_GET['db_dl_ref'])) {
		$db = $parser->getDBInfo();
		foreach ($db as $piece) { //version number must be greater than current version, else do nothing
			if ($piece['version'] > $dbversion
							&& $piece['version'] == $_GET['db_dl_ref']) {
				if (!file_exists(KB_CACHEDIR."/update")) {
					mkdir(KB_CACHEDIR."/update", 0777);
				}

				$hostFileName = $piece['url'];
				$lastPart = explode('/', $hostFileName);
				$cacheFileName = KB_CACHEDIR."/update/".$lastPart[count($lastPart) - 1];
				new FileCacher($hostFileName, $cacheFileName);
				break;
			}
		}
	}
	//overwrite existing database upgrade
	else if (isset($_GET['db_apply_ref'])) {
		$db = $parser->getDBInfo();
		foreach ($db as $piece) {
			//version number must be greater than current version, else do nothing
			if ($piece['version'] > $dbversion
							&& $piece['version'] == $_GET['db_apply_ref']) {
				$hostFileName = $piece['url'];
				$lastPart = explode('/', $hostFileName);
				$cacheFileName = KB_CACHEDIR."/update/".$lastPart[count($lastPart) - 1];

				//cachefile holds the stuff we want to import
				$update = new DBUpdater($cacheFileName);
				$update->runQueries();

				Config::set('upd_dbVersion', $piece['version']);
				$qry = DBFactory::getDBQuery(true);
				$qry->execute("INSERT INTO `kb3_config` (cfg_site, cfg_key, cfg_value) ".
								"SELECT cfg_site, 'upd_dbVersion', '{$piece['version']}' FROM `kb3_config` ".
								"GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '{$piece['version']}';");
				$dbversion = $piece['version'];
				break;
			}
		}
	}
	//cache a code file update to the cache directory
	else if (isset($_GET['code_dl_ref'])) {
		$code = $parser->getcodeInfo();
		foreach ($code as $piece) {
			//version number must be greater than current version, else do nothing
			if ($piece['version'] > $codeversion
							&& $piece['version'] == $_GET['code_dl_ref']) {
				if (!file_exists(getcwd()."/update")) {
					mkdir(KB_CACHEDIR."/update", 0777);
				}

				$hostFileName = $piece['url'];
				$lastPart = explode('/', $hostFileName);
				$cacheFileName = KB_CACHEDIR."/update/".$lastPart[count($lastPart) - 1];
				new FileCacher($hostFileName, $cacheFileName);
				break;
			}
		}
	}
	//unzip and overwrite existing code upgrade
	else if (isset($_GET['code_apply_ref'])) {
		$code = $parser->getCodeInfo();
		foreach ($code as $piece) { //version number must be greater than current version, else do nothing
			if ($piece['version'] > $dbversion && $piece['version'] == $_GET['code_apply_ref']) {
				if (!file_exists(KB_CACHEDIR."/update/backup")) {
					mkdir(KB_CACHEDIR."/update/backup", 0777);
				}

				$hostFileName = $piece['url'];
				$lastPart = explode('/', $hostFileName);
				$cacheFileName = KB_CACHEDIR."/update/".$lastPart[count($lastPart) - 1];

				//get the file list from the zip, and backup the existing files, this allows
				//the board admin to roll back the source manually at a later time.
				$readingZip = new Zip($cacheFileName);
				$fileList = $readingZip->getFileList();
				$deleteList = array();
				if (is_array($fileList)) {
					foreach ($fileList as $file) {
						if ($file['filename'] == "cache/todelete.txt") {
							$tmp = $readingZip->extractFile($file['index']);
							$deleteList = explode("\n", $tmp[0]["content"]);
						} else if (is_dir($file['filename'])) {
							// Add empty directories to the backup list.
							$dirlist = scandir($file['filename']);
							if (count($dirList) == 2) {
								$fileName[] = $file['filename'];
							}
							unset($dirlist);
						}
						else $fileName[] = $file['filename'];
					}
				}
				if ($readingZip->getErrors()) {
					$page_error[] = $readingZip->getErrors();
				}
				if ($deleteList) {
					foreach ($deleteList as &$curFile) {
						$curFile = trim($curFile);
						if ($curFile && substr($curFile, 0, 1) != "/") {
							$fileName[] = $curFile;
						}
					}
				}

				$writingZip = new Zip(KB_CACHEDIR.'/update/backup/'.$codeversion.'.zip');
				$writingZip->addFileArray($fileName);
				if ($writingZip->createZip()) {
					if ($writingZip->getErrors()) {
						$page_error[] = $writingZip->getErrors();
					}
				}

				$readingZip->extractZip(getcwd());
				if ($deleteList) {
					foreach ($deleteList as $curFile) {
						if ($curFile && substr($curFile, 0, 1) != "/") {
							if (file_exists($curFile) && !@unlink($curFile)) {
								$page_error[] = "Could not unlink ".$curFile;
							}
						}
					}
				}

				if ($readingZip->getErrors()) {
					$page_error[] = $readingZip->getErrors();
				} else {
					Config::set('upd_CodeVersion', $piece['version']);
					$qry = DBFactory::getDBQuery(true);
					$qry->execute("INSERT INTO `kb3_config` (cfg_site, cfg_key, cfg_value) ".
									"SELECT cfg_site, 'upd_codeVersion', '{$piece['version']}' FROM `kb3_config` ".
									"GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '{$piece['version']}';");
					$codeversion = $piece['version'];
				}
				//kill the template and page caches
				CacheHandler::removeByAge('store', 0, true);
				CacheHandler::removeByAge('templates_c', 0, true);
				break;
			}
		}
	}

	//if we've finished an action, reparse the xml
	if (isset($_GET['db_apply_ref']) || isset($_GET['db_dl_ref']) || isset($_GET['code_apply_ref']) || isset($_GET['code_dl_ref'])) {
		$parser->retrieveData();
	}

	//list the db updates
	$db = $parser->getDBInfo();
	$lowestDB = $parser->getLowestDBVersion();
	if ($parser->getLatestDBVersion() > $dbversion) {
		$i = 0;
		foreach ($db as $piece) {
			if ($piece['version'] == $lowestDB) {
				$dbList[$i]['lowest'] = true;
			}
			if ($piece['version'] > $dbversion) {
				$dbList[$i]['hash'] = $piece['hash'];
				$dbList[$i]['version'] = $piece['version'];
				$dbList[$i]['desc'] = $piece['desc'];

				$hostFileName = $piece['url'];
				$lastPart = explode('/', $hostFileName);
				$dbList[$i]['short_name'] = $lastPart[count($lastPart) - 1];
				$cacheFileName = KB_CACHEDIR."/update/".$lastPart[count($lastPart) - 1];

				if (!file_exists($cacheFileName)) {
					$dbList[$i]['cached'] = false;
				} else {
					$dbList[$i]['cached'] = true;

					//match the hashes, else the file is buggered. Disallow update application.
					if ($piece['hash'] == md5_file($cacheFileName)) {
						$dbList[$i]['hash_match'] = true;
					}
				}
				$i++;
			}
		}
	}

	//list the code updates
	$code = $parser->getCodeInfo();
	$lowestCode = $parser->getLowestCodeVersion();
	if ($parser->getLatestCodeVersion() > $codeversion) {
		$i = 0;
		foreach ($code as $piece) {
			if ($piece['version'] == $lowestCode) {
				$codeList[$i]['lowest'] = true;
			}
			if ($piece['version'] > $codeversion) {
				$codeList[$i]['hash'] = $piece['hash'];
				$codeList[$i]['version'] = $piece['version'];
				$codeList[$i]['desc'] = $piece['desc'];
				$codeList[$i]['svnrev'] = $piece['svnrev'];

				$hostFileName = $piece['url'];
				$lastPart = explode('/', $hostFileName);
				$codeList[$i]['short_name'] = $lastPart[count($lastPart) - 1];
				$cacheFileName = KB_CACHEDIR."/update/".$lastPart[count($lastPart) - 1];

				if (!file_exists($cacheFileName)) {
					$codeList[$i]['cached'] = false;
				} else {
					$codeList[$i]['cached'] = true;
					if ($piece['hash'] == md5_file($cacheFileName)) {
						$codeList[$i]['hash_match'] = true;
					}
				}
				$i++;
			}
		}
	}
}

$time = Config::get('upd_cacheTime') + 86400; // add a day
$update_time = date("Y-m-d G:i:s", $time);

$smarty->assign('update_time', $update_time);
$smarty->assign('dbList', $dbList);
$smarty->assign('codeList', $codeList);
$smarty->assign('page_error', $page_error);
$smarty->assign('DBmessage', $parser->getLatestDBMessage());
$smarty->assign('codemessage', $parser->getLatestCodeMessage());
$smarty->assign('dbversion', Config::get('upd_dbVersion'));
$smarty->assign('codeversion', KB_VERSION);


$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_upgrade')));
$page->generate();
