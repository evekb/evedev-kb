<?php
/**
 * $Date: 2010-05-29 14:46:12 +1000 (Sat, 29 May 2010) $
 * $Revision: 699 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.db.php $
 * @package EDK
 */

/**
 * mysqli uncached query class. Manages SQL queries to a MySQL DB using mysqli.
 * @package EDK
 */
class DBNormalQuery extends DBBaseQuery
{
	/** @var MySQLi_Result|boolean */
	private $resid;

	/**
	 * Prepare a connection for a new mysqli query.
	 */
	function DBNormalQuery()
	{
		self::$dbconn = new DBConnection();
	}

	/**
	 * Execute an SQL string.
	 *
	 * If DB_HALTONERROR is set then this will exit on an error.
	 * @return boolean false on error or true if successful.
	 */
	function execute($sql)
	{
		$t1 = microtime(true);

		$this->resid = mysqli_query(self::$dbconn->id(), $sql);

		if ($this->resid === false || self::$dbconn->id()->errno) {
			if (defined('KB_PROFILE')) {
				DBDebug::recordError("Database error: " . self::$dbconn->id()->error);
				DBDebug::recordError("SQL: " . $sql);
			}
			if (defined('DB_HALTONERROR') && DB_HALTONERROR) {
				echo "Database error: " . self::$dbconn->id()->error . "<br />";
				echo "SQL: " . $sql . "<br />";
				trigger_error("SQL error (" . self::$dbconn->id()->error, E_USER_ERROR);
				exit;
			} else {
				trigger_error("SQL error (" . self::$dbconn->id()->error, E_USER_WARNING);
				return false;
			}
		}

		$this->exectime = microtime(true) - $t1;
		self::$totalexectime += $this->exectime;
		$this->executed = true;

		if (defined('KB_PROFILE')) {
			DBDebug::profile($sql, $this->exectime);
		}

		$this->queryCount(true);

		return true;
	}

	/**
	 * Return the number of rows returned by the last query.
	 *
	 * @return boolean
	 */
	function recordCount()
	{
		if ($this->resid) {
			return $this->resid->num_rows;
		}
		return false;
	}

	/**
	 * Return the next row of results from the last query.
	 *
	 * @return array
	 */
	function getRow()
	{
		return $this->resid ? $this->resid->fetch_assoc() : false;
	}

	/**
	 * Reset list of results to return the first row from the last query.
	 */
	function rewind()
	{
		if (!is_null($this->resid)) {
			@mysqli_data_seek($this->resid, 0);
		}
	}

	/**
	 * Return the auto-increment ID from the last insert operation.
	 */
	function getInsertID()
	{
		return self::$dbconn->id()->insert_id;
	}

	/**
	 * Return the most recent error message for the DB connection.
	 */
	function getErrorMsg()
	{
		$msg .= "<br>Query failed. " . mysqli_error(self::$dbconn->id());

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
	function autocommit($commit = true)
	{
		if (defined('KB_PROFILE') && KB_PROFILE == 3) {
			if (!$commit) {
				DBDebug::recordError("Transaction started.");
			} else {
				DBDebug::recordError("Transaction ended.");
			}
		}

		return self::$dbconn->id()->autocommit($commit);
	}

	/**
	 * Rollback all queries in the current transaction.
	 *
	 * @return boolean
	 */
	function rollback()
	{
		return mysqli_rollback(self::$dbconn->id());
	}
}

