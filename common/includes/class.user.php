<?php
require_once('class.box.php');

class user
{
    function user()
    {
        trigger_error('The class "user" may only be invoked statically.', E_USER_ERROR);
    }

    function login($login, $password)
    {
        if (user::loggedin())
        {
            return true;
        }
        $db = new DBQuery();
        $db->execute('select * from kb3_user
                      left join kb3_user_extra on kb3_user.usr_id = kb3_user_extra.use_usr_id
                      left join kb3_user_titles on kb3_user.usr_id = kb3_user_titles.ust_usr_id
                      left join kb3_user_roles on kb3_user.usr_id = kb3_user_roles.uro_usr_id
                      WHERE usr_login='."'".slashfix($login)."' and usr_site='".KB_SITE."'");
        if (!$row = $db->getRow())
        {
            return false;
        }
        if ($row['usr_pass'] != md5($password))
        {
            return false;
        }
        $user = $row;
        $titles = $roles = array();
        if ($row['use_key'])
        {
            $user[$row['use_key']] = $row['use_value'];
        }

        // user titles
        if ($row['ust_ttl_id'])
        {
            $titles[] = $row['ust_ttl_id'];
        }

        // user roles
        if ($row['uro_rol_id'])
        {
            $roles[] = $row['uro_rol_id'];
        }

        Session::create();
        while ($row = $db->getRow())
        {
            // user extra information
            if ($row['use_key'])
            {
                $user[$row['use_key']] = $row['use_value'];
            }

            // user titles
            if ($row['ust_ttl_id'])
            {
                $titles[] = $row['ust_ttl_id'];
            }

            // user roles
            if ($row['uro_rol_id'])
            {
                $roles[] = $row['uro_rol_id'];
            }
        }
        $_SESSION['user'] = $user;
        user::loggedin(true);
        return true;
    }

    function role($role)
    {
        return false;
    }

    function loggedin($bool = null)
    {
        static $state;

        if ($bool !== null)
        {
            $state = $bool;
        }
        if ($state == null)
        {
            $state = false;
        }
        return $state;
    }

    // generates the menu for the user
    function menu()
    {
        $box = new Box('User');
        $box->setIcon('menu-item.gif');

        if (!user::loggedin())
        {
            $box->addOption('link', 'Login', '?a=login');
            $box->addOption('link', 'Register', '?a=register');
        }
        else
        {
            if (user::get('usr_pilot_id'))
            {
                require_once('class.pilot.php');

                $plt = new pilot(user::get('usr_pilot_id'));
                $box->addOption('link', $plt->getName(), '?a=pilot_detail&plt_id='.$plt->getID());
            }
            $box->addOption('link', 'Logout', '?a=logout');
        }

        event::call('user_menu_create', $box);
        return $box->generate();
    }

    function get($key)
    {
        if (!isset($_SESSION['user']))
        {
            return null;
        }
        return $_SESSION['user'][$key];
    }

    // login,pass,pilot
    function register($login, $password, $pilot = null, $p_charid = null)
    {
        $db = new DBQuery();

        $values[] = KB_SITE;
        $values[] = $login;
        $values[] = md5($password);
        if ($pilot)
        {
             $pilot = new Pilot($pilot);
             $values[] = $pilot->getId();
             if ($p_charid)
             {
                 $pilot->setCharacterID($p_charid);
             }
        }
        else
        {
            $values[] = 0;
        }

        // standard state
        $values[] = 0;
        $values = "'".join("','", $values)."'";
        $db->execute('insert into kb3_user (usr_site, usr_login, usr_pass, usr_pilot_id, usr_state) VALUES ('.$values.')');
        event::call('user_created', $values);
    }
}
?>