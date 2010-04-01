<?php
define('DGM_TECHLEVEL', 422);

class Item
{
	private $executed = false;
	private $id = 0;
	public $row_ = null;
	private $qry = null;

	//! Construct a new Item.

	/*!
	 * If $row is set then it will be used as an array of item attributes for
	 * this Item. Otherwise the attributes will be fetched from the db using
	 * the given ID.
	 *
	 * \param $id Item ID
	 * \param $row Array of attributes.
	*/
	function Item($id = 0, $row = null)
	{
		$this->id = $id;
		if(isset($row))
		{
			$this->row_ = $row;
			$this->executed = true;
		}
	}

	public function getID()
	{
		return $this->id;
	}

	public function getName()
	{
		if(!$this->row_['typeName'])$this->execQuery();
		return $this->row_['typeName'];
	}

	public function getIcon($size)
	{
		$this->execQuery();
		global $smarty;

		// cat 18 are combat drones
		if ($this->row_['itt_cat'] == 18)
		{
			$img = IMG_URL.'/drones/'.$size.'_'.$size.'/'.$this->row_['itm_externalid'].'.png';
		}
		// cat 6 are ships (destroyed in cargo)
		elseif ($this->row_['itt_cat'] == 6 || $this->row_['itt_cat'] == 23)
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
		elseif ($size == 48 || $size = 32)
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
		elseif (($this->row_['itm_techlevel'] > 10) && $d_s && (strstr($it_name,"-Type"))) // or it's just a deadspace item.
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
			) // finally if it's a faction it should have its prefix
		{
			$icon = IMG_URL.'/items/'.$size.'_'.$size.'/f'.$show_style.'.png';
		}
		else // but maybe it was only a T1 item :P
		{
			$icon = IMG_URL.'/items/'.$size.'_'.$size.'/blank.gif';
		}

		if (($size == 32 || $size == 48 || true) && ($show_style == '_backglowing'))
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

	public function getSlot()
	{
		$this->execQuery();

		// if item has no slot get the slot from parent item
		if ($this->row_['itt_slot'] == 0)
		{
			$qry = DBFactory::getDBQuery();
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

	private function execQuery()
	{
		if (!$this->executed)
		{
			if (!$this->id)return false;
			if (!isset($this->qry)) $this->qry = DBFactory::getDBQuery();

			$sql = "select inv.*, kb3_item_types.*, dga.value as techlevel, itp.price, dc.value as usedcharge, dl.value as usedlauncher
                               from kb3_invtypes inv
                               left join kb3_dgmtypeattributes dga on dga.typeID=inv.typeID and dga.attributeID=633
                               left join kb3_item_price itp on itp.typeID=inv.typeID
                               left join kb3_item_types on groupID=itt_id
                               left join kb3_dgmtypeattributes dc on dc.typeID = inv.typeID AND dc.attributeID IN (128)
                               left join kb3_dgmtypeattributes dl on dl.typeID = inv.typeID AND dl.attributeID IN (137,602)
          	                   where inv.typeID = '".$this->id."'";
			$this->qry->execute($sql);
			$this->row_ = $this->qry->getRow();
			$this->row_['itm_icon'] = $this->row_['icon'];
			$this->row_['itm_techlevel'] = $this->row_['techlevel'];
			$this->row_['itm_externalid'] = $this->row_['typeID'];
			$this->row_['itm_value'] = $this->row_['price'];
			$this->executed = true;
		}
	}

	// loads $this->id  by name
	public function lookup($name)
	{
		$name = trim($name);
		$qry = DBFactory::getDBQuery();
		$query = "select typeID as itm_id from kb3_invtypes itm
                  where typeName = '".slashfix($name)."'";
		$qry->execute($query);
		$row = $qry->getRow();
		if (!isset($row['itm_id']))
		{
			return false;
		}
		$this->id = $row['itm_id'];
		unset($this->row);
		$this->executed = false;
		return true;
	}

	// return typeID by name, dont change $this->id
	public function get_item_id($name)
	{
		$qry = DBFactory::getDBQuery();
		$query = "select typeID as itm_id
                  from kb3_invtypes
                  where typeName = '".slashfix($name)."'";
		$qry->execute($query);

		$row = $qry->getRow();
		if ($row['itm_id']) return $row['itm_id'];
	}

	public function get_used_launcher_group($name = null)
	{
		if(is_null($name) && $this->executed) return $this->row_['usedlauncher'];
		$qry  = DBFactory::getDBQuery();
		// I dont think CCP will change this attribute in near future ;-)
		$query = "SELECT value
                     FROM kb3_dgmtypeattributes d
                     INNER JOIN kb3_invtypes i ON i.typeID = d.typeID
                     WHERE i.typeName = '".slashfix($name)."' AND d.attributeID IN (137,602);";
		$qry->execute($query);
		$row = $qry->getRow();
		return $row['value'];
	}

	public function get_used_charge_size($name = null)
	{
		if(is_null($name) && $this->executed) return $this->row_['usedcharge'];
		$qry  = DBFactory::getDBQuery();
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

	public function get_ammo_size($name)
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

	public function get_group_id($name = null)
	{
		if(is_null($name) && $this->executed) return $this->row_['groupID'];
		$qry = DBFactory::getDBQuery();
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
	//! Return an attribute of this item.
	public function getAttribute($key)
	{
		if (!$this->executed) $this->execQuery();
		return $this->row_[$key];
	}
}
