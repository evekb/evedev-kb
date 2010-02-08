<?php
require_once 'class.dbbasequery.php';
require_once 'class.dbconnection.php';
require_once('class.dbdebug.php');

//! mysqli memcached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBMemcachedQuery extends DBBaseQuery
{
    function DBMemcachedQuery($nocache = false)
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
    }

    private function genCache()
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
        self::$dbconn = new DBConnection;

        $t1 = strtok(microtime(), ' ') + strtok('');

        $this->resid_ = self::$dbconn->id()->query($sql);

        if (!$this->resid_ || self::$dbconn->id()->errno)
        {
            if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".self::$dbconn->id()->error);
				DBDebug::recordError("SQL: ".$this->_sql);
			}
            if (DB_HALTONERROR === true)
            {
                echo "Database error: ".self::$dbconn->id()->error."<br/>";
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
        $this->genCache();

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

    //! Return the auto-increment ID from the last insert operation.
    function getInsertID()
    {
        return self::$dbconn->id()->insert_id;
    }

    //! Return the most recent error message for the DB connection.
    function getErrorMsg()
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
