<?php

class DBMemcachedQuery
{
    function DBMemcachedQuery()
    {
        static $totalexectime = 0;
		$this->totalexectime_ = &$totalexectime;
        $this->executed_ = false;
        $this->_cache = array();
        $this->_cached = false;

        // this is the minimum runtime a query has to run to be
        // eligible for caching in seconds
        $this->_minruntime = 0.1;

        // maximum size of a cached result set (512kB)
        $this->_maxcachesize = 524288;
        $this->d = true;
    }

    function checkCache()
    {
        global $mc;

        // only cache selects
        // we don't use select ... into so there is no problem
        $this->_sql = str_replace(array("\r\n", "\n"), ' ', $this->_sql);
        if (strtolower(substr($this->_sql, 0, 6)) != 'select' && strtolower(substr($this->_sql, 0, 4)) != 'show')
            return false;

        $cached = $mc->get(KB_SITE . '_sql_' . $this->_hash);
        if($cached) {
            return true;
        }

        return false;
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

            $bsize += strlen(join('', $row));
            if ($bsize > $this->_maxcachesize)
            {
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

    function execute($sql)
    {
        global $mc; 

        $this->_sql = trim($sql);
        $this->_hash = md5($this->_sql);
        $this->_cache = array();
        $this->_cached = false;

        $cached = $mc->get(KB_SITE . '_sql_' . $this->_hash);
        if($cached) {
            $this->_cache = $cached;
            $this->_cached = true;
            $this->_currrow = 0;
            $this->executed_ = true;
            $this->queryCachedCount(true);
            return true;
        }

        // we got no or no valid cache so open the connection and run the query
        $this->dbconn_ = new DBConnection;

        $t1 = strtok(microtime(), ' ') + strtok('');

        $this->resid_ = mysql_query($sql, $this->dbconn_->id());

        if ($this->resid_ === false)
        {
            if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".mysql_error($this->dbconn_->id()));
				DBDebug::recordError("SQL: ".$this->_sql);
			}
            if (DB_HALTONERROR === true)
            {
                echo "Database error: ".mysql_error($this->dbconn_->id())."<br/>";
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

        if (KB_PROFILE == 2)
        {
            file_put_contents('/tmp/profile.lst', $sql."\nExecution time: ".$this->exectime_."\n", FILE_APPEND);
        }

        // if the query was too slow we'll fetch all rows and run it cached
        $this->genCache();

        $this->queryCount(true);
        return true;
    }

    function queryCount($increase = false)
    {
        static $count;

        if ($increase)
        {
            $count++;
        }

        return $count;
    }

    function queryCachedCount($increase = false)
    {
        static $count;

        if ($increase)
        {
            $count++;
        }

        return $count;
    }

    function recordCount()
    {
        if ($this->_cached)
        {
            return count($this->_cache);
        }
        return mysql_num_rows($this->resid_);
    }

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
        if (is_resource($this->resid_))
        {
            return mysql_fetch_assoc($this->resid_);
        }
        return false;
    }

    function rewind()
    {
        if ($this->_cached)
        {
            $this->_currrow = 0;
        }
        @mysql_data_seek($this->resid_, 0);
    }

    function getInsertID()
    {
        return mysql_insert_id();
    }

    function execTime()
    {
        return $this->exectime_;
    }

    function executed()
    {
        return $this->executed_;
    }

    function getErrorMsg()
    {
        $msg = $this->sql_."<br>";
        $msg .= "Query failed. ".mysql_error($this->dbconn_->id());

        return $msg;
    }
    //Not implemented with mysql library
    function autocommit($commit = true)
    {
        return false;
    }
    
    //Not implemented with mysql library
    function rollback()
    {
        return false;
    }
}
?>