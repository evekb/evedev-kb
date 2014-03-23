<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


$modInfo['mail_forward']['name'] = "Mail Forwarder";
$modInfo['mail_forward']['abstract'] = "Forward all posted mails to another board.";
$modInfo['mail_forward']['about'] = "Core distribution mod.";

event::register('killmail_added', 'post_forward::handler');
event::register('killmail_imported', 'import_forward::importhandler');

/**
 * @package EDK
 */
class post_forward
{
    public static function handler($object)
    {
        if (config::get('forward_active') == false)
        {
            return;
        }
        $req = new http_request(config::get('forward_site').'?a=post');

        $req->set_postform('password', config::get('forward_pass'));
        $req->set_postform('killmail', stripslashes($_POST['killmail']));
        $req->request();
    }
}


class import_forward
{
    public static function importhandler($object)
    {
   		if (config::get('forward_active') == false)
        {
            return;
        }
        require_once('common/includes/class.http.php');

        $req = new http_request(config::get('forward_site').'?a=post');

        $req->set_postform('password', config::get('forward_pass'));
        $req->set_postform('killmail', stripslashes($object->killmail_));
        $req->request();
   }
}

?>