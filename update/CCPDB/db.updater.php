<?php 
//This is a very basic sql parser, akin to the kind used in the installer. It
//takes a gzipped sql file, and feeds the queries to the db functions.
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
    $qry = new DBQuery(true);
	while(!feof($fp))
	{
	    $line = fgets($fp);
		if(empty($line)) continue;
		if(stripos($line, '--') == 0) continue;
	    $qry->execute($line);
	    $qry->queryCount(true);   
	}
	fclose($fp);
	return $qry->queryCount();
    }
}

