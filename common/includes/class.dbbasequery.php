<?php
require_once('class.dbconnection.php');

//! Base DB query class.
abstract class DBBaseQuery
{
	static protected $totalexectime = 0;
	protected $exectime = 0;
	protected $executed = false;
	static protected $dbconn = null;
	static protected $queryCount = 0;
	static protected $queryCachedCount = 0;
    //! Return the count of queries performed.

    /*!
     * \param $increase if true then increment the count.
     * \return the count of queries so far.
     */
    public static function queryCount($increase = false)
    {
        if ($increase)
        {
            self::$queryCount++;
        }

        return self::$queryCount;
    }

    //! Return the count of cached queries performed.

    /*!
     * \param $increase if true then increment the count.
     * \return the count of queries so far.
     */
    public static function queryCachedCount($increase = false)
    {
        if ($increase)
        {
            self::$queryCachedCount++;
        }

        return self::$queryCachedCount;
    }
	//! Execute an SQL string.

	/*
     * If DB_HALTONERROR is set then this will exit on an error.
     * \return false on error or true if successful.
	*/
	abstract public function execute($sql);
	//! Return the number of rows returned by the last query.
	abstract public function recordCount();
	//! Return the next row of results from the last query.
	abstract public function getRow();
	//! Reset list of results to return the first row from the last query.
	abstract public function rewind();
	//! Return the auto-increment ID from the last insert operation.
	public function getInsertID()
	{
		if(is_null(self::$dbconn)) return null;
		return self::$dbconn->id()->insert_id;
	}

	//! Return the execution time of the last query.
	static function getTotalTime()
	{
		return self::$totalexectime;
	}
	//! Return the execution time of the last query.
	public function execTime()
	{
		return $this->exectime;
	}
	//! Return true if a query has been executed or false if none has been.
	public function executed()
	{
		return $this->executed;
	}
	//! Return an escaped string for use in a query.
	public static function escape($string)
	{
		if(is_null(self::$dbconn)) self::$dbconn = new DBConnection();
		return self::$dbconn->id()->real_escape_string($string);
	}
	//! Return the most recent error message for the DB connection.
	abstract public function getErrorMsg();
	//! Set the autocommit status.

	/*! The default of true commits after every query.
     * If set to false the queries will not be commited until autocommit is set
     * to true.
     *  \param $commit The new autocommit status.
     *  \return true on success and false on failure.
	*/
	abstract public function autocommit($commit = true);
	//! Rollback all queries in the current transaction.
	abstract public function rollback();
}
