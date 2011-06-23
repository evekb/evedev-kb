<?php
/**
 * @package EDK
 */


include_once('file.cacher.php');

/**
 * @package EDK
 */
class UpdateXMLParser
{
	private $dom = null;
	private $domFileLocation = null;
	private $manualLoad = false;
	private $codeData;
	private $dbData;
	private $latestVersion;
	private $latestMessage;
	private $lowestDBVersion = '999.999.999';
	private $lowestCodeVersion = '999.999.999';

	//! Get the XML file from the update server.

	/*! If the cached xml file doesn't exist, retrieve it from the update server
	 * and validate it against the XSD validation fle.
	 */
	public function getXML()
	{
		$this->domFileLocation = KB_CACHEDIR . "/update/update.xml";
		if (@$this->retrieve())
		{
			$returnval = 1;
			if (@$this->validateSchema())
				$returnval = 2;
			else
				$returnval = 3;
		}
		else
			$returnval = 4;

		return $returnval;
	}

	private function retrieve()
	{
		$cacheFileName = KB_CACHEDIR . "/update/update.xml";
		$hostFileName = KB_UPDATE_URL . "/update.xml";
		$cachedTime = Config::get('upd_cacheTime');

		//unix time: if a day has passed, reload the xml from the web
		//if we don't have the file in the cache, then get it and place it there
		if (($cachedTime + 86400 <= date("U")) || !file_exists($cacheFileName) || $this->manualLoad)
		{
			$result = new FileCacher($hostFileName, $cacheFileName, true);
			if ($result != -99)
			{
				Config::set('upd_cacheTime', date("U"));
			}
			else
				return false;
		}
		return true;
	}

	private function validateSchema()
	{
		//the validation - should it sit on the google SVN or locally, I wonder...
		$xsdURL = KB_UPDATE_URL . "/update.xsd";
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->load($this->domFileLocation);
		//check if cURL exists, else use fsocket open
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $xsdURL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else
		{
			$file = fopen($xsdURL, 'r');
			if (!$file)
			{
				$content = "";
			} else
			{
				$content = stream_get_contents($file);
				fclose($file);
			}
		}
		if (!$content)
		{
			trigger_error("XSD could not be retrieved", E_USER_WARNING);
			return false;
		} else
		{
			file_put_contents(KB_CACHEDIR . "/update/update.xsd", $content);
		}

		if ($this->dom->schemaValidate(KB_CACHEDIR . "/update/update.xsd"))
		{
			return true;
		}
		else
			return false;
	}

	//! Fills a few arrays with the pertinant information  contained in the update XML file.

	/*! The resulting data structures are return via getter functions.
	 */
	public function retrieveData()
	{
		$elements = $this->dom->getElementsByTagName("codeVersion");
		$this->latestVersion[0] = $elements->item(0)->getAttribute("latest");

		$k = 0;
		foreach ($elements as $element)
		{
			$parameters = $elements->item($k)->getElementsByTagName('message');
			$this->latestMessage[0] = $parameters->item(0)->nodeValue;

			$upgrades = $elements->item($k)->getElementsByTagName('upgrades');

			$i = 0;
			foreach ($upgrades as $upgs)
			{
				$upgrade = $upgrades->item($i)->getElementsByTagName('upgrade');

				$j = 0;
				foreach ($upgrade as $upg)
				{
					$urlTag = $upgrade->item($j)->getElementsByTagName('url');
					$descriptionTag = $upgrade->item($j)->getElementsByTagName('description');

					$this->codeData[$i][$j]['version'] = $upgrade->item($j)->getAttribute('version');
					$this->codeData[$i][$j]['svnrev'] = $upgrade->item($j)->getAttribute('svnrev');
					$this->codeData[$i][$j]['hash'] = strtolower($upgrade->item($j)->getAttribute('hash'));
					$this->codeData[$i][$j]['url'] = $urlTag->item(0)->nodeValue;
					$this->codeData[$i][$j]['desc'] = $descriptionTag->item(0)->nodeValue;

					if ($this->lowestCodeVersion >= $this->codeData[$i][$j]['version'])
					{
						$this->lowestCodeVersion = $this->codeData[$i][$j]['version'];
					}
					$j++;
				}
				$i++;
			}
			$k++;
		}
		//basically repeat again for the db version check code
		$elements = $this->dom->getElementsByTagName("dbVersion");

		$this->latestVersion[1] = $elements->item(0)->getAttribute("latest");
		$k = 0;
		foreach ($elements as $element)
		{
			$parameters = $elements->item($k)->getElementsByTagName('message');
			$this->latestMessage[1] = $parameters->item(0)->nodeValue;

			$upgrades = $elements->item($k)->getElementsByTagName('upgrades');

			$i = 0;
			foreach ($upgrades as $upgs)
			{
				$upgrade = $upgrades->item($i)->getElementsByTagName('upgrade');

				$j = 0;
				foreach ($upgrade as $upg)
				{
					$urlTag = $upgrade->item($j)->getElementsByTagName('url');
					$descriptionTag = $upgrade->item($j)->getElementsByTagName('description');

					$this->dbData[$i][$j]['version'] = $upgrade->item($j)->getAttribute('version');
					$this->dbData[$i][$j]['hash'] = strtolower($upgrade->item($j)->getAttribute('hash'));
					$this->dbData[$i][$j]['url'] = $urlTag->item(0)->nodeValue;
					$this->dbData[$i][$j]['desc'] = $descriptionTag->item(0)->nodeValue;

					if ($this->lowestDBVersion >= $this->dbData[$i][$j]['version'])
					{
						$this->lowestDBVersion = $this->dbData[$i][$j]['version'];
					}
					$j++;
				}
				$i++;
			}
			$k++;
		}
	}

	//! return the entire code update structure as an array.
	public function getCodeInfo()
	{
		return $this->codeData[0];
	}

	//! return the entire database update structure as an array.
	public function getDBInfo()
	{
		return $this->dbData[0];
	}

	/*! each update type has a message associated with it for general type information.
	 * This function returns the one relating to the code updates.
	 */
	public function getLatestCodeMessage()
	{
		return $this->latestMessage[0];
	}

	/*! each update type has a message associated with it for general type information.
	 * This function returns the one relating to the database.
	 */
	public function getLatestDBMessage()
	{
		return $this->latestMessage[1];
	}

	public function getLatestCodeVersion()
	{
		return $this->latestVersion[0];
	}

	public function getLatestDBVersion()
	{
		return $this->latestVersion[1];
	}

	/*! Lowest version numbers are run first in the update queue
	 */
	public function getLowestDBVersion()
	{
		return $this->lowestDBVersion;
	}

	/*! Lowest version numbers are run first in the update queue
	 */
	public function getLowestCodeVersion()
	{
		return $this->lowestCodeVersion;
	}

	public function setManualLoad($manual = true)
	{
		$this->manualLoad = $manual;
	}
}
