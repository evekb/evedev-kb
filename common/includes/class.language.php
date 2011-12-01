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

	private static function init()
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

	/**
	 * Translate a given word to the base (english) equivalent.
	 * @staticvar string $pqry
	 * @staticvar string $id
	 * @staticvar string $text
	 * @param type $word
	 * @param type $table
	 * @param type $column
	 * @return type
	 */
	public static function translateToBase($word, $table = 'kb3_invtypes',
			$column='typeName')
	{
		static $pqry;
		static $keyID;
		static $text;
		static $tcID;

		$text=$word;
		$keyID=0;
		switch($table) {
			case "kb3_dgmattributetypes":
				$column = 'description';
				$tcID = 75;
				$type = 'attributeID';
				break;
			case "kb3_dgmeffects":
				if($column != 'displayName') {
					$column = 'description';
					$tcID = 75;
				} else {
					$tcID = 74;
				}
				$type = 'effectID';
				break;
			case "kb3_invtypes":
			default:
				$table = "kb3_invtypes";
				if($column != 'typeName') {
					$column = 'description';
					$tcID = 33;
				} else {
					$tcID = 8;
				}
				$type = 'typeID';
				break;

		}

		if(is_null(self::$lang)) {
			self::init();
		}
		if($language === null) {
			$language = config::get("cfg_language");
		} else {
			$language = preg_replace('/[^a-z-]/', '', strtolower($language));
		}

		if (!isset(self::$pqry)) {
			$pqry = new DBPreparedQuery();
			$sql = "SELECT keyID FROM trntranslations"
					." WHERE text LIKE ? AND tcID=? LIMIT 1";
			$keyID = 0;
			$pqry->prepare($sql);
			$pqry->bind_params('si', array($text, $tcID));
			$pqry->bind_result($keyID);
		}

		if ($pqry->execute() && $pqry->recordCount()) {
			$pqry->fetch();
		} else {
			return $word;
		}

		$qry = DBFactory::getDBQuery();
		$qry->execute("SELECT $column AS text FROM $table WHERE $type=$keyID");
		if (!$qry->recordCount()) {
			return $word;
		} else {
			$row = $qry->getRow();
			return $row['text'];
		}
	}
}

