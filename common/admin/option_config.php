<?php
require_once('update/CCPDB/xml.parser.php');
require_once('common/includes/class.config.php');

options::cat('Advanced', 'Configuration', 'Available updates');
options::fadd('Code updates', 'none', 'custom', array('update', 'codeCheck'));
options::fadd('Database updates', 'none', 'custom', array('update', 'dbCheck'));

options::cat('Advanced', 'Configuration', 'Killboard Configuration');
options::fadd('Display profiling information', 'cfg_profile', 'checkbox');
options::fadd('Killboard Title', 'cfg_kbtitle', 'edit:size:50');
options::fadd('Killboard Host', 'cfg_kbhost', 'edit:size:50','', array('admin_config', 'checkHost'));
options::fadd('Image base URL', 'cfg_img', 'edit:size:50','', array('admin_config', 'checkImg'));
options::fadd('Main Webpage Link', 'cfg_mainsite', 'edit:size:50');
options::fadd('Allow Masterfeed', 'feed_allowmaster', 'checkbox');
options::fadd('Compress pages', 'cfg_compress', 'checkbox','','','Enable unless you encounter errors');

options::cat('Advanced', 'Configuration', 'Public-Mode');
options::fadd('Only Kills in SummaryTables', 'public_summarytable', 'checkbox','','','CORP_ID and ALLIANCE_ID in config has to be 0 to work "public"');
options::fadd('Remove Losses Page', 'public_losses', 'checkbox');
options::fadd('Stats Page', 'public_stats', 'select',array('admin_config', 'createSelectStats'));

options::cat('Advanced', 'Configuration', 'Corp/Alliance ID');
options::fadd('PILOT_ID', 'cfg_pilotid', 'custom', array('admin_config', 'createPilot'),array('admin_config', 'reload'));
options::fadd('CORP_ID', 'cfg_corpid', 'custom', array('admin_config', 'createCorp'),array('admin_config', 'reload'));
options::fadd('ALLIANCE_ID', 'cfg_allianceid', 'custom', array('admin_config', 'createAlliance'), array('admin_config', 'reload'));

class admin_config
{
	function checkHost()
	{
		if(!isset($_POST['option_cfg_kbhost'])) return;
		$newhost = preg_replace('/\/+$/','',$_POST['option_cfg_kbhost']);
		config::set('cfg_kbhost', $newhost);
		$_POST['option_cfg_kbhost'] = $newhost;
	}
	function checkImg()
	{
		if(!isset($_POST['option_cfg_img'])) return;
		$newimg = preg_replace('/\/+$/','',$_POST['option_cfg_img']);
		config::set('cfg_img', $newimg);
		$_POST['option_cfg_img'] = $newimg;
	}
	function createSelectStats()
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
	function createPilot()
	{
		$qry = new DBQuery();
		if(isset($_POST['option_cfg_pilotid'])) $plt_id=intval($_POST['option_cfg_pilotid']);
		else $plt_id = PILOT_ID;
		$qry->execute("SELECT plt_name FROM kb3_pilots WHERE plt_id = ".$plt_id);
		$html = '<input type="text" id="option_cfg_pilotid" name="option_cfg_pilotid" value="'.$plt_id.'" size="5" maxlength="15" />';
		if(!$qry->recordCount()) return $html;
		$res = $qry->getRow();
		return $html . ' &nbsp;('.$res['plt_name'].')';
	}
	function createCorp()
	{
		$qry = new DBQuery();
		if(isset($_POST['option_cfg_pilotid'])) $plt_id = intval($_POST['option_cfg_pilotid']);
		else $plt_id = PILOT_ID;

		if($plt_id) $crp_id = 0;
		elseif(isset($_POST['option_cfg_corpid'])) $crp_id=intval($_POST['option_cfg_corpid']);
		else $crp_id = CORP_ID;
		$qry->execute("SELECT crp_name FROM kb3_corps WHERE crp_id = ".$crp_id);
		$html = '<input type="text" id="option_cfg_corpid" name="option_cfg_corpid" value="'.$crp_id.'" size="5" maxlength="15" />';
		if(!$qry->recordCount()) return $html;
		$res = $qry->getRow();
		return $html . ' &nbsp;('.$res['crp_name'].')';
	}
	function createAlliance()
	{
		$qry = new DBQuery();
		if(isset($_POST['option_cfg_pilotid']))
		{
			$plt_id = intval($_POST['option_cfg_pilotid']);
		}
		else $plt_id = PILOT_ID;
		if(isset($_POST['option_cfg_corpid']))
		{
			$crp_id = intval($_POST['option_cfg_corpid']);
		}
		else
		{
			$crp_id = CORP_ID;
		}
		if($plt_id || $crp_id) $all_id = 0;
		elseif($_POST['option_cfg_allianceid']) $all_id=intval($_POST['option_cfg_allianceid']);
		else $all_id = ALLIANCE_ID;
		$qry->execute("SELECT all_name FROM kb3_alliances WHERE all_id = ".$all_id);
		$html = '<input type="text" id="option_cfg_allianceid" name="option_cfg_allianceid" value="'.$all_id.'" size="5" maxlength="15" />';
		if(!$qry->recordCount()) return $html;
		$res = $qry->getRow();
		return $html . ' &nbsp;('.$res['all_name'].')';
	}
	function reload()
	{
		header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
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
	function codeCheck()
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
	function dbCheck()
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
	function checkStatus()
	{
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