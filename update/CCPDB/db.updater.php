<?php 
/**
 * This is a very basic sql parser, akin to the kind used in the installer. It
 * takes a gzipped sql file, and feeds the queries to the db functions.
 * @package EDK
 */
class DBUpdater
{
    private $fileName = '';
    private $working = true;
    
    function DBUpdater($pathToGZ = '')
    {
	if($pathToGZ == '')
	{
	    $this->working = false;
	    die('Nothing to extract');
	}
	else
	{
	    $this->fileName = $pathToGZ;
	}
    }

    function runQueries()
    {
	$fp = gzopen($this->fileName, 'r');
	$qry = DBFactory::getDBQuery(true);
	while(!feof($fp))
	{
	    $line = fgets($fp);
	    //empty() won't work because the variable is set, even if the content is NULL
	    if(strlen(trim($line)) == 0)  continue;
	    //explicetly check for false instead of 0 because 0 can be a valid location
	    if(stripos($line, '--') === false)
	    {
		$qry->execute($line);
		$qry->queryCount(true);
	    }
	}
	fclose($fp);
	return $qry->queryCount();
    }
}

