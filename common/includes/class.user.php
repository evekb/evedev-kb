<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class user
{
	function user()
	{
		trigger_error('The class "user" may only be invoked statically.', E_USER_ERROR);
	}

    public static function login($login, $password)
    {
        if (user::loggedin())
        {
            return true;
        }
        $db = DBFactory::getDBQuery(true);
        $db->execute('select * from kb3_user
                      left join kb3_user_extra on kb3_user.usr_id = kb3_user_extra.use_usr_id
                      left join kb3_user_titles on kb3_user.usr_id = kb3_user_titles.ust_usr_id
                      left join kb3_user_roles on kb3_user.usr_id = kb3_user_roles.uro_usr_id
                      WHERE usr_login='."'".slashfix($login)."'  and usr_state=0 and usr_site='".KB_SITE."'");
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


		Session::create();

		// user extra information
		if ($row['use_key'])
		{
			$user[$row['use_key']] = $row['use_value'];
		}
		// user roles
		if ($row['uro_rol_id'])
		{
			$roles[] = $row['uro_rol_id'];
		}
		// user titles

		if ($row['ust_ttl_id'])
		{
			$db2 = DBFactory::getDBQuery(true);
			$db2->execute('select distinct rol_name from kb3_titles_roles a,kb3_roles b where a.rol_id=b.rol_id and  a.ttl_id='.$row['ust_ttl_id']);
			while ($ttle = $db2->getRow())
			{
				$roles[$ttle['rol_name']] = 1;
			}
		}
		$user['uro_rol_id']=$roles;
		if ($row['usr_state'])
			$user['usr_state']=$row['usr_state'];
		$_SESSION['user'] = $user;

		user::loggedin(true);
		event::call("user_login", $user);
		return true;
	}

	public static function role($role)
	{
		$user = Session::get('user');
		if (is_null($user)) return null;

		if (isset($user['uro_rol_id'][$role])) return true;

		return false;
	}

	public static function loggedin($bool = null)
	{
		static $state;

		if ($bool !== null) $state = $bool;

		if ($state == null) $state = false;
		return $state;
	}

	// generates the menu for the user
	public static function menu()
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
				$plt = new pilot(user::get('usr_pilot_id'));
				$box->addOption('link', $plt->getName(), '?a=pilot_detail&plt_id='.$plt->getID());
			}
			$box->addOption('link', 'Logout', '?a=logout');
		}

		event::call('user_menu_create', $box);
		return $box->generate();
	}

	public static function get($key)
	{
		$user = Session::get('user');
		if (is_null($user))
		{
			return null;
		}
		return $user[$key];
	}

	// login,pass,pilot
	public static function register($login, $password, $pilot = null, $p_charid = null)
	{
		$db = DBFactory::getDBQuery(true);

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
    
    public static function delete($login, $site = KB_SITE)
    {
		$qry = DBFactory::getDBQuery(true);
		$name = slashfix($login);
		
		$qry->execute("SELECT usr_id FROM kb3_user WHERE usr_login = '{$name}' AND usr_site = '{$site}'");
		$row = $qry->getRow();
		$usr_id = $row['usr_id'];
		
		$qry->execute("DELETE FROM kb3_user WHERE usr_id = {$usr_id}");
		$qry->execute("DELETE FROM kb3_user_extra WHERE use_usr_id = {$usr_id}");
		$qry->execute("DELETE FROM kb3_user_roles WHERE uro_usr_id = {$usr_id}");
		$qry->execute("DELETE FROM kb3_user_titles WHERE ust_usr_id = {$usr_id}");
		event::call("user_deleted", $login);
    }
}
