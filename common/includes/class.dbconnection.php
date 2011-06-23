<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/**
 * mysqli connection class.
 * 
 * Establishes the connection to the database.
 * @package EDK
 */
class DBConnection
{
	private static $conn_id = null;

	function DBConnection()
	{
		self::init();
	}

	/**
	 * Set up a mysqli DB connection.
	 */
	private static function init()
	{
		if (isset(self::$conn_id))
		{
			return;
		}
		if(defined('DB_PORT'))
			self::$conn_id = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
		else
			self::$conn_id = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		if (mysqli_connect_error() != null)
		{
			if(defined('KB_PROFILE'))
			{
				DBDEBUG::recordError('Connect Error('.mysqli_connect_errno().') '.mysqli_connect_error());
			}
			die(mysqli_connect_error()."<br />\nUnable to connect to mysql database.");
		}
		if(method_exists(self::$conn_id,'set_charset')) self::$conn_id->set_charset('utf8');
	}
	/**
	 * Return the connection id for this connection.
	 *
	 * Used for connection specific commands.
	 *
	 * @return mysqli
	 */
	public static function id()
	{
		if(is_null(self::$conn_id)) self::init();
		return self::$conn_id;
	}
	/**
	 * Return the number of rows affected by a query.
	 *
	 * @return integer
	 */
	public static function affectedRows()
	{
		if(is_null(self::$conn_id)) self::init();
		return mysqli_affected_rows(self::$conn_id);
	}
	/**
	 * Close the connection if it exists.
	 *
	 * @return boolean TRUE on success or FALSE on failure.
	 */
	public static function close()
	{
		if(is_null(self::$conn_id)) return true;
		$res = self::$conn_id->close();
		self::$conn_id = null;
		return $res;
	}
}

