<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

include_once('api/class.idtoname.php');
include_once('api/class.nametoid.php');

options::cat('Advanced', 'Configuration', 'Available updates');
options::fadd('Code updates', 'none', 'custom', array('update', 'codeCheck'));
options::fadd('Database updates', 'none', 'custom', array('update', 'dbCheck'));

options::cat('Advanced', 'Configuration', 'Killboard Configuration');
options::fadd('Killboard Title', 'cfg_kbtitle', 'edit:size:50');
options::fadd('Killboard Host', 'cfg_kbhost', 'edit:size:50','', array('admin_config', 'checkHost'));
options::fadd('Image base URL', 'cfg_img', 'edit:size:50','', array('admin_config', 'checkImg'));
options::fadd('Main Webpage Link', 'cfg_mainsite', 'edit:size:50');
options::fadd('Allow Masterfeed', 'feed_allowmaster', 'checkbox');
options::fadd('Compress pages', 'cfg_compress', 'checkbox','','','Enable unless you encounter errors');
options::fadd('Display profiling information', 'cfg_profile', 'checkbox');
options::fadd('Log errors', 'cfg_log', 'checkbox');

options::cat('Advanced', 'Configuration', 'Public-Mode');
options::fadd('Only Kills in SummaryTables', 'public_summarytable', 'checkbox','','','CORP_ID and ALLIANCE_ID in config has to be 0 to work "public"');
options::fadd('Remove Losses Page', 'public_losses', 'checkbox');
options::fadd('Stats Page', 'public_stats', 'select',array('admin_config', 'createSelectStats'));

options::cat('Advanced', 'Configuration', 'Pilot/Corp/Alliance ID (Provide either exact full name, ID or external ID)');
options::fadd('PILOT_ID', 'cfg_pilotid', 'custom', array('admin_config', 'createPilot'),array('admin_config', 'reload'));
options::fadd('CORP_ID', 'cfg_corpid', 'custom', array('admin_config', 'createCorp'),array('admin_config', 'reload'));
options::fadd('ALLIANCE_ID', 'cfg_allianceid', 'custom', array('admin_config', 'createAlliance'), array('admin_config', 'reload'));

class admin_config
{
	public static function checkHost()
	{
		if(!isset($_POST['option_cfg_kbhost'])) return;
		$newhost = preg_replace('/\/+$/','',$_POST['option_cfg_kbhost']);
		config::set('cfg_kbhost', $newhost);
		$_POST['option_cfg_kbhost'] = $newhost;
	}
	public static function checkImg()
	{
		if(!isset($_POST['option_cfg_img'])) return;
		$newimg = preg_replace('/\/+$/','',$_POST['option_cfg_img']);
		config::set('cfg_img', $newimg);
		$_POST['option_cfg_img'] = $newimg;
	}
	public static function createSelectStats()
	{
		$options = array();
		if (config::get('public_stats') == 'none')
		{
			$state = 1;
		}
		else
		{
			$state = 0;
		}
		$options[] = array('value' => 'do nothing', 'descr' => 'do nothing', 'state' => $state);

		if (config::get('public_stats') == 'remove')
		{
			$state = 1;
		}
		else
		{
			$state = 0;
		}
		$options[] = array('value' => 'remove', 'descr' => 'remove', 'state' => $state);

		if (config::get('public_stats') == 'replace')
		{
			$state = 1;
		}
		else
		{
			$state = 0;
		}
		$options[] = array('value' => 'replace', 'descr' => 'replace (not rdy yet)', 'state' => $state);

		return $options;
	}
	public static function createPilot()
	{
		$numeric = false;
		$qry = DBFactory::getDBQuery();
		$plt_id = PILOT_ID;
		if(isset($_POST['option_cfg_pilotid']))
		{
		    $_POST['option_cfg_pilotid'] = preg_replace("/[^0-9a-zA-Z-_.' ]/",'', $_POST['option_cfg_pilotid']);
		    $plt_id = $_POST['option_cfg_pilotid'];

		    if(is_numeric($_POST['option_cfg_pilotid']))
		    {
			$numeric = true;
		    }
		}

		if(strlen(trim($plt_id == '')) > 0 )
		    $plt_id = 0;

		if($numeric || $plt_id > 0) //second condition is for when nothing was posted and it uses the old PILOT_ID
		{
		    if($plt_id > 100000000) //external IDs are over 100 million
		    {
			$qry->execute("SELECT `plt_name`, `plt_id` FROM `kb3_pilots` WHERE `plt_externalid` = ".$plt_id);
			if(!$qry->recordCount())
			{
			    return admin_config::nameToId('idtoname' ,'p', $plt_id);
			}
			$res = $qry->getRow();
			$_POST['option_cfg_pilotid'] = $plt_id = $res['plt_id'];
			config::set('cfg_pilotid', $plt_id);

			$html = '<input type="text" id="option_cfg_pilotid" name="option_cfg_pilotid" value="'.$plt_id.'" size="40" maxlength="64" />';
			return $html . ' &nbsp;('.$res['plt_name'].')';
		    }
		    else
		    { //id not within external range
			$qry->execute("SELECT `plt_name` FROM `kb3_pilots` WHERE `plt_id` = ".$plt_id);
			$html = '<input type="text" id="option_cfg_pilotid" name="option_cfg_pilotid" value="'.$plt_id.'" size="40" maxlength="64" />';
			if(!$qry->recordCount())
			    return $html;
			$res = $qry->getRow();
			return $html . ' &nbsp;('.$res['plt_name'].')';
		    }
		}
		else if(is_string($plt_id) && strlen($plt_id) > 0)
		{ //non-numeric
		    $qry->execute("SELECT `plt_id`, `plt_name` FROM `kb3_pilots` WHERE `plt_name` like '".$plt_id."'");
			
		    if(!$qry->recordCount())
		    {//name not found, let's look it up
			return admin_config::nameToId( 'nametoid', 'p', $plt_id);
		    }
		    else
		    { //name is found
			$res = $qry->getRow();
			$_POST['option_cfg_pilotid'] = $plt_id = $res['plt_id'];
			config::set('cfg_pilotid', $plt_id);
			$html = '<input type="text" id="option_cfg_pilotid" name="option_cfg_pilotid" value="'.$plt_id.'" size="40" maxlength="64" />';
			return $html . ' &nbsp;('.$res['plt_name'].')';
		    }
		}
		else
		{ //sometimes this may happen
		    $html = '<input type="text" id="option_cfg_pilotid" name="option_cfg_pilotid" value="0" size="40" maxlength="64" />';
		    return $html;
		}
	}
	public static function createCorp()
	{
		$qry = DBFactory::getDBQuery();
		$numeric = false;
		$crp_id = CORP_ID;

		if(isset($_POST['option_cfg_pilotid']))
		    $plt_id = intval($_POST['option_cfg_pilotid']);
		else $plt_id = PILOT_ID;

		if($plt_id) $crp_id = 0;

		if(isset($_POST['option_cfg_corpid']))
		{
		    $_POST['option_cfg_corpid'] = preg_replace("/[^0-9a-zA-Z-_.' ]/",'', $_POST['option_cfg_corpid']);
		    $crp_id = $_POST['option_cfg_corpid'];

		    if(is_numeric($_POST['option_cfg_corpid']))
		    {
			$numeric = true;
		    }
		}

		if(strlen(trim($crp_id == '')) > 0 )
		    $crp_id = 0;

		if($numeric || $crp_id > 0) //second condition is for when nothing was posted and it uses the old PILOT_ID
		{
		    if($crp_id > 100000000) //external IDs are over 100 million
		    {
			$qry->execute("SELECT `crp_name`, `crp_id` FROM `kb3_corps` WHERE `crp_external_id` = ".$crp_id);
			if(!$qry->recordCount())
			{
			    return admin_config::nameToId('idtoname' ,'c', $crp_id);
			}
			$res = $qry->getRow();
			$_POST['option_cfg_corpid'] = $crp_id = $res['crp_id'];
			config::set('cfg_corpid', $crp_id);

			$html = '<input type="text" id="option_cfg_corpid" name="option_cfg_corpid" value="'.$crp_id.'" size="40" maxlength="64" />';
			return $html . ' &nbsp;('.$res['crp_name'].')';
		    }
		    else
		    { //id not within external range
			$qry->execute("SELECT `crp_name` FROM `kb3_corps` WHERE `crp_id` = ".$crp_id);
			$html = '<input type="text" id="option_cfg_corpid" name="option_cfg_corpid" value="'.$crp_id.'" size="40" maxlength="64" />';
			if(!$qry->recordCount())
			    return $html;
			$res = $qry->getRow();
			return $html . ' &nbsp;('.$res['crp_name'].')';
		    }
		}
		else if(is_string($crp_id) && strlen($crp_id) > 0)
		{ //non-numeric
		    $qry->execute("SELECT `crp_id`, `crp_name` FROM `kb3_corps` WHERE `crp_name` like '".$crp_id."'");

		    if(!$qry->recordCount())
		    {//name not found, let's look it up
			return admin_config::nameToId( 'nametoid', 'c', $crp_id);
		    }
		    else
		    { //name is found
			$res = $qry->getRow();
			$_POST['option_cfg_corpid'] = $crp_id = $res['crp_id'];
			config::set('cfg_corpid', $crp_id);
			$html = '<input type="text" id="option_cfg_corpid" name="option_cfg_corpid" value="'.$crp_id.'" size="40" maxlength="64" />';
			return $html . ' &nbsp;('.$res['crp_name'].')';
		    }
		}
		else
		{ //sometimes this may happen
		    $html = '<input type="text" id="option_cfg_corpid" name="option_cfg_corpid" value="0" size="40" maxlength="64" />';
		    return $html;
		}
	}
	public static function createAlliance()
	{
		$qry = DBFactory::getDBQuery();
		$numeric = false;
		$all_id = ALLIANCE_ID;

		if(isset($_POST['option_cfg_pilotid']))
		{
		    $plt_id = intval($_POST['option_cfg_pilotid']);
		}
		else $plt_id = PILOT_ID;

		if(isset($_POST['option_cfg_corpid']))
		{
		    $crp_id = intval($_POST['option_cfg_corpid']);
		}
		else $crp_id = CORP_ID;

		if($plt_id || $crp_id) $all_id = 0;

		if(isset($_POST['option_cfg_allianceid']))
		{
		    $_POST['option_cfg_allianceid'] = preg_replace("/[^0-9a-zA-Z-_.' ]/",'', $_POST['option_cfg_allianceid']);
		    $all_id = $_POST['option_cfg_allianceid'];

		    if(is_numeric($_POST['option_cfg_allianceid']))
		    {
			$numeric = true;
		    }
		}

		if(strlen(trim($all_id == '')) > 0 )
		    $all_id = 0;
		
		if($numeric || $all_id > 0) //second condition is for when nothing was posted and it uses the old ALLIANCE_ID
		{
		    if($all_id > 100000000) //external IDs are over 100 million
		    {
			$qry->execute("SELECT `all_name`, `all_id` FROM `kb3_alliances` WHERE `all_external_id` = ".$all_id);
			if(!$qry->recordCount())
			{
			    return admin_config::nameToId('idtoname' ,'a', $all_id);
			}
			$res = $qry->getRow();
			$_POST['option_cfg_allianceid'] = $all_id = $res['all_id'];
			config::set('cfg_allianceid', $all_id);

			$html = '<input type="text" id="option_cfg_allianceid" name="option_cfg_allianceid" value="'.$all_id.'" size="40" maxlength="64" />';
			return $html . ' &nbsp;('.$res['all_name'].')';
		    }
		    else
		    { //id not within external range
			$qry->execute("SELECT `all_name` FROM `kb3_alliances` WHERE `all_id` = ".$all_id);
			$html = '<input type="text" id="option_cfg_allianceid" name="option_cfg_allianceid" value="'.$all_id.'" size="40" maxlength="64" />';
			if(!$qry->recordCount())
			    return $html;
			$res = $qry->getRow();
			return $html . ' &nbsp;('.$res['all_name'].')';
		    }
		}
		else if(is_string($all_id) && strlen($all_id) > 0)
		{ //non-numeric
		    $qry->execute("SELECT `all_id`, `all_name` FROM `kb3_alliances` WHERE `all_name` like '".$all_id."'");

		    if(!$qry->recordCount())
		    {//name not found, let's look it up
			return admin_config::nameToId( 'nametoid', 'a', $all_id);
		    }
		    else
		    { //name is found
			$res = $qry->getRow();
			$_POST['option_cfg_allianceid'] = $all_id = $res['all_id'];
			config::set('cfg_allianceid', $all_id);
			$html = '<input type="text" id="option_cfg_allianceid" name="option_cfg_allianceid" value="'.$all_id.'" size="40" maxlength="64" />';
			return $html . ' &nbsp;('.$res['all_name'].')';
		    }
		}
		else
		{ //sometimes this may happen
		    $html = '<input type="text" id="option_cfg_allianceid" name="option_cfg_allianceid" value="0" size="40" maxlength="64" />';
		    return $html;
		}
	}
	public static function reload()
	{
		header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
	}

	public static function nameToId($type, $set, $value)
	{
	    if($type == 'nametoid') {
		$api = new API_NametoID();
		$api->setNames($value);
	    }
	    else if($type == 'idtoname')
	    {
		$api = new API_IDtoName();
		$api->setIDs($value);
	    }
	    $api->fetchXML();
	    
	    if($type == 'nametoid') { $char_info = $api->getNameData(); }
	    else if($type == 'idtoname') { $char_info = $api->getIDData(); }

	    if(isset($char_info[0]['characterID']) && strlen($char_info[0]['characterID']) > 0)
	    {
		$timestamp = gmdate('%Y.%m.%d %H:%i:%s', time());

		if($set == 'p')
		{
		    $all = new Alliance();
		    $all->add('Unknown');

		    $crp = new Corporation();
		    $crp->add('Unknown', $all, $timestamp, 0, false);

		    $plt = new Pilot();
		    $plt->add($char_info[0]['name'], $crp, $timestamp, $char_info[0]['characterID'], false);

		    $_POST['option_cfg_pilotid'] = $value = $plt->getID();
		    config::set('cfg_pilotid', $value);

		    $html = '<input type="text" id="option_cfg_pilotid" name="option_cfg_pilotid" value="'.$value.'" size="40" maxlength="64" />';
		}
		else if($set == 'c')
		{
		    $all = new Alliance();
		    $all->add('Unknown');
		    
		    $crp = new Corporation();
		    $crp->add($char_info[0]['name'], $all, $timestamp, $char_info[0]['characterID'], false);

		    $_POST['option_cfg_corpid'] = $value = $crp->getID();
		    config::set('cfg_corpid', $value);

		    $html = '<input type="text" id="option_cfg_corpid" name="option_cfg_corpid" value="'.$value.'" size="40" maxlength="64" />';
		}
		else if($set == 'a')
		{
		    $all = new Alliance();
		    $all->add('Unknown');

		    $_POST['option_cfg_allianceid'] = $value = $all->getID();
		    config::set('option_cfg_allianceid', $value);

		    $html = '<input type="text" id="option_cfg_allianceid" name="option_cfg_allianceid" value="'.$value.'" size="40" maxlength="64" />';
		}
		return $html . ' &nbsp;('.$char_info[0]['name'].')';
	    }
	    else return $html;
	}
}

class update
{
	private static $codeVersion;
	private static $dbVersion;
	//! Check if board is at latest update

	/*
	 * Display a link to update or show that no update is needed.
	*/
	public static function codeCheck()
	{
		if(!class_exists('DOMDocument')) return "The required DOMDocument libraries in PHP are not installed.";
		update::checkStatus();
		if(update::$codeVersion > Config::get('upd_codeVersion'))
		{
			return "<div>Code updates are available, <a href='?a=admin_upgrade'>here</a></div><br/>";
		}
		return "<div>No updates available</div>";
	}
	//! Check if database is at latest update

	/*
	 * Display a link to update or show that no update is needed.
	*/
	public static function dbCheck()
	{
		if(!class_exists('DOMDocument')) return "The required DOMDocument libraries in PHP are not installed.";
		update::checkStatus();
		if(update::$dbVersion > Config::get('upd_dbVersion'))
		{
			return "<div>Database updates are available, <a href='?a=admin_upgrade'>here</a></div><br/>";
		}
		return "<div>No updates available</div>";
	}
	//! Updates status xml if necessary.
	public static function checkStatus()
	{
		require_once('update/CCPDB/xml.parser.php');
		$xml = new UpdateXMLParser();
		if($xml->getXML() < 3)
		{
			$xml->retrieveData();
			{
				update::$codeVersion = $xml->getLatestCodeVersion();
				update::$dbVersion = $xml->getLatestDBVersion();
			}
		}
		return;
	}
}