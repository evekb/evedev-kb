<?php
require_once 'class.dbbasequery.php';
require_once 'class.dbconnection.php';
require_once('class.dbdebug.php');

//! mysqli memcached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBMemcachedQuery extends DBBaseQuery
{
	// maximum size of a cached result set (512kB)
	private static $maxcachesize = 524288;
	private static $maxmem = null;
	
	private $executed = false;
	private $cache = array();
	private $cached = false;
	private $nocache = false;
	private $sql = '';
	private $hash = null;
	private $resid = null;
	private $currow = 0;
	
	function DBMemcachedQuery($nocache = false)
	{
		if(is_null(self::$maxmem)) self::$maxmem = preg_replace('/M/', '000000', ini_get('memory_limit')) * 0.8;
		if(!self::$maxmem) self::$maxmem = 128000000;
		$this->nocache = $nocache;
	}
	
	private function genCache()
	{
		global $mc;
		
		// this function fetches all rows and writes the data into a textfile
		
		// don't attemp to cache updates!
		if (strtolower(substr($this->sql, 0, 6)) != 'select' && strtolower(substr($this->sql, 0, 4)) != 'show')
		{
			return false;
		}
		
		$bsize = 0;
		while ($row = $this->getRow())
		{
			$this->cache[] = $row;
			
			// If we're running out of memory then run uncached.
			$bsize += strlen(join('', $row));
			if ($bsize > self::$maxcachesize || self::$maxmem < memory_get_usage())
			{
				unset($this->cache);
				$this->cache[] = array();
				$this->cached = false;
				$this->rewind();
				return false;
			}
		}
		
		// write data to memcache
		$mc->set('sql_' . $this->hash, $this->cache, 0, 600);
		
		$this->cached = true;
		$this->currow = 0;
		$this->executed = true;
	}
	
	//! Execute an SQL string.
	
	/*
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
	*/
	public function execute($sql)
	{
		global $mc;
		
		$this->sql = trim($sql);
		$this->hash = md5($this->sql);
		unset($this->cache);
		$this->cache = array();
		$this->cached = false;
		
		if(!$this->nocache)
		{
			$cached = $mc->get('sql_' . $this->hash);
			if($cached)
			{
				$this->cache = $cached;
				$this->cached = true;
				$this->currow = 0;
				$this->executed = true;
				$this->queryCachedCount(true);
				return true;
			}
		}
		// we got no or no valid cache so open the connection and run the query
		self::$dbconn = new DBConnection;
		
		$t1 = strtok(microtime(), ' ') + strtok('');
		
		$this->resid = self::$dbconn->id()->query($sql);
		
		if (!$this->resid || self::$dbconn->id()->errno)
		{
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
		
		$this->exectime = strtok(microtime(), ' ') + strtok('') - $t1;
		self::$totalexectime += $this->exectime;
		$this->executed = true;
		
		if(defined('KB_PROFILE')) DBDebug::profile($sql, $this->exectime);
		
		// if the query was too slow we'll fetch all rows and run it cached
		$this->genCache();
		
		$this->queryCount(true);
		return true;
	}
	
	//! Return the number of rows returned by the last query.
	public function recordCount()
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
	public function getRow()
	{
		if ($this->cached)
		{
			if (!isset($this->cache[$this->currow]))
			{
				return false;
			}
			// return the current row and increase the pointer by one
			return $this->cache[$this->currow++];
		}
		if ($this->resid)
		{
			return $this->resid->fetch_assoc();
		}
		return false;
	}
	
	//! Reset list of results to return the first row from the last query.
	public function rewind()
	{
		if ($this->cached)
		{
			$this->currow = 0;
		}
		@mysqli_data_seek($this->resid, 0);
	}
	
	//! Return the auto-increment ID from the last insert operation.
	public function getInsertID()
	{
		return self::$dbconn->id()->insert_id;
	}
	
	//! Return the most recent error message for the DB connection.
	public function getErrorMsg()
	{
		$msg = $this->sql_."<br>";
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
	public function autocommit($commit = true)
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
	public function rollback()
	{
		// if there's no connection to the db then there's nothing to roll back
		if(!self::$dbconn) return true;
		return self::$dbconn->id()->rollback();
	}
}
