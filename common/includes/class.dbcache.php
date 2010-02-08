<?php
require_once 'class.dbbasequery.php';
require_once 'class.dbconnection.php';
require_once('class.dbdebug.php');

//! mysqli file-cached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBCachedQuery extends DBBaseQuery
{
	//! Set up a mysqli cached query object with default values.
	function DBCachedQuery($nocache = false)
	{
		$this->executed_ = false;
		$this->_cache = array();
		$this->_cached = false;
		$this->_nocache = $nocache;

		// this is the minimum runtime a query has to run to be
		// eligible for caching in seconds
		$this->_minruntime = 0.1;

		// maximum size of a cached result set (512kB)
		$this->_maxcachesize = 524288;
		$this->d = true;
		$this->maxmem = preg_replace('/M/', '000000', ini_get('memory_limit')) * 0.8;
		if(!$this->maxmem) $this->maxmem = 128000000;
	}
	//! Check if this query has been cached and the cache valid.

	/*
     * \return true if this query has been cached and the cache is valid.
	*/
	private function checkCache()
	{
		// only cache selects
		// we don't use select ... into so there is no problem
		$this->_sql = str_replace(array("\r\n", "\n"), ' ', $this->_sql);
		if (strtolower(substr($this->_sql, 0, 6)) != 'select' && strtolower(substr($this->_sql, 0, 4)) != 'show')
		{
			// this is no select, update the table
			$this->markAffectedTables();
			return false;
		}

		if($this->_nocache) return false;
		if (file_exists(KB_QUERYCACHEDIR.'/qcache_qry_'.$this->_hash))
		{
			$this->_mtime = filemtime(KB_QUERYCACHEDIR.'/qcache_qry_'.$this->_hash);
			/// Remove cached queries more than an hour old.
			if (time() - $this->_mtime > 3600 )
			{
				unlink(KB_QUERYCACHEDIR.'/qcache_qry_'.$this->_hash);
				return false;
			}
			if ($this->isCacheValid())
			{
				return true;
			}
		}

		return false;
	}

	//! Extract all tables affected by a database modification.

	//! The resulting list is set internally to this object.
	private function parseSQL($sql)
	{
		// gets all involved tables for a select statement
		$sql = strtolower($sql).' ';

		// we try to get the text from 'from' to 'where' because all involved
		// tables are declared in that part
		$from = strpos($sql, 'from')+5;
		if($from > strlen($sql)) return '';
		// if there is a subquery then recurse into the string between the next
		// from and first unclosed ) or where
		$from2 = strpos($sql, 'from', $from);
		if($from2) $sql = substr_replace($sql, $this->parseSQL(substr($sql,$from2 - 1)), $from2);

		if (!$to = strpos($sql, 'where'))
		{
			$to = strlen($sql);
		}
		// Find an unmatched ')'.
		$bracketpos = $from;
		$countbr = 0;
		while($bracketpos < $to && $countbr >=0)
		{
			$bracketpos++;
			if($sql[$bracketpos] == '(') $countbr++;
			elseif($sql[$bracketpos] == ')') $countbr++;
		}
		$to = $bracketpos;

		$parse = trim(substr($sql, $from, $to-$from));
		$parse = str_replace('`', ' ', $parse);

		$tables = array();
		if (strpos($parse, ',') !== false)
		{
			// , is a synonym for join so we'll replace them
			$parse = str_replace(',', ' join ', $parse);
		}

		if (strpos($parse, 'join'))
		{
			// if this query is a join we parse it with regexp to get all tables
			$parse = 'join '.$parse;
			preg_match_all('/join\s+([^ ]+)\s/', $parse, $match);
			$this->_usedtables = $this->_usedtables + $match[1];
		}
		else
		{
			// no join so it is hopefully a simple table select
			$this->_usedtables[] = preg_replace('/\s.*/', '', $parse);
		}
		return substr_replace($sql, '', $from, $to-$from);
	}
	//! Check if the cached query is valid.

	/*! Determines whether the tables used by a query have been modified
     * since the query was cached
	*/
	private function isCacheValid()
	{
		// check if cachefiles are still valid
		$this->_usedtables = array();
		// first, we need to get all involved tables
		$this->parseSQL($this->_sql);

		foreach ($this->_usedtables as $table)
		{
			$file = KB_QUERYCACHEDIR.'/qcache_tbl_'.trim($table);
			if (file_exists($file))
			{
				// if one of the tables is outdated, the query is outdated
				if ($this->_mtime <= filemtime($file))
				{
					return false;
				}
			}
		}
		return true;
	}
	//! Marks all tables affected by a database modification
	private function markAffectedTables()
	{
		// this function invalidates cache files for touched tables
		$text = trim(strtolower($this->_sql));
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
			$file = KB_QUERYCACHEDIR.'/qcache_tbl_'.$table;
			@touch($file);
		}
		// refresh php's filestatcache so we dont get wrong timestamps on changed files
		clearstatcache();
	}
	//! Generate the query cache.

	//! Serialise a query and write to file.
	private function genCache()
	{
		// this function fetches all rows and writes the data into a textfile
		// don't attemp to cache updates!
		if (strtolower(substr($this->_sql, 0, 6)) != 'select' && strtolower(substr($this->_sql, 0, 4)) != 'show')
		{
			return false;
		}

		$bsize = 0;
		while ($row = $this->getRow())
		{
			$this->_cache[] = $row;

			// if the bytesize of the table exceeds the limit we'll abort
			// the cache generation and leave this query unbuffered
			// If we're running out of memory then run uncached.
			$bsize += strlen(join('', $row));
			if ($bsize > $this->_maxcachesize || $this->maxmem < memory_get_usage())
			{
				unset($this->_cache);
				$this->_cache[] = array();
				$this->_cached = false;
				$this->rewind();
				return false;
			}
		}

		// write data into textfile
		file_put_contents(KB_QUERYCACHEDIR.'/qcache_qry_'.$this->_hash, serialize($this->_cache));

		$this->_cached = true;
		$this->_currrow = 0;
		$this->executed_ = true;
	}
	//! Read a cached query from file.
	private function loadCache()
	{
		// loads the cachefile into the memory
		$this->_cache = unserialize(file_get_contents(KB_QUERYCACHEDIR.'/qcache_qry_'.$this->_hash));

		$this->_cached = true;
		$this->_currrow = 0;
		$this->executed_ = true;
	}

	//! Execute an SQL string.

	/*
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
	*/
	function execute($sql)
	{
		$this->_sql = trim($sql);
		$this->_hash = md5($this->_sql);
		unset($this->_cache);
		$this->_cache = array();
		$this->_cached = false;

		if ($this->checkCache())
		{
			$this->loadCache();
			$this->queryCachedCount(true);
			return true;
		}

		// we got no or no valid cache so open the connection and run the query
		$this->dbconn_ = new DBConnection();
		//if(isset($this->resid_)) $this->resid_->free();

		$t1 = strtok(microtime(), ' ') + strtok('');

		$this->resid_ = mysqli_query($this->dbconn_->id(), $sql);

		if (!$this->resid_ || $this->dbconn_->id()->errno)
		{
			// Clear the cache to prevent errors spreading.
			DBDebug::killCache();
			if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".$this->dbconn_->id()->error);
				DBDebug::recordError("SQL: ".$this->_sql);
			}
			if (DB_HALTONERROR === true)
			{
				echo "Database error: ".$this->dbconn_->id()->error."<br/>";
				echo "SQL: ".$this->_sql."<br/>";
				exit;
			}
			else
			{
				return false;
			}
		}

		$this->exectime = strtok(microtime(), ' ') + strtok('') - $t1;
		self::$totalexectime += $this->exectime;
		$this->executed_ = true;

		if(defined('KB_PROFILE')) DBDebug::profile($sql, $this->exectime);

		// if the query was too slow we'll fetch all rows and run it cached
		if ($this->exectime > $this->_minruntime)
		{
			$this->genCache();
			// We will use the cached version now so free the mysqli resource.
			// Except now it crashes so we won't.
			if(false && $this->_cached)
			{
				$this->resid_->free();
				unset($this->resid_);
			}
		}

		$this->queryCount(true);
		return true;
	}

	//! Return the number of rows returned by the last query.
	function recordCount()
	{
		if ($this->_cached)
		{
			return count($this->_cache);
		}
		elseif ($this->resid_)
		{
			return $this->resid_->num_rows;
		}
		return false;
	}

	//! Return the next row of results from the last query.
	function getRow()
	{
		if ($this->_cached)
		{
			if (!isset($this->_cache[$this->_currrow]))
			{
				return false;
			}
			// return the current row and increase the pointer by one
			return $this->_cache[$this->_currrow++];
		}
		if ($this->resid_)
		{
			return $this->resid_->fetch_assoc();
		}
		return false;
	}

	//! Reset list of results to return the first row from the last query.
	function rewind()
	{
		if ($this->_cached)
		{
			$this->_currrow = 0;
		}
		@mysqli_data_seek($this->resid_, 0);
	}

	//! Return the most recent error message for the DB connection.
	function getErrorMsg()
	{
		$msg = $this->sql_."<br>";
		$msg .= "Query failed. ".mysqli_error($this->dbconn_->id());

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

		if(!$this->dbconn_) $this->dbconn_ = new DBConnection();
		return $this->dbconn_->id()->autocommit($commit);
	}

	//! Rollback all queries in the current transaction.
	function rollback()
	{
		// if there's no connection to the db then there's nothing to roll back
		if(!$this->dbconn_) return true;
		return $this->dbconn_->id()->rollback();
	}
}
