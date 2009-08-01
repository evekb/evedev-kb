<?php
require_once('common/includes/class.comments.php');

$comments = new Comments($kll_id);
if (isset($_POST['comment']))
{
    $pw = false;
    if (!config::get('comments_pw') || $page->isAdmin())
    {
        $pw = true;
    }
    if ($_POST['password'] == config::get("comment_password") || $pw)
    {
        if ($_POST['comment'] == '')
        {
            $html .= 'Error: Silent type hey? good for you, bad for a comment.';
        }
        else
        {
            $comment = $_POST['comment'];
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

$smarty->assign_by_ref('page', $page);

$comment = $comments->getComments();
?>