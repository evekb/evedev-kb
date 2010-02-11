<?php
require_once('common/includes/class.parser.php');
require_once('common/includes/class.phpmailer.php');
require_once('common/includes/class.kill.php');
require_once('common/includes/class.logger.php');

$page = new Page('Post killmail');
global $smarty;
if (isset($_POST['killmail']))
{
    if (config::get("post_password") == '' || crypt($_POST['password'],config::get("post_password")) == config::get("post_password") || $page->isAdmin())
    {
        $parser = new Parser($_POST['killmail']);

        // Filtering
        if (config::get('filter_apply'))
        {
            $filterdate = config::get('filter_date');
            $year = substr($_POST['killmail'], 0, 4);
            $month = substr($_POST['killmail'], 5, 2);
            $day = substr($_POST['killmail'], 8, 2);
            $killstamp = mktime(0, 0, 0, $month, $day, $year);
            if ($killstamp < $filterdate)
            {
                $killid = -3;
            }
            else
            {
                $killid = $parser->parse(true, null, false);
            }
        }
        else
        {
            $killid = $parser->parse(true, null, false);
        }

        if ($killid == 0 || $killid == -1 || $killid == -2 || $killid == -3)
        {
            if ($killid == 0)
            {
                $html = "Killmail is malformed.<br/>";
                if ($errors = $parser->getError())
                {
                    foreach ($errors as $error)
                    {
                        $html .= 'Error: '.$error[0];
                        if ($error[1])
                        {
                            $html .= ' The text lead to this error was: "'.$error[1].'"';
                        }
                        $html .= '<br/>';
                    }
                }
            }
            elseif ($killid == -1)
            {
                $html = "That killmail has already been posted <a href=\"?a=kill_detail&kll_id=".$parser->dupeid_."\">here</a>.";
            }
            elseif ($killid == -2)
            {
                $html = "You are not authorized to post this killmail.";
            }
            elseif ($killid == -3)
            {
                $filterdate = kbdate("j F Y", config::get("filter_date"));
                $html = "You are not allowed to post killmails older than $filterdate.";
            }
        }
        else
        {
            if (config::get('post_mailto') != "")
            {
                $mailer = new PHPMailer();
                $kill = new Kill($killid);

                if (!$server = config::get('post_mailserver'))
                {
                    $server = 'localhost';
                }
                $mailer->From = "mailer@".config::get('post_mailhost');
                $mailer->FromName = config::get('post_mailhost');
                $mailer->Subject = "Killmail #" . $killid;
                $mailer->Host = $server;
                $mailer->Port = 25;
                $mailer->Helo = $server;
                $mailer->Mailer = "smtp";
                $mailer->AddReplyTo("no_reply@".config::get('post_mailhost'), "No-Reply");
                $mailer->Sender = "mailer@".config::get('post_mailhost');
                $mailer->Body = $_POST['killmail'];
                $mailer->AddAddress(config::get('post_mailhost'));
                $mailer->Send();
            }

            logger::logKill($killid);
//			$qry = DBFactory::getDBQuery();;
//            $qry->execute("insert into kb3_log (log_kll_id, log_site, log_ip_address, log_timestamp) values(".
//                    $killid.",'".KB_SITE."','".getip()."', now())");

            header("Location: ?a=kill_detail&kll_id=".$killid);
            exit;
        }
    }
    else
    {
        $html = "Invalid password.";
    }
}
if($html) $smarty->assign('error', $html);
$smarty->assign('isadmin', $page->isAdmin());
$smarty->assign('post_forbid', config::get('post_forbid'));
$smarty->assign('post_oog_forbid', config::get('post_oog_forbid'));

$page->setContent($smarty->fetch(get_tpl(post)));
$page->generate();
