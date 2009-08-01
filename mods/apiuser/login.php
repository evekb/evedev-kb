<?php
$page = new Page('Login');
//		print_r($_SESSION['user']);

if (trim($_POST['usrpass']))
{
 $result = user::login($_POST['usrlogin'], $_POST['usrpass']);
    if ( $_POST['usrlogin'] == '' &&
		(crypt($_POST['usrpass'],ADMIN_PASSWORD) == ADMIN_PASSWORD || $_POST['usrpass'] == ADMIN_PASSWORD)
		|| user::role('admin')     )
    {
        session::create(true);

        header('Location: ?a=admin');
    }
    else
    {

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

$page->setContent($smarty->fetch('../mods/apiuser/templates/user_login.tpl'));
$page->generate();
?>