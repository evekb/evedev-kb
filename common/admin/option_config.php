<?php
options::cat('Advanced', 'Configuration', 'Killboard Configuration');
options::fadd('Display profiling information', 'cfg_profile', 'checkbox');
options::fadd('Killboard Title', 'cfg_kbtitle', 'edit:size:50');
options::fadd('Killboard Host', 'cfg_kbhost', 'edit:size:50','', array('admin_config', 'checkHost'));
options::fadd('Image base URL', 'cfg_img', 'edit:size:50','', array('admin_config', 'checkImg'));
options::fadd('Main Webpage Link', 'cfg_mainsite', 'edit:size:50');
options::fadd('Allow Masterfeed', 'feed_allowmaster', 'checkbox');

options::cat('Advanced', 'Configuration', 'Public-Mode');
options::fadd('Only Kills in SummaryTables', 'public_summarytable', 'checkbox','','','CORP_ID and ALLIANCE_ID in config has to be 0 to work "public"');
options::fadd('Remove Losses Page', 'public_losses', 'checkbox');
options::fadd('Stats Page', 'public_stats', 'select',array('admin_config', 'createSelectStats'));

options::cat('Advanced', 'Configuration', 'Corp/Alliance ID');
options::fadd('CORP_ID', 'cfg_corpid', 'custom', array('admin_config', 'createCorp'),array('admin_config', 'reload'));
options::fadd('ALLIANCE_ID', 'cfg_allianceid', 'custom', array('admin_config', 'createAlliance'), array('admin_config', 'reload'));

class admin_config
{
	function checkHost()
	{
		if(!isset($_REQUEST['option_cfg_kbhost'])) return;
		$newhost = preg_replace('/\/+$/','',$_REQUEST['option_cfg_kbhost']);
		config::set('cfg_kbhost', $newhost);
		$_REQUEST['option_cfg_kbhost'] = $newhost;
	}
	function checkImg()
	{
		if(!isset($_REQUEST['option_cfg_img'])) return;
		$newimg = preg_replace('/\/+$/','',$_REQUEST['option_cfg_img']);
		config::set('cfg_img', $newimg);
		$_REQUEST['option_cfg_img'] = $newimg;
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
	function createCorp()
	{
		$qry = new DBQuery();
		if(isset($_POST['option_cfg_corpid'])) $crp_id=intval($_POST['option_cfg_corpid']);
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
		if(isset($_POST['option_cfg_corpid'])) $crp_id=intval($_POST['option_cfg_corpid']);
		else $crp_id = CORP_ID;
		if($crp_id) $all_id=0;
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
?>
