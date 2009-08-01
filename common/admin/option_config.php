<?php
options::cat('Advanced', 'Configuration', 'Killboard Configuration');
options::fadd('Display profiling information', 'cfg_profile', 'checkbox');
options::fadd('KB_TITLE', 'cfg_kbtitle', 'edit:size:50');
options::fadd('KB_HOST', 'cfg_kbhost', 'edit:size:50');
options::fadd('Style URL', 'cfg_style', 'edit:size:50');
options::fadd('Common URL', 'cfg_common', 'edit:size:50');
options::fadd('IMG URL', 'cfg_img', 'edit:size:50');
options::fadd('Main Webpage Link', 'cfg_mainsite', 'edit:size:50');
options::fadd('Allow Masterfeed', 'feed_allowmaster', 'checkbox');

options::cat('Advanced', 'Configuration', 'Public-Mode');
options::fadd('Only Kills in SummaryTables', 'public_summarytable', 'checkbox','','','CORP_ID and ALLIANCE_ID in config has to be 0 to work "public"');
options::fadd('Remove Losses Page', 'public_losses', 'checkbox');
options::fadd('Stats Page', 'public_stats', 'select',array('admin_config', 'createSelectStats'));

options::cat('Advanced', 'Configuration', 'Corp/Alliance ID');
options::fadd('CORP_ID', 'cfg_corpid', 'custom', array('admin_config', 'createCorp'));
options::fadd('ALLIANCE_ID', 'cfg_allianceid', 'custom', array('admin_config', 'createAlliance'));

class admin_config
{
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
		if(isset($_POST['option']['cfg_corpid'])) $crp_id=intval($_POST['option']['cfg_corpid']);
		else $crp_id = CORP_ID;
		$qry->execute("SELECT crp_name FROM kb3_corps WHERE crp_id = ".$crp_id);
		$html = '<input type="edit" id="option[cfg_corpid]" name="option[cfg_corpid]" value="'.$crp_id.'" size="5" maxlength="15">';
		if(!$qry->recordCount()) return $html;
		$res = $qry->getRow();
		return $html . ' &nbsp;('.$res['crp_name'].')';
	}
	function createAlliance()
	{
		$qry = new DBQuery();
		if(isset($_POST['option']['cfg_corpid'])) $crp_id=intval($_POST['option']['cfg_corpid']);
		else $crp_id = CORP_ID;
		if($crp_id) $all_id=0;
		elseif($_POST['option']['cfg_allianceid']) $all_id=intval($_POST['option']['cfg_allianceid']);
		else $all_id = ALLIANCE_ID;
		$qry->execute("SELECT all_name FROM kb3_alliances WHERE all_id = ".$all_id);
		$html = '<input type="edit" id="option[cfg_allianceid]" name="option[cfg_allianceid]" value="'.$all_id.'" size="5" maxlength="15">';
		if(!$qry->recordCount()) return $html;
		$res = $qry->getRow();
		return $html . ' &nbsp;('.$res['all_name'].')';
	}
}
?>
