<?php

class API {
private $error = null;

function API() {
// Loads pheal, so we can do some stuff with it, 
require_once("common/pheal/Pheal.php");
spl_autoload_register("Pheal::classload");

PhealConfig::getInstance()->http_method = 'curl';
PhealConfig::getInstance()->http_post = false;
PhealConfig::getInstance()->http_keepalive = true; 
// default 15 seconds
PhealConfig::getInstance()->http_keepalive = 10; 
// KeepAliveTimeout in seconds
PhealConfig::getInstance()->http_timeout = 60;
//PhealConfig::getInstance()->cache = new PhealMemcache(array('port' => 11211));
PhealConfig::getInstance()->cache = new PhealFileCache('cache/pheal/');
PhealConfig::getInstance()->api_customkeys = true;
PhealConfig::getInstance()->log = new PhealFileLog('cache/');

}

function CallAPI( $scope, $call, $data, $userid, $key ) {
//echo $scope, $call, $userid, $key;
$pheal = new Pheal($userid, $key, $scope);

$this->error = null;

try {
if( is_array( $data ) ) {
$result = $pheal->{$call}($data);
} else {
$result = $pheal->{$call}();
}
} catch(PhealException $e) {
//echo 'error: ' . $e->code . ' message: ' . $e->getMessage();
$this->error = $e->code;
return false;
}

return $result;
}

/**
* Return any errors encountered or false if none.
*/
function getError()
{
return $this->error;
}	
}