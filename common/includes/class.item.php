<?php
define('DGM_TECHLEVEL', 422);

class Item
{
    function Item($id = 0)
    {
        $this->id_ = $id;
        $this->executed_ = false;
    }

    function getID()
    {
        return $this->id_;
    }

    function getName()
    {
        if(!$this->row_['typeName'])$this->execQuery();
        return $this->row_['typeName'];  
    }

    function getIcon($size)
    {
        $this->execQuery();
        global $smarty;
	
        // cat 18 are combat drones
        if ($this->row_['itt_cat'] == 18)
        {
            $img = IMG_URL.'/drones/'.$size.'_'.$size.'/'.$this->row_['itm_externalid'].'.png';
        }
        // cat 6 are ships (destroyed in cargo)
        elseif ($this->row_['itt_cat'] == 6)
        {
            $img = IMG_URL.'/ships/'.$size.'_'.$size.'/'.$this->row_['itm_externalid'].'.png';
        }
	// cat 9 are blueprints
	elseif ($this->row_['itt_cat'] == 9)
	{
		$img = IMG_URL.'/blueprints/'.$size.'_'.$size.'/'.$this->row_['itm_externalid'].'.png';
	}
	else
        {
		 
		// fix for new db structure, just make sure old clients dont break
		if (!strstr($this->row_['itm_icon'], 'icon')) 
		{
			$this->row_['itm_icon'] = 'icon'.$this->row_['itm_icon'];
		}
		$img = IMG_URL.'/items/'.$size.'_'.$size.'/'.$this->row_['itm_icon'].'.png';
        }  

        
	if ($size == 24)
	{
		$show_style .= '_'.config::get('fp_ammostyle');
		$t_s = config::get('fp_ttag');
		$f_s = config::get('fp_ftag');
		$d_s = 0;
		$o_s = 0;
	} 
	elseif ($size == 48)
	{
		$show_style .= '_'.config::get('fp_highstyle');
		$t_s = config::get('fp_ttag');
		$f_s = config::get('fp_ftag');
		$d_s = config::get('fp_dtag');
		$o_s = config::get('fp_otag');
	} 
	else
	{
		$show_style = "";
		$t_s = 1;
		$f_s = config::get('kd_ftag');
		$d_s = config::get('kd_dtag');
		$o_s = config::get('kd_otag');
	
	}
	$it_name = $this->getName();
        if (($this->row_['itm_techlevel'] == 5) && $t_s) // is a T2?
        {
            $icon .= IMG_URL.'/items/'.$size.'_'.$size.'/t2'.$show_style.'.png';
        }
        elseif (($this->row_['itm_techlevel'] > 5) && ($this->row_['itm_techlevel'] < 10) && $f_s) // is a faction item?
	{
            $icon .= IMG_URL.'/items/'.$size.'_'.$size.'/f'.$show_style.'.png';
	}
        elseif (($this->row_['itm_techlevel'] > 10) && strstr($it_name,"Modified") && $o_s) // or it's an officer?
	{
		$icon .= IMG_URL.'/items/'.$size.'_'.$size.'/o'.$show_style.'.png';
	}
	elseif (($this->row_['itm_techlevel'] > 10) && $d_s && !(strstr($it_name,"Modified"))) // or it's just a deadspace item.
	{
		$icon .= IMG_URL.'/items/'.$size.'_'.$size.'/d'.$show_style.'.png';
	}
	elseif ($f_s 
		&& (
		strstr($it_name,"Blood ")
		|| strstr($it_name,"Sansha")
		|| strstr($it_name,"Arch")
		|| strstr($it_name,"Domination")
		|| strstr($it_name,"Republic")
		|| strstr($it_name,"Navy")
		|| strstr($it_name,"Guardian")
		|| strstr($it_name,"Guristas")
		|| strstr($it_name,"Shadow")
		)
		) // finally if it's a faction should have its prefix
        {
		$icon = IMG_URL.'/items/'.$size.'_'.$size.'/f'.$show_style.'.png';
        }
	else // but maybe it was only a T1 item :P
        {
            $icon = IMG_URL.'/items/'.$size.'_'.$size.'/blank.gif';
        }

	if (($size == 48) && ($show_style == '_backglowing'))
	{
		$temp = $img;
		$img = $icon;
		$icon = $temp;
	}

        $smarty->assign('img', $img);
        $smarty->assign('icon', $icon);
        $smarty->assign('name', $it_name);
        return $smarty->fetch(get_tpl('icon'.$size));
    }

    function getSlot()
    {
        $this->execQuery();
        
    	// if item has no slot get the slot from parent item
        if ($this->row_['itt_slot'] == 0)
        {
            $qry = new DBQuery();
            $query = "select itt_slot from kb3_item_types
                        inner join kb3_dgmtypeattributes d
                        where itt_id = d.value
                        and d.typeID = ".$this->row_['typeID']."
                        and d.attributeID in (137,602);";
            $qry->execute($query);
            $row = $qry->getRow();
            
           	if (!$row['itt_slot'])
           		return 0;
           		
            return $row['itt_slot'];
        }  
        return $this->row_['itt_slot'];
    }
    
    function execQuery()
    {
        if (!$this->executed_)
        {
            if (!$this->id_)return false;
            if (!isset($this->qry_)) $this->qry_ = new DBQuery();
            
            $this->sql_ = "select inv.*, kb3_item_types.*, dga.value as techlevel, itp.price, dc.value as usedcharge, dl.value as usedlauncher
                               from kb3_invtypes inv
                               left join kb3_dgmtypeattributes dga on dga.typeID=inv.typeID and dga.attributeID=633
                               left join kb3_item_price itp on itp.typeID=inv.typeID
                               left join kb3_item_types on groupID=itt_id
                               left join kb3_dgmtypeattributes dc on dc.typeID = inv.typeID AND dc.attributeID IN (128) 
                               left join kb3_dgmtypeattributes dl on dl.typeID = inv.typeID AND dl.attributeID IN (137,602)
          	                   where inv.typeID = '".$this->id_."'";
            $this->qry_->execute($this->sql_);
            $this->row_ = $this->qry_->getRow();
            $this->row_['itm_icon'] = $this->row_['icon'];
            $this->row_['itm_techlevel'] = $this->row_['techlevel'];
            $this->row_['itm_externalid'] = $this->row_['typeID'];
            $this->row_['itm_value'] = $this->row_['price'];
            $this->executed_ = true;
        }
    }

    // loads $this->id_  by name 
    function lookup($name)
    {
        $name = trim($name);
        $qry = new DBQuery();
        $query = "select typeID as itm_id from kb3_invtypes itm
                  where typeName = '".slashfix($name)."'";
        $qry->execute($query);
        $row = $qry->getRow();
        if (!isset($row['itm_id']))
        {
        	return false;  
        }
        $this->id_ = $row['itm_id'];
		unset($this->row);
		$this->executed_ = false;
		return true;
    }
 
    // return typeID by name, dont change $this->id_
    function get_item_id($name)
    {
        $qry = new DBQuery();
        $query = "select typeID as itm_id
                  from kb3_invtypes
                  where typeName = '".slashfix($name)."'";
        $qry->execute($query);

        $row = $qry->getRow();
        if ($row['itm_id']) return $row['itm_id'];
    }
    
    function get_used_launcher_group($name = null)
    {
        if(is_null($name) && $this->qry_->executed_) return $this->row_['usedlauncher'];
        $qry  = new DBQuery();
        // I dont think CCP will change this attribute in near future ;-)
        $query = "SELECT value
             FROM kb3_dgmtypeattributes d
             INNER JOIN kb3_invtypes i ON i.typeID = d.typeID
             WHERE i.typeName = '".slashfix($name)."' AND d.attributeID IN (137,602);";
        $qry->execute($query);
        $row = $qry->getRow();
        return $row['value'];
    }

    function get_used_charge_size($name = null)
    {
        if(is_null($name) && $this->executed_) return $this->row_['usedcharge'];
        $qry  = new DBQuery();
        // I dont think CCP will change this attribute in near future ;-)
        if(is_null($name))
        $query = "SELECT value
             FROM kb3_dgmtypeattributes d
             INNER JOIN kb3_invtypes i ON i.typeID = d.typeID
             WHERE i.typeID = ".$this->row_['typeID']." AND d.attributeID IN (128);";
        else
        $query = "SELECT value
             FROM kb3_dgmtypeattributes d
             INNER JOIN kb3_invtypes i ON i.typeID = d.typeID
             WHERE i.typeName = '".slashfix($name)."' AND d.attributeID IN (128);";
        $qry->execute($query);
        $row = $qry->getRow();
        return $row['value'];
    }

    function get_ammo_size($name)
    {
	$temp = substr($name, strlen($name) - 2, 2);
	if (strstr($name,'Mining'))
	{
		$a_size = 1;
	}
	elseif ($temp == 'XL')
	{
		$a_size = 4;
	}
	elseif ($temp == ' L')
	{
		$a_size = 3;
	}
	elseif ($temp == ' M')
	{
		$a_size = 2;
	}
	elseif ($temp == ' S')
	{
		$a_size = 1;
	}
	else
	{
		$a_size = 0;

	}
	return $a_size;
    }	 
    
    function get_group_id($name = null)
    {
        if(is_null($name) && $this->executed_) return $this->row_['groupID'];
        $qry = new DBQuery();
        if(is_null($name))
        $query = "select groupID
                        from kb3_invtypes
                        where typeName = ".$this->row_['typeID'];
        else
        $query = "select groupID
                        from kb3_invtypes
                        where typeName = '".slashfix($name)."'";
        $qry->execute($query);

        $row = $qry->getRow();
        if ($row['groupID']) return $row['groupID'];
    }
}
?>
