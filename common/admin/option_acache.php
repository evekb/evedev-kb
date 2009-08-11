<?php
/*
* This file contains the generic admin options in the new format
* look here for some examples.
*/
options::cat('Advanced', 'Cache', 'Page Cache');
options::fadd('Caching enabled', 'cache_enabled', 'checkbox','', array('admin_acache', 'clearPCache'));
options::fadd('Cache lifetime (min)', 'cache_time', 'edit:size:4');
options::fadd('Cache directory', 'cache_dir', 'edit:size:40');
options::fadd('Ignore pages', 'cache_ignore', 'edit:size:60','','','page1,page2 [no spaces]');
options::fadd('Page specific times', 'cache_times', 'edit:size:60','','','page:time,page2:time [no spaces]');
options::fadd('Pages updated every kill', 'cache_update', 'edit:size:60','','','* or page1,page2 [no spaces]');

options::cat('Advanced', 'Cache', 'Query Cache');
options::fadd('Enable SQL-Query MemCache', 'cfg_memcache', 'checkbox');
options::fadd('Memcached server', 'cfg_memcache_server', 'edit:size:50');
options::fadd('Memcached port', 'cfg_memcache_port', 'edit:size:8');
options::fadd('Halt on SQLError', 'cfg_sqlhalt', 'checkbox');
options::fadd('Enable SQL-Query File Cache', 'cfg_qcache', 'checkbox', '', array('admin_acache', 'clearQCache'));

options::cat('Advanced', 'Cache', 'Killmail Cache');
options::fadd('Killmail Caching enabled','km_cache_enabled','checkbox');
options::fadd('Killmail Cache directory', 'km_cache_dir', 'edit:size:40');
options::fadd('Cached Killmails', 'none', 'custom', array('admin_acache', 'getKillmails'));

options::cat('Advanced', 'Cache', 'Reinforce Control');
options::fadd('Enable Reinforced Management', 'auto_reinforced', 'checkbox', '', array('admin_acache', 'setNotReinforced'));
options::fadd('Current Load', 'none', 'custom', array('admin_acache', 'showLoad'));
options::fadd('Reinforcement threshold', 'reinforced_threshold', 'edit:size:4', '', '', 'load above this threshold triggers reinforced mode');
options::fadd('Disabling threshold', 'reinforced_disable_threshold', 'edit:size:4', '', '', 'load below this threshold exits reinforced mode');
options::fadd('Reinforcement chance', 'reinforced_prob', 'edit:size:4', '', '', '1/x chance each page view');
options::fadd('Reinforcement end chance', 'reinforced_rf_prob', 'edit:size:4','','','1/x chance each page view');

options::cat('Advanced', 'Cache', 'Clear Caches');
options::fadd('Page Cache', 'none', 'custom', array('admin_acache', 'optionClearPage'), array('admin_acache', 'clearCaches'));
options::fadd('Template Cache', 'none', 'custom', array('admin_acache', 'optionClearTemplate'), array('admin_acache', 'clearCaches'));
//options::fadd('Mail Cache', 'none', 'custom', array('admin_acache', 'optionClearMail'), array('admin_acache', 'clearCaches'));
options::fadd('SQL Query Cache', 'none', 'custom', array('admin_acache', 'optionClearSQL'), array('admin_acache', 'clearCaches'));
options::fadd('Kill Summary Cache', 'none', 'custom', array('admin_acache', 'optionClearSum'), array('admin_acache', 'clearCaches'));
options::fadd('All Caches', 'none', 'custom', array('admin_acache', 'optionClearAll'), array('admin_acache', 'clearCaches'));

class admin_acache
{
    function showLoad()
    {
        $load = @file_get_contents('/proc/loadavg');
        if (false === $load)
        {
            return "Your web host does not allow access to the load metric. Reinforced mode will not work.";
        }
        else
        {
            $array = explode(' ', $load);
            return (float)$array[0];
        }
    }

    function getKillmails()
    {
		$count = 0;
		if (config::get('km_cache_dir'))
		{
			$dir   = config::get('km_cache_dir');
			if(is_dir($dir))
			{
				if($handle = opendir($dir))
				{
					while(($file = readdir($handle)) !== false)
					{
						if (!is_dir($file)) $count++;
					}
					closedir($handle);
				}
			}
		}
		return $count;
    }
    function clearPCache()
    {
		admin_acache::removeOld(0, KB_CACHEDIR.'/'.KB_SITE, false);
    }
    function clearQCache()
    {
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
    function setNotReinforced()
    {
            config::set('is_reinforced', '0');
    }
	function optionClearPage()
	{
		return '<input type="checkbox" name="option_clear_page" />Clear cache ?';
	}
	function optionClearMail()
	{
		return '<input type="checkbox" name="option_clear_mail" />Clear cache ?';
	}
	function optionClearSum()
	{
		return '<input type="checkbox" name="option_clear_sum" />Clear cache ?';
	}
	function optionClearSQL()
	{
		return '<input type="checkbox" name="option_clear_sql" />Clear cache ?';
	}
	function optionClearTemplate()
	{
		return '<input type="checkbox" name="option_clear_template" />Clear cache ?';
	}
	function optionClearAll()
	{
		return '<input type="checkbox" name="option_clear_all" />Clear cache ?';
	}
	function clearCaches()
	{
        if ($_POST['option_clear_page'] == 'on')
        {
			admin_acache::removeOld(0, KB_CACHEDIR.'/'.KB_SITE, false);
			$_POST['option_clear_page'] = 'off';
        }
        if ($_POST['option_clear_template'] == 'on')
        {//hardcoded in index.php
			admin_acache::removeOld(0, 'cache/templates_c', false);
			$_POST['option_clear_template'] == 'off';
        }
        if ($_POST['option_clear_mail'] == 'on')
        {
			admin_acache::removeOld(0, config::get('km_cache_dir'), false);
			$_POST['option_clear_mail'] == 'off';
        }
        if ($_POST['option_clear_sum'] == 'on')
        {
			$qry = new DBQuery();
			$qry->execute("DELETE FROM kb3_sum_alliance");
			$qry->execute("DELETE FROM kb3_sum_corp");
			$qry->execute("DELETE FROM kb3_sum_pilot");
			// Clear page and query cache as well since they also contain the
			// summaries.
			admin_acache::clearQCache();
			admin_acache::removeOld(0, KB_CACHEDIR.'/'.KB_SITE, false);
			$_POST['option_clear_sum'] == 'off';
        }
        if ($_POST['option_clear_sql'] == 'on')
        {
			admin_acache::clearQCache();
			// Also clear the page cache since it will have cached the results
			// of the file cache
			admin_acache::removeOld(0, KB_CACHEDIR.'/'.KB_SITE, false);
			$_POST['option_clear_sql'] == 'off';
        }
        if ($_POST['option_clear_all'] == 'on')
        {
			admin_acache::removeOld(0, KB_CACHEDIR.'/'.KB_SITE, false);
			admin_acache::removeOld(0, 'cache/templates_c', false);
			admin_acache::removeOld(0, config::get('km_cache_dir'), false);
			admin_acache::clearQCache();
			admin_acache::removeOld(0, 'cache/portraits', false);
			admin_acache::removeOld(0, 'cache/data', false);
			admin_acache::removeOld(0, 'cache/map', false);
			admin_acache::removeOld(0, 'cache/api', false);
			admin_acache::removeOld(0, 'cache/corp', false);
			$qry = new DBQuery();
			$qry->execute("DELETE FROM kb3_sum_alliance");
			$qry->execute("DELETE FROM kb3_sum_corp");
			$qry->execute("DELETE FROM kb3_sum_pilot");
			$_POST['option_clear_all'] == 'off';
        }
		return;
	}
	function removeOld($hours, $dir, $recurse = false)
	{
		if(!session::isAdmin()) return false;
		if(strpos($dir, '.') !== false) return false;
		//$dir = KB_CACHEDIR.'/'.$dir;
		if(!is_dir($dir)) return false;
		if(substr($dir,-1) != '/') $dir = $dir.'/';
		$seconds = $hours*60*60;
		$files = scandir($dir);

		foreach ($files as $num => $fname)
		{
			if (file_exists("{$dir}{$fname}") && !is_dir("{$dir}{$fname}") && substr($fname,0,1) != "." && ((time() - filemtime("{$dir}{$fname}")) > $seconds))
			{ 
				$mod_time = filemtime("{$dir}{$fname}");
				if (unlink("{$dir}{$fname}")) $del = $del + 1;
			}
			if ($recurse && file_exists("{$dir}{$fname}") && is_dir("{$dir}{$fname}")
				 && substr($fname,0,1) != "." && $fname !== ".." )
			{
				$del = $del + admin_acache::remove_old($hours, $dir.$fname."/");
			}
		}
		return $del;
	}
}
?>
