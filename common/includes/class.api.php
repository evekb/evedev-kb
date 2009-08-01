<?php
/* 
 * Modified by Anne Sapyx [I.NET] 17-07-2008 
 */

class Api
{	
    function Api()
    {
        $this->apiroot_ = "api.eve-online.com";
    }
	
    function getCharId($name)
    {
        return getdata($this->apiroot_,"/eve/CharacterID.xml.aspx",$name);
    }
}
        
function getdata($apiroot, $target, $name)
{
    $fp = fsockopen($apiroot, 80);

    if (!$fp)
    {
        echo "Could not connect to API URL<br>";
    } else {
        // make the Namelist
        $list="names=".str_replace(' ', '%20', $name);
            
        // request the xml
        fputs ($fp, "POST $target HTTP/1.0\r\n");
        fputs ($fp, "Host: $apiroot\r\n");
        fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        fputs ($fp, "User-Agent: EDNKillboard\r\n");
        fputs ($fp, "Content-Length: " . strlen($list) . "\r\n");
        fputs ($fp, "Connection: close\r\n\r\n");
        fputs ($fp, "$list\r\n");

        // retrieve contents
        $contents = "";
        while (!feof($fp))
            $contents .= fgets($fp);

        // close connection
        fclose($fp);

        // Retrieve Char ID
        $start = strpos($contents, "characterID=\"");
        if ($start !== FALSE)
            $contents = substr($contents, $start + strlen("characterID=\""));
        
        $start = strpos($contents, "\" xmlns:row=\"characterID\" />");
        if ($start !== FALSE)
            $contents = substr($contents, 0, (strlen(substr($contents, $start)))*(-1));
    } 
    return (int)$contents;
}
?>
