<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

//TODO: Make a useful registration mod
$page = new Page('User - Registration');

if (config::get('user_regdisabled'))
{
    $page->error('Registration has been disabled.');
    return;
}

if (isset($_POST['submit']))
{
    $error = false;
    if (config::get('user_regpass'))
    {
        if ($_POST['regpass'] != config::get('user_regpass'))
        {
            $smarty->assign('error', 'Registration password does not match.');
            $error = true;
        }
    }

    if (!$_POST['usrlogin'])
    {
        $smarty->assign('error', 'You missed to specify a login.');
        $error = true;
    }

    if (!$_POST['usrpass'])
    {
        $smarty->assign('error', 'You missed to specify a password.');
        $error = true;
    }

    if (strlen($_POST['usrpass']) < 3)
    {
        $smarty->assign('error', 'Your password needs to have at least 4 chars.');
        $error = true;
    }

    if (!$error)
    {
        $pilot = null;
        $id = null;
        user::register(slashfix($_POST['usrlogin']), slashfix($_POST['usrpass']), $pilot, $id);
        $page->setContent('Account registered.');
        $page->generate();
        return;
    }
}


$page->setContent($smarty->fetch(get_tpl('user_register')));
$page->generate();