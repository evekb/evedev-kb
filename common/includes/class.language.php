<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Wrapper class to pick the correct language from config settings and return
 * a translation. English is the default language.
 *
 * @package EDK
 */
class Language {
	private static $lang = null;

	function init()
	{
		if(!config::get("cfg_language")) {
			config::set("cfg_language", "en");
		}
		@include_once("common/language/".config::get("cfg_language").".php");
		include_once("common/language/en.php");
		self::$lang = $language;
	}

	/**
	 * Translate a standard phrase.
	 * 
	 * @param string $phrase The phrase to translate
	 * @param string $language Optional parameter to select a specific language.
	 * @return string Translated phrase or original if not translation available
	 */
	public static function get($phrase = "", $language = null)
	{
		if(is_null(self::$lang)) {
			self::init();
		}

		if(!$phrase) {
			return "";
		} else if(isset($language) && isset(self::$lang[$language][$phrase])) {
			return self::$lang[config::get("cfg_language")][$phrase];
		} else if(isset(self::$lang[config::get("cfg_language")][$phrase])) {
			return self::$lang[config::get("cfg_language")][$phrase];
		} else if(isset(self::$lang["en"][$phrase])) {
			return self::$lang["en"][$phrase];
		} else {
			return $phrase;
		}
	}
}

