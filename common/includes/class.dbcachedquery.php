<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * mysqli file-cached query class. Manages SQL queries to a MySQL DB using mysqli.
 * @package EDK
 */
class DBCachedQuery extends DBBaseQuery
{
	/**
	 * @var float
	 * this is the minimum runtime a query has to run to be
	 * eligible for caching in seconds
	 */
	protected static $minruntime = 0.1;
	/** @var float */
	protected static $maxmem = null;
	/**
	 * @var integer
	 * maximum size of a cached result set (512kB)
	 */
	protected static $maxcachesize = 524288;
	/** @var string */
	protected static $location = "SQL";
	/** @var integer */
	protected static $maxage = 10800;

	/** @var array */
	protected $cache = array();
	/** @var array */
	protected $usedtables = array();
	/** @var boolean */
	protected $cached = false;
	/** @var boolean */
	protected $nocache = false;
	/** @var string */
	protected $sql = '';
	/** @var integer */
	protected $mtime = 0;
	/**
	 * @var integer
	 * The current row of the cached result.
	 */
	protected $currrow = 0;
	/** @var MySQLi_Result|boolean */
	protected $resid = null;
	/** @var CacheHandlerHashed */
	protected static $cachehandler = null;
	/** @var array */
	protected static $baseTables = null;

	/**
	 * Set up a mysqli cached query object with default values.
	 *
	 * @param boolean $nocache true to retrieve results directly from the db.
	 */
	function DBCachedQuery($nocache = false)
	{
		$this->nocache = $nocache;

		self::$cachehandler = new CacheHandlerHashed();

		if(is_null(self::$maxmem))
		{
			$tmp = @ini_get('memory_limit');
			$tmp = @str_replace('M', '000000', $tmp) * 0.8;
			self::$maxmem = @intval(str_replace('G', '000000000', $tmp) * 0.8);
			if(!self::$maxmem) self::$maxmem = 128000000;
		}
	}
	/**
	 * Check if this query has been cached and the cache valid.
	 *
     * @return boolean true if this query has been cached and the cache is valid.
	*/
	protected function checkCache()
	{
		// only cache selects
		// we don't use select ... into so there is no problem
		$this->sql = str_replace(array("\r\n", "\n"), ' ', $this->sql);
		if (strtolower(substr($this->sql, 0, 6)) != 'select' && strtolower(substr($this->sql, 0, 4)) != 'show')
		{
			// this is no select, update the table
			self::markAffectedTables($this->sql);
			return false;
		}

		if($this->nocache) return false;
		if (self::$cachehandler->exists($this->sql, self::$location))
		{
			$this->mtime = self::$cachehandler->age($this->sql, self::$location);
			if ($this->mtime > self::$maxage ) return false;
			if ($this->isCacheValid()) return true;
		}
		return false;
	}

	/**
	 * Extract all tables affected by a database modification.
	 */
	protected function parseSQL($sql)
	{
		// Check list of tables daily.
		$daily = 86400;
		if(is_null(self::$baseTables)
			&& ( self::$cachehandler->age('SHOW TABLES', self::$location) > $daily
				|| !self::$cachehandler->exists('SHOW TABLES', self::$location)))
		{
			// we have no valid cache so open the connection and run the query
			if(is_null(self::$dbconn))self::$dbconn = new DBConnection();

			$t1 = microtime(true);

			$resid = mysqli_query(self::$dbconn->id(), 'SHOW TABLES');

			if (!$resid || self::$dbconn->id()->errno)
			{
				// Clear the cache to prevent errors spreading.
				DBDebug::killCache();
				if(defined('KB_PROFILE'))
				{
					DBDebug::recordError("Database error: ".self::$dbconn->id()->error);
					DBDebug::recordError("SQL: ".$this->sql);
				}
				trigger_error("SQL error (".self::$dbconn->id()->error, E_USER_WARNING);

				if (DB_HALTONERROR === true)
				{
					echo "Database error: ".self::$dbconn->id()->error."<br/>";
					echo "SQL: ".$this->sql."<br/>";
					exit;
				}
				else
				{
					return false;
				}
			}

			$this->exectime = microtime(true) - $t1;
			self::$totalexectime += $this->exectime;

			if(defined('KB_PROFILE')) DBDebug::profile($sql, $this->exectime);

			$this->queryCount(true);

			$bsize = 0;
			while ($row = $resid->fetch_assoc())
			{
				$table = strtolower(array_shift($row));
				self::$baseTables[$table] = $table;
			}
			// write data to cache
			self::$cachehandler->put('SHOW TABLES', self::$baseTables, self::$location, $daily);
		}
		else if(is_null(self::$baseTables)) self::$baseTables = self::$cachehandler->get('SHOW TABLES', self::$location);


		// gets all involved tables for a select statement
		$sql = strtolower($sql).' ';

		$regex = '/'.implode('|', self::$baseTables).'/';
		$matches = array();
		if(!preg_match_all($regex, $sql, $matches)) $this->usedtables = array();
		else $this->usedtables = $matches[0];

		return '';
	}
	/**
	 * Check if the cached query is valid.
	 *
	 * Determines whether the tables used by a query have been modified
     * since the query was cached.
	 *
	 * @return boolean
	 */
	protected function isCacheValid()
	{
		// check if cachefiles are still valid
		$this->usedtables = array();
		// first, we need to get all involved tables
		$this->parseSQL($this->sql);

		foreach ($this->usedtables as $table)
		{
			$file = 'qcache_tbl_'.trim($table);

			if (self::$cachehandler->exists($file, self::$location))
			{
				// if one of the tables is outdated, the query is outdated
				$age = self::$cachehandler->age($file, self::$location);

				if ($this->mtime >= $age) return false;
			}
		}
		return true;
	}
	/**
	 * Marks all tables affected by a database modification
	 */
	public static function markAffectedTables($sql = null)
	{
		if(is_null($sql)) return true;
		// this function invalidates cache files for touched tables
		$text = trim(strtolower($sql));
		$text = str_replace(array('ignore','`', "\r\n", "\n"), '', $text);
		$text = str_replace('(', ' (', $text);
		$ta = preg_split('/\s/', $text, 0, PREG_SPLIT_NO_EMPTY);

		// check for sql keywords and get the table from the appropriate position
		$tables = array();
		if ($ta[0] == 'update')
		{
			$tables[] = $ta[1];
		}
		elseif ($ta[0] == 'insert')
		{
			$tables[] = $ta[2];
		}
		elseif ($ta[0] == 'replace')
		{
			$tables[] = $ta[2];
		}
		elseif ($ta[0] == 'delete')
		{
			$tables[] = $ta[2];
		}elseif ($ta[0] == 'drop')
		{
			$tables[] = $ta[2];
		}
		elseif ($ta[0] == 'alter')
		{
			if ($ta[1] == 'ignore') $tables[] = $ta[3];
			else $tables[] = $ta[2];
		}
		elseif ($ta[0] == 'create')
		{
			return false;
		}
		elseif ($ta[0] == 'truncate')
		{
			if($ta[1] == 'table') $tables[] = $ta[2];
			else $tables[] = $ta[1];
		}
		elseif ($ta[0] == 'lock')
		{
			return false;
		}
		elseif ($ta[0] == 'unlock')
		{
			return false;
		}
		elseif ($ta[0] == 'set')
		{
			return false;
		}
		else
		{
			trigger_error('No suitable handler for query found. "'.$ta[0].'"',E_USER_NOTICE);
			return false;
		}

		foreach ($tables as $table)
		{
			self::$cachehandler->put('qcache_tbl_'.$table, time(), self::$location, 0);
		}
	}
	/**
	 * Generate the query cache.
	 *
	 * Serialise a query and write to file.
	 *
	 * @return boolean
	 */
	protected function genCache()
	{

		// this function fetches all rows and writes the data into a textfile
		// don't attemp to cache updates!
		if (strtolower(substr($this->sql, 0, 6)) != 'select'
			&& strtolower(substr($this->sql, 0, 4)) != 'show')
		{
			return false;
		}

		$bsize = 0;
		while ($row = $this->getRow())
		{
			$this->cache[] = $row;

			// if the bytesize of the table exceeds the limit we'll abort
			// the cache generation and leave this query unbuffered
			// If we're running out of memory then run uncached.
			$bsize += strlen(join('', $row));
			if ($bsize > self::$maxcachesize || self::$maxmem < memory_get_usage())
			{
				unset($this->cache);
				$this->cache = array();
				$this->cached = false;
				$this->rewind();
				return false;
			}
		}

		// write data to cache
		self::$cachehandler->put($this->sql, $this->cache, self::$location, self::$maxage);

		$this->cached = true;
		$this->currrow = 0;
		$this->executed = true;
	}
	/**
	 * Read a cached query from file.
	 */
	protected function loadCache()
	{
		// loads the cachefile into the memory
		$this->cache = self::$cachehandler->get($this->sql, self::$location);

		$this->cached = true;
		$this->currrow = 0;
		$this->executed = true;
	}

	/**
	 * Execute an SQL string.
	 *
     * If DB_HALTONERROR is set then this will exit on an error.
	 *
	 * @param string $sql
	 * @return boolean false on error or true if successful.
	 */
	function execute($sql)
	{
		$t1 = microtime(true);

		$this->sql = trim($sql);
		unset($this->cache);
		$this->cache = array();
		$this->cached = false;

		if ($this->checkCache())
		{
			$this->loadCache();
			$this->queryCachedCount(true);
			$this->exectime = microtime(true) - $t1;
			self::$totalexectime += $this->exectime;
			return true;
		}
		// we have no valid cache so open the connection and run the query
		if(is_null(self::$dbconn))self::$dbconn = new DBConnection();

		$this->resid = mysqli_query(self::$dbconn->id(), $this->sql);

		if (!$this->resid || self::$dbconn->id()->errno)
		{
			// Clear the cache to prevent errors spreading.
			DBDebug::killCache();
			if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".self::$dbconn->id()->error);
				DBDebug::recordError("SQL: ".$this->sql);
			}
			if (defined('DB_HALTONERROR') && DB_HALTONERROR)
			{
				echo "Database error: ".self::$dbconn->id()->error."<br />";
				echo "SQL: " . $this->sql . "<br />";
				trigger_error("SQL error (".self::$dbconn->id()->error, E_USER_ERROR);
				exit;
			}
			else
			{
				trigger_error("SQL error (".self::$dbconn->id()->error, E_USER_WARNING);
				return false;
			}
		}

		$this->exectime = microtime(true) - $t1;
		self::$totalexectime += $this->exectime;
		$this->executed = true;

		if(defined('KB_PROFILE')) DBDebug::profile($sql, $this->exectime);

		// if the query was too slow we'll fetch all rows and run it cached
		if ($this->exectime > self::$minruntime)
		{

			$this->genCache();
		}

		$this->queryCount(true);
		return true;
	}

	/**
	 * Return the number of rows returned by the last query.
	 *
	 * @return integer|boolean
	 */
	function recordCount()
	{
		if ($this->cached)
		{
			return count($this->cache);
		}
		elseif ($this->resid)
		{
			return $this->resid->num_rows;
		}
		return false;
	}

	/**
	 * Return the next row of results from the last query.
	 *
	 * @return array
	 */
	function getRow()
	{
		if ($this->cached)
		{
			if (!isset($this->cache[$this->currrow]))
			{
				return false;
			}
			// return the current row and increase the pointer by one
			return $this->cache[$this->currrow++];
		}
		if ($this->resid)
		{
			return $this->resid->fetch_assoc();
		}
		return false;
	}

	/**
	 * Reset list of results to return the first row from the last query.
	 */
	function rewind()
	{
		if ($this->cached)
		{
			$this->currrow = 0;
		}
		if(!is_null($this->resid)) {
			@mysqli_data_seek($this->resid, 0);
		}
	}

	/**
	 * Return the most recent error message for the DB connection.
	 */
	function getErrorMsg()
	{
		$msg = $this->sql."<br>";
		$msg .= "Query failed. ".mysqli_error(self::$dbconn->id());

		return $msg;
	}

	/**
	 * Set the autocommit status.
	 *
	 * The default of true commits after every query.
     * If set to false the queries will not be commited until autocommit is set
     * to true.
     *  @param boolean$commit The new autocommit status.
     *  @return boolean true on success and false on failure.
	*/
	function autocommit($commit = true)
	{
		if(defined('KB_PROFILE') && KB_PROFILE == 3)
		{
			if(!$commit) DBDebug::recordError("Transaction started.");
			else DBDebug::recordError("Transaction ended.");
		}

		if(!self::$dbconn) self::$dbconn = new DBConnection();
		return self::$dbconn->id()->autocommit($commit);
	}

	/**
	 * Rollback all queries in the current transaction.
	 *
	 * @return boolean true on success.
	 */
	function rollback()
	{
		// if there's no connection to the db then there's nothing to roll back
		if(!self::$dbconn) {
			return true;
		}
		return self::$dbconn->id()->rollback();
	}
}
