<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

options::cat('Advanced', 'Posting Options', 'Posting Options');

options::fadd('Enable Comments', 'comments', 'checkbox');
options::fadd('Require password for Comments', 'comments_pw', 'checkbox');
options::fadd('Forbid killmail posting', 'post_forbid', 'checkbox');
options::fadd('Forbid ESI link posting', 'post_crest_forbid', 'checkbox');
options::fadd('Require password for ESI link posting', 'crest_pw_needed', 'checkbox');

options::fadd('Killmail post password', 'post_password', 'password', '', array('admin_posting', 'setPostPassword'));
options::fadd('ESI link post password', 'post_crest_password', 'password', '', array('admin_posting', 'setCrestPostPassword'));
options::fadd('Comment post password', 'comment_password', 'password', '', array('admin_posting', 'setCommentPassword'));

options::fadd('Disallow any killmails before', 'filter_date', 'custom', array('admin_posting', 'dateSelector'), array('admin_posting', 'postDateSelector'));

class admin_posting
{
        static function dateSelector()
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
            $html = "<input type=\"text\" name=\"option_filter_day\" id=\"option_filter_day\" style=\"width:20px\" value=\"{$date['mday']}\"/>&nbsp;";
            $html .= "<select name=\"option_filter_month\" id=\"option_filter_month\">";
            for ($i = 1; $i <= 12; $i++)
            {
                    $t = gmmktime(0, 0, 0, $i, 1, 1980);
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

            $html .= "<select name=\"option_filter_year\" id=\"option_filter_year\">";
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
            $html .= "<input type=\"checkbox\" name=\"option_filter_apply\" id=\"option_filter_apply\"";
            if ($apply)
            {
                $html .= " checked=\"checked\"";
            }
            $html .= "/>Apply&nbsp;";
            return $html;
        }

        static function postDateSelector()
        {
            if ($_POST['option_filter_apply'] == 'on')
            {
                config::set('filter_apply', '1');
                config::set('filter_date', gmmktime(0, 0, 0, $_POST['option_filter_month'], ($_POST['option_filter_day'] > 31 ? 31 : $_POST['option_filter_day']), $_POST['option_filter_year']));
            }
            else
            {
                    config::set('filter_apply', '0');
                    config::set('filter_date', 0);
            }

        }
        
    static function makePassword($pwd)
    {
        return crypt($pwd);
    }
        
    static function passwordChanged($pwd, $oldpwd)
    {
        return !($pwd == '' ||
            crypt($pwd, $oldpwd) == $oldpwd
            || ($pwd == $oldpwd && substr($oldpwd,0,3) == '$1$'));
    }
    
        static function setPostPassword()
    {
        if(admin_posting::passwordChanged($_POST['option_post_password'],config::get('post_password')))
            config::set('post_password', admin_posting::makePassword($_POST['option_post_password']));
    }
    
        static function setCrestPostPassword()
    {
        if(admin_posting::passwordChanged($_POST['option_post_crest_password'],config::get('post_crest_password')))
            config::set('post_crest_password', admin_posting::makePassword($_POST['option_post_crest_password']));
    }
    
        static function setCommentPassword()
    {
        if(admin_posting::passwordChanged($_POST['option_comment_password'],config::get('comment_password')))
            config::set('comment_password', admin_posting::makePassword($_POST['option_comment_password']));
    }
    
        static function createCommentQ()
    {
        if(config::get('comment_password')) $pwd = 'SET';
        else $pwd = '';
        return '<input type="text" id="option_comment_password" name="option_comment_password" value="'.$pwd.'" size="20" maxlength="20" />';
    }
    
        static function createPostQ()
    {
        if(config::get('post_password')) $pwd = 'SET';
        else $pwd = '';
        return '<input type="text" id="option_post_password" name="option_post_password" value="'.$pwd.'" size="20" maxlength="20" />';
    }
    
        static function reload()
    {
        header("Location: ".getRequestScheme().$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
        die;
    }
}

?>
