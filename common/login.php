<?php
$page = new Page('Login');

if (trim($_POST['usrpass']))
{
	if ($_POST['usrlogin'] == '' && crypt($_POST['usrpass'],ADMIN_PASSWORD) == ADMIN_PASSWORD )
	{
		session::create(true);

		session_write_close();
		header('Location: ?a=admin');
		die;
	}
	else
	{
		$result = user::login($_POST['usrlogin'], $_POST['usrpass']);
		if ($result)
		{
			header('Location: ?a=home');
			die;
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