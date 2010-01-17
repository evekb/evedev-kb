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
	while(!feof($fp))
	{
	    $line = fgets($fp);
	    $qry = new DBNormalQuery(true);
	    $qry->execute($line);
	    $qry->queryCount(true);   
	}
	fclose($fp);
	return $qry->queryCount();
    }
}
?>
