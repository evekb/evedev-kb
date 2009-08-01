<?php
$page = new Page('Login');

if (trim($_POST['usrpass']))
{
    // Allow plain text passwords for compatibility with older boards when updating.
    if ($_POST['usrlogin'] == '' && (crypt($_POST['usrpass'],ADMIN_PASSWORD) == ADMIN_PASSWORD || $_POST['usrpass'] == ADMIN_PASSWORD))
    {
        session::create(true);

        header('Location: ?a=admin');
    }
    else
    {
        $result = user::login($_POST['usrlogin'], $_POST['usrpass']);
        if ($result)
        {
            header('Location: ?a=home');
        }
        else
        {
            $smarty->assign('error', 'Login error, please check your username and password.');
        }
    }
}

$page->setContent($smarty->fetch(get_tpl('user_login')));
$page->generate();
?>