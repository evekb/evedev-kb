<?php
$page = new Page('Login');

if (trim($_POST['usrpass']))
{
	if($_POST['usrlogin'] == '' && $_POST['usrpass'] == ADMIN_PASSWORD 
		&& substr(ADMIN_PASSWORD,0,3) != '$1$'
		&& substr(ADMIN_PASSWORD,0,3) != '$2$'
		&& substr(ADMIN_PASSWORD,0,3) != '$2a$')
	{
		@chmod("kbconfig.php", 0660);
		if(!is_writeable("kbconfig.php"))
		{
			$smarty->assign('error', 'Admin password is unencrypted and '.
				'kbconfig.php is not writeable. Either encrypt the admin '.
				'password or set kbconfig.php writeable.');
		}
		else
		{
			$kbconfig = file_get_contents('kbconfig.php');
			$newpwd = preg_replace('/(\$|\\\\)/', '\\\\$1',crypt(ADMIN_PASSWORD));
			$kbconfig = preg_replace('/define\s*\(\s*[\'"]ADMIN_PASSWORD[\'"][^)]*\)/',
					"define('ADMIN_PASSWORD', '".$newpwd."')", $kbconfig);
			file_put_contents("kbconfig.php", trim($kbconfig));
			chmod("kbconfig.php", 0440);

			session::create(true);

			session_write_close();
			header('Location: ?a=admin');
			die;
		}
	}
	elseif ($_POST['usrlogin'] == '' && crypt($_POST['usrpass'],ADMIN_PASSWORD) == ADMIN_PASSWORD )
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