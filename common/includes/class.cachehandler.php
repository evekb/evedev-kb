<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Cache handling methods.
 *
 * Contains methods to handle the killboard's cache directory.
 * @package EDK
 */
class CacheHandler
{
	/** @var string Internal root path. */
	protected static $internalroot = KB_CACHEDIR;
	/** @var string External root path. */
	protected static $externalroot = KB_CACHEDIR;
	/** @var array Cache of generated paths to avoid recalculation. */
	protected static $paths = array();
	/** @var string Default location in cache to store files. */
	protected static $defaultLocation = "store";
	/** @var integer Depth of subdirectories. */
	protected static $depth = 2;

	/**
	 * Add a file to the cache.
	 *
	 * @param string $filename String filename, starting from below cache dir.
	 * @param string $data String containing the data to store in the cache.
	 * @param string $location set a specific subdirectory of the cache to use.
	 *
	 * @return boolean true if successful, false if an error occurred.
	 */
	public static function put($filename, $data, $location = null)
	{
		return file_put_contents(self::$internalroot."/"
				.self::getPath($filename, $location, true), $data);
	}

	/**
	 * Get a file from the cache.
	 *
	 * @param string $filename Filename, starting from below cache dir.
	 * @param string $location Set a specific subdirectory of the cache to use.
	 *
	 * @return boolean true if successful, false if an error occurred.
	 */
	public static function get($filename, $location = null)
	{
		return @file_get_contents(self::$internalroot."/"
				.self::getPath($filename, $location, false));
	}

	/**
	 * Remove a cached file
	 *
	 * @param string $filename Name of cached file.
	 * @param string $location Optional location. Sets a specific subdirectory
	 * of the cache to use.
	 * @return boolean true on success, false on failure.
	 */
	public static function remove($filename, $location = null)
	{
		if (!self::exists($filename, $location)) {
			return true;
		}

		$dir = self::getPath($filename, $location, false);
		$path = self::$internalroot.'/'.$dir;

		if (!unlink($path)) {
			return false;
		}
		// Remove the cache directory holding the file if it is empty.
		self::removeDir($dir);
		return true;
	}

	/**
	 * Remove a cache directory if empty.
	 *
	 * @param string $dir String The directory to remove.
	 * @param boolean $parents Remove empty parent directory if true.
	 * @return boolean true on success, false on failure.
	 */
	protected static function removeDir($dir, $parents = true)
	{
		if (substr($dir, -1) != '/') {
			$dir .= '/';
		}
		$dirfiles = @scandir(self::$internalroot.'/'.$dir);
		if ($dirfiles === false) {
			return false;
		}
		if (count($dirfiles) > 2) {
			// Remove empty subdirectories
			foreach ($dirfiles as $fname) {
				if (substr($fname, 0, 1) != "."
						&& is_dir(self::$internalroot.'/'.$dir.$fname)) {
					self::removeDir($dir.$fname."/", false);
				}
			}
			// Is the directory empty now?
			$dirfiles = scandir(self::$internalroot.'/'.$dir);
			if (count($dirfiles) > 2) {
				return true;
			}
		}
		$dir = substr($dir, 0, -1);
		$pdir = substr($dir, 0, strrpos($dir, '/'));
		rmdir(self::$internalroot.'/'.$dir);
		if (!$parents) {
			return true;
		}

		if (empty($pdir)) {
			return true;
		} else {
			return self::removeDir($pdir);
		}
	}

	/**
	 * Remove all files in a cache directory older than the given time.
	 *
	 * @param string $dir The directory to remove files from.
	 * @param integer $hours The minimum age in hours of files to remove.
	 * @param boolean $removeDir Set true to remove empty directories.
	 *
	 * @return integer|false The count of files removed or false on error.
	 */
	public static function removeByAge($dir = null, $hours = 24, $removeDir = true)
	{
		if (is_null($dir)) {
			$dir = self::$defaultLocation;
		}
		if (!is_dir(self::$internalroot.'/'.$dir)) {
			return 0;
		}
		if (substr($dir, -1) != '/') {
			$dir .= '/';
		}
		$seconds = (int) $hours * 60 * 60;
		$del = 0;
		$files = @scandir(self::$internalroot.'/'.$dir);
		if (!$files) {
			return false;
		}

		foreach ($files as $num => $fname) {
			if (substr($fname, 0, 1) != ".") {
				if (is_dir(self::$internalroot.'/'.$dir.$fname)) {
					$del += self::removeByAge(
							$dir.$fname."/", $hours, $removeDir);
				} else if ((time() - filemtime(self::$internalroot.'/'
						.$dir.$fname)) > $seconds) {
					$del += (int)@unlink(self::$internalroot.'/'.$dir.$fname);
				}
			}
		}
		// Directories with files in are not deleted.
		if ($removeDir && substr_count($dir, '/') > 1) {
			@rmdir(self::$internalroot.'/'.$dir);
		}
		return $del;
	}

	/**
	 * Remove files in a cache directory to reduce total size to that given.
	 *
	 * @param string $dir The directory to remove files from.
	 * @param integer $maxsize The maximum size in Megabytes for the cache.
	 * @param boolean $bysize Whether to remove largest files first or oldest.
	 *
	 * @return integer|false The count of files removed or false on error.
	 */
	public static function removeBySize($dir, $maxsize = 0, $bysize = false)
	{
		if (!is_numeric($maxsize)) {
			if (substr($maxsize, -1) == 'k') {
				$maxsize = substr($maxsize, 0, strlen($maxsize) - 1);
				$maxsize = $maxsize * pow(2, 10);
			} else if (substr($maxsize, -1) == 'M') {
				$maxsize = substr($maxsize, 0, strlen($maxsize) - 1);
				$maxsize = $maxsize * pow(2, 20);
			} else if (substr($maxsize, -1) == 'G') {
				$maxsize = substr($maxsize, 0, strlen($maxsize) - 1);
				$maxsize = $maxsize * pow(2, 30);
			} else {
				return false;
			}
		} else {
			$maxsize = $maxsize * pow(2, 20);
		}
		$files = self::getFiles($dir);
		if ($bysize) {
			usort($files, array('CacheHandler', 'compareSize'));
		} else {
			usort($files, array('CacheHandler', 'compareAge'));
		}
		$cursize = 0;
		$delcount = 0;
		foreach ($files as $file) {
			$cursize += $file[2];
		}
		foreach ($files as $key => $file) {
			if ($cursize < $maxsize) {
				break;
			}
			if (unlink($file[1])) {
				$cursize -= $file[2];
				unset($files[$key]);
				$delcount++;
			}
		}
		self::removeDir($dir);
		return $delcount;
	}

	/**
	 * Remove files in a cache directory to reduce total file count to that given.
	 * Oldest files are removed first.
	 *
	 * @param string $dir The directory to remove files from.
	 * @param integer $maxsize The maximum count of files in the cache.
	 *
	 * @return integer|false The count of files removed or false on error.
	 */
	public static function removeByCount($dir, $maxsize = 0)
	{
		$maxsize = (int) $maxsize;

		$files = self::getFiles($dir);
		usort($files, array('CacheHandler', 'compareAge'));

		$cursize = 0;
		$delcount = 0;
		$cursize = count($files);
		
		foreach ($files as $key => $file) {
			if ($cursize < $maxsize) {
				break;
			}
			if (unlink($file[1])) {
				$cursize--;
				unset($files[$key]);
				$delcount++;
			}
		}
		self::removeDir($dir);
		return $delcount;
	}

	/**
	 * Return an array of all files under the given dir.
	 *
	 * @param string $dir Root directory to search in.
	 *
	 * @return array (age, name, size)
	 */
	private static function &getFiles($dir)
	{
		if (strpos($dir, '..')
				|| !is_dir(self::$internalroot.'/'.$dir)) {
			return array();
		}

		if (substr($dir, -1) != '/') {
			$dir .= '/';
		}
		$del = 0;
		$files = scandir(self::$internalroot.'/'.$dir);
		if (!$files) return false;
		$result = array();

		foreach ($files as $num => $fname) {
			if (substr($fname, 0, 1) != ".") {
				if (!is_dir(self::$internalroot.'/'.$dir.$fname)) {
					if (is_writeable(self::$internalroot.'/'.$dir.$fname))
							$result[] = array(filemtime(self::$internalroot.'/'.$dir.$fname), self::$internalroot.'/'.$dir.$fname, filesize(self::$internalroot.'/'.$dir.$fname));
				} else {
					$subResult = self::getFiles($dir.$fname."/");
					$result = array_merge($result, $subResult);
				}
			}
		}
		return $result;
	}

	/**
	 * Age comparison function for use with file array returned by getFiles.
	 *
	 * @param array $a (age, name, size)
	 * @param array $b (age, name, size)
	 * @return integer -1 if a is older, 0 for same age, +1 if b is older.
	 */
	private static function compareAge($a, $b)
	{
		if ($a[0] == $b[0]) {
			return 0;
		}
		return ($a[0] < $b[0]) ? -1 : 1;
	}

	/**
	 * Size comparison function for use with file array returned by getFiles.
	 *
	 * @param array $a (age, name, size)
	 * @param array $b (age, name, size)
	 * @return integer -1 if a is bigger, 0 for same age, +1 if b is bigger.
	 */
	private static function compareSize($a, $b)
	{
		if ($a[2] == $b[2]) {
			return 0;
		}
		return ($a[2] > $b[2]) ? -1 : 1;
	}

	/**
	 * Add subdirectories to the given depth and return the full cachefile path.
	 * @param string $filename Name of cached file.
	 * @param string $location Optional location. Sets a specific subdirectory
	 * of the cache to use.
	 * @param boolean $create Set true to create the path if it does not exist.
	 * @return string The full cachefile path.
	 */
	protected static function getPath($filename, $location = null, $create = false)
	{
		if (is_null($location)) {
			$location = self::$defaultLocation;
		}
		if (isset(self::$paths[$location.$filename])) {
			return self::$paths[$location.$filename];
		}

		$newfilename = str_replace(array("?", "[", "]", "=", "+", "<", ">",
			"|", ":", ";", "'", "*", ",", "/", "\\"), "", $filename);
		if ($newfilename == ".." || $newfilename == ".") {
			trigger_error("Invalid cache filename. Name given was: ".htmlentities($newfilename), E_USER_ERROR);
			die;
		}
		$newlocation = $location;

		if ($newlocation != self::$defaultLocation) {
			if (strpos($newlocation, "..") !== false
					|| substr($newlocation, 0, 1) == '/'
					|| substr($newlocation, 0, 1) == '\\') {
				trigger_error("Invalid cache path. Path given was: ".htmlentities($newlocation), E_USER_ERROR);
				die;
			} else {
				$newlocation = str_replace("\\", "/", $newlocation);
				$newlocation = str_replace(array("?", "[", "]", "=", "+", "<", ">", "|", ":", ";", "'", "*", ","), "", $newlocation);
				if ($newlocation == "") $newlocation = self::$defaultLocation;
			}
		}

		// Create subdirectories and left pad with 0s if length is too short.
		$depth = self::$depth;
		if (substr($newlocation, -1) != '/') {
			$newlocation .= '/';
		}
		if (strrpos($filename, ".") === false) {
			$length = 8; //2 * $depth + 2
		} else {
			$length = 8 + strlen($filename) - strrpos($filename, ".");
		}
		$newfilename = str_pad($newfilename, $length, "0", STR_PAD_LEFT);

		$nameslice = $newfilename;
		while ($depth > 0) {
			$newlocation .= substr($nameslice, 0, 2).'/';
			$nameslice = substr($nameslice, 2);
			$depth--;
		}
		if ($create) {
			if (!file_exists(self::$internalroot.'/'.$newlocation)) {
				// Race conditions can cause errors here but we don't care so ignore them.
				@mkdir(self::$internalroot.'/'.$newlocation, 0755, true);
			}
			self::$paths[$location.$filename] = $newlocation.$newfilename;
		}

		return $newlocation.$newfilename;
	}

	/**
	 * Return true if the file is in the cache.
	 * @param string $filename Name of cached file.
	 * @param string $location Optional location. Sets a specific subdirectory
	 * of the cache to use.
	 * @return boolean true if the file exists, false otherwise.
	 */
	public static function exists($filename, $location = null)
	{
		return file_exists(self::$internalroot.'/'
				.self::getPath($filename, $location, false));
	}

	/**
	 * Return the age of the given cache file.
	 * @param string $filename Name of cached file.
	 * @param string $location Optional location. Sets a specific subdirectory
	 * of the cache to use.
	 * @return integer|boolean the age of the file in seconds or false on error.
	 */
	public static function age($filename, $location = null)
	{
		$mtime = @filemtime(self::getPath($filename, $location, false));

		return $mtime ? time() - $mtime : false;
	}

	/**
	 * Get the internally accessible address of the cached file.
	 * @param string $filename Name of cached file.
	 * @param string $location Optional location. Sets a specific subdirectory
	 * of the cache to use.
	 * @return string The path to the given file.
	 */
	public static function getInternal($filename, $location = null)
	{
		return self::$internalroot.'/'
				.self::getPath($filename, $location, true);
	}

	/**
	 * Get the externally accessible address of the cached file.
	 * @param string $filename Name of cached file.
	 * @param string $location Optional location. Sets a specific subdirectory
	 * of the cache to use.
	 * @return string The external path to the given file.
	 */
	public static function getExternal($filename, $location = null)
	{
		return self::$externalroot.'/'
				.self::getPath($filename, $location, false);
	}

	/**
	 * Change the default cache directory used to make external links.
	 *
	 * Note that the safety of the path returned is up to the caller to verify.
	 *
	 * @param string $dir The new root path
	 * @return string The new root path as set.
	 */
	public static function setExternalPath($dir)
	{
		self::$externalroot = $dir;
		return self::$externalroot;
	}

	/**
	 * Change the default cache root directory
	 *
	 * @param string $dir valid path to a suitable cache directory.
	 * @return boolean true if the new path was valid, false if not.
	 */
	public static function setInternalPath($dir)
	{
		if (substr($dir, 0, 1) == '/'
				|| strpos($dir, '..') !== false
				|| !is_dir($dir)
				|| !is_writeable($dir)) {
			return false;
		} else {
			self::$internalroot = $dir;
			return true;
		}
	}

}
