<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

/*
 * http_request class
 *
 * useful to avoid allow_url_fopen_wrapper issues
 * and to get or post data from anywhere we want
 *
 */

trigger_error("Directly including class.http.php is deprecated. If you need a direct include, use class.httprequest.php", E_USER_DEPRECATED);

require_once("class.httprequest.php");