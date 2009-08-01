<?php
require_once('common/includes/class.comments.php');

$comments = new Comments($kll_id);
if (isset($_POST['comment']))
{
    $pw = false;
    if ((!config::get('comments_pw') && !config::get('apiuser_comment'))
		|| $page->isAdmin() 
		||(config::get('apiuser_comment') && (user::role('comment') || user::role('admin') )) )
        $pw = true;

    if (($_POST['password'] == config::get("post_password") && !config::get('apiuser_comment')) || $pw)
    {
        if ($_POST['comment'] == '')
        {
            $html .= 'Error: Silent type hey? good for you, bad for a comment.';
        }
        else
        {
            $comment = $_POST['comment'];
			if (user::loggedin())
				$name=user::get('usr_login');
			else
				if ($page->isAdmin())
					$name = 'admin';
			else
	            $name = $_POST['name'];
            if ($name == null)
            {
                $name = 'Anonymous';
            }
            $comments->addComment($name, $comment);
			//Remove cached file.
			if(KB_CACHE) cache::deleteCache();
			//Redirect to avoid refresh reposting comments.
			header('Location: '.$_SERVER['REQUEST_URI'],TRUE,303);
			die();
        }
    }
    else
    {
        // Password is wrong
        $html .= 'Error: Wrong Password';
    }
}
$allowedToPost=$pw;
$smarty->assign('affForms',$allowedToPost);

$smarty->assign('valUser',intval(user::loggedin()));
$smarty->assign('apiuserEnable',$config->get('apiuser_comment'));
$smarty->assign_by_ref('page', $page);
$comment = $comments->getComments();

?>