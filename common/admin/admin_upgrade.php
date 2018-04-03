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
    if (file_exists(KB_CACHEDIR."/update/update2.xml")) {
            unlink(KB_CACHEDIR."/update/update2.xml");
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


    //cache a code file update to the cache directory
    if (isset($_GET['code_dl_ref'])) {
        $code = $parser->getcodeInfo();
        foreach ($code as $piece) {
            //version number must be greater than current version, else do nothing
            if (isNewerVersion($piece['version'], $codeversion)
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
            if (isNewerVersion($piece['version'], $codeversion) && $piece['version'] == $_GET['code_apply_ref']) {
                if (!file_exists(KB_CACHEDIR."/update/backup")) {
                    mkdir(KB_CACHEDIR."/update/backup", 0777);
                }
                
                // execute checks to verify whether the new version can be installed
                try 
                {
                    checkPrerequisites($piece);
                } 
                
                catch (Exception $ex) 
                {
                    $page_error[] = $ex->getMessage();
                    break;
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
                                
                // first check if all files to update/delete
                // a) do not exist right now
                // b) exist and are writable
                foreach($fileList as $file)
                {
                    // file exists and is not writeable
                    if(file_exists($file['filename']) && !is_writeable($file['filename']))
                    {
                        // try to make it writable!
                        if(@chmod($file['filename'], 0777))
                        {
                            // clear cache, check again
                            clearstatcache(TRUE, $file['filename']);
                            if(is_writable($file['filename']))
                            {
                                continue;
                            }
                        }

                        // at this point the file is nor writeable
                        // and an attempt to make it writeable has failed
                        $page_error[] = $file['filename']." is not writeable. Please manually set file permissions.";
                    }
                }

                // check for deleteList
                foreach($deleteList as $file)
                {
                    // file exists and is not writeable
                    if(file_exists($file) && !is_writeable($file))
                    {
                        // try to make it writable!
                        if(@chmod($file, 0777))
                        {
                            // clear cache, check again
                            clearstatcache(TRUE, $file);
                            if(is_writable($file))
                            {
                                continue;
                            }
                        }

                        // at this point the file is nor writeable
                        // and an attempt to make it writeable has failed
                        $page_error[] = $file['filename']." is not writeable. Please manually set file permissions.";
                    }
                }

                if(!empty($page_error))
                {
                    $page_error[] = "Update has NOT been applied";
                    break;
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
    if (isset($_GET['code_apply_ref']) || isset($_GET['code_dl_ref'])) {
        $parser->retrieveData();
    }

    //list the code updates
    $code = $parser->getCodeInfo();
    $lowestCode = $parser->getLowestCodeVersion();
    if (isNewerVersion($parser->getLatestCodeVersion(),$codeversion)) {
        $i = 0;
        foreach ($code as $piece) {
            if ($piece['version'] == $lowestCode) {
                $codeList[$i]['lowest'] = true;
            }
            if (isNewerVersion($piece['version'], $codeversion)) {
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
$smarty->assign('codeList', $codeList);
$smarty->assign('page_error', $page_error);
$smarty->assign('codemessage', $parser->getLatestCodeMessage());
$smarty->assign('codeversion', KB_VERSION);


$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_upgrade')));
$page->generate();


/**
 * Checks whether prerequisites for the given version to update are met. 
 * 
 * @param array $versionInfo array containing the following indices:
 * <ul><li>version - the EDK version string</li>
 * <li>svnrev - the SVN revision, no longer contains a valid value since moved to GIT</li>
 * <li>hash - the MD5 hash of the zip-file containing the update</li>
 * <li>url - the download URL for the zip-file containing the update</li>
 * <li>desc - a description of the new version</li>
 * </ul>
 * @throws Exception if any check fails
 */
function checkPrerequisites($versionInfo)
{
   checkPhpVersion($versionInfo);  
}

/**
 * Checks whether the current PHP version meets the requirements of the EDK version
 * to upgrade to.
 * 
 * @param array $versionInfo array containing the following indices:
 * <ul><li>version - the EDK version string</li>
 * <li>svnrev - the SVN revision, no longer contains a valid value since moved to GIT</li>
 * <li>hash - the MD5 hash of the zip-file containing the update</li>
 * <li>url - the download URL for the zip-file containing the update</li>
 * <li>desc - a description of the new version</li>
 * </ul>
 * @throws Exception if the minimum PHP version is not met.
 */
function checkPhpVersion($versionInfo)
{
    $edkVersionToUpdateTo = $versionInfo['version'];
    $edkVersionParsed = explode('.', $edkVersionToUpdateTo);
    if(count($edkVersionParsed) > 1)
    {
        // initialize the minimal required PHP version
        $minimalPhpVersion = PHP_VERSION;
        // do we want to update to EDK 4.3 or above?
        if(intval($edkVersionParsed[0]) >= 4 && intval($edkVersionParsed[1]) >= 3)
        {
            $minimalPhpVersion = '5.6';
        }
        
        // execute PHP version check
        if(!version_compare(PHP_VERSION, $minimalPhpVersion, '>='))
        {
            throw new Exception("EDK $edkVersionToUpdateTo requires PHP version $minimalPhpVersion or above! You are running PHP version ".PHP_VERSION.".");
        }
    }
}
