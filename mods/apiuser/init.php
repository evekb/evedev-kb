<?php

event::register('mod_xajax_initialised', 'apiuser::run');

role::register('autopilot', 'Allow Acces to the Autopilot Mod');
role::register('access', 'Allow to consult the Killboard');
role::register('post_killmail', 'Allow to post Killmail');
role::register('comment', 'Allow to post Comment');

class apiuser
{
    function run()
    {
        global $xajax;
        if (isset($xajax))
        {
            if (get_class($xajax) == 'xajax')
            {
                $xajax->register(XAJAX_FUNCTION, array('editTitleName', 'apiuser', 'editTitleName'));
                $xajax->register(XAJAX_FUNCTION, array('validTitleName', 'apiuser', 'validTitleName'));

                $xajax->register(XAJAX_FUNCTION, array('editTitleDescription', 'apiuser', 'editTitleDescription'));
                $xajax->register(XAJAX_FUNCTION, array('validTitleDesc', 'apiuser', 'validTitleDesc'));

                $xajax->register(XAJAX_FUNCTION, array('editTitleRoles', 'apiuser', 'editTitleRoles'));
                $xajax->register(XAJAX_FUNCTION, array('validTitleRoles', 'apiuser', 'validTitleRoles'));

                $xajax->register(XAJAX_FUNCTION, array('modifyBanStatus', 'apiuser', 'modifyBanStatus'));
                $xajax->register(XAJAX_FUNCTION, array('showGroup', 'apiuser', 'showGroup'));
                $xajax->register(XAJAX_FUNCTION, array('editGroup', 'apiuser', 'editGroup'));
            }
        }
    }

    //Name Of Title
    function editTitleName($id)
    {
        $objResponse = new xajaxResponse();
        $qry = new DBQuery();
        $qry->execute('select ttl_name from kb3_titles  where ttl_id='.$id.' and ttl_site=\''.KB_SITE."'");
        $row = $qry->getRow();
        $objResponse->assign('titleName'.$id, 'innerHTML', '<form id="titleNameForm'.$id.'"><input  type=text value= \''.$row['ttl_name'].'\' id="titleNameChamp'.$id.'">
        <input type=submit value="Ok" onclick="xajax_validTitleName('.$id.',document.getElementById(\'titleNameChamp'.$id.'\').value);return false;"></form>');
        return $objResponse;
    }
    function validTitleName($id,$val)
    {
        $objResponse = new xajaxResponse();
        $qry = new DBQuery();
        $qry->execute("update kb3_titles set ttl_name='".$val."' where ttl_id=".$id." and ttl_site='".KB_SITE."'");
        $objResponse->assign('titleName'.$id, 'innerHTML','<div  onclick="xajax_editTitleName('.$id.')">'.$val.'</div>');
        return $objResponse;
    }

    //Description
    function editTitleDescription($id)
    {
        $objResponse = new xajaxResponse();
        $qry = new DBQuery();
        $qry->execute('select ttl_descr from kb3_titles  where ttl_id='.$id.' and ttl_site=\''.KB_SITE."'");
        $row = $qry->getRow();


        $objResponse->assign('titleDescr'.$id, 'innerHTML', '<form><input  type=text  size=60 value= \''.$row['ttl_descr'].'\' id="titleDescChamp'.$id.'">
        <input type=submit value="Ok" onclick="xajax_validTitleDesc('.$id.',document.getElementById(\'titleDescChamp'.$id.'\').value);return false;"></form>');

        return $objResponse;
    }
    function validTitleDesc($id,$val)
    {
        $objResponse = new xajaxResponse();
        $qry = new DBQuery();
        $qry->execute("update kb3_titles set ttl_descr='".$val."' where ttl_id=".$id." and ttl_site='".KB_SITE."'");
        $objResponse->assign('titleDescr'.$id, 'innerHTML','<div  onclick="xajax_editTitleName('.$id.')">'.$val.'</div>');
        return $objResponse;
    }
    function editTitleRoles($id)
    {
        $objResponse = new xajaxResponse();
        $qry = new DBQuery();

        $html='<form>';
        $qry->execute("select ttl_id,rol_name,a.rol_id,rol_descr  from kb3_roles a  left join  kb3_titles_roles b on (a.rol_id=b.rol_id and  ttl_id=".$id.") where  rol_site ='".KB_SITE."' ");
        while ($row = $qry->getRow())
        {
            if (intval($row['ttl_id'])>0)
                $html.='<input checked ';
            else
                $html.='<input ';

            $html.='type=checkbox name=chk'.$row['rol_id'].'>'.$row['rol_descr'].'<br>';
        }
        $html.='<input type=submit value="Ok" onclick="xajax_validTitleRoles('.$id.',xajax.getFormValues(this.form));return false;"></form>';
        $objResponse->assign('titleRoles'.$id, 'innerHTML',$html);
        return $objResponse;
    }
    function validTitleRoles($id,$form)
    {
        $objResponse = new xajaxResponse();
        $qry = new DBQuery();
        $qry->execute("delete from kb3_titles_roles where ttl_id='".$id."' ");
        foreach ($form as $key=>$val)
        {
            $roleID=substr($key,3,strlen($key)-1);
            $qry->execute("insert into kb3_titles_roles values(".$id.",".$roleID.')');
        }


        $qry->execute("select rol_name  from kb3_roles a,kb3_titles_roles b where a.rol_id=b.rol_id and ttl_id=".$id." order by rol_name");
        while ($row = $qry->getRow())
            $html.=$row['rol_name'].', ';
        $html=substr($html,0,strlen($html)-2);
        if (strlen($html)==0)
            $html='Nothing';
        $objResponse->assign('titleRoles'.$id, 'innerHTML','<div  onclick="xajax_editTitleRoles('.$id.')"> '.$html.'</div>');
        return $objResponse;
    }

    function affListMember()
    {
        $qry = new DBQuery();
        $sql="select  kb3_api_user.usr_id,ttl_name,sta_value,userID,charID,charName,corpName,allianceName,usr_state as ban
	from kb3_api_user
	left join kb3_corps on (crp_name=corpName) ";
        if (CORP_ID)
            $sql.='left join kb3_standings ON(sta_from='.CORP_ID.' AND sta_from_type=\'c\' and sta_to=crp_id)';
        else
            $sql.='left join kb3_standings ON(sta_from='.ALLIANCE_ID.' AND sta_from_type=\'a\' and sta_to=crp_id)';
        $sql.=' left join kb3_user u on (charName=usr_login)
        			left join kb3_user_titles on (ust_usr_id=u.usr_id)
        			left join kb3_titles on (ttl_id=ust_ttl_id)
        		';
        $sql .= 'where u.usr_site=\''.KB_SITE.'\' ';
        if (!config::get('apiuser_show3char'))
            $sql.=' and kb3_api_user.usr_id>0 ';

        if (!isset($_GET['view']) || $_GET['view']=='userID')
            $sql.="order by userID,ban desc,charName";
        else
        if (isset($_GET['view'])&&$_GET['view']=='alliance')
            $sql.="order by allianceName,userID,ban desc,charName";
        elseif (isset($_GET['view'])&&$_GET['view']=='corp')
            $sql.="order by corpName,charName,ban desc,charName";
        elseif (isset($_GET['view'])&&$_GET['view']=='pname')
            $sql.="order by charName,corpName,ban desc,charName";
        elseif (isset($_GET['view'])&&$_GET['view']=='standing')
            $sql.="order by sta_value desc,corpName,charName,ban desc,charName";
        elseif (isset($_GET['view'])&&$_GET['view']=='titles')
            $sql.="order by ttl_name desc,corpName,charName,ban desc,charName";

        $qry->execute($sql);


        $return = "<table class=kb-table cellspacing=1>";
        $return .= "<tr class=kb-table-header>
        <td><a href=?a=user_management&view=userID>userID</a></td>
        	<td><a href=?a=user_management&view=pname>CharName</a></td>
        	<td><a href=?a=user_management&view=corp>Corporation</a></td>
        	<td><a href=?a=user_management&view=alliance>Alliance</a></td>
        	<td><a href=?a=user_management&view=titles>Titles</a></td>
        	<td align=\"right\">Admin</td>
        	</tr></div>";

        while ($row = $qry->getRow())
        {
            if ($row['sta_value'] > 5)
                $icon = 'darkblue';
            elseif ($row['sta_value'] > 0)
                $icon = '#0066FF';
            elseif ($row['sta_value'] > -5)
                $icon = '#FF6633';
            elseif ($row['sta_value'] == -10)
                $icon = 'red';
            else
                $icon = '';


            if ($olduserid<>$row['userID'])
                $return .= "<tr class=kb-table-row-odd><td>".$row['userID']."&nbsp;</td>";
            else
                $return .= "<tr class=kb-table-row><td>&nbsp;</td>";
            $return.="
                    <td><a href=index.php?a=viewChar&charID=".$row['charID'].">".$row['charName']."</div></a>&nbsp;</td>
                    <td bgcolor=".$icon.">".$row['corpName']." <img src=\"./img/sta_".$icon.".png\"/></td>
                    <td >&nbsp;".$row['allianceName']."</td>

                    ";

            if (intval($row['usr_id'])>0)
            {
                if (empty($row['ttl_name']))
                {
                    $row['ttl_name'] = '[None]&nbsp;';
                }
                $return.="<td id=group".$row['userID']." style=\"border:1px solid #CC9900;\" ><div onclick=\"xajax_showGroup(".$row['userID'].")\">
                	&nbsp;".$row['ttl_name']."	</div></td>

                	<td ><div id=ban".$row['userID'].">&nbsp;";

                if ($row['ban']==1)
                    $return.="<a href=\"#\" onclick=\"xajax_modifyBanStatus(".$row['userID'].",'test');\">UnBan</a>";
                else
                    $return.="<a href=# onclick=\"xajax_modifyBanStatus(".$row['userID'].",'test');\">Ban</a>";
                $return.=" / <a href=index.php?a=user_management&delete&userID=".$row['userID'].">Delete</a></div></td></tr></div>";
            }
            else
                $return.="<td>&nbsp;</td><td>&nbsp;</td></tr>";
            $olduserid=$row['userID'];
        }
        $return .= "</table>";
        $return.=' <div id=test>Sort By : <a href=?a=user_management&view=standing>Standings</a></div>';
        return $return;
    }
    function modifyBanStatus($userID)
    {
        $qry = new DBQuery();
        $qry->execute('select usr_state,a.usr_id from kb3_user a,kb3_api_user b where b.charName=a.usr_login and b.userID='.$userID);
        $row = $qry->getRow();
        if ($row['usr_state']==0)
            $qry->execute('update kb3_user set usr_state=1 where usr_id='.$row['usr_id']);
        else
            $qry->execute('update kb3_user set usr_state=0 where usr_id='.$row['usr_id']);

        $return="&nbsp;";
        if ($row['usr_state']==0)
            $return.="<a href=\"#\" onclick=\"xajax_modifyBanStatus(".$userID.");\">UnBan</a>";
        else
            $return.="<a href=# onclick=\"xajax_modifyBanStatus(".$userID.");\">Ban</a>";
        $return.=" / <a href=index.php?a=user_management&delete&userID=".$userID.">Delete</a></div>";
        $objResponse = new xajaxResponse();
        $objResponse->assign('ban'.$userID	, 'innerHTML', $return);

        return $objResponse;
    }
    function showGroup($userID)
    {
        $qry = new DBQuery();
        $qry->execute('select *
from kb3_titles a
left join kb3_user_titles on (ttl_id=ust_ttl_id)
order by ttl_name');
        $selected = false;
        while ($row = $qry->getRow())
        {
            if (strlen($return)==0)
                $return="<select name=\"selectGroup".$userID."\" onchange=\"xajax_editGroup(".$userID.",this.value)\">";
            if (intval($row['ust_usr_id'])>0)
            {
                $return.='<option selected value="'.$row['ttl_id'].'">'.$row['ttl_name'].'</option>';
                $selected = true;
            }
            else
                $return.='<option  value="'.$row['ttl_id'].'">'.$row['ttl_name'].'</option>';

        }
        if (!$selected)
        {
            $return.='<option selected value="">None</option>';
        }
        $return.="</select>";

        $objResponse = new xajaxResponse();
        $objResponse->assign('group'.$userID, 'innerHTML', $return);
        return $objResponse;
    }
    function editGroup($userID,$newTitle)
    {
        $qry = new DBQuery();
        $qry->execute('select usr_id from kb3_api_user where userID='.$userID.' and usr_id>0');
        $row = $qry->getRow();
        $qry->execute('delete from kb3_user_titles where ust_usr_id='.$row['usr_id']);
        $qry->execute('insert into kb3_user_titles values('.$row['usr_id'].','.$newTitle.')');
        $qry->execute('select ttl_name from kb3_titles where ttl_id='.$newTitle. " and ttl_site='".KB_SITE."'");
        $row = $qry->getRow();
        $return="<div onclick=\"xajax_showGroup(".$userID.")\">".$row['ttl_name']."</div>";
        $objResponse = new xajaxResponse();
        $objResponse->assign('group'.$userID, 'innerHTML', $return);
        return $objResponse;
    }
}