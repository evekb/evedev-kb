<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * simple class for getting CREST references via URL
 */
class SimpleCrest
{
    
    /**
     * takes a CREST URL and gets the referenced object
     * @param string $url
     * @return Object a decoded json object
     */
    public static function getReferenceByUrl($url)
    {
        if($url == null)
        {
            return null;
        }
        
        // build header
        $header = "Accept-language: en\r\n";
        
        $opts = array(
            'http' => array(
                'method' => "GET",
                'timeout' => 15
            ),
            'socket' => array(
                'bindto' => "0.0.0.0:0"
            )
            
        );
        
        $opts['http']['header'] = $header;
        
        $context = stream_context_create($opts);

        if (false === ($data = @file_get_contents($url, false, $context))) {

            if (false === $headers = (@get_headers($url, 1))) {
                throw new \Exception("could not connect to api");
            }

            throw new \Exception("an error occured with the http request: ".$headers[0]);
        } else 
            {
            return json_decode($data);
        }
        
        return null;
    }
}

