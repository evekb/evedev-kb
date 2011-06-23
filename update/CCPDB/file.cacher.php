<?php 
/**
 * @package EDK
 */
class FileCacher
{
    /** Caches a file to a selected folder/filename pair ($cacheFileName) from
     * a given source url ($hostFileName).
     * $updateXML specifies that if the file we want to cache is our update definition file
     * then it must make sure that the entire file is read during cache to ensure
     * consistency.
	 *
	 * @param string $hostFileName
	 * @param string $cacheFileName
	 * @param boolean $updateXML
	 * @return integer 
	 */
    function FileCacher($hostFileName, $cacheFileName, $updateXML = false)
    {	
	//check if cURL exists, else use fsocket open
	if (function_exists('curl_init'))
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $hostFileName);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $content = curl_exec($ch);
	    curl_close($ch);
	}
	else
	{
	    $file = fopen($hostFileName, 'r');
	    if (! $file)
	    {
		return -99;
	    }
	    $content = stream_get_contents($file);
	    fclose($file);
	}
	//if the cache dir doesn't exist, create it
	
	if (!file_exists(KB_CACHEDIR."/update"))
	{
	    mkdir(KB_CACHEDIR."/update", 0777);
	}

	if(strpos($content, "</EDK>") == false && $updateXML) {
	    return -99;
	}

	//save the file to the cache directory
	$xmlFile = fopen($cacheFileName, 'wb');
	fwrite($xmlFile, $content);
	fclose($xmlFile);		
    }
}
