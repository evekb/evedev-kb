<?php
options::cat('Maintenance', 'Database', 'Table Checks');
options::fadd('This checks automatically your database', 'none', 'custom', array('admin_db', 'checkDatabase'), array('admin_db', 'none'));
options::fadd('Current SQL cache size', 'none', 'custom', array('admin_db', 'checkCache'), array('admin_db', 'killCache'));

class admin_db
{
    function checkDatabase()
    {
        // nothing to do atm
        require_once("common/includes/autoupgrade.php");
        updateDB();
    }

    function none()
    {
        // do nothing on submit
    }

    function checkCache()
    {
        $size = 0;
        $dir = opendir(KB_CACHEDIR);
        while ($line = readdir($dir))
        {
            if (strstr($line, 'qcache_qry') !== false)
            {
                $size += filesize(KB_CACHEDIR.'/'.$line);
            }
        }

        // GB
        if (($size / 1073741824) > 1){
        	return round($size/1073741824, 4).' GB <input type="checkbox" name="option_sql_clearcache" />Clear cache ?';
        // MB
        }elseif (($size / 1048576) > 1){
        	return round($size/1048576, 4).' MB <input type="checkbox" name="option_sql_clearcache" />Clear cache ?';
		// KB
    	}else{
	        return round($size/1024, 2).' KB <input type="checkbox" name="option_sql_clearcache" />Clear cache ?';
        }
    }

    function killCache()
    {
        if ($_POST['option_sql_clearcache'] != 'on')
        {
            return;
        }

        $dir = opendir(KB_CACHEDIR);
        while ($line = readdir($dir))
        {
            if (strstr($line, 'qcache_qry') !== false)
            {
                @unlink(KB_CACHEDIR.'/'.$line);
            }
            elseif (strstr($line, 'qcache_tbl') !== false)
            {
                @unlink(KB_CACHEDIR.'/'.$line);
            }
        }
    }
}
?>