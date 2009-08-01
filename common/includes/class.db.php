<?php

class DBConnection
{
	function DBConnection()
	{
		static $conn_id;

		if (is_resource($conn_id))
		{
			$this->id_ = $conn_id;
			return;
		}
		if(defined('DB_PORT'))
		{
			if (!$this->id_ = mysql_connect(DB_HOST.':'.DB_PORT, DB_USER, DB_PASS))
				die("Unable to connect to mysql database.");
		}
		else
		{
			if (!$this->id_ = mysql_connect(DB_HOST, DB_USER, DB_PASS))
				die("Unable to connect to mysql database.");
		}
		mysql_select_db(DB_NAME);
		$conn_id = $this->id_;
	}

	function id()
	{
		return $this->id_;
	}

	function affectedRows()
	{
		return mysql_affected_rows($this->id_);
	}
}

class DBQuery
{
	var $object;

	// php5 style object overloading
	// we internally load up the wanted object and reroute all
	// object actions to it
	function __construct($forceNormal = false)
	{
		if (DB_TYPE_USED === 'mysqli' )
		{
			if (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE === true && !$forceNormal)
			{
				$object = new DBMemCachedQuery_mysqli();
			}
			elseif (defined('DB_USE_QCACHE') && DB_USE_QCACHE === true && !$forceNormal)
			{
				$object = new DBCachedQuery_mysqli();
			}
			else
			{
				$object = new DBNormalQuery_mysqli();
			}
		}
		elseif (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE === true && !$forceNormal)
		{
			$object = new DBMemCachedQuery();
		}
		elseif (defined('DB_USE_QCACHE') && DB_USE_QCACHE === true && !$forceNormal)
		{
			$object = new DBCachedQuery();
		}
		else
		{
			$object = new DBNormalQuery();
		}
		$this->object = $object;
	}

	function __call($name, $args)
	{
		return call_user_func_array(array($this->object, $name), $args);
	}

	function __set($name, $value)
	{
		$this->object->$name = $value;
	}

	function __unset($name)
	{
		unset($this->object->$name);
	}

	function __isset($name)
	{
		return isset($this->object->$name);
	}

	function __get($name)
	{
		return $this->object->$name;
	}

	// php4 style object overloading
	// we just hijack $this but we need to use a helper
	// function for this because php5 fatals if it sees
	// $this = ... in the src
	function DBQuery($forceNormal = false)
	{
		$object = &$this->getRef($this);
		if (DB_TYPE_USED === 'mysqli' )
		{
			if (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE === true && !$forceNormal)
			{
				$object = new DBMemCachedQuery_mysqli();
			}
			elseif (defined('DB_USE_QCACHE') && DB_USE_QCACHE === true && !$forceNormal)
			{
				$object = new DBCachedQuery_mysqli();
			}
			else
			{
				$object = new DBNormalQuery_mysqli();
			}
		}
		elseif (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE === true && !$forceNormal)
		{
			$object = new DBMemCachedQuery();
		}
		elseif (defined('DB_USE_QCACHE') && DB_USE_QCACHE === true && !$forceNormal)
		{
			$object = new DBCachedQuery();
		}
		else
		{
			$object = new DBNormalQuery();
		}
	}

	function &getRef(&$var)
	{
		return $var;
	}
}

//! mysql uncached query class. Manages SQL queries to a MySQL DB using mysql.
class DBNormalQuery
{
//! Prepare a connection for a new mysql query.
	function DBNormalQuery()
	{
		static $totalexectime = 0;
		$this->totalexectime_ = &$totalexectime;
		$this->executed_ = false;
		$this->dbconn_ = new DBConnection;
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

	//! Return the count of cached queries performed - 0 for uncaches queries.
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
		$t1 = strtok(microtime(), ' ') + strtok('');

		$this->resid_ = mysql_query($sql, $this->dbconn_->id());

		if (!$this->resid_ || mysql_errno($this->dbconn_->id()))
		{
			if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".mysql_error($this->dbconn_->id()));
				DBDebug::recordError("SQL: ".$sql);
			}
			if (defined('DB_HALTONERROR') && DB_HALTONERROR)
			{
				echo "Database error: " . mysql_error($this->dbconn_->id()) . "<br>";
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
		return mysql_num_rows($this->resid_);
	}

	//! Return the next row of results from the last query.
	function getRow()
	{
		if ($this->resid_)
		{
			return mysql_fetch_assoc($this->resid_);
		}
		return false;
	}

	//! Reset list of results to return the first row from the last query.
	function rewind()
	{
		@mysql_data_seek($this->resid_, 0);
	}

	//! Return the auto-increment ID from the last insert operation.
	function getInsertID()
	{
		return mysql_insert_id();
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
		$msg = $this->sql_ . "<br>";
		$msg .= "Query failed. " . mysql_error($this->dbconn_->id());

		return $msg;
	}
	//! Set the autocommit status.

	//! Not implemented with mysql library
	function autocommit($commit = true)
	{
		return false;
	}

	//! Rollback all queries in the current transaction.

	//! Not implemented with mysql library
	function rollback()
	{
		return false;
	}
}

class DBDebug
{
	function recordError($text)
	{
		$qerrfile = "/tmp/EDKprofile.lst";
		if($text) file_put_contents($qerrfile, $text."\n", FILE_APPEND);
	}
	function profile($sql, $text='')
	{
		$qerrfile = "/tmp/EDKprofile.lst";
		if($text) file_put_contents($qerrfile, $text."\n", FILE_APPEND);
		if (KB_PROFILE == 2)
		{
			file_put_contents($qerrfile, $sql . "\nExecution time: " . $this->exectime_ . "\n", FILE_APPEND);
		}
		if (KB_PROFILE == 3)
		{
			if(DB_TYPE == 'mysqli' && strtolower(substr($sql,0,6))=='select')
			{
				$this->dbconn_ = new DBConnection_mysqli;
				$prof_out_ext = $prof_out_exp = '';
				$prof_qry= mysqli_query($this->dbconn_->id(),'EXPLAIN extended '.$sql.";");
				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_exp .= implode(' | ', $prof_row)."\n";
				$prof_qry= mysqli_query($this->dbconn_->id(),'show warnings');

				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_ext .= implode(' | ', $prof_row)."\n";
				file_put_contents($qerrfile, $sql . "\n".
					$prof_out_ext. $prof_out_exp.
					"\n-- Execution time: " . $this->exectime_ . " --\n", FILE_APPEND);
			}
			else file_put_contents($qerrfile, $sql."\nExecution time: ".$this->exectime_."\n", FILE_APPEND);
		}

		if (KB_PROFILE == 4)
		{
			if($this->exectime_ > 0.1 && strtolower(substr($sql,0,6))=='select')
			{
				$this->dbconn_ = new DBConnection_mysqli;
				$prof_out_exp = $prof_out_exp = '';
				$prof_qry= mysqli_query($this->dbconn_->id(),'EXPLAIN extended '.$sql);
				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_exp .= implode(' | ', $prof_row)."\n";
				$prof_qry= mysqli_query($this->dbconn_->id(),'show warnings');

				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_ext .= implode(' | ', $prof_row)."\n";
				file_put_contents($qerrfile, $sql . "\n".
					$prof_out_ext. $prof_out_exp.
					"\n-- Execution time: " . $this->exectime_ . " --\n", FILE_APPEND);
			}
		}

	}
}

?>