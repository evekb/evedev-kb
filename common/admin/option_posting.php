<?php
options::cat('Advanced', 'Posting Options', 'Posting Options');
options::fadd('Enable Comments', 'comments', 'checkbox');
options::fadd('Require password for Comments', 'comments_pw', 'checkbox');
options::fadd('Forbid posting', 'post_forbid', 'checkbox');
options::fadd('Forbid out of game posting', 'post_oog_forbid', 'checkbox');
//options::fadd('Enable auto-addition of unknown Items', 'adapt_items', 'checkbox');
options::fadd('ReAdd known killmails', 'readd_dupes', 'checkbox');
options::fadd('Mail post password', 'post_password', 'edit');
options::fadd('Comment post password', 'comment_password', 'edit');
options::fadd('Killmail CC', 'post_mailto', 'edit');
options::fadd('Mailhost', 'post_mailhost', 'edit');
options::fadd('Mailserver', 'post_mailserver', 'edit', '', '', 'This is the server where php connects to send the mail.');
options::fadd('Disallow any killmails before', 'filter_date', 'custom', array('admin_posting', 'dateSelector'), array('admin_posting', 'postDateSelector'));

class admin_posting
{
    function dateSelector()
    {
        $apply = config::get('filter_apply');
        $date = config::get('filter_date');

    	if ($date > 0)
        {
    		$date = getdate($date);
    	}
        else
        {
    		$date = getdate();
    	}
    	$html = "<input type=\"text\" name=\"option[filter_day]\" id=\"option[filter_day]\" style=\"width:20px\" value=\"{$date['mday']}\"/>&nbsp;";
    	$html .= "<select name=\"option[filter_month]\" id=\"option[filter_month]\">";
    	for ($i = 1; $i <= 12; $i++)
        {
    		$t = mktime(0, 0, 0, $i, 1, 1980);
    		$month = gmdate("M", $t);
    		if($date['mon'] == $i)
            {
                $selected = " selected=\"selected\"";
            }
            else
            {
                $selected = "";
            }

    		$html .= "<option value=\"$i\"$selected>$month</option>";
    	}
    	$html .= "</select>&nbsp;";

    	$html .= "<select name=\"option[filter_year]\" id=\"option[filter_year]\">";
    	for ($i = gmdate("Y")-7; $i <= gmdate("Y"); $i++)
        {
    		if ($date['year'] == $i)
            {
                $selected = " selected=\"selected\"";
            }
            else
            {
                $selected = "";
            }
    		$html .= "<option value=\"$i\"$selected>$i</option>";
    	}
    	$html .= "</select>&nbsp;";
    	$html .= "<input type=\"checkbox\" name=\"option[filter_apply]\" id=\"option[filter_apply]\"";
    	if ($apply)
        {
            $html .= " checked=\"checked\"";
        }
    	$html .= "/>Apply&nbsp;";
    	return $html;
    }

    function postDateSelector()
    {
        if ($_POST['option']['filter_apply'] == 'on')
        {
            config::set('filter_apply', '1');
            config::set('filter_date', mktime(0, 0, 0, $_POST['option']['filter_month'], ($_POST['option']['filter_day'] > 31 ? 31 : $_POST['option']['filter_day']), $_POST['option']['filter_year']));
        }
        else
        {
        	config::set('filter_apply', '0');
        	config::set('filter_date', 0);
        }

    }
}

?>