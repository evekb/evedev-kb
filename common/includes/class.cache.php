<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Page caching class
 *
 * Contains methods to create and retrieve a complete cache of the current page.
 * @package EDK
 */
class cache
{

    private static $cacheName = null;
    // default page name, updated in cache::check(), used in cache::generate()
    private static $page = null; 
    protected static $reinforced_enable_threshold = 10;
    protected static $reinforced_disable_threshold = 5;
    protected static $reinforced_prob = 20;

    /**
     * Check if the current page should be cached.
     *
     * @param string $page The current page
     * @return boolean
     */
    protected static function shouldCache($page)
    {
        // never cache for admins
        if (session::isAdmin() || session::isCachingForciblyDisabled()) {
            return false;
        }
        // Don't cache the image files.
        if ($page == 'thumb' || $page == 'sig') {
            return false;
        }

        $cacheignore = explode(',', config::get('cache_ignore'));
        if (config::get('cache_enabled')
                && count($_POST) == 0
                && !(isset($page) && $page != '' && in_array($page, $cacheignore))) {
            return true;
        }
        return false;
    }

    /**
     * Check if the current page is cached and valid then send it if so.
     *
     * @param string $page The current page.
     */
    public static function check($page)
    {
        self::$page = $page;  // store $page value for cache::generate()

        // Set an old expiry date to discourage the browser from trying to
        // cache the page.
        if ($page != 'mapview' && $page != 'sig') {
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            header('Cache-Control: no-cache');
        }
        $usegz = config::get('cfg_compress')
                && !ini_get('zlib.output_compression');
        $cachefile = cache::genCacheName();
        if (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true) {
            $cachehandler = new CacheHandlerHashedMem();
        }  elseif (defined('DB_USE_REDIS') && DB_USE_REDIS == true) {
            $cachehandler = new CacheHandlerHashedRedis();
        } else {
            $cachehandler = new CacheHandlerHashed();
        }
        // If the cache doesn't exist then we don't need to check times.
        if (cache::shouldCache($page)
                && $cachehandler->exists(cache::genCacheName())) {
            $cachetime = self::expiry($page);
            $timestamp = time() - $cachehandler->age($cachefile);

            if (config::get('cache_update') == '*'
                    && file_exists(KB_CACHEDIR.'/killadded.mk')
                    && $timestamp < @filemtime(KB_CACHEDIR.'/killadded.mk')) {
                $timestamp = 0;
            } else {
                $cacheupdate = explode(',', config::get('cache_update'));
                if (($page != '' && in_array($page, $cacheupdate))
                        && file_exists(KB_CACHEDIR.'/killadded.mk')
                        && $timestamp < @filemtime(KB_CACHEDIR.'/killadded.mk')) {
                    $timestamp = 0;
                }
            }
            if (time() - $cachetime < $timestamp) {
                // Alternatively, use a hash of the file. More cpu for a little
                // less bandwidth. Possibly more useful if we keep an index.
                // filename, age, hash. Age would be used for cache clearing.
                $etag = md5($cachefile.$timestamp);
                if ($usegz
                        && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip") !== false)
                        $etag .= 'gz';
                header("Etag: \"".$etag."\"");

                header("Last-Modified: ".gmdate("D, d M Y H:i:s", $timestamp)." GMT");

                // There was a reason for having both checks. etag not always
                // checked maybe?
                if ((isset($_SERVER['HTTP_IF_NONE_MATCH'])
                                && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $etag) !== false)
                        || (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
                                && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
                                        == $timestamp)) {
                    header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
                    exit;
                }

                if ($usegz) {
                    ob_start("ob_gzhandler");
                } else {
                    ob_start();
                }
                echo $cachehandler->get($cachefile);
                ob_end_flush();
                exit();
            }
            if ($usegz) {
                ob_start("ob_gzhandler");
            } else {
                ob_start();
            }
        }
        // Don't turn on gzip when sending images.
        elseif (cache::shouldCache($page)) {
            if ($usegz) {
                ob_start("ob_gzhandler");
            } else {
                ob_start();
            }
        }
        // If the page cache is off we still compress pages if asked.
        else if ($usegz) {
            ob_start("ob_gzhandler");
        }
    }

    /**
     * Generate the cache for the current page.
     */
    public static function generate()
    {
        $page = self::$page;  // Equals null unless cache::check() was called

        if (!is_string($page)) {
            $page = null;
        }
        if (cache::shouldCache($page)) {  // check caching for particular page
            $usegz = config::get('cfg_compress')
                    && !ini_get('zlib.output_compression');
            $cachefile = cache::genCacheName();

            if (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true) {
                $cachehandler = new CacheHandlerHashedMem();
            } elseif (defined('DB_USE_REDIS') && DB_USE_REDIS == true) {
                $cachehandler = new CacheHandlerHashedRedis();
            } else {
                $cachehandler = new CacheHandlerHashed();
            }
            $cachehandler->put($cachefile,
                    preg_replace('/profile -->.*<!-- \/profile/',
                            'profile -->Cached '
                            .gmdate("d M Y H:i:s").'<!-- /profile', ob_get_contents()), null,
                    self::expiry($page));

            $timestamp = time() - $cachehandler->age($cachefile);
            $etag = md5($cachefile.$timestamp);
            if ($usegz
                    && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip") !== false) {
                $etag .= 'gz';
            }

            header("Etag: \"".$etag."\"");
            header("Last-Modified: ".gmdate("D, d M Y H:i:s", $timestamp)." GMT");
            ob_end_flush();
        }
    }

    /**
     * Generate the cache filename.
     *
     * Security modification could change this function to generate access
     * level specific cache files.
     *
     * @return string string of path and filename for the current page's
     * cachefile.
     */
    protected static function genCacheName()
    {
        if (isset(self::$cacheName)) {
            return self::$cacheName;
        }

        global $themename, $stylename;
        $basename = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].IS_IGB
                .$themename.$stylename;
        event::call('cacheNaming', $basename);
        self::$cacheName = KB_SITE.$basename;
        return self::$cacheName;
    }

    /**
     * Remove the cache of the current page.
     */
    public static function deleteCache()
    {
        $cachefile = cache::genCacheName();

        if (defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true) {
            $cachehandler = new CacheHandlerHashedMem();
        }  elseif (defined('DB_USE_REDIS') && DB_USE_REDIS == true) {
            $cachehandler = new CacheHandlerHashedRedis();
        } else {
            $cachehandler = new CacheHandlerHashed();
        }
        $cachehandler->remove($cachefile);
    }

    /**
     * Mark the cached page as still current without rebuilding it.
     */
    public static function touchCache()
    {
        if (!config::get('cache_enabled')) {
            return;
        }
        if (!file_exists(KB_PAGECACHEDIR.'/'.KB_SITE)) {
            mkdir(KB_PAGECACHEDIR.'/'.KB_SITE);
        }
        touch(cache::genCacheName());
    }

    /**
     * Notify the cache that a kill has been added.
     */
    public static function notifyKillAdded()
    {
        if (!config::get('cache_enabled')) {
            return;
        }
        if (!file_exists(KB_PAGECACHEDIR)) {
            mkdir(KB_PAGECACHEDIR);
        }
        touch(KB_CACHEDIR.'/killadded.mk');
    }

    /**
     * Get the expiry time for a given page.
     * @param string $page
     *
     * @return integer timestamp for the page's expiry time
     */
    protected static function expiry($page)
    {
        $cachetimes = array();
        if (config::get('cache_times')) {
            $times = explode(',', config::get('cache_times'));
            foreach ($times as $string) {
                $array = explode(':', $string);
                $cachetimes[$array[0]] = $array[1];
            }
        }

        if (isset($cachetimes[$page])) {
            $cachetime = $cachetimes[$page];
        } else {
            $cachetime = config::get('cache_time');
        }

        $cachetime = $cachetime * 60;

        return $cachetime;
    }

}
