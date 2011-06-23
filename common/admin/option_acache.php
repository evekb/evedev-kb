<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/*
* This file contains the generic admin options in the new format
* look here for some examples.
*/
options::cat('Advanced', 'Cache', 'Page Cache');
options::fadd('Enable Page Cache', 'cache_enabled', 'checkbox','', array('admin_acache', 'clearPCache'), "Cache created webpages");
options::fadd('Global lifetime', 'cache_time', 'edit:size:4','','','minutes');
options::fadd('Ignore pages', 'cache_ignore', 'edit:size:60','','','page1,page2 [no spaces]');
options::fadd('Page specific lifetime', 'cache_times', 'edit:size:60','','','page:time,page2:time [no spaces]');
options::fadd('Pages updated every kill', 'cache_update', 'edit:size:60','','','* or page1,page2 [no spaces]');

options::cat('Advanced', 'Cache', 'Query Cache');
options::fadd('Enable SQL-Query File Cache', 'cfg_qcache', 'checkbox', '', '','Select only one of file cache or memcache');
options::fadd('Enable SQL-Query MemCache', 'cfg_memcache', 'checkbox','','','Requires a separate memcached installation');
options::fadd('Memcached server', 'cfg_memcache_server', 'edit:size:50');
options::fadd('Memcached port', 'cfg_memcache_port', 'edit:size:8');
options::fadd('Halt on SQLError', 'cfg_sqlhalt', 'checkbox');

options::cat('Advanced', 'Cache', 'Killmail Cache');
options::fadd('Killmail Caching enabled','km_cache_enabled','checkbox');
options::fadd('Cached Killmails', 'none', 'custom', array('admin_acache', 'getKillmails'));

options::cat('Advanced', 'Cache', 'Reinforced Control');
options::fadd('Enable Reinforced Management', 'auto_reinforced', 'checkbox', '', array('admin_acache', 'setNotReinforced'));
options::fadd('Current Load', 'none', 'custom', array('admin_acache', 'showLoad'));
options::fadd('Reinforcement threshold', 'reinforced_threshold', 'edit:size:4', '', '', 'load above this threshold triggers reinforced mode');
options::fadd('Disabling threshold', 'reinforced_disable_threshold', 'edit:size:4', '', '', 'load below this threshold exits reinforced mode');
options::fadd('Reinforcement chance', 'reinforced_prob', 'edit:size:4', '', '', '1/x chance each page view');
options::fadd('Reinforcement end chance', 'reinforced_rf_prob', 'edit:size:4','','','1/x chance each page view');

options::cat('Advanced', 'Cache', 'Clear Caches');
options::fadd('File Cache', 'none', 'custom', array('admin_acache', 'optionClearCaches'), array('admin_acache', 'clearCaches'));
options::fadd('Kill Summary Cache', 'none', 'custom', array('admin_acache', 'optionClearSum'), array('admin_acache', 'clearCaches'));

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
		if(defined('KB_MAILCACHEDIR'))
		{
			if(is_dir(KB_MAILCACHEDIR))
			{
				if($files = scandir(KB_MAILCACHEDIR))
				{
					foreach($files as $file)
					{
						if (substr($file, 0, 1) != '.') $count++;
					}
					
				}
			}
		}
		return $count;
    }
    function clearPCache()
    {
		CacheHandler::removeByAge('store', 0);
    }
    function setNotReinforced()
    {
            config::set('is_reinforced', '0');
    }
	function optionClearCaches()
	{
		return '<input type="checkbox" name="option_clear_caches" />Clear caches ?';
	}
	function optionClearSum()
	{
		return '<input type="checkbox" name="option_clear_sum" />Clear cache ?';
	}
	function clearCaches()
	{
        if ($_POST['option_clear_caches'] == 'on')
        {
			CacheHandler::removeByAge('bob', 0, true);
			CacheHandler::removeByAge('data', 0, true);
			CacheHandler::removeByAge('api', 0, true);
			CacheHandler::removeByAge('store', 0, true);
			CacheHandler::removeByAge('mails', 0, true);
			CacheHandler::removeByAge('img', 0, true);
			CacheHandler::removeByAge('templates_c/'.KB_SITE, 0);
			CacheHandler::removeByAge('SQL', 0, true);
			$_POST['option_clear_caches'] = 'off';
        }
        if ($_POST['option_clear_sum'] == 'on')
        {
			$qry = DBFactory::getDBQuery();;
			$qry->execute("DELETE FROM kb3_sum_alliance");
			$qry->execute("DELETE FROM kb3_sum_corp");
			$qry->execute("DELETE FROM kb3_sum_pilot");
			// Clear page and query cache as well since they also contain the
			// summaries.
			CacheHandler::removeByAge('SQL', 0, true);
			CacheHandler::removeByAge('store', 0, true);
			$_POST['option_clear_sum'] == 'off';
        }
		return;
	}
	function removeOld($hours, $dir, $recurse = false)
	{
		if(!session::isAdmin()) return false;
		if(strpos($dir, '.') !== false) return false;
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
				$del = $del + admin_acache::removeOld($hours, $dir.$fname."/",$recurse);
			}
		}
		return $del;
	}
}