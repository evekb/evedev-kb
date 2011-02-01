<?php
/*
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 */

// **********************************************************************************************************************************************
// ****************                                 API Skill Training Sheet - char/SkillInTraining.xml.aspx                     ****************
// **********************************************************************************************************************************************
class API_SkillInTraining
{

	function getSkillInTraining()
	{
		return $this->SkillInTraining_;
	}

	function getCurrentTQTime()
	{
		return $this->CurrentTQTime_;
	}

	function getTrainingEndTime()
	{
		return $this->TrainingEndTime_;
	}

	function getTrainingStartTime()
	{
		return $this->TrainingStartTime_;
	}

	function getTrainingTypeID()
	{
		return $this->TrainingTypeID_;
	}

	function getTrainingStartSP()
	{
		return $this->TrainingStartSP_;
	}

	function getTrainingDestinationSP()
	{
		return $this->TrainingDestinationSP_;
	}

	function getTrainingToLevel()
	{
		return $this->TrainingToLevel_;
	}

	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function getSPTrained()
	{
		return $this->SPTrained_;
	}

	function getTrainingTimeRemaining()
	{
		return $this->TrainingTimeRemaining_;
	}

	function fetchXML()
	{
		// is a player feed - take details from logged in user
		if (user::get('usr_pilot_id'))
    	{
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();

			$this->CharName_ = $usersname;

			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = DBFactory::getDBQuery();;
			$qry->execute($sql);
   		 	$row = $qry->getRow();

    		$pilot_id = $row['plt_id'];
    		$API_charID = $row['plt_externalid'];

    		if ( $pilot_id == 0 )
			{
        		return "Something went wrong with finiding pilots external ID<br>";
    		}

			$newsql = 'SELECT userID , apiKey FROM kb3_api_user WHERE charID = "' . $API_charID . '"';
			$qry->execute($newsql);
    		$userrow = $qry->getRow();

			$API_userID = $userrow['userID'];
			$API_apiKey = $userrow['apiKey'];

			$myKeyString = "userID=" . $API_userID . "&apiKey=" . $API_apiKey . "&characterID=" . $API_charID;

			$data = $this->loaddata($myKeyString);
		} else {
			return "You are not logged in.";
		}

        $xml_parser = xml_parser_create();
        xml_set_object ( $xml_parser, $this );
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler ( $xml_parser, 'characterData' );

        if (!xml_parse($xml_parser, $data, true))
            return "<i>Error getting XML data from ".API_SERVER."/SkillInTraining.xml.aspx  </i><br><br>";

        xml_parser_free($xml_parser);

		// Calculate Time Training remaining and amount of SP Done
		if ($this->SkillInTraining_ == 1)
		{
			$trainingleft = $this->TrainingEndTime_;

    		$now       = time();
			$gmmktime  = gmmktime();
    		$finaltime = $gmmktime - $now;

    		$year   = (int)substr($trainingleft, 0, 4);
    		$month  = (int)substr($trainingleft, 5, 2);
    		$day    = (int)substr($trainingleft, 8, 2);
    		$hour   = (int)substr($trainingleft, 11, 2) + (($finaltime > 0) ? floor($finaltime / 60 / 60) : 0); //2007-06-22 16:47:50
    		$minute = (int)substr($trainingleft, 14, 2);
    		$second = (int)substr($trainingleft, 17, 2);

    		$difference = gmmktime($hour, $minute, $second, $month, $day, $year) - $now;
			$timedone = $difference;
    		if ($difference >= 1)
			{
        		$days = floor($difference/86400);
        		$difference = $difference - ($days*86400);
        		$hours = floor($difference/3600);
        		$difference = $difference - ($hours*3600);
        		$minutes = floor($difference/60);
        		$difference = $difference - ($minutes*60);
        		$seconds = $difference;
				$this->TrainingTimeRemaining_ = "$days Days, $hours Hours, $minutes Minutes and $seconds Seconds.";
    		} else {
        		$this->TrainingTimeRemaining_ = "Done !";
    		}

			// Calculate SP done by using the ratio gained from Time spent training so far.
    		$finaltime = strtotime($this->TrainingEndTime_) - strtotime($this->TrainingStartTime_); // in seconds
			$ratio =  1 - ($timedone / $finaltime);
			$this->SPTrained_ = ($this->TrainingDestinationSP_ - $this->TrainingStartSP_) * $ratio;
		}

		return $this->html; // should be empty, but keeping just incase - errors are returned by Text so worth looking anyway.
	}

	function startElement($parser, $name, $attribs)
    {
		// Nothing to do here
    }

    function endElement($parser, $name)
    {
		// Details
		if ($name == "SKILLINTRAINING")
			$this->SkillInTraining_ = $this->characterDataValue;
		if ($name == "CURRENTTQTIME")
			$this->CurrentTQTime_ = $this->characterDataValue;
		if ($name == "TRAININGENDTIME")
			$this->TrainingEndTime_ = $this->characterDataValue;
		if ($name == "TRAININGSTARTTIME")
			$this->TrainingStartTime_ = $this->characterDataValue;
		if ($name == "TRAININGTYPEID")
			$this->TrainingTypeID_ = $this->characterDataValue;
		if ($name == "TRAININGSTARTSP")
			$this->TrainingStartSP_ = $this->characterDataValue;
		if ($name == "TRAININGDESTINATIONSP")
			$this->TrainingDestinationSP_ = $this->characterDataValue;
		if ($name == "TRAININGTOLEVEL")
			$this->TrainingToLevel_ = $this->characterDataValue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $this->characterDataValue;
		if ($name == "CACHEDUNTIL")
		{
			$this->CachedUntil_ = $this->characterDataValue;
			//ApiCache::set('API_eve_RefTypes' , $this->characterDataValue);
			ApiCache::set( $this->CharName_ . '_SkillInTraining' , $this->characterDataValue);
		}
    }

    function characterData($parser, $data)
    {
		$this->characterDataValue = $data;
    }

	function loaddata($keystring)
    {
		$configvalue = $this->CharName_ . '_SkillInTraining';

		$CachedTime = ApiCache::get($configvalue);
		$UseCaching = config::get('API_UseCache');

        $url = "https://".API_SERVER."/char/SkillInTraining.xml.aspx" . $keystring;

        $path = '/char/SkillInTraining.xml.aspx';

		// API Caching system, If we're still under cachetime reuse the last XML, if not download the new one. Helps with Bug hunting and just better all round.
		if ($CachedTime == "")
    	{
        	$CachedTime = "2005-01-01 00:00:00"; // fake date to ensure that it runs first time.
    	}

		if (is_file(KB_CACHEDIR.'/api/'.$configvalue.'.xml'))
			$cacheexists = true;
		else
			$cacheexists = false;

		// if API_UseCache = 1 (off) then don't use cache
		if ((strtotime(gmdate("M d Y H:i:s")) - strtotime($CachedTime) > 0) || ($UseCaching == 1)  || !$cacheexists )
    	{
        	$fp = @fsockopen(API_SERVER, 80);

        	if (!$fp)
        	{
            	$this->Output_ .= "Could not connect to API URL";
        	} else {
           	 	// request the xml
            	fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
            	fputs ($fp, "Host: ".API_SERVER."\r\n");
            	fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            	fputs ($fp, "User-Agent: PHPApi\r\n");
            	fputs ($fp, "Content-Length: " . strlen($keystring) . "\r\n");
            	fputs ($fp, "Connection: close\r\n\r\n");
            	fputs ($fp, $keystring."\r\n");
				stream_set_timeout($fp, 10);

           	 	// retrieve contents
            	$contents = "";
            	while (!feof($fp))
            	{
                	$contents .= fgets($fp);
            	}

            	// close connection
            	fclose($fp);

            	$start = strpos($contents, "?>");
            	if ($start !== FALSE)
            	{
                	$contents = substr($contents, $start + strlen("\r\n\r\n"));
            	}

				// Save the file if we're caching (0 = true in Thunks world)
				if ($UseCaching == 0)
				{
					$file = fopen(KB_CACHEDIR.'/api/'.$configvalue.'.xml', 'w+');
        			fwrite($file, $contents);
       				fclose($file);
					@chmod(KB_CACHEDIR.'/api/'.$configvalue.'.xml',0666);
				}
        	}
		} else {
			// re-use cached XML
			if ($fp = @fopen(KB_CACHEDIR.'/api/'.$configvalue.'.xml', 'r')) {
    	    	$contents = fread($fp, filesize(KB_CACHEDIR.'/api/'.$configvalue.'.xml'));
        		fclose($fp);
    		} else {
				return "<i>error loading cached file ".$configvalue.".xml</i><br><br>";
    		}
		}
        return $contents;
    }
}
