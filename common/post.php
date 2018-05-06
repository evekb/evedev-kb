<?php
use EDK\ESI\ESI;

$page = new Page('Post kill');

if (isset($_POST['undelete']) && isset($_POST['kll_id']) && $page->isAdmin()) 
{
    $kll_id = intval($_POST['kll_id']);
    $qry = DBFactory::getDBQuery();
    $qry->execute("DELETE FROM kb3_mails WHERE kll_id = ".$kll_id);
    if (isset($_POST['submit']) && isset($_POST['killmail'])) 
    {
        $html = post();
    } 
    elseif (isset($_POST['submit_crest']) && isset($_POST['crest_url'])) 
    {
        $html = post_crest();
    }
    else {
        $html = "Mail lock has been removed.";
    }
}
else
{
    if (isset($_POST['submit']) && isset($_POST['killmail'])) 
    {
        $html = post();
    }
    elseif (isset($_POST['submit_crest']) && isset($_POST['crest_url'])) 
    {
        $html = post_crest();
    }
}

if (isset($html)) 
{
    $smarty->assign('error', $html);
}

$smarty->assign('isadmin', $page->isAdmin());
$smarty->assign('post_forbid', config::get('post_forbid'));
$smarty->assign('post_crest_forbid', config::get('post_crest_forbid'));
$smarty->assign('crest_pw_needed', config::get('crest_pw_needed'));

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
			logger::logKill($killid);
			header("Location: ".html_entity_decode(edkURI::page('kill_detail',
							$killid, 'kll_id')));
			exit;
		}
	} else {
		$html = "Invalid password for posting a kill mail.";
	}
	return $html;
}


function post_crest()
{
    global $page;
    if (config::get("crest_pw_needed") == false
    || config::get("post_crest_password") == ''
        || crypt($_POST['password_crest'], config::get("post_crest_password")) == config::get("post_crest_password")
        || $page->isAdmin()) {
        
        // validate the CREST URL
        $url = $_POST['crest_url'];
        try
        {
            validateUrl($url);
        } 
        
        catch (Exception $e) 
        {
            // abort
            $html .= $e->getMessage();
            return $html;
        }
        
        $EsiParser = new EsiParser(extractKillId($url), extractKillHash($url));
        try
        {
            $killId = $EsiParser->parse();
            logger::logKill($killId);
        }
        catch (ApiException $e) 
        {
            // abort
            $html .= $e->getMessage();
            return $html;
        }
        catch (KillException $e) 
        {
            // abort
            $html .= $e->getMessage();
            return $html;
        }
        catch(EsiParserException $e) 
        {
            if($e->getCode() == -4 )
            {
                $html = "That mail has been deleted. Kill id was ".$EsiParser->getDupeID();
                if($page->isAdmin()) {
                    $html .= '<br />
                        <form id="postform" name="postform" class="f_killmail" method="post" action="'.KB_HOST.'/?a=post">
                                <input type="hidden" name="crest_url" id="crest_url" value = "'.htmlentities($url).'"/>
                                <input type="hidden" name="kll_id" id="kill_id" value = "'.$EsiParser->getDupeID().'"/>
                                <input type="hidden" name="undelete" id="undelete" value = "1"/>
                        <input id="submit_crest" name="submit_crest" type="submit" value="Undelete" />
                        </form>';
                }
            }

            else 
            {
                $html .= $e->getMessage();
            }
            return $html;
        }

        header("Location: ".html_entity_decode(edkURI::page('kill_detail', $killId, 'kll_id')));
        exit();
    } 

    else 
    {
        $html = "Invalid password for posting a CREST link.";
    }
    return $html;
}

/**
 * Validates, that the given URL is a well-formed CREST kill URL.
 * If the URL fails validation, an exception is thrown
 * @param type $url
 * @throws \EsiParserException
 */
function validateUrl($url)
{
    // should look like this:
    // https://esi.tech.ccp.is/latest/killmails/65053820/6607d2104caa8a7ffd06f438bb370bb0ef7ba8a8/
    $urlPieces = explode("/", $url);
    
    
    $Esi = new ESI();
    $allowedEsiHosts = array(
        $Esi->getConfig()->getHost(), // from the current ESI cliebt
        'https://esi.tech.ccp.is'     // legacy, for example for allowing zKB links
    );
    
    // check host
    $isHostValid = false;
    foreach($allowedEsiHosts as $host)
    {
        if(substr($url, 0, strlen($host)) == $host)
        {
            $isHostValid = true;
            break;
        }
    }
    
    if(count($urlPieces) < 7 || 
            !$isHostValid ||
            $urlPieces[4] != "killmails" ||
            !is_numeric($urlPieces[5]) ||
            strlen($urlPieces[6]) != 40)
    {

        throw new Exception("Invalid URL: ".$url);
    }        
}

/**
 * Extracts the kill ID from a well-formed CREST kill URL
 * @param string $url a well-formed CREST kill URL
 * @return int the kill ID
 */
function extractKillId($url)
{
    // should look like this:
    // https://esi.tech.ccp.is/latest/killmails/65053820/6607d2104caa8a7ffd06f438bb370bb0ef7ba8a8/
    $urlPieces = explode("/", $url);
    return (int)$urlPieces[5];
}

/**
 * Extracts the hash from a well-formed CREST kill URL
 * @param string $url a well-formed CREST kill URL
 * @return string the kill hash
 */
function extractKillHash($url)
{
    // should look like this:
    // https://esi.tech.ccp.is/latest/killmails/65053820/6607d2104caa8a7ffd06f438bb370bb0ef7ba8a8/
    $urlPieces = explode("/", $url);
    return $urlPieces[6];
}
?>

