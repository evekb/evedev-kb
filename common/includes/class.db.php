<?php
require_once 'class.dbbasequery.php';
require_once 'class.dbconnection.php';
require_once('class.dbdebug.php');

class DBQuery
{
	var $object;

	// php5 style object overloading
	// we internally load up the wanted object and reroute all
	// object actions to it
	function __construct($forceNormal = false)
	{
		$this->object = DBFactory::getDBQuery($forceNormal);
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
}

//! mysqli uncached query class. Manages SQL queries to a MySQL DB using mysqli.
class DBNormalQuery extends DBBaseQuery
{
	private $resid;
	//! Prepare a connection for a new mysqli query.
	function DBNormalQuery()
	{
		$this->executed_ = false;
		self::$dbconn = new DBConnection();
	}

	//! Execute an SQL string.

	/*
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
	*/
	function execute($sql)
	{
		$t1 = strtok(microtime(), ' ') + strtok('');

		$this->resid = mysqli_query(self::$dbconn->id(),$sql);

		if ($this->resid === false || self::$dbconn->id()->errno)
		{
			if(defined('KB_PROFILE'))
			{
				DBDebug::recordError("Database error: ".self::$dbconn->id()->error);
				DBDebug::recordError("SQL: ".$sql);
			}
			trigger_error("SQL error (".self::$dbconn->id()->error, E_USER_WARNING);
			
			if (defined('DB_HALTONERROR') && DB_HALTONERROR)
			{
				echo "Database error: " . self::$dbconn->id()->error . "<br>";
				echo "SQL: " . $sql . "<br>";
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

		$this->queryCount(true);

		return true;
	}
	//! Return the number of rows returned by the last query.
	function recordCount()
	{
		if ($this->resid)
		{
			return $this->resid->num_rows;
		}
		return false;
	}
	//! Return the next row of results from the last query.
	function getRow()
	{
		if ($this->resid)
		{
			return $this->resid->fetch_assoc();
		}
		return false;
	}
	//! Reset list of results to return the first row from the last query.
	function rewind()
	{
		if(!is_null($this->resid_)) @mysqli_data_seek($this->resid_, 0);
	}
	//! Return the auto-increment ID from the last insert operation.
	function getInsertID()
	{
		return self::$dbconn->id()->insert_id;
	}
	//! Return the most recent error message for the DB connection.
	function getErrorMsg()
	{
		$msg .= "<br>Query failed. " . mysqli_error(self::$dbconn->id());

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
		
		return self::$dbconn->id()->autocommit($commit);
	}
	//! Rollback all queries in the current transaction.
	function rollback()
	{
		return mysqli_rollback(self::$dbconn->id());
	}
}

