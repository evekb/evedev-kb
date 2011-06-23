<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/pclzip/pclzip.lib.php');

/**
 * @package EDK
 */
class Zip
{
    private $archive;
    private $filename;
    private $errors;
    private $fileList;

    /**
	 * Basic zip file parser. It allows you to extract zip files, create
     * zips, but not expand existing zips.
     */
    function Zip($fileName)
    {
	$this->filename = $fileName;
    }

    /*
	 * Adds but one file to a zip's internal list
     */
    function addFile($fileName)
    {
	$this->fileList[] = $fileName;
    }

    /**
	 * Adds a list of files to the zip's index
     */
    function addFileArray($fileNames)
    {
	$this->fileList = $fileNames;
    }

    function createZip()
    {	//check that the files we're adding exist, else remove them
	if(count($this->fileList) == 0)
	    return false;
	foreach($this->fileList as $index => $oneFile)
	{
	    if(!file_exists($oneFile))
		unset($this->fileList[$index]);
	}

	//actually create the zip file
	$this->archive = new PclZip($this->filename);
	$this->errors = $this->archive->create($this->fileList);
	return true;
    }

    function extractZip($pathToExtractTo) {
	if(!isset($this->archive)) $this->archive = new PclZip($this->filename);
	$this->errors =$this->archive->extract(PCLZIP_OPT_PATH, $pathToExtractTo);
    }

    function extractFile($index) {
	if(!isset($this->archive)) $this->archive = new PclZip($this->filename);
	return $this->archive->extractByIndex($index, PCLZIP_OPT_EXTRACT_AS_STRING);
    }

    /**
	 * Errors are returned as textual messages.
     * This may get upgradedd to make use of proper codes.
     */
    function getErrors() {
	if($this->errors == 0)
	    return $this->archive->errorInfo(true);
	else return false;
    }

    /**
	 * Gets the file list from an existing zip.
     */
    function getFileList() {
	if(!isset($this->archive)) $this->archive = new PclZip($this->filename);
	$this->errors = $this->archive->listContent();
	return $this->errors;
    }
}
