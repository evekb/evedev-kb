<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

require_once 'class.dbbasequery.php';
require_once 'class.dbconnection.php';
require_once('class.dbdebug.php');
require_once('class.cachehandlerhashed.php');

//! mysqli file-cached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBCachedQuery extends DBBaseQuery
{
	// this is the minimum runtime a query has to run to be
	// eligible for caching in seconds
	protected static $minruntime = 0.1;
	protected static $maxmem = null;
	// maximum size of a cached result set (512kB)
	protected static $maxcachesize = 524288;
	protected static $location = "SQL";
	protected static $maxage = 10800;

	protected $cache = array();
	protected $usedtables = array();
	protected $cached = false;
	protected $nocache = false;
	protected $sql = '';
	protected $mtime = 0;
	protected $currrow = 0;
	protected $resid = null;
	protected static $cachehandler = null;
	protected static $baseTables = null;

	//! Set up a mysqli cached query object with default values.
	function DBCachedQuery($nocache = false)
	{
		$this->nocache = $nocache;

		self::$cachehandler = new CacheHandlerHashed();

		if(is_null(self::$maxmem))
		{
			self::$maxmem = @ini_get('memory_limit');
			self::$maxmem = @str_replace('M', '000000', self::$maxmem) * 0.8;
			self::$maxmem = @intval(str_replace('M', '000000000', self::$maxmem) * 0.8);
			if(!self::$maxmem) self::$maxmem = 128000000;
		}
	}
	//! Check if this query has been cached and the cache valid.

	/*
     * \return true if this query has been cached and the cache is valid.
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

	//! Extract all tables affected by a database modification.

	//! The resulting list is set internally to this object.
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
//		// check inside brackets first.
//		$pos = 0;
//		$from = 0;
//		$bracketpos1 = -1;
//		$bracketpos2 = -1;
//		$countbr = 0;
//		$count = 1;
//
//		while($pos < strlen($sql))
//		{
//			if($sql[$pos] == '(') $bracketpos1 = $pos;
//			elseif($sql[$pos] == ')')
//			{
//				if($bracketpos1 == -1) break;
//				$bracketpos2 = $pos;
//				$from = strpos($sql, "from", $bracketpos1);
//				if($from > $bracketpos1 && $from < $bracketpos2)
//					$sql = substr_replace($sql, $this->parseSQL(substr($sql,$bracketpos1+1, $bracketpos2 - $bracketpos1 - 1)), $bracketpos1, $bracketpos2 - $bracketpos1 + 1);
//				else $sql = substr_replace($sql, '', $bracketpos1, $bracketpos2 - $bracketpos1 + 1);
//
//				$pos = 0;
//				$from = 0;
//				$bracketpos1 = -1;
//				$bracketpos2 = -1;
//				continue;
//			}
//			$pos++;
//		}
//
//		// we try to get the text from 'from' to 'where' because all involved
//		// tables are declared in that part
//		$from = strpos($sql, 'from')+5;
//		if($from > strlen($sql)) return '';
//		// if there is a subquery then recurse into the string between the next
//		// from and first unclosed ) or where
//		$from2 = strpos($sql, 'from', $from);
//		if($from2) $sql = substr_replace($sql, $this->parseSQL(substr($sql,$from2 - 1)), $from2);
//
//		if (!$to = strpos($sql, 'where'))
//		{
//			$to = strlen($sql);
//		}
//
//		$parse = substr($sql, $from, $to-$from);
//		$parse = str_replace('`', ' ', $parse);
//		$parse = trim($parse);
//		if(!$parse) return '';
//
//		$tables = array();
//		if (strpos($parse, ',') !== false)
//		{
//			// , is a synonym for join so we'll replace them
//			$parse = str_replace(',', ' join ', $parse);
//		}
//
//		if (strpos($parse, 'join'))
//		{
//			// if this query is a join we parse it with regexp to get all tables
//			$parse = 'join '.$parse;
//			preg_match_all('/join\s+([^ ]+)\s/', $parse, $match);
//			$this->usedtables = $this->usedtables + $match[1];
//		}
//		else
//		{
//			// no join so it is hopefully a simple table select
//			$this->usedtables[] = preg_replace('/\s.*/', '', $parse);
//		}
//		return substr_replace($sql, '', $from, $to-$from);
	}
	//! Check if the cached query is valid.

	/*! Determines whether the tables used by a query have been modified
     * since the query was cached
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
	//! Marks all tables affected by a database modification
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
	//! Generate the query cache.

	//! Serialise a query and write to file.
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
	//! Read a cached query from file.
	protected function loadCache()
	{
		// loads the cachefile into the memory
		$this->cache = self::$cachehandler->get($this->sql, self::$location);

		$this->cached = true;
		$this->currrow = 0;
		$this->executed = true;
	}

	//! Execute an SQL string.

	/*
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
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

		$this->resid = mysqli_query(self::$dbconn->id(), $sql);

		if (!$this->resid || self::$dbconn->id()->errno)
		{
			// Clear the cache to prevent errors spreading.
			DBDebug::killCache();
			if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".self::$dbconn->id()->error);
				DBDebug::recordError("SQL: ".$this->sql);
			}
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

	//! Return the number of rows returned by the last query.
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

	//! Return the next row of results from the last query.
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

	//! Reset list of results to return the first row from the last query.
	function rewind()
	{
		if ($this->cached)
		{
			$this->currrow = 0;
		}
		if(!is_null($this->resid)) @mysqli_data_seek($this->resid, 0);
	}

	//! Return the most recent error message for the DB connection.
	function getErrorMsg()
	{
		$msg = $this->sql."<br>";
		$msg .= "Query failed. ".mysqli_error(self::$dbconn->id());

		return $msg;
	}

	//! Set the autocommit status.

	/*! The default of true commits after every query.
     * If set to false the queries will not be commited until autocommit is set
     * to true.
     *  \param $commit The new autocommit status.
     *  \return true on success and false on failure.
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

	//! Rollback all queries in the current transaction.
	function rollback()
	{
		// if there's no connection to the db then there's nothing to roll back
		if(!self::$dbconn) return true;
		return self::$dbconn->id()->rollback();
	}
}
