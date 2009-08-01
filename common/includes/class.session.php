<?php

class session
{
    function init()
    {
        if (isset($_REQUEST[session_name()]))
        {
            session_start();
            if (isset($_SESSION['user']))
            {
                user::loggedin(true);
            }
        }
    }

    function isAdmin()
    {
        return (bool)(isset($_SESSION['admin']) && $_SESSION['rsite'] == $_SERVER["HTTP_HOST"] && md5(KB_SITE) == $_SESSION['site']);
    }

    function isSuperAdmin()
    {
        return (bool)(isset($_SESSION['admin_super']) && $_SESSION['rsite'] == $_SERVER["HTTP_HOST"] && md5(KB_SITE) == $_SESSION['site']);
    }

    function create($admin = false)
    {
        session_start();
        $_SESSION['admin'] = $admin;
		$_SESSION['rsite'] = $_SERVER["HTTP_HOST"];
		$_SESSION['site'] = md5(KB_SITE);
    }

    function destroy()
    {
        session_destroy();
    }
}
?>
