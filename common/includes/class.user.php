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
	
	/**
	 * Checks to see if the given username and password are valid for this site,
	 * and if they are, log the user in.
	 * Roles, roles by title and extra user data are all stored in the user's session once
	 * they are logged in.
	 *
	 * Roles are marked differently if the user has the role explicitly or given to them
	 * by a title they have.
	 *
	 * Extra data is stored as key => value pairs within the user's session.
	 */
    public static function login($login, $password)
    {
        if (user::loggedin())
        {
            return true;
        }
        $db = DBFactory::getDBQuery(true);
        $db->execute("select usr_id,usr_login,usr_pass,usr_pilot_id from kb3_user
                      WHERE usr_login='".slashfix($login)."' and usr_state=0 and usr_site='".KB_SITE."'
					  AND usr_pass = '".md5($password)."'");
		if (!$db->recordCount())
		{
			return false;
		}
		
		$roles = array();
		$user = null;

		Session::create();
		$row = $db->getRow();
		$user = $row;
		$userID = $row['usr_id'];
		
		// Extra data
		$db->execute("SELECT * FROM kb3_user_extra WHERE use_usr_id = " . $userID);
		while ($row = $db->getRow())
			$user[$row['use_key']] = $row['use_value'];
		
		// Titles
		$db->execute("SELECT DISTINCT rol_id FROM kb3_user_titles t INNER JOIN kb3_titles_roles r ON t.ust_ttl_id = r.ttl_id WHERE t.ust_usr_id = " . $userID);
		while ($row = $db->getRow())
			$roles[$row['rol_id']] = 2;
		
		// Roles
		$db->execute("SELECT uro_rol_id FROM kb3_user_roles WHERE uro_usr_id = " . $userID);
		while ($row = $db->getRow())
			$roles[$row['uro_rol_id']] = 1;

		$user['roles']=$roles;
		$_SESSION['user'] = $user;

		user::loggedin(true);
		event::call("user_login", $user);
		return true;
	}

	public static function role($role)
	{
		$user = Session::get('user');
		if (is_null($user)) return null;

		if (array_key_exists($role, $user['roles'])) return true;

		return false;
	}

	public static function loggedin($bool = null)
	{
		static $state;

		if ($bool !== null) $state = $bool;

		if ($state == null) $state = false;
		return $state;
	}

	/**
	 * Generates the menu for the user
	 * @return string
	 */
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
