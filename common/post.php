<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
$page = new Page('Post killmail');

if (isset($_POST['undelete']) && isset($_POST['kll_id']) && $page->isAdmin()) {
	$kll_id = intval($_POST['kll_id']);
	$qry = DBFactory::getDBQuery();
	$qry->execute("DELETE FROM kb3_mails WHERE kll_id = ".$kll_id);
	if (isset($_POST['killmail'])) {
		$html = post();
	} else {
		$html = "Mail lock has been removed.";
	}
} else if (isset($_POST['killmail'])) {
	$html = post();
}
if (isset($html)) {
	$smarty->assign('error', $html);
}
$smarty->assign('isadmin', $page->isAdmin());
$smarty->assign('post_forbid', config::get('post_forbid'));
$smarty->assign('post_oog_forbid', config::get('post_oog_forbid'));

$page->setContent($smarty->fetch(get_tpl('post')));
$page->generate();

function post()
{
	global $page;
	if (config::get("post_password") == ''
			|| crypt($_POST['password'], config::get("post_password"))
			== config::get("post_password")
			|| $page->isAdmin()) {
		$parser = new Parser($_POST['killmail']);

		// Filtering
		if (config::get('filter_apply')) {
			$filterdate = config::get('filter_date');
			$year = substr($_POST['killmail'], 0, 4);
			$month = substr($_POST['killmail'], 5, 2);
			$day = substr($_POST['killmail'], 8, 2);
			$killstamp = mktime(0, 0, 0, $month, $day, $year);
			if ($killstamp < $filterdate) {
				$killid = -3;
			} else {
				$killid = $parser->parse(true, null, false);
			}
		} else {
			$killid = $parser->parse(true, null, false);
		}

		if ($killid <= 0) {
			if ($killid == 0) {
				$html = "Killmail is malformed.<br/>";
				if ($errors = $parser->getError()) {
					foreach ($errors as $error) {
						$html .= 'Error: '.$error[0];
						if ($error[1]) {
							$html .= ' The text leading to this error was: "'
									.$error[1].'"';
						}
						$html .= '<br/>';
					}
				}
			} elseif ($killid == -1) {
				$html = "That killmail has already been posted <a href=\""
						."?a=kill_detail&kll_id=".$parser->getDupeID()
						."\">here</a>.";
			} elseif ($killid == -2) {
				$html = "You are not authorized to post this killmail.";
			} elseif ($killid == -3) {
				$filterdate = kbdate("j F Y", config::get("filter_date"));
				$html = "You are not allowed to post killmails older than"
						." $filterdate.";
			} elseif ($killid == -4) {
				$html = "That mail has been deleted. Kill id was "
						.$parser->getDupeID();
				if ($page->isAdmin())
						$html .= '<br />
<form id="postform" name="postform" class="f_killmail" method="post" action="'.KB_HOST.'/?a=post">
	<input type="hidden" name="killmail" id="killmail" value = "'.htmlentities($_POST['killmail']).'"/>
	<input type="hidden" name="kll_id" id="kill_id" value = "'.$parser->getDupeID().'"/>
	<input type="hidden" name="undelete" id="undelete" value = "1"/>
<input id="submit" name="submit" type="submit" value="Undelete" />
</form>';
			}
		} else {
			if (config::get('post_mailto') != "") {
				$mailer = new PHPMailer();
				$kill = new Kill($killid);

				if (!$server = config::get('post_mailserver')) {
					$server = 'localhost';
				}
				$mailer->From = "mailer@".config::get('post_mailhost');
				$mailer->FromName = config::get('post_mailhost');
				$mailer->Subject = "Killmail #".$killid;
				$mailer->Host = $server;
				$mailer->Port = 25;
				$mailer->Helo = $server;
				$mailer->Mailer = "smtp";
				$mailer->AddReplyTo("no_reply@".config::get('post_mailhost'),
						"No-Reply");
				$mailer->Sender = "mailer@".config::get('post_mailhost');
				$mailer->Body = $_POST['killmail'];
				$mailer->AddAddress(config::get('post_mailhost'));
				$mailer->Send();
			}

			logger::logKill($killid);
			header("Location: ".html_entity_decode(edkURI::page('kill_detail',
							$killid, 'kll_id')));
			exit;
		}
	} else {
		$html = "Invalid password.";
	}
	return $html;
}