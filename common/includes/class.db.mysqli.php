<?php
// mssql: select SCOPE_IDENTITY() AS id
// postgresql: INSERT INTO mytable (lastname) VALUES ('Cher') RETURNING id;

//! mysqli connection class.
//! Establishes the connection to the database.
class DBConnection_mysqli
{
    //! Set up a mysqli DB connection. 
    function DBConnection_mysqli()
    {
        static $conn_id;

        if ($conn_id)
        {
            $this->id_ = $conn_id;
            return;
        }
        if(defined('DB_PORT'))
        {
            if (!$this->id_ = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT))
            die("Unable to connect to mysql database.");
            if(method_exists($this->id_,'set_charset')) $this->id_->set_charset('utf8');
        }
        else
        {
            if (!$this->id_ = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME))
            die("Unable to connect to mysql database.");
            if(method_exists($this->id_,'set_charset')) $this->id_->set_charset('utf8');
        }

        //mysqli_select_db(DB_NAME);
        $conn_id = $this->id_;
    }
    //! Return the connection id for this connection. Used for connection specific commands.
    function id()
    {
        return $this->id_;
    }
    //! Return the number of rows affected by a query.
    function affectedRows()
    {
        return mysqli_affected_rows($this->id_);
    }
}
//! mysqli uncached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBNormalQuery_mysqli
{
    //! Prepare a connection for a new mysqli query.
    function DBNormalQuery_mysqli()
    {
        $this->executed_ = false;
        $this->dbconn_ = new DBConnection_mysqli;
        static $totalexectime = 0;
		$this->totalexectime_ = &$totalexectime;
    }
    //! Return the count of queries performed.

    /*!
     * \param $increase if true then increment the count.
     * \return the count of queries so far.
     */
    function queryCount($increase = false)
    {
        static $count;

        if ($increase)
        {
            $count++;
        }

        return $count;
    }
    //! Return the count of cached queries performed - 0 for uncache queries.
    function queryCachedCount($increase = false)
    {
        return 0;
    }
    //! Execute an SQL string.

    /* 
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
     */
    function execute($sql)
    {
		if(isset($this->stmt)) $this->execute_prepared();

        $t1 = strtok(microtime(), ' ') + strtok('');

		//if(isset($this->resid_)) $this->resid_->free();

        $this->resid_ = mysqli_query($this->dbconn_->id(),$sql);

        if ($this->resid_ === false || $this->dbconn_->id()->errno)
        {
            if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".$this->dbconn_->id()->error);
				DBDebug::recordError("SQL: ".$sql);
			}
            if (defined('DB_HALTONERROR') && DB_HALTONERROR)
            {
                echo "Database error: " . $this->dbconn_->id()->error . "<br>";
                echo "SQL: " . $sql . "<br>";
                exit;
            }
            else
            {
                return false;
            }
        }

        $this->exectime_ = strtok(microtime(), ' ') + strtok('') - $t1;
        $this->totalexectime_ += $this->exectime_;
        $this->executed_ = true;

        if(defined('KB_PROFILE')) DBDebug::profile($sql);

        $this->queryCount(true);

        return true;
    }
    //! Return the number of rows returned by the last query.
    function recordCount()
    {
		if($this->stmt) return $this->stmt->num_rows;
        if ($this->resid_)
        {
            return $this->resid_->num_rows;
        }
        return false;
    }
    //! Return the next row of results from the last query.
    function getRow()
    {
        if ($this->resid_)
        {
            return $this->resid_->fetch_assoc();
        }
        return false;
    }
    //! Reset list of results to return the first row from the last query.
    function rewind()
    {
        @mysqli_data_seek($this->resid_, 0);
    }
    //! Return the auto-increment ID from the last insert operation.
    function getInsertID()
    {
        return $this->dbconn_->id()->insert_id;
    }
    //! Return the execution time of the last query.
    function execTime()
    {
        return $this->exectime_;
    }
    //! Return true if a query has been executed or false if none has been.
    function executed()
    {
        return $this->executed_;
    }
    //! Return the most recent error message for the DB connection.
    function getErrorMsg()
    {
		if($this->stmt)
		{
			return "Prepared statement failed: ".$this->stmt->errno;
		}
        $msg = $this->sql_ . "<br>";
        $msg .= "Query failed. " . mysqli_error($this->dbconn_->id());

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
        return $this->dbconn_->id()->autocommit($commit);
    }
    //! Rollback all queries in the current transaction.
    function rollback()
    {
        return mysqli_rollback($this->dbconn_->id());
    }
}
//! mysqli file-cached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBCachedQuery_mysqli
{
    //! Set up a mysqli cached query object with default values.
    function DBCachedQuery_mysqli($nocache = false)
    {
        static $totalexectime = 0;
		$this->totalexectime_ = &$totalexectime;
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
    function checkCache()
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
    function parseSQL($sql)
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
    function isCacheValid()
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
    function markAffectedTables()
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
            return false;
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
    function genCache()
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
    function loadCache()
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
        $this->dbconn_ = new DBConnection_mysqli();
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

        $this->exectime_ = strtok(microtime(), ' ') + strtok('') - $t1;
        $this->totalexectime_ += $this->exectime_;
        $this->executed_ = true;

        if(defined('KB_PROFILE')) DBDebug::profile($sql);

        // if the query was too slow we'll fetch all rows and run it cached
        if ($this->exectime_ > $this->_minruntime)
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

    //! Return the count of queries performed.

    /*!
     * \param $increase if true then increment the count.
     * \return the count of queries so far.
     */
    function queryCount($increase = false)
    {
        static $count;

        if ($increase)
        {
            $count++;
        }

        return $count;
    }
    //! Return the count of cached queries performed.

    /*!
     * \param $increase if true then increment the count.
     * \return the count of queries so far.
     */
    function queryCachedCount($increase = false)
    {
        static $count;

        if ($increase)
        {
            $count++;
        }

        return $count;
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

    //! Return the auto-increment ID from the last insert operation.
    function getInsertID()
    {
        return $this->dbconn_->id()->insert_id;
    }

    //! Return the execution time of the last query.
    function execTime()
    {
        return $this->exectime_;
    }

    //! Return true if a query has been executed or false if none has been.
    function executed()
    {
        return $this->executed_;
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
        if(!$this->dbconn_) $this->dbconn_ = new DBConnection_mysqli();
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

//! mysqli memcached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBMemcachedQuery_mysqli
{
    function DBMemcachedQuery_mysqli($nocache = false)
    {
        static $totalexectime = 0;
		$this->totalexectime_ = &$totalexectime;
        $this->executed_ = false;
        $this->_cache = array();
        $this->_cached = false;
		$this->_nocache = $nocache;

        // this is the minimum runtime a query has to run to be
        // eligible for caching in seconds
        $this->_minruntime = 0.1;

        // maximum size of a cached result set (512kB)
        $this->_maxcachesize = 524288;
		$this->maxmem = preg_replace('/M/', '000000', ini_get('memory_limit')) * 0.8;
		if(!$this->maxmem) $this->maxmem = 128000000;
        $this->d = true;
    }

    function genCache()
    {
        global $mc;

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
        $mc->set(KB_SITE . '_sql_' . $this->_hash, $this->_cache, 0, 600);

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
        global $mc;

        $this->_sql = trim($sql);
        $this->_hash = md5($this->_sql);
		unset($this->_cache);
        $this->_cache = array();
        $this->_cached = false;

		if(!$this->_nocache)
		{
			$cached = $mc->get(KB_SITE . '_sql_' . $this->_hash);
			if($cached) {
				$this->_cache = $cached;
				$this->_cached = true;
				$this->_currrow = 0;
				$this->executed_ = true;
				$this->queryCachedCount(true);
				return true;
			}
		}
        // we got no or no valid cache so open the connection and run the query
        $this->dbconn_ = new DBConnection_mysqli;

        $t1 = strtok(microtime(), ' ') + strtok('');

        $this->resid_ = $this->dbconn_->id()->query($sql);

        if (!$this->resid_ || $this->dbconn_->id()->errno)
        {
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

        $this->exectime_ = strtok(microtime(), ' ') + strtok('') - $t1;
        $this->totalexectime_ += $this->exectime_;
        $this->executed_ = true;

        if(defined('KB_PROFILE')) DBDebug::profile($sql);

        // if the query was too slow we'll fetch all rows and run it cached
        $this->genCache();

        $this->queryCount(true);
        return true;
    }

    //! Return the count of queries performed.

    /*!
     * \param $increase if true then increment the count.
     * \return the count of queries so far.
     */
    function queryCount($increase = false)
    {
        static $count;

        if ($increase)
        {
            $count++;
        }

        return $count;
    }

    //! Return the count of cached queries performed.

    /*!
     * \param $increase if true then increment the count.
     * \return the count of queries so far.
     */
    function queryCachedCount($increase = false)
    {
        static $count;

        if ($increase)
        {
            $count++;
        }

        return $count;
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

    //! Return the auto-increment ID from the last insert operation.
    function getInsertID()
    {
        return $this->dbconn_->id()->insert_id;
    }

    //! Return the execution time of the last query.
    function execTime()
    {
        return $this->exectime_;
    }

    //! Return true if a query has been executed or false if none has been.
    function executed()
    {
        return $this->executed_;
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
        if(!$this->dbconn_) $this->dbconn_ = new DBConnection_mysqli();
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