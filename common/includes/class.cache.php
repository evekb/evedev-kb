<?php
//! Page caching class

//! Contains methods to create and retrieve a complete cache of the current page.
class cache
{
	//! Check the server load using /proc/loadavg.
    function checkLoad()
    {
        if (PHP_OS != 'Linux')
        {
            return false;
        }

        $load = @file_get_contents('/proc/loadavg');
        if (false === $load)
        {
            return false;
        }
        $array = explode(' ', $load);
        if ((float)$array[0] > (float)config::get('reinforced_threshold'))
        {
            // put killboard into RF
            config::set('is_reinforced', 1);
        }
        elseif ((float)$array[0] > (float)config::get('reinforced_disable_threshold') && config::get('is_reinforced'))
        {
            // do nothing, we are in RF, load is dropping but stil over disabling threshold
        }
        else
        {
            // load low, dont enter reinforced
            config::set('is_reinforced', 0);
        }
    }
	//! Check if the current page should be cached.
    function shouldCache($page = '')
    {
        // never cache for admins
        if (session::isAdmin())
        {
            return false;
        }
		// Don't cache the image files.
		if (strpos($_SERVER['REQUEST_URI'],'thumb') ||
			strpos($_SERVER['REQUEST_URI'],'mapview')) return false;
        if (config::get('auto_reinforced') && config::get('is_reinforced') && count($_POST) == 0)
        {
            return true;
        }

        $cacheignore = explode(',', config::get('cache_ignore'));
		if (config::get('cache_enabled') == 1 && count($_POST) == 0 && !($page != '' && in_array($page, $cacheignore)))
        {
            return true;
        }
		return false;
    }
	//! Check if the current page is cached and valid then send it if so.
    function check($page)
    {
        if (cache::shouldCache($page))
        {
            if (!file_exists(KB_CACHEDIR.'/'.KB_SITE))
            {
                mkdir(KB_CACHEDIR.'/'.KB_SITE);
            }

			$cachefile = cache::genCacheName();
			
            $times = explode(',', config::get('cache_times'));
            foreach ($times as $string)
            {
                $array = explode(':', $string);
                $cachetimes[$array[0]] = $array[1];
            }

            if ($cachetimes[$page])
            {
                $cachetime = $cachetimes[$page];
            }
            else
            {
                $cachetime = config::get('cache_time');
            }
			
			$cachetime = config::get('cache_time');
            $cachetime = $cachetime * 60;

			if (config::get('is_reinforced'))
            {
                global $smarty;
                $smarty->assign('message', 'Note: This killboard has entered reinforced operation mode.');
				// cache is extended in reinforced mode
				$cachetime = $cachetime * 20;
            }
			if(file_exists($cachefile)) $timestamp = @filemtime($cachefile);
			else $timestamp = 0;
			
			if(config::get('cache_update') == '*')
				if(file_exists(KB_CACHEDIR.'/killadded.mk'))
					if($timestamp < @filemtime(KB_CACHEDIR.'/killadded.mk'))
						$timestamp = 0;
			else
			{
				$cacheupdate = explode(',', config::get('cache_update'));
				if (($page != '' && in_array($page, $cacheupdate)))
					if(file_exists(KB_CACHEDIR.'/killadded.mk'))
						if($timestamp < @filemtime(KB_CACHEDIR.'/killadded.mk'))
							$timestamp = 0;
			}
            if (time() - $cachetime < $timestamp)
            {
				$etag=md5($cachefile);
				header("Last-Modified: ".gmdate("D, d M Y H:i:s", $timestamp)." GMT");
// Breaks comment posting.
//				header('Expires: ' . gmdate('D, d M Y H:i:s', $timestamp + $cachetime) . ' GMT');
				header("Etag: ".md5($etag));
				header("Cache-Control:");
				header('Pragma:');

				if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $timestamp ||
					trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)
				{
					header("HTTP/1.1 304 Not Modified");
					exit;
				}

                ob_start();
                @readfile($cachefile);
                ob_end_flush();
                exit();
            }
            ob_start();
        }
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
//		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+60) . ' GMT');
		header("Etag: ".md5($cachefile));
		header("Cache-Control:");
		header('Pragma:');
    }
	//! Generate the cache for the current page.
    function generate()
    {
        if (cache::shouldCache())
        {
            $cachefile = cache::genCacheName();
            $fp = @fopen($cachefile, 'w');
			//@fwrite($fp, ob_get_contents());

            @fwrite($fp, preg_replace('/profile -->.*<!-- \/profile/','profile -->Cached '.gmdate("d M Y H:i:s").'<!-- /profile',ob_get_contents()));
            //if(!strpos($_SERVER['REQUEST_URI'], 'feed')) @fwrite($fp, '<!-- Generated from cache -->');
            @fclose($fp);
            ob_end_flush();
        }
    }
	//! Generate the cache filename.

	//! \return string of path and filename for the current page's cachefile.
	function genCacheName()
	{
		// Security mods can add access specific cache names here
//		return KB_CACHEDIR.'/'.KB_SITE.'/'.md5($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].@implode($_SESSION)).'.cache';
		return KB_CACHEDIR.'/'.KB_SITE.'/'.md5($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'.cache';
	}
	//! Remove the cache of the current page.
	function deleteCache()
	{
		$cachefile = cache::genCacheName();
		@unlink($cachefile);
	}
	//! Mark the cached page as still current without rebuilding it.
	function touchCache()
	{
		if(! config::get('cache_enabled') ) return;
		if (!file_exists(KB_CACHEDIR.'/'.KB_SITE))
			mkdir(KB_CACHEDIR.'/'.KB_SITE);
		touch(cache::genCacheName());
	}
	//! Notify the cache that a kill has been added.
	function notifyKillAdded()
	{
		if(! config::get('cache_enabled') ) return;
		if (!file_exists(KB_CACHEDIR))
			mkdir(KB_CACHEDIR);
		touch(KB_CACHEDIR.'/killadded.mk');
	}
}
?>