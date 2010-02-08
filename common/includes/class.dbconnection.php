<?php

//! mysqli connection class.
//! Establishes the connection to the database.
class DBConnection
{
	//! Set up a mysqli DB connection.
	function DBConnection()
	{
		static $conn_id;

		if ($conn_id)
		{
			$this->id_ = $conn_id;
			return;
		}
		if(defined('DB_PORT'))
		{
			if (!$this->id_ = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT))
				die("Unable to connect to mysql database.");
			if(method_exists($this->id_,'set_charset')) $this->id_->set_charset('utf8');
		}
		else
		{
			if (!$this->id_ = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME))
				die("Unable to connect to mysql database.");
			if(method_exists($this->id_,'set_charset')) $this->id_->set_charset('utf8');
		}

		//mysqli_select_db(DB_NAME);
		$conn_id = $this->id_;
	}
	//! Return the connection id for this connection. Used for connection specific commands.
	function id()
	{
		return $this->id_;
	}
	//! Return the number of rows affected by a query.
	function affectedRows()
	{
		return mysqli_affected_rows($this->id_);
	}
}

