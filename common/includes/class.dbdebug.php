<?php

class DBDebug
{
	function recordError($text)
	{
		$qerrfile = "/tmp/EDKprofile.lst";
		if($text) file_put_contents($qerrfile, $text."\n", FILE_APPEND);
	}
	function profile($sql, $time = 0)
	{
		$qerrfile = "/tmp/EDKprofile.lst";
		if($text) file_put_contents($qerrfile, $text."\n", FILE_APPEND);
		if (KB_PROFILE == 2)
		{
			file_put_contents($qerrfile, $sql . "\nExecution time: " . $time . "\n", FILE_APPEND);
		}
		if (KB_PROFILE == 3)
		{
			if(DB_TYPE == 'mysqli' && strtolower(substr($sql,0,6))=='select')
			{
				$dbconn = new DBConnection;
				$prof_out_ext = $prof_out_exp = '';
				$prof_qry= mysqli_query($dbconn->id(),'EXPLAIN extended '.$sql.";");
				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_exp .= implode(' | ', $prof_row)."\n";
				$prof_qry= mysqli_query($dbconn->id(),'show warnings');

				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_ext .= implode(' | ', $prof_row)."\n";
				file_put_contents($qerrfile, $sql . "\n".
						$prof_out_ext. $prof_out_exp.
						"\n-- Execution time: " . $time . " --\n", FILE_APPEND);
			}
			else file_put_contents($qerrfile, $sql."\nExecution time: ".$time."\n", FILE_APPEND);
		}

		if (KB_PROFILE == 4)
		{
			if($time > 0.1 && strtolower(substr($sql,0,6))=='select')
			{
				$dbconn = new DBConnection;
				$prof_out_exp = $prof_out_exp = '';
				$prof_qry= mysqli_query($dbconn->id(),'EXPLAIN extended '.$sql);
				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_exp .= implode(' | ', $prof_row)."\n";
				$prof_qry= mysqli_query($dbconn->id(),'show warnings');

				while($prof_row = mysqli_fetch_assoc($prof_qry))
					$prof_out_ext .= implode(' | ', $prof_row)."\n";
				file_put_contents($qerrfile, $sql . "\n".
						$prof_out_ext. $prof_out_exp.
						"\n-- Execution time: " . $time . " --\n", FILE_APPEND);
			}
		}

	}
	function killCache()
	{
		if(!is_dir(KB_QUERYCACHEDIR)) return;
		$dir = opendir(KB_QUERYCACHEDIR);
		while ($line = readdir($dir))
		{
			if (strstr($line, 'qcache_qry') !== false)
			{
				@unlink(KB_QUERYCACHEDIR.'/'.$line);
			}
			elseif (strstr($line, 'qcache_tbl') !== false)
			{
				@unlink(KB_QUERYCACHEDIR.'/'.$line);
			}
		}
	}

}

