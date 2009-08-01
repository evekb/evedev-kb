<?php
$page = new Page('User - Registration');

if (config::get('user_regdisabled'))
{
    $page->error('Registration has been disabled.');
    return;
}

if (!config::get('user_noigb') && !IS_IGB)
{
    $page->error('You have to use the IGB to register.');
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
        if (IS_IGB)
        {
            $pilot = $_SERVER["HTTP_EVE_CHARNAME"];
            $_POST['usrlogin'] = $pilot;
            $id = $_SERVER["HTTP_EVE_CHARID"];
        }
        user::register(slashfix($_POST['usrlogin']), slashfix($_POST['usrpass']), $pilot, $id);
        $page->setContent('Account registered.');
        $page->generate();
        return;
    }
}

if (IS_IGB)
{
    $smarty->assign('user_name', $_SERVER["HTTP_EVE_CHARNAME"]);
}

$page->setContent($smarty->fetch(get_tpl('user_register')));
$page->generate();
?>