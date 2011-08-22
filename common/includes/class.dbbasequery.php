<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Base DB query class.
 * @package EDK
 */
abstract class DBBaseQuery
{
	/** @var integer */
	static protected $totalexectime = 0;
	/** @var integer */
	protected $exectime = 0;
	/** @var boolean */
	protected $executed = false;
	/** @var DBConnection */
	static protected $dbconn = null;
	/** @var integer */
	static protected $queryCount = 0;
	/** @var integer */
	static protected $queryCachedCount = 0;

	/**
	 * Return the count of queries performed.
	 * 
	 * @param boolean $increase if true then increment the count.
	 * @return integer the count of queries so far.
	 */
	public static function queryCount($increase = false)
	{
		if($increase) {
			self::$queryCount++;
		}

		return self::$queryCount;
	}

	/** Return the count of cached queries performed.
	 *
	 * @param boolean $increase if true then increment the count.
	 * @return integer the count of cached queries so far.
	 */
	public static function queryCachedCount($increase = false)
	{
		if($increase) {
			self::$queryCachedCount++;
		}

		return self::$queryCachedCount;
	}

	/** Return the number of rows affected by the last query.
	 *
	 * Returns the number of rows from the last query, including those by
	 * other objects.
	 *
	 * @return integer
	 */
	public static function affectedRows()
	{
		if(is_null(self::$dbconn)) {
			return 0;
		}
		return self::$dbconn->affectedRows();
	}

	/** Execute an SQL string.
	 *
	 * If DB_HALTONERROR is set then this will exit on an error.
	 * @return boolean false on error or true if successful.
	 */
	abstract public function execute($sql);

	/**
	 * Return the number of rows returned by the last query.
	 * 
	 * @return integer
	 */
	abstract public function recordCount();

	/**
	 * Return the next row of results from the last query.
	 * 
	 * @return array
	 */
	abstract public function getRow();

	/**
	 * Reset list of results to return the first row from the last query.
	 */
	abstract public function rewind();

	/**
	 * Return the auto-increment ID from the last insert operation.
	 * 
	 * @return integer
	 */
	public function getInsertID()
	{
		if(is_null(self::$dbconn)) {
			return null;
		}
		return self::$dbconn->id()->insert_id;
	}

	/**
	 * Return the execution time of all queries so far.
	 * 
	 * @return integer
	 */
	static function getTotalTime()
	{
		return self::$totalexectime;
	}

	/**
	 * Return the execution time of the last query.
	 *
	 * @return integer
	 */
	public function execTime()
	{
		return $this->exectime;
	}

	/**
	 * Return true if a query has been executed or false if none has been.
	 *
	 * @return boolean
	 */
	public function executed()
	{
		return $this->executed;
	}

	/**
	 * Return an escaped string for use in a query.
	 *
	 * @param string $string The string to escape.
	 * @param boolean $escapeall Set true to also escape _ and % for LIKE queries.
	 * @return string 
	 */
	public static function escape($string, $escapeall = false)
	{
		if(is_null(self::$dbconn)) {
			self::$dbconn = new DBConnection();
		}

		if($escapeall) {
			return addcslashes(
					self::$dbconn->id()->real_escape_string($string), '%_');
		} else {
			return self::$dbconn->id()->real_escape_string($string);
		}
	}

	/**
	 * Return the most recent error message for the DB connection.
	 * 
	 * @return string
	 */
	abstract public function getErrorMsg();

	/** Set the autocommit status.
	 *
	 * The default of true commits after every query.
	 * If set to false the queries will not be commited until autocommit is set
	 * to true.
	 * @param boolean $commit The new autocommit status.
	 * @return boolean true on success and false on failure.
	 */
	abstract public function autocommit($commit = true);

	/**
	 * Rollback all queries in the current transaction.
	 */
	abstract public function rollback();
}
