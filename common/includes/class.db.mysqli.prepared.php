<?php
require_once('common/includes/class.db.mysqli.php');
//! mysqli uncached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBPreparedQuery
{
//! Prepare a connection for a new mysqli query.
	function DBPreparedQuery()
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
	//! Execute the prepared command.

    /*
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
     */
	function execute()
	{
		return $this->execute_prepared();
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
	//! Return the auto-increment ID from the last insert operation.
	function getInsertID()
	{
		return $this->stmt->insert_id;
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
	//! Prepare a statement.

	/* \param $sql String containing a prepared statement.
	 * \return true on success and false on failure.
	 */
	function prepare($sql)
	{
		$this->stmt = $this->dbconn_->id()->prepare($sql);
		if(!$this->stmt)
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
		return true;
	}
	//! Bind the prepared query parameters to the given variables.

	/*! bound parameters can not be changed. While this can be changed as per
	 * bind_results it would break future caching. For now it stays unbound.
	 */
	function bind_param()
	{
		$arr[0]=$this->stmt;
		$args = func_get_args();
		$Args = array();
		foreach($args as $k => &$arg) $Args[$k] = &$arg;
		array_unshift($Args,$this->stmt);
		return call_user_func_array('mysqli_stmt_bind_param',$Args);
	}
	//! Bind the prepared query results to the given variables.

	/*! The hideous argument list is there as func_get_args only returns a copy
	 * of the arguments rather than a reference so references to the original
	 * arguments do not reach the prepared statement.
	 */

	function bind_result(&$arg0=null, &$arg1=null, &$arg2=null, &$arg3=null,
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
	//! Execute the prepared command.
	
    /*
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
     */
	function execute_prepared()
	{
		if(!$this->stmt->execute())
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
		$this->stmt->store_result();
		return true;
	}
	//! Fetch the next results of the prepared statement into bound variables.
	function fetch_prepared()
	{
		return $this->stmt->fetch();
	}
}
