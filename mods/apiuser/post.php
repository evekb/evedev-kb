<?php
require_once('common/includes/class.parser.php');
require_once('common/includes/class.phpmailer.php');
require_once('common/includes/class.kill.php');
$page = new Page('Post killmail');
$kb = new Killboard(KB_SITE);

if (isset($_POST['killmail']))
{

    if (($_POST['password'] == config::get('post_password') &&  !config::get('apiuser_killmail'))
		|| (config::get('apiuser_killmail') && ((user::role('post_killmail')) || user::role('admin') ) )
        || $page->isAdmin()
		)
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
                $killid = $parser->parse(true);
            }
        }
        else
        {
            $killid = $parser->parse(true);
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

            $qry = new DBQuery();
            $qry->execute("insert into kb3_log values(".$killid.",'".KB_SITE."','".$_SERVER['REMOTE_ADDR']."', now())");
			if (config::get('apiuser_killmail') && !$page->isAdmin())
			{

				$qry->execute("select charID from kb3_api_user where charName='".$_POST['username']."'");
				$tbl=$qry->getRow();

	            $qry->execute("insert into kb3_kill_poster values(".$killid.",'".$tbl['charID']."')");
			}

            header("Location: ?a=kill_detail&kll_id=".$killid);
            exit;
        }
    }
    else
    {
        $html = "Invalid password.";
    }

}
elseif (!config::get('post_forbid') && !config::get('post_oog_forbid') && (config::get('apiuser_killmail') && (user::role('post_killmail') || user::role('admin')) || !config::get('apiuser_killmail')))
{

    $html .= "Paste the killmail from your EVEMail inbox into the box below. Make sure you post the <b>ENTIRE</b> mail.<br>Posting fake or otherwise edited mails is not allowed. All posts are logged.";
    $html .= "<br><br>Remember to post your losses as well.<br><br>";


    $html .= "<b>Killmail:</b><br>";
    $html .= "<form id=postform name=postform class=f_killmail method=post action=\"?a=post\">";
    $html .= "<textarea name=killmail id=killmail class=f_killmail cols=\"70\" rows=\"24\"></textarea>";

    if (!$page->isAdmin())
    {

	if (config::get('apiuser_killmail') && !user::loggedin())
	    $html .= "<i>You need to have a valid APIkey user  and be ident to post killmail.<br><br></i>";
		else if (!config::get('apiuser_killmail'))
        $html .= "<br><br><b>Password:</b><br><input id=password name=password type=password></input>";
		// HACK API USER
    }
    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=submit name=submit type=submit value=\"Process !\"></input>";
    $html .= "</form>";
}
else
{
	if (config::get('post_oog_forbid'))
	{
		$html .= 'Out of game posting is disabled, please use the ingame browser.<br/>';
	}
	else if (config::get('post_forbid'))
	{
   		$html .= 'Posting killmails is disabled<br/>';
	}
	else if (config::get('apiuser_killmail') && !user::role('post_killmail') && !user::role('admin') && user::loggedin())
			$html .= 'You don\'t have the  role \'post_killmail\' <br/>';
		else

   		$html .= 'You need to be logged  to post killmail, go <a href="?a=login">Here</a><br/>';

}
$page->setContent($html);
$page->generate();

?>