<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// TODO Check if caching is enabled and flag tables as dirty even if we don't
// cache prepared queries.

/**
 * mysqli uncached query class. Manages SQL queries to a MySQL DB using mysqli.
 * @package EDK
 */
class DBPreparedQuery
{
	static protected $totalexectime = 0;
	protected $exectime = 0;
	protected $executed = false;
	static protected $dbconn = null;
	static protected $queryCount = 0;
	static protected $queryCachedCount = 0;
	protected $stmt = null;
	
/**
 * Prepare a connection for a new mysqli query.
 */
	function DBPreparedQuery()
	{
		self::$dbconn = new DBConnection();
	}
    /**
     * Return the count of queries performed.
     *
     * @param boolean $increase if true then increment the count.
     * @return mixed the count of queries so far.
     */
    public static function queryCount($increase = false)
    {
        if ($increase)
        {
            self::$queryCount++;
        }

        return self::$queryCount;
    }

    /**
     * Return the count of cached queries performed.
     *
     * @param boolean $increase if true then increment the count.
     * @return mixed the count of queries so far.
     */
    public static function queryCachedCount($increase = false)
    {
        if ($increase)
        {
            self::$queryCachedCount++;
        }

        return self::$queryCachedCount;
    }
	/**
	 * Execute the prepared command.
	 */

    /*
     * If DB_HALTONERROR is set then this will exit on an error.
     * @return mixed false on error or true if successful.
     */
	public function execute()
	{
		$t1 = microtime(true);

		//TODO redo this with hooks that cached classes can use.
		if ( (DB_USE_MEMCACHE || DB_USE_QCACHE )
			&& strtolower(substr($this->sql, 0, 6)) != 'select'
			&& strtolower(substr($this->sql, 0, 4)) != 'show')
		{
			$qc = DBFactory::getDBQuery();
			$qc->markAffectedTables($this->sql);
		}

		if(!$this->stmt->execute())
		{
			if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("SQL execution error: ".$this->stmt->error);
				DBDebug::recordError("SQL: ".$this->sql);
			}
			if (defined('DB_HALTONERROR') && DB_HALTONERROR)
			{
				echo "SQL execution error: " . $this->stmt->error . "<br>";
				echo "SQL: " . $this->sql . "<br>";
				trigger_error("SQL execution error.", E_USER_ERROR);
				exit;
			}
			else
			{
				return false;
			}
		}
		$this->stmt->store_result();
		$this->exectime = microtime(true) - $t1;
		self::$totalexectime += $this->exectime;
		$this->executed = true;
		return true;
	}
	/**
	 * Return the number of rows returned by the last query.
	 */
	public function recordCount()
	{
		if($this->stmt) return $this->stmt->num_rows;
		return false;
	}
	/**
	 * Return the auto-increment ID from the last insert operation.
	 */
	public function getInsertID()
	{
		return $this->stmt->insert_id;
	}
	/**
	 * Return the most recent error message for the DB connection.
	 */
	public function getErrorMsg()
	{
		if($this->stmt)
		{
			return "Prepared statement failed: ".$this->stmt->errno;
		}
		$msg = "<br>Query failed. "
			. mysqli_error(self::$dbconn->id());

		return $msg;
	}
	/**
	 * Set the autocommit status.
	 * The default of true commits after every query.
     * If set to false the queries will not be commited until autocommit is set
     * to true.
     *  @param boolean $commit The new autocommit status.
     *  @return mixed true on success and false on failure.
     */
	public function autocommit($commit = true)
	{
		return self::$dbconn->id()->autocommit($commit);
	}
	/**
	 * Rollback all queries in the current transaction.
	 */
	public function rollback()
	{
		return mysqli_rollback(self::$dbconn->id());
	}
	/**
	 * Prepare a statement.
	 */

	/* @param string $sql String containing a prepared statement.
	 * @return mixed true on success and false on failure.
	 */
	public function prepare($sql)
	{
		$this->sql = $sql;
		$this->stmt = self::$dbconn->id()->prepare($sql);
		if(!$this->stmt)
		{
			if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Prepare Statement error: ". self::$dbconn->id()->error);
				DBDebug::recordError("SQL: ".$sql);
			}
			if (defined('DB_HALTONERROR') && DB_HALTONERROR)
			{
				echo "Prepare Statement error: " . self::$dbconn->id()->error . "<br>";
				echo "SQL: " . $sql . "<br>";
				trigger_error("Prepare Statement error.", E_USER_ERROR);
				exit;
			}
			else
			{
				return false;
			}
		}
		return true;
	}
	/**
	 * Bind the prepared query parameters to the given variables.
	 * bound parameters can not be changed. While this can be changed as per
	 * bind_results it would break future caching. For now it stays unbound.
	 */
	public function bind_param()
	{
		$arr[0]=$this->stmt;
		$args = func_get_args();
		$Args = array();
		foreach($args as $k => &$arg) $Args[$k] = &$arg;
		array_unshift($Args,$this->stmt);
		return call_user_func_array('mysqli_stmt_bind_param',$Args);
	}
	/**
	 * Bind the prepared query parameters to the variables in the given array.
	 *
	 * @param array params An array of variables to bind as query parameters.
	 */
	public function bind_params(&$params)
	{
		return call_user_func_array(array($this->stmt,'bind_param'),$params);
	}
	/**
	 * Bind the prepared query results to the given variables.
	 * The hideous argument list is there as func_get_args only returns a copy
	 * of the arguments rather than a reference so references to the original
	 * arguments do not reach the prepared statement.
	 */

	public function bind_result(&$arg0=null, &$arg1=null, &$arg2=null, &$arg3=null,
		&$arg4=null, &$arg5=null, &$arg6=null, &$arg7=null, &$arg8=null,
		&$arg9=null, &$arg10=null, &$arg11=null, &$arg12=null, &$arg13=null,
		&$arg14=null, &$arg15=null, &$arg16=null, &$arg17=null, &$arg18=null,
		&$arg19=null)
	{
		/*
		 * Only returns a reference to the original variable if &$arg is used
		 * in function definition so might as well use them directly.
		 *
		 *
        $stack = debug_backtrace();
        $args = array();
        if (isset($stack[0]["args"]))
            for($i=0; $i < count($stack[0]["args"]); $i++)
                $args[$i] = & $stack[0]["args"][$i];
        return call_user_func_array(array($this->stmt,'bind_result'),$args);
		*/
		$args = array();
		for($i=0;$i<func_num_args();$i++)
		{
			$temparg = 'arg'.$i;
			$args[$i] = & $$temparg;
		}
		return call_user_func_array(array($this->stmt,'bind_result'),$args);
	}
	/**
	 * Bind the prepared query results to the variables in the given array.
	 *
	 * @param array $results
	 * @return boolean
	 */
	public function bind_results(&$results)
	{
		return call_user_func_array(array($this->stmt,'bind_result'),$results);
	}
	/**
	 * Fetch the next results of the prepared statement into bound variables.
	 *
	 * @return mixed
	 */
	public function fetch()
	{
		return $this->stmt->fetch();
	}
}